<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class MuwajjahModel extends Model {
    protected $table = 'muwajjah_absensi';

    protected function getActiveYearId() {
        $year = $this->db->query("SELECT id FROM academic_years WHERE is_active = 1 LIMIT 1")->fetch();
        return $year ? (int)$year['id'] : 0;
    }

    /**
     * Check if a specific date is Thursday Night or Friday Night (Libur Rutin)
     * Thursday = 4, Friday = 5
     */
    public function isRoutineHoliday($dateStr) {
        $dayOfWeek = (int)date('N', strtotime($dateStr)); // 1=Mon, 4=Thu, 5=Fri, 7=Sun
        return ($dayOfWeek === 4 || $dayOfWeek === 5);
    }

    /**
     * Check if user is on Piket Muwajjah duty for given day
     */
    public function isUserPiketMuwajjah($userId, $dateStr) {
        $dayNameMap = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Ahad'
        ];
        $dayNum = (int)date('N', strtotime($dateStr));
        $dayName = $dayNameMap[$dayNum] ?? '';

        $yearId = $this->getActiveYearId();
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM piket_schedule WHERE user_id = ? AND day = ? AND type = 'muwajjah' AND academic_year_id = ?");
        $stmt->execute([$userId, $dayName, $yearId]);
        return ($stmt->fetchColumn() > 0);
    }

    /**
     * Get all active classes with their assigned Wali Kelas directly from `kelas` table (teacher_id & teacher_id_2)
     */
    public function getClassesWithWaliKelas() {
        $yearId = $this->getActiveYearId();

        $stmt = $this->db->prepare("
            SELECT k.id as kelas_id, k.tingkat, k.abjad, k.gender, k.location,
                   k.teacher_id, k.teacher_id_2,
                   u1.nama as wali1_name, tp1.gender as wali1_gender,
                   u2.nama as wali2_name, tp2.gender as wali2_gender
            FROM kelas k
            LEFT JOIN users u1 ON k.teacher_id = u1.id AND u1.deleted_at IS NULL
            LEFT JOIN teacher_profiles tp1 ON u1.id = tp1.user_id
            LEFT JOIN users u2 ON k.teacher_id_2 = u2.id AND u2.deleted_at IS NULL
            LEFT JOIN teacher_profiles tp2 ON u2.id = tp2.user_id
            WHERE k.academic_year_id = ?
            ORDER BY k.tingkat ASC, k.abjad ASC
        ");
        $stmt->execute([$yearId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $classes = [];
        foreach ($rows as $r) {
            $waliKelas = [];
            
            // Wali Kelas 1
            if (!empty($r['teacher_id']) && !empty($r['wali1_name'])) {
                $prefix1 = ($r['wali1_gender'] === 'Perempuan') ? 'Al-Ustadzah ' : 'Al-Ustadz ';
                $formatted1 = (strpos($r['wali1_name'], 'Ustadz') === false) ? $prefix1 . $r['wali1_name'] : $r['wali1_name'];
                $waliKelas[] = [
                    'teacher_id' => (int)$r['teacher_id'],
                    'teacher_name' => $r['wali1_name'],
                    'gender' => $r['wali1_gender'],
                    'formatted_nama' => $formatted1
                ];
            }

            // Wali Kelas 2
            if (!empty($r['teacher_id_2']) && !empty($r['wali2_name'])) {
                $prefix2 = ($r['wali2_gender'] === 'Perempuan') ? 'Al-Ustadzah ' : 'Al-Ustadz ';
                $formatted2 = (strpos($r['wali2_name'], 'Ustadz') === false) ? $prefix2 . $r['wali2_name'] : $r['wali2_name'];
                $waliKelas[] = [
                    'teacher_id' => (int)$r['teacher_id_2'],
                    'teacher_name' => $r['wali2_name'],
                    'gender' => $r['wali2_gender'],
                    'formatted_nama' => $formatted2
                ];
            }

            $classes[] = [
                'kelas_id' => $r['kelas_id'],
                'id' => $r['kelas_id'],
                'tingkat' => $r['tingkat'],
                'abjad' => $r['abjad'],
                'gender' => $r['gender'],
                'location' => $r['location'],
                'wali_kelas' => $waliKelas
            ];
        }

        return $classes;
    }

    /**
     * Get absensi record for a specific date
     */
    public function getAbsensiByDate($dateStr) {
        $yearId = $this->getActiveYearId();
        $stmt = $this->db->prepare("
            SELECT * FROM muwajjah_absensi
            WHERE tanggal = ? AND academic_year_id = ?
        ");
        $stmt->execute([$dateStr, $yearId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $map = [];
        foreach ($rows as $r) {
            $key = $r['kelas_id'] . '_' . $r['teacher_id'];
            $map[$key] = $r;
        }
        return $map;
    }

    /**
     * Save/Update batch absensi for a date
     */
    public function saveAbsensiBatch($dateStr, $attendanceData, $recordedBy) {
        $yearId = $this->getActiveYearId();
        
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                INSERT INTO muwajjah_absensi (tanggal, kelas_id, teacher_id, status, pengganti_id, catatan, recorded_by, academic_year_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    status = VALUES(status),
                    pengganti_id = VALUES(pengganti_id),
                    catatan = VALUES(catatan),
                    recorded_by = VALUES(recorded_by),
                    updated_at = CURRENT_TIMESTAMP
            ");

            $stmtDelete = $this->db->prepare("
                DELETE FROM muwajjah_absensi
                WHERE tanggal = ? AND kelas_id = ? AND teacher_id = ? AND academic_year_id = ?
            ");

            foreach ($attendanceData as $item) {
                $kelasId = (int)$item['kelas_id'];
                $teacherId = (int)$item['teacher_id'];
                $status = $item['status'] ?? 'hadir';

                if ($status === 'delete') {
                    $stmtDelete->execute([$dateStr, $kelasId, $teacherId, $yearId]);
                    continue;
                }

                if ($status === 'diganti') {
                    $status = 'badal';
                }
                $penggantiId = !empty($item['pengganti_id']) ? (int)$item['pengganti_id'] : null;
                $catatan = !empty($item['catatan']) ? trim($item['catatan']) : null;

                $stmt->execute([
                    $dateStr,
                    $kelasId,
                    $teacherId,
                    $status,
                    $penggantiId,
                    $catatan,
                    $recordedBy,
                    $yearId
                ]);
            }
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Get Report Statistics with Dynamic Sorting
     * @param string|null $startDate
     * @param string|null $endDate
     * @param string $sortBy 'kelas' (default) or 'rate' (% Kehadiran)
     */
    public function getComplianceReport($startDate = null, $endDate = null, $sortBy = 'kelas') {
        $yearId = $this->getActiveYearId();

        if (empty($startDate)) $startDate = date('Y-m-01');
        if (empty($endDate)) $endDate = date('Y-m-t');

        // 1. Identify Effective Muwajjah Dates in the range
        $stmtDates = $this->db->prepare("
            SELECT DISTINCT tanggal 
            FROM muwajjah_absensi 
            WHERE academic_year_id = ? AND tanggal BETWEEN ? AND ?
        ");
        $stmtDates->execute([$yearId, $startDate, $endDate]);
        $allEnteredDates = $stmtDates->fetchAll(PDO::FETCH_COLUMN);

        $effectiveDates = [];
        foreach ($allEnteredDates as $d) {
            if (!$this->isRoutineHoliday($d)) {
                $effectiveDates[] = $d;
            }
        }

        $totalEffectiveDays = count($effectiveDates);

        // 2. Fetch all Wali Kelas mapped for this academic year from `kelas` ordered naturally by tingkat & abjad
        $stmtClasses = $this->db->prepare("
            SELECT k.id as kelas_id, k.tingkat, k.abjad, k.teacher_id, k.teacher_id_2
            FROM kelas k
            WHERE k.academic_year_id = ?
            ORDER BY k.tingkat ASC, k.abjad ASC
        ");
        $stmtClasses->execute([$yearId]);
        $classList = $stmtClasses->fetchAll(PDO::FETCH_ASSOC);

        $teacherClassesMap = [];
        $teacherIds = [];

        foreach ($classList as $c) {
            $cName = $c['tingkat'] . '-' . $c['abjad'];
            if (!empty($c['teacher_id'])) {
                $t1 = (int)$c['teacher_id'];
                $teacherIds[] = $t1;
                $teacherClassesMap[$t1][] = $cName;
            }
            if (!empty($c['teacher_id_2'])) {
                $t2 = (int)$c['teacher_id_2'];
                $teacherIds[] = $t2;
                $teacherClassesMap[$t2][] = $cName;
            }
        }

        $teacherIds = array_values(array_unique($teacherIds));

        if (empty($teacherIds)) {
            return [
                'effective_days' => $totalEffectiveDays,
                'effective_dates_list' => $effectiveDates,
                'wali_stats' => []
            ];
        }

        // Fetch User Info for these Wali Kelas
        $inTeacherClause = implode(',', array_fill(0, count($teacherIds), '?'));
        $stmtUsers = $this->db->prepare("
            SELECT u.id as teacher_id, u.nama as teacher_name, tp.gender
            FROM users u
            LEFT JOIN teacher_profiles tp ON u.id = tp.user_id
            WHERE u.id IN ($inTeacherClause) AND u.deleted_at IS NULL
        ");
        $stmtUsers->execute($teacherIds);
        $waliList = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

        foreach ($waliList as &$w) {
            $tId = $w['teacher_id'];
            $classesAssigned = array_values(array_unique($teacherClassesMap[$tId] ?? []));
            $w['class_names'] = implode(', ', $classesAssigned);
            $w['first_class'] = $classesAssigned[0] ?? 'ZZZ';
        }
        unset($w);

        if (empty($effectiveDates)) {
            foreach ($waliList as &$w) {
                $w['total_effective_days'] = 0;
                $w['hadir'] = 0;
                $w['badal'] = 0;
                $w['izin'] = 0;
                $w['alfa'] = 0;
                $w['compliance_rate'] = 0;
            }
        } else {
            // 3. Fetch all attendance records for these effective dates
            $inDatesClause = implode(',', array_fill(0, count($effectiveDates), '?'));
            $params = array_merge([$yearId], $effectiveDates);

            $stmtAtt = $this->db->prepare("
                SELECT teacher_id, status, COUNT(*) as count_status
                FROM muwajjah_absensi
                WHERE academic_year_id = ? AND tanggal IN ($inDatesClause)
                GROUP BY teacher_id, status
            ");
            $stmtAtt->execute($params);
            $attRows = $stmtAtt->fetchAll(PDO::FETCH_ASSOC);

            $statsMap = [];
            foreach ($attRows as $r) {
                $statsMap[$r['teacher_id']][$r['status']] = (int)$r['count_status'];
            }

            foreach ($waliList as &$w) {
                $tId = $w['teacher_id'];
                $hadirRaw = $statsMap[$tId]['hadir'] ?? 0;
                $terlambatRaw = $statsMap[$tId]['terlambat'] ?? 0;
                $badalRaw = ($statsMap[$tId]['badal'] ?? 0) + ($statsMap[$tId]['diganti'] ?? 0);
                $izinRaw = $statsMap[$tId]['izin'] ?? 0;
                $alfaRaw = $statsMap[$tId]['alfa'] ?? 0;

                $hadirCount = $hadirRaw + $terlambatRaw + $badalRaw;
                $izinCount = $izinRaw;

                $totalRecorded = $hadirRaw + $terlambatRaw + $badalRaw + $izinRaw + $alfaRaw;
                $missingDays = max(0, $totalEffectiveDays - $totalRecorded);
                $tidakHadirCount = $alfaRaw + $missingDays;

                $complianceRate = ($totalEffectiveDays > 0) ? round(($hadirCount / $totalEffectiveDays) * 100, 1) : 0;

                $w['total_effective_days'] = $totalEffectiveDays;
                $w['hadir'] = $hadirCount;
                $w['badal'] = $badalRaw;
                $w['izin'] = $izinCount;
                $w['alfa'] = $tidakHadirCount;
                $w['compliance_rate'] = min(100, $complianceRate);
            }
            unset($w);
        }

        // Apply Sorting based on $sortBy parameter
        if ($sortBy === 'rate') {
            usort($waliList, function($a, $b) {
                if ($b['compliance_rate'] == $a['compliance_rate']) {
                    return strnatcmp($a['first_class'], $b['first_class']);
                }
                return $b['compliance_rate'] <=> $a['compliance_rate'];
            });
        } else {
            // Default sort by Kelas natural order
            usort($waliList, function($a, $b) {
                return strnatcmp($a['first_class'], $b['first_class']);
            });
        }

        return [
            'effective_days' => $totalEffectiveDays,
            'effective_dates_list' => $effectiveDates,
            'wali_stats' => $waliList
        ];
    }
}
