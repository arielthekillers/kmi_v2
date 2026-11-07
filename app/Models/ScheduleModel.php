<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class ScheduleModel extends Model {
    protected $table = 'schedules';

    public function getByClass($classId) {
        $stmt = $this->db->prepare("
            SELECT s.* 
            FROM schedules s
            INNER JOIN (
                SELECT MAX(id) as max_id 
                FROM schedules 
                WHERE kelas_id = ? AND academic_year_id = ? 
                GROUP BY day, hour
            ) latest ON s.id = latest.max_id
        ");
        $stmt->execute([$classId, $this->academic_year_id]);
        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[$row['day']][$row['hour']] = [
                'mapel' => $row['subject_id'],
                'pengajar' => $row['teacher_id']
            ];
        }
        return $result;
    }

    public function getByTeacher($teacherId) {
        $stmt = $this->db->prepare("
            SELECT s.* 
            FROM schedules s
            INNER JOIN (
                SELECT MAX(id) as max_id 
                FROM schedules 
                WHERE academic_year_id = ? 
                GROUP BY kelas_id, day, hour
            ) latest ON s.id = latest.max_id
            WHERE s.teacher_id = ?
        ");
        $stmt->execute([$this->academic_year_id, $teacherId]);
        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[$row['day']][$row['hour']] = [
                'mapel' => $row['subject_id'],
                'kelas' => $row['kelas_id']
            ];
        }
        return $result;
    }

    /**
     * Get primary/first schedule entry for each slot in a class
     */
    public function getPrimaryScheduleByClass($classId) {
        $stmt = $this->db->prepare("
            SELECT s.* 
            FROM schedules s
            INNER JOIN (
                SELECT MIN(id) as min_id 
                FROM schedules 
                WHERE kelas_id = ? AND academic_year_id = ? 
                GROUP BY day, hour
            ) primary_slot ON s.id = primary_slot.min_id
        ");
        $stmt->execute([$classId, $this->academic_year_id]);
        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[$row['day']][$row['hour']] = [
                'id' => $row['id'],
                'mapel' => $row['subject_id'],
                'pengajar' => $row['teacher_id']
            ];
        }
        return $result;
    }

    /**
     * Get replacement (2nd / latest) schedule entry for each slot in a class that has more than 1 entry
     */
    public function getReplacementScheduleByClass($classId) {
        $stmt = $this->db->prepare("
            SELECT s.* 
            FROM schedules s
            INNER JOIN (
                SELECT MAX(id) as max_id, COUNT(*) as cnt 
                FROM schedules 
                WHERE kelas_id = ? AND academic_year_id = ? 
                GROUP BY day, hour
                HAVING cnt > 1
            ) latest ON s.id = latest.max_id
        ");
        $stmt->execute([$classId, $this->academic_year_id]);
        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[$row['day']][$row['hour']] = [
                'id' => $row['id'],
                'mapel' => $row['subject_id'],
                'pengajar' => $row['teacher_id']
            ];
        }
        return $result;
    }

    /**
     * Edit Jadwal Utama (Hard Edit / Fix Typo):
     * Updates existing latest records without creating new history, or inserts initial records if slot is empty.
     */
    public function updateBatchUtama($classId, $scheduleData) {
        try {
            $this->db->beginTransaction();

            $primarySchedule = $this->getPrimaryScheduleByClass($classId);

            $stmtUpdate = $this->db->prepare("UPDATE schedules SET subject_id = ?, teacher_id = ? WHERE id = ?");
            $stmtInsert = $this->db->prepare("INSERT INTO schedules (kelas_id, day, hour, subject_id, teacher_id, academic_year_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtDelete = $this->db->prepare("DELETE FROM schedules WHERE id = ?");

            foreach ($scheduleData as $day => $hours) {
                foreach ($hours as $hour => $slot) {
                    $mapelId = !empty($slot['mapel']) ? (int)$slot['mapel'] : null;
                    $pengajarId = !empty($slot['pengajar']) ? (int)$slot['pengajar'] : null;

                    $existing = $primarySchedule[$day][$hour] ?? null;

                    if (!empty($mapelId) && !empty($pengajarId)) {
                        if ($existing) {
                            $stmtUpdate->execute([$mapelId, $pengajarId, $existing['id']]);
                        } else {
                            $stmtInsert->execute([$classId, $day, $hour, $mapelId, $pengajarId, $this->academic_year_id]);
                        }
                    } else {
                        // Empty slot submitted -> if existing primary entry present, remove it
                        if ($existing) {
                            $stmtDelete->execute([$existing['id']]);
                        }
                    }
                }
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Add Jadwal Pengganti (Pergantian Resmi):
     * Inserts new history entries for slots that have changed.
     */
    public function updateBatchPengganti($classId, $scheduleData) {
        try {
            $this->db->beginTransaction();

            $currentActive = $this->getByClass($classId);

            $stmtInsert = $this->db->prepare("INSERT INTO schedules (kelas_id, day, hour, subject_id, teacher_id, academic_year_id) VALUES (?, ?, ?, ?, ?, ?)");

            foreach ($scheduleData as $day => $hours) {
                foreach ($hours as $hour => $slot) {
                    $mapelId = !empty($slot['mapel']) ? (int)$slot['mapel'] : null;
                    $pengajarId = !empty($slot['pengajar']) ? (int)$slot['pengajar'] : null;

                    $activeSlot = $currentActive[$day][$hour] ?? null;
                    $activeMapel = $activeSlot['mapel'] ?? null;
                    $activePengajar = $activeSlot['pengajar'] ?? null;

                    if (!empty($mapelId) && !empty($pengajarId)) {
                        // Insert replacement entry if different from currently active slot
                        if ($activeMapel != $mapelId || $activePengajar != $pengajarId) {
                            $stmtInsert->execute([$classId, $day, $hour, $mapelId, $pengajarId, $this->academic_year_id]);
                        }
                    }
                }
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function getAllAssignments($academicYearId) {
        $stmt = $this->db->prepare("
            SELECT DISTINCT 
                s.kelas_id, 
                s.subject_id, 
                sub.nama as subject_name,
                s.teacher_id, 
                u.nama as teacher_name
            FROM schedules s
            INNER JOIN (
                SELECT MAX(id) as max_id
                FROM schedules
                WHERE academic_year_id = ?
                GROUP BY kelas_id, day, hour
            ) latest ON s.id = latest.max_id
            JOIN subjects sub ON s.subject_id = sub.id
            JOIN users u ON s.teacher_id = u.id
            ORDER BY sub.nama ASC
        ");
        $stmt->execute([$academicYearId]);
        
        $map = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $map[$row['kelas_id']][] = [
                'subject_id' => $row['subject_id'],
                'subject_name' => $row['subject_name'],
                'teacher_id' => $row['teacher_id'],
                'teacher_name' => $row['teacher_name']
            ];
        }
        return $map;
    }

    /**
     * Get all teachers with their teaching schedule summary and weekly timetable grid
     */
    public function getAllTeachersWithSchedule($academicYearId = null, $selectedTeacherId = null) {
        $yearId = $academicYearId ?: $this->academic_year_id;

        // 1. Fetch teachers
        $teacherParams = [];
        $teacherSql = "
            SELECT u.id, u.nama, u.username, u.is_active,
                   tp.nip, tp.gender, tp.phone as hp
            FROM users u
            LEFT JOIN teacher_profiles tp ON u.id = tp.user_id
            WHERE u.role IN ('pengajar', 'admin') 
              AND u.deleted_at IS NULL
              AND (u.is_active = 1 OR u.is_active IS NULL)
        ";
        if ($selectedTeacherId) {
            $teacherSql .= " AND u.id = ? ";
            $teacherParams[] = $selectedTeacherId;
        }
        $teacherSql .= " ORDER BY u.nama ASC";

        $stmtTeachers = $this->db->prepare($teacherSql);
        $stmtTeachers->execute($teacherParams);
        $teachers = $stmtTeachers->fetchAll(PDO::FETCH_ASSOC);

        // 2. Fetch all active schedules for the academic year
        $schedParams = [$yearId];
        $schedSql = "
            SELECT s.id, s.teacher_id, s.day, s.hour, s.subject_id, s.kelas_id,
                   sub.nama as subject_name,
                   k.tingkat, k.abjad
            FROM schedules s
            INNER JOIN (
                SELECT MAX(id) as max_id 
                FROM schedules 
                WHERE academic_year_id = ? 
                GROUP BY kelas_id, day, hour
            ) latest ON s.id = latest.max_id
            JOIN subjects sub ON s.subject_id = sub.id
            JOIN kelas k ON s.kelas_id = k.id
            WHERE s.teacher_id IS NOT NULL AND s.teacher_id > 0
        ";
        if ($selectedTeacherId) {
            $schedSql .= " AND s.teacher_id = ? ";
            $schedParams[] = $selectedTeacherId;
        }
        $schedSql .= " ORDER BY FIELD(s.day, 'Sabtu', 'Ahad', 'Senin', 'Selasa', 'Rabu', 'Kamis'), s.hour ASC";

        $stmtSchedules = $this->db->prepare($schedSql);
        $stmtSchedules->execute($schedParams);
        $schedules = $stmtSchedules->fetchAll(PDO::FETCH_ASSOC);

        // Index schedules by teacher_id
        $teacherSchedules = [];
        foreach ($schedules as $sc) {
            $teacherSchedules[$sc['teacher_id']][] = $sc;
        }

        $days = ['Sabtu', 'Ahad', 'Senin', 'Selasa', 'Rabu', 'Kamis'];
        $result = [];

        foreach ($teachers as $t) {
            $tId = $t['id'];
            $items = $teacherSchedules[$tId] ?? [];

            $teachingSummary = [];
            $grid = [];
            $daysActive = [];

            foreach ($days as $d) {
                for ($h = 1; $h <= 7; $h++) {
                    $grid[$d][$h] = null;
                }
            }

            foreach ($items as $item) {
                $subName = $item['subject_name'];
                $className = $item['tingkat'] . '-' . $item['abjad'];
                $key = $item['subject_id'] . '_' . $item['kelas_id'];

                if (!isset($teachingSummary[$key])) {
                    $teachingSummary[$key] = [
                        'subject_name' => $subName,
                        'class_name' => $className,
                        'tingkat' => $item['tingkat'],
                        'abjad' => $item['abjad'],
                        'total_hours' => 0,
                        'slots' => []
                    ];
                }
                $teachingSummary[$key]['total_hours']++;
                $teachingSummary[$key]['slots'][] = $item['day'] . ' Jam ' . $item['hour'];

                if (!in_array($item['day'], $daysActive)) {
                    $daysActive[] = $item['day'];
                }

                if (isset($grid[$item['day']][$item['hour']])) {
                    $grid[$item['day']][$item['hour']] = [
                        'subject_name' => $subName,
                        'class_name' => $className
                    ];
                }
            }

            // Sort summary by subject name then class name
            uasort($teachingSummary, function($a, $b) {
                $cmp = strnatcmp($a['subject_name'], $b['subject_name']);
                if ($cmp === 0) return strnatcmp($a['class_name'], $b['class_name']);
                return $cmp;
            });

            $result[] = [
                'teacher' => $t,
                'total_hours' => count($items),
                'total_classes' => count($teachingSummary),
                'days_active' => $daysActive,
                'summary' => array_values($teachingSummary),
                'grid' => $grid
            ];
        }

        return $result;
    }
}
