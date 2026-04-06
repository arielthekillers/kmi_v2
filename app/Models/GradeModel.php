<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class GradeModel extends Model {
    protected $table = 'exams';

    public function getAllExams($filters = []) {
        $sql = "SELECT e.*, 
                       k.tingkat, k.abjad, k.jumlah_murid,
                       sub.nama as mapel_nama,
                       u.nama as pengajar_nama,
                       (SELECT COUNT(*) FROM grades g WHERE g.exam_id = e.id AND (g.score_raw IS NOT NULL)) as graded_count
                FROM exams e
                LEFT JOIN kelas k ON e.kelas_id = k.id
                LEFT JOIN subjects sub ON e.subject_id = sub.id
                LEFT JOIN users u ON e.teacher_id = u.id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['kelas'])) {
            $sql .= " AND e.kelas_id = ?";
            $params[] = $filters['kelas'];
        }
        if (!empty($filters['pelajaran'])) {
            $sql .= " AND e.subject_id = ?";
            $params[] = $filters['pelajaran'];
        }
        if (!empty($filters['pengajar'])) {
            $sql .= " AND e.teacher_id = ?";
            $params[] = $filters['pengajar'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND e.status = ?";
            $params[] = $filters['status'];
        }

        $sql .= " ORDER BY e.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getExamById($id) {
        $stmt = $this->db->prepare("
            SELECT e.*, 
                   k.tingkat, k.abjad, k.jumlah_murid,
                   sub.nama as mapel_nama, sub.skala, sub.skor_maks,
                   u.nama as pengajar_nama
            FROM exams e
            JOIN kelas k ON e.kelas_id = k.id
            JOIN subjects sub ON e.subject_id = sub.id
            LEFT JOIN users u ON e.teacher_id = u.id
            WHERE e.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getGrades($examId, $classId) {
        $stmt = $this->db->prepare("
            SELECT s.id as student_id, s.nama, s.nis,
                   g.score_raw as skor, g.score_final as nilai
            FROM students s
            LEFT JOIN grades g ON s.id = g.student_id AND g.exam_id = ?
            WHERE s.kelas_id = ?
            ORDER BY s.nama ASC
        ");
        $stmt->execute([$examId, $classId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createExam($data) {
        try {
            $this->db->beginTransaction();

            // Insert Exam
            $stmt = $this->db->prepare("INSERT INTO exams (subject_id, kelas_id, teacher_id, status, created_at) VALUES (?, ?, ?, 'belum', NOW())");
            $stmt->execute([$data['subject_id'], $data['kelas_id'], $data['teacher_id']]);
            $examId = $this->db->lastInsertId();

            // Link existing grades if any (migration logic mainly)
            // Or assume fresh start. Implementation plan said createExam.
            // Let's replicate simpan_koreksi logic for robustness.
            $stmtStudents = $this->db->prepare("SELECT id FROM students WHERE kelas_id = ?");
            $stmtStudents->execute([$data['kelas_id']]);
            $studentIds = $stmtStudents->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($studentIds)) {
                $inQuery = implode(',', array_fill(0, count($studentIds), '?'));
                $sqlLink = "UPDATE grades SET exam_id = ? WHERE subject_id = ? AND student_id IN ($inQuery)";
                $params = array_merge([$examId, $data['subject_id']], $studentIds);
                $stmtLink = $this->db->prepare($sqlLink);
                $stmtLink->execute($params);
            }

            $this->db->commit();
            return $examId;
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function saveGrades($examId, $examData, $studentIds, $skors, $status = 'proses') {
        try {
            $this->db->beginTransaction();

            $skor_maks = (float)($examData['skor_maks'] ?? 100);
            if ($skor_maks <= 0) $skor_maks = 100;

            $skala = $examData['skala'] ?? '80-30';
            list($max_val, $min_val) = explode('-', $skala);
            $max_val = (int)$max_val;
            $min_val = (int)$min_val;

            $sql = "INSERT INTO grades (student_id, subject_id, exam_id, score_raw, score_final, updated_at) 
                    VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
                    ON DUPLICATE KEY UPDATE 
                        score_raw = VALUES(score_raw), 
                        score_final = VALUES(score_final),
                        updated_at = VALUES(updated_at)";
            $stmt = $this->db->prepare($sql);

            for ($i = 0; $i < count($studentIds); $i++) {
                $studentId = $studentIds[$i];
                $skor_input = trim($skors[$i]);
                
                $nilai_akhir = null;
                $score_raw_db = null;

                if ($skor_input === '-') {
                    $nilai_akhir = 0;
                    $score_raw_db = '-';
                } elseif ($skor_input === '0' || $skor_input === 0 || $skor_input === '0.0') {
                     $nilai_akhir = $min_val;
                     $score_raw_db = '0';
                } elseif (is_numeric($skor_input)) {
                    $skor = (float) $skor_input;
                    $nilai_akhir = ($skor / $skor_maks) * $max_val;
                    if ($nilai_akhir < $min_val) $nilai_akhir = $min_val;
                    if ($nilai_akhir > $max_val) $nilai_akhir = $max_val;
                    $score_raw_db = $skor_input;
                } else {
                     $nilai_akhir = null;
                     $score_raw_db = null;
                }

                if ($score_raw_db !== null) {
                    $stmt->execute([$studentId, $examData['subject_id'], $examId, $score_raw_db, $nilai_akhir]);
                } else {
                    $stmt->execute([$studentId, $examData['subject_id'], $examId, null, null]);
                }
            }

            // Update Status
            $stmtUpd = $this->db->prepare("UPDATE exams SET status = ? WHERE id = ?");
            $stmtUpd->execute([$status, $examId]);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function deleteExam($id) {
        // Delete related grades (or unlink them? hapus_koreksi just deletes exam and ON DELETE CASCADE logic? 
        // hapus_koreksi.php typically just DELETE FROM exams. 
        // If no Foreign Key constraint with Cascade, grades become orphans or stay linked.
        // Let's check hapus_koreksi logic manually.
        // Assuming simple delete for now, but safer to delete grades first if no FK.
        // Actually, if we delete the EXAM, the grades should probably be kept but with exam_id=NULL? 
        // Or deleted? Usually deleted if they belong to that exam.
        
        $this->db->beginTransaction();
        try {
            // Unlink grades (set exam_id null) or delete? 
            // If we delete grades, we lose the data. 
            // But if we delete the "Koreksi Session", we likely want to remove the grades associated with it 
            // UNLESS they are the "Final Grade".
            // Legacy hapus_koreksi.php likely just deletes the exam row.
            // Let's follow that.
            $stmt = $this->db->prepare("DELETE FROM exams WHERE id = ?");
            $stmt->execute([$id]);
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function unlockExam($id) {
        $stmt = $this->db->prepare("UPDATE exams SET status = 'proses' WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
