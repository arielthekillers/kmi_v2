<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class TanqihModel extends Model {
    protected $table = 'tanqih';

    public function getVerificationsByDate($date) {
        $stmt = $this->db->prepare("
            SELECT t.*, u.nama as verifier_name 
            FROM {$this->table} t 
            LEFT JOIN users u ON t.verifier_id = u.id 
            WHERE date = ? AND academic_year_id = ?
        ");
        $stmt->execute([$date, $this->academic_year_id]);
        
        $logs = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $logs[$row['kelas_id'] . '|' . $row['hour']] = $row;
        }
        return $logs;
    }

    public function verify($date, $kelasId, $hour, $verifierId, $status = 'verified') {
        $stmt = $this->db->prepare("
            INSERT INTO {$this->table} (date, kelas_id, hour, verifier_id, status, academic_year_id, verified_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE 
            status = VALUES(status), verifier_id = VALUES(verifier_id), verified_at = NOW()
        ");
        return $stmt->execute([$date, $kelasId, $hour, $verifierId, $status, $this->academic_year_id]);
    }

    public function unverify($date, $kelasId, $hour) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE date = ? AND kelas_id = ? AND hour = ? AND academic_year_id = ?");
        return $stmt->execute([$date, $kelasId, $hour, $this->academic_year_id]);
    }

    /**
     * Get aggregated report data
     */
    public function getReportStats($startDate, $endDate, $academicYearId = null) {
        $ayId = $academicYearId ?? $this->academic_year_id;
        
        $schedules = $this->getAllSchedules($ayId);
        $verifications = $this->getVerificationsInRange($startDate, $endDate, $ayId);
        
        $activityModel = new \App\Models\ActivityModel();
        $dispensationsByDate = $activityModel->getDispensationsByRange($startDate, $endDate, $ayId);

        // Fetch academic calendar events categorized as Libur (Holiday)
        $stmtCal = $this->db->prepare("
            SELECT tanggal_mulai, tanggal_selesai 
            FROM academic_calendar_events 
            WHERE academic_year_id = ? 
              AND kategori = 'Libur'
              AND (tanggal_mulai <= ? AND (tanggal_selesai >= ? OR tanggal_selesai IS NULL))
        ");
        $stmtCal->execute([$ayId, $endDate, $startDate]);
        $calendarHolidays = $stmtCal->fetchAll(PDO::FETCH_ASSOC);

        $holidays = [];
        foreach ($calendarHolidays as $cal) {
            $s = $cal['tanggal_mulai'];
            $e = !empty($cal['tanggal_selesai']) ? $cal['tanggal_selesai'] : $cal['tanggal_mulai'];
            $cur = $s;
            while ($cur <= $e) {
                $holidays[$cur] = true;
                $cur = date('Y-m-d', strtotime($cur . ' +1 day'));
            }
        }

        $report = [];
        $globalStats = [
            'total_jadwal' => 0,
            'total_asistensi' => 0,
            'total_verified' => 0,
            'total_justified' => 0,
            'total_belum' => 0
        ];

        $currentDate = $startDate;
        $dayMap = [
            'Sun' => 'Ahad', 'Mon' => 'Senin', 'Tue' => 'Selasa', 
            'Wed' => 'Rabu', 'Thu' => 'Kamis', 'Fri' => 'Jumat', 'Sat' => 'Sabtu'
        ];

        while (strtotime($currentDate) <= strtotime($endDate)) {
            $dayNameEnglish = date('D', strtotime($currentDate));
            $dayNameVideo = $dayMap[$dayNameEnglish] ?? '';
            $dayOfWeek = date('w', strtotime($currentDate));
            $isFriday = ($dayOfWeek == 5);
            $isHoliday = isset($holidays[$currentDate]);
            $dayDispensations = $dispensationsByDate[$currentDate] ?? [];

            // Get schedules for this day
            $daySchedules = $schedules[$dayNameVideo] ?? [];

            foreach ($daySchedules as $slot) {
                $pid = $slot['teacher_id'];
                if (!$pid) continue;

                // Check dispensation / holiday
                $isDisp = false;
                if ($isFriday || $isHoliday) {
                    $isDisp = true;
                } else {
                    foreach ($dayDispensations as $disp) {
                        if (in_array((int)$slot['kelas_id'], $disp['kelas_ids'])) {
                            if ($disp['is_full_day']) {
                                $isDisp = true;
                                break;
                            } else {
                                foreach ($disp['hours'] as $h) {
                                    if ((int)$slot['hour'] >= (int)$h['hour_start'] && (int)$slot['hour'] <= (int)$h['hour_end']) {
                                        $isDisp = true;
                                        break 2;
                                    }
                                }
                            }
                        }
                    }
                }

                if ($isDisp) {
                    continue;
                }

                if (!isset($report[$pid])) {
                    $report[$pid] = [
                        'name' => $slot['teacher_nama'] ?? 'Unknown',
                        'expected' => 0,
                        'verified_all' => 0,
                        'verified_real' => 0,
                        'justified' => 0
                    ];
                }

                $report[$pid]['expected']++;
                $globalStats['total_jadwal']++;

                // Check verification
                $key = $currentDate . '|' . $slot['kelas_id'] . '|' . $slot['hour'];
                $status = $verifications[$key]['status'] ?? 'missing';

                if ($status === 'verified') {
                    $report[$pid]['verified_all']++;
                    $report[$pid]['verified_real']++;
                    $globalStats['total_asistensi']++;
                    $globalStats['total_verified']++;
                } elseif ($status === 'justified') {
                    $report[$pid]['verified_all']++;
                    $report[$pid]['justified']++;
                    $globalStats['total_asistensi']++;
                    $globalStats['total_justified']++;
                } else {
                    $globalStats['total_belum']++;
                }
            }
            
            $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
        }

        // Sort by compliance percentage desc (only verified, not justified)
        uasort($report, function($a, $b) {
            $pctA = $a['expected'] > 0 ? ($a['verified_real'] / $a['expected']) : 0;
            $pctB = $b['expected'] > 0 ? ($b['verified_real'] / $b['expected']) : 0;
            if ($pctA === $pctB) return strcasecmp($a['name'], $b['name']);
            return $pctB <=> $pctA;
        });

        return ['report' => $report, 'globalStats' => $globalStats];
    }

    private function getAllSchedules($academicYearId) {
        $sql = "SELECT s.*, u.nama as teacher_nama 
                FROM schedules s 
                LEFT JOIN users u ON s.teacher_id = u.id
                WHERE s.academic_year_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$academicYearId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row['day']][] = $row;
        }
        return $grouped;
    }

    private function getVerificationsInRange($startDate, $endDate, $academicYearId) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE (date BETWEEN ? AND ?) AND academic_year_id = ?");
        $stmt->execute([$startDate, $endDate, $academicYearId]);
        
        $data = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $key = $row['date'] . '|' . $row['kelas_id'] . '|' . $row['hour'];
            $data[$key] = $row;
        }
        return $data;
    }
}
