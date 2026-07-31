<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class TeachingSubstitutionModel extends Model {
    protected $table = 'teaching_substitutions';

    public function createBatch($leaveId, $substitutions) {
        $stmt = $this->db->prepare("INSERT INTO teaching_substitutions (leave_id, hour, kelas_id, subject_id, substitute_teacher_id, note) VALUES (?, ?, ?, ?, ?, ?)");
        
        $this->db->beginTransaction();
        try {
            foreach ($substitutions as $sub) {
                $stmt->execute([
                    $leaveId,
                    $sub['hour'],
                    $sub['kelas_id'],
                    $sub['subject_id'],
                    $sub['substitute_teacher_id'] ?? null,
                    $sub['note'] ?? null
                ]);
            }
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
