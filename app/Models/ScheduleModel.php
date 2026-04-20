<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class ScheduleModel extends Model {
    protected $table = 'schedules';

    public function getByClass($classId) {
        $stmt = $this->db->prepare("SELECT * FROM schedules WHERE kelas_id = ? AND academic_year_id = ?");
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
        $stmt = $this->db->prepare("SELECT * FROM schedules WHERE teacher_id = ? AND academic_year_id = ?");
        $stmt->execute([$teacherId, $this->academic_year_id]);
        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[$row['day']][$row['hour']] = [
                'mapel' => $row['subject_id'],
                'kelas' => $row['kelas_id']
            ];
        }
        return $result;
    }

    public function updateBatch($classId, $scheduleData) {
        try {
            $this->db->beginTransaction();

            // 1. Delete existing schedule for this class for current year
            $stmtDelete = $this->db->prepare("DELETE FROM schedules WHERE kelas_id = ? AND academic_year_id = ?");
            $stmtDelete->execute([$classId, $this->academic_year_id]);

            // 2. Insert new schedule
            $stmtInsert = $this->db->prepare("INSERT INTO schedules (kelas_id, day, hour, subject_id, teacher_id, academic_year_id) VALUES (?, ?, ?, ?, ?, ?)");

            foreach ($scheduleData as $day => $hours) {
                foreach ($hours as $hour => $slot) {
                    $mapelId = $slot['mapel'] ?? null;
                    $pengajarId = $slot['pengajar'] ?? null;

                    if (!empty($mapelId) && !empty($pengajarId)) {
                        $stmtInsert->execute([$classId, $day, $hour, $mapelId, $pengajarId, $this->academic_year_id]);
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
}
