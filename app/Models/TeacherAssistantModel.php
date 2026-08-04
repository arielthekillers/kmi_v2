<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class TeacherAssistantModel extends Model {
    protected $table = 'teacher_assistants';

    public function getAll($academicYearId = null) {
        $where = "";
        $params = [];
        if ($academicYearId) {
            $where = "WHERE a.academic_year_id = ?";
            $params[] = $academicYearId;
        }

        $stmt = $this->db->prepare("
            SELECT a.*, 
                   ts.nama as teacher_name,
                   ta.nama as assistant_name,
                   s.nama as subject_name,
                   CONCAT(k.tingkat, '-', k.abjad, ' ', k.gender) as kelas_name
            FROM teacher_assistants a
            JOIN users ts ON a.teacher_id = ts.id
            JOIN users ta ON a.assistant_id = ta.id
            LEFT JOIN subjects s ON a.subject_id = s.id
            LEFT JOIN kelas k ON a.kelas_id = k.id
            $where
            ORDER BY ts.nama ASC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO teacher_assistants (academic_year_id, teacher_id, assistant_id, subject_id, kelas_id) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['academic_year_id'] ?? null,
            $data['teacher_id'],
            $data['assistant_id'],
            $data['subject_id'] ?: null,
            $data['kelas_id'] ?: null
        ]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM teacher_assistants WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getAssistantForSubject($teacherId, $subjectId, $kelasId, $academicYearId) {
        // Try specific subject and class first
        $stmt = $this->db->prepare("SELECT assistant_id FROM teacher_assistants WHERE teacher_id = ? AND subject_id = ? AND kelas_id = ? AND academic_year_id = ? LIMIT 1");
        $stmt->execute([$teacherId, $subjectId, $kelasId, $academicYearId]);
        $assistant = $stmt->fetchColumn();
        if ($assistant) return $assistant;

        // Try general assistant
        $stmt = $this->db->prepare("SELECT assistant_id FROM teacher_assistants WHERE teacher_id = ? AND subject_id IS NULL AND kelas_id IS NULL AND academic_year_id = ? LIMIT 1");
        $stmt->execute([$teacherId, $academicYearId]);
        return $stmt->fetchColumn();
    }
}
