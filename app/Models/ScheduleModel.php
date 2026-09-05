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
}
