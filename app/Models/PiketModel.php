<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class PiketModel extends Model {
    protected $table = 'piket_schedule';

    /**
     * Get schedule by type
     * @param string $type 'syeikh' or 'keliling'
     * @return array ['Senin' => [user_id1, user_id2], ...]
     */
    public function getSchedule($type) {
        $stmt = $this->db->prepare("SELECT * FROM piket_schedule WHERE type = ? AND academic_year_id = ?");
        $stmt->execute([$type, $this->academic_year_id]);
        
        $schedule = [];
        $days = ['Sabtu', 'Ahad', 'Senin', 'Selasa', 'Rabu', 'Kamis'];
        
        // Initialize empty arrays
        foreach ($days as $day) {
            $schedule[$day] = [];
        }

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $schedule[$row['day']][] = $row['user_id'];
        }
        
        return $schedule;
    }

    /**
     * Update schedule for a specific type
     * @param string $type 'syeikh' or 'keliling'
     * @param array $data ['Senin' => [id1, id2], ...]
     */
    public function updateSchedule($type, $data) {
        try {
            $this->db->beginTransaction();

            // 1. Delete existing for this type for current year
            $stmt = $this->db->prepare("DELETE FROM piket_schedule WHERE type = ? AND academic_year_id = ?");
            $stmt->execute([$type, $this->academic_year_id]);

            // 2. Insert new
            $stmtInsert = $this->db->prepare("INSERT INTO piket_schedule (user_id, day, type, academic_year_id) VALUES (?, ?, ?, ?)");
            
            foreach ($data as $day => $userIds) {
                if (!is_array($userIds)) continue;
                // Filter unique and valid IDs
                $userIds = array_values(array_unique(array_filter($userIds)));
                
                foreach ($userIds as $userId) {
                    $stmtInsert->execute([$userId, $day, $type, $this->academic_year_id]);
                }
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }
}
