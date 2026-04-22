<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class PiketModel extends Model {
    protected $table = 'piket_schedule';

    /**
     * Get schedule by type
     * @param string $type 'syeikh' or 'keliling'
     * @return array 
     *      For 'syeikh': ['Senin' => [user_id1, user_id2], ...]
     *      For 'keliling': ['Senin' => [session_id => [user_id1, ...]], ...]
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
            if ($type === 'keliling') {
                $sess = $row['session'] ?? 1;
                $schedule[$row['day']][$sess][] = $row['user_id'];
            } else {
                $schedule[$row['day']][] = $row['user_id'];
            }
        }
        
        return $schedule;
    }

    /**
     * Update schedule for a specific type
     * @param string $type 'syeikh' or 'keliling'
     * @param array $data 
     *      For 'syeikh': ['Senin' => [id1, id2], ...]
     *      For 'keliling': ['Senin' => [session_id => [id1, id2]], ...]
     */
    public function updateSchedule($type, $data) {
        try {
            $this->db->beginTransaction();

            // 1. Delete existing for this type for current year
            $stmt = $this->db->prepare("DELETE FROM piket_schedule WHERE type = ? AND academic_year_id = ?");
            $stmt->execute([$type, $this->academic_year_id]);

            // 2. Insert new
            $stmtInsert = $this->db->prepare("INSERT INTO piket_schedule (user_id, day, type, session, academic_year_id) VALUES (?, ?, ?, ?, ?)");
            
            foreach ($data as $day => $content) {
                if ($type === 'keliling') {
                    // content is [session => [ids]]
                    if (!is_array($content)) continue;
                    foreach ($content as $session => $userIds) {
                        if (!is_array($userIds)) continue;
                        $userIds = array_values(array_unique(array_filter($userIds)));
                        foreach ($userIds as $userId) {
                            $stmtInsert->execute([$userId, $day, $type, $session, $this->academic_year_id]);
                        }
                    }
                } else {
                    // content is [ids] (for syeikh)
                    if (!is_array($content)) continue;
                    $userIds = array_values(array_unique(array_filter($content)));
                    foreach ($userIds as $userId) {
                        $stmtInsert->execute([$userId, $day, $type, 0, $this->academic_year_id]);
                    }
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
