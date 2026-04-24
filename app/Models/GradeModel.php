<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class GradeModel extends Model {
    protected $table = 'exams';

    public function getAllExams($filters = []) {
        $sql = "SELECT e.*, 
                       k.tingkat, k.abjad, 
                       (SELECT COUNT(*) FROM student_enrollments se WHERE se.kelas_id = k.id AND se.status = 'Active' AND se.academic_year_id = e.academic_year_id) as jumlah_murid,
                       sub.nama as mapel_nama,
                       u.nama as pengajar_nama,
                       es.type as exam_type, es.is_open as session_is_open, e.has_oral,
                       (SELECT COUNT(*) FROM grades g WHERE g.exam_id = e.id AND (g.score_raw IS NOT NULL)) as graded_count
                FROM exams e
                LEFT JOIN kelas k ON e.kelas_id = k.id
                LEFT JOIN subjects sub ON e.subject_id = sub.id
                LEFT JOIN users u ON e.teacher_id = u.id
                LEFT JOIN exam_sessions es ON e.exam_session_id = es.id
                WHERE e.is_deleted = 0";

        $params = [];

        // Academic Year Filter
        if (!empty($filters['academic_year_id'])) {
            $sql .= " AND e.academic_year_id = ?";
            $params[] = $filters['academic_year_id'];
        } else {
            $sql .= " AND e.academic_year_id = ?";
            $params[] = $this->academic_year_id;
        }

        if (!empty($filters['kelas'])) {
            $sql .= " AND e.kelas_id = ?";
            $params[] = $filters['kelas'];
        }
        if (!empty($filters['pelajaran'])) {
            $sql .= " AND e.subject_id = ?";
            $params[] = $filters['pelajaran'];
        }
        if (!empty($filters['exam_session_id'])) {
            $sql .= " AND e.exam_session_id = ?";
            $params[] = $filters['exam_session_id'];
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
                   k.tingkat, k.abjad, 
                   (SELECT COUNT(*) FROM student_enrollments se WHERE se.kelas_id = k.id AND se.status = 'Active' AND se.academic_year_id = e.academic_year_id) as jumlah_murid,
                   sub.nama as mapel_nama, sub.skala, 
                   u.nama as pengajar_nama,
                   es.type as exam_type, es.is_open as session_is_open, e.has_oral
            FROM exams e
            JOIN kelas k ON e.kelas_id = k.id
            JOIN subjects sub ON e.subject_id = sub.id
            LEFT JOIN users u ON e.teacher_id = u.id
            LEFT JOIN exam_sessions es ON e.exam_session_id = es.id
            WHERE e.id = ? AND e.is_deleted = 0
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getGrades($examId, $classId, $academicYearId = null) {
        $ayId = $academicYearId ?: $this->academic_year_id;
        $stmt = $this->db->prepare("
            SELECT s.id as student_id, s.nama, s.nis,
                   g.no_bayanat, g.score_raw as skor, g.score_final as nilai, g.score_oral
            FROM students s
            INNER JOIN student_enrollments se ON s.id = se.student_id
            LEFT JOIN grades g ON s.id = g.student_id AND g.exam_id = ?
            WHERE se.kelas_id = ? AND se.academic_year_id = ? AND se.status = 'Active'
            ORDER BY CASE WHEN g.no_bayanat IS NULL THEN 1 ELSE 0 END, g.no_bayanat ASC, s.nama ASC
        ");
        $stmt->execute([$examId, $classId, $ayId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createExam($data) {
        try {
            $this->db->beginTransaction();

            // Get Current Active Session
            $session = $this->getActiveSession($this->academic_year_id);
            if (!$session) {
                throw new \Exception("Tidak ada sesi ujian (UUPT/UPT/dll) yang aktif untuk tahun ajaran ini.");
            }

            // Check for duplicate
            $stmtCheck = $this->db->prepare("SELECT id FROM exams WHERE subject_id = ? AND kelas_id = ? AND academic_year_id = ? AND exam_session_id = ? AND is_deleted = 0");
            $stmtCheck->execute([$data['subject_id'], $data['kelas_id'], $this->academic_year_id, $session['id']]);
            if ($stmtCheck->fetch()) {
                throw new \Exception("Pelajaran ini sudah ada di daftar koreksi untuk kelas tersebut pada sesi ini.");
            }

            // Insert Exam
            $stmt = $this->db->prepare("INSERT INTO exams (subject_id, kelas_id, teacher_id, skor_maks, has_oral, status, academic_year_id, exam_session_id, semester, created_at) VALUES (?, ?, ?, ?, ?, 'belum', ?, ?, ?, NOW())");
            
            // Map session type to semester for legacy support
            $semester = in_array($session['type'], ['UUPT', 'UPT']) ? 1 : 2;
            
            $stmt->execute([
                $data['subject_id'], 
                $data['kelas_id'], 
                $data['teacher_id'], 
                $data['skor_maks'] ?? 100, 
                $data['has_oral'] ?? 0,
                $this->academic_year_id, 
                $session['id'],
                $semester
            ]);
            $examId = $this->db->lastInsertId();

            // Link existing grades if any
            $stmtStudents = $this->db->prepare("
                SELECT s.id FROM students s 
                INNER JOIN student_enrollments se ON s.id = se.student_id
                WHERE se.kelas_id = ? AND se.academic_year_id = ? AND se.status = 'Active'
            ");
            $stmtStudents->execute([$data['kelas_id'], $this->academic_year_id]);
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

    public function saveGrades($examId, $subjectId, $skor_maks, $skala, $studentIds, $skors, $status = 'proses', $noBayanats = [], $data = []) {
        try {
            $this->db->beginTransaction();

            $skor_maks = (float)($skor_maks ?? 100);
            if ($skor_maks <= 0) $skor_maks = 100;

            // Also ensure the DB is updated if it was changed in examData (passed from Controller)
// Data will be updated in a single query at the end to ensure consistency.

            $skala = $skala ?? '80-30';
            list($max_val, $min_val) = explode('-', $skala);
            $max_val = (int)$max_val;
            $min_val = (int)$min_val;

            $sql = "INSERT INTO grades (student_id, subject_id, exam_id, score_raw, score_final, score_oral, no_bayanat, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
                    ON DUPLICATE KEY UPDATE 
                        score_raw = COALESCE(VALUES(score_raw), score_raw), 
                        score_final = COALESCE(VALUES(score_final), score_final),
                        score_oral = COALESCE(VALUES(score_oral), score_oral),
                        no_bayanat = COALESCE(VALUES(no_bayanat), no_bayanat),
                        updated_at = VALUES(updated_at)";
            $stmt = $this->db->prepare($sql);

            // If studentIds is empty (Admin case), fetch all existing grades to recalculate them with new skor_maks
            if (empty($studentIds)) {
                $stmtGet = $this->db->prepare("SELECT student_id, score_raw, score_oral FROM grades WHERE exam_id = ?");
                $stmtGet->execute([$examId]);
                while ($row = $stmtGet->fetch(PDO::FETCH_ASSOC)) {
                    $studentIds[] = $row['student_id'];
                    $skors[] = $row['score_raw'] ?? '';
                    if (!isset($data['skor_lisan'])) {
                        // Keep existing oral score if not provided
                        $skorsLisan[] = $row['score_oral'] ?? null;
                    }
                }
            } else {
                $skorsLisan = $data['skor_lisan'] ?? [];
            }


            for ($i = 0; $i < count($studentIds); $i++) {
                $studentId = $studentIds[$i];
                $skor_input = isset($skors[$i]) ? trim($skors[$i]) : null;

                // If score is not provided in current request (e.g. Admin update), 
                // but we might need to recalculate value based on new skor_maks,
                // try to fetch existing raw score from DB.
                if ($skor_input === null) {
                    $stmtGet = $this->db->prepare("SELECT score_raw FROM grades WHERE exam_id = ? AND student_id = ?");
                    $stmtGet->execute([$examId, $studentId]);
                    $rowScore = $stmtGet->fetch(PDO::FETCH_ASSOC);
                    $skor_input = isset($rowScore['score_raw']) ? $rowScore['score_raw'] : null;
                }
                
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
                    if ($skor < 0) $skor = 0; // Sanity check: no negative scores
                    $nilai_akhir = round(($skor / $skor_maks) * $max_val);
                    if ($nilai_akhir < $min_val) $nilai_akhir = $min_val;
                    if ($nilai_akhir > $max_val) $nilai_akhir = $max_val;
                    $score_raw_db = $skor;
                } else {
                     $nilai_akhir = null;
                     $score_raw_db = null;
                }

                $noBayanat = !empty($noBayanats[$i]) ? (int)$noBayanats[$i] : null;
                if ($noBayanat !== null && $noBayanat < 1) $noBayanat = null; // Sanity check: must be >= 1

                $skorLisan = isset($skorsLisan[$i]) ? trim($skorsLisan[$i]) : null;
                if ($skorLisan === '') $skorLisan = null;

                if ($score_raw_db !== null || $skorLisan !== null) {
                    $stmt->execute([$studentId, $subjectId, $examId, $score_raw_db, $nilai_akhir, $skorLisan, $noBayanat]);
                } else {
                    $stmt->execute([$studentId, $subjectId, $examId, null, null, null, $noBayanat]);
                }
            }

            // Update Status and Skor Maks in one go
            $stmtUpd = $this->db->prepare("UPDATE exams SET status = ?, skor_maks = ? WHERE id = ?");
            $stmtUpd->execute([$status, $skor_maks, $examId]);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function deleteExam($id) {
        $this->db->beginTransaction();
        try {
            // Soft delete: just mark as deleted so it can be restored if needed
            $stmt = $this->db->prepare("UPDATE exams SET is_deleted = 1 WHERE id = ?");
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

    // --- Session & Committee Management ---

    public function getSessions($academicYearId) {
        $stmt = $this->db->prepare("SELECT * FROM exam_sessions WHERE academic_year_id = ? ORDER BY id ASC");
        $stmt->execute([$academicYearId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getActiveSession($academicYearId) {
        $stmt = $this->db->prepare("SELECT * FROM exam_sessions WHERE academic_year_id = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([$academicYearId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateSessionStatus($sessionId, $data) {
        $fields = [];
        $params = [];
        if (isset($data['is_open'])) {
            $fields[] = "is_open = ?";
            $params[] = $data['is_open'];
        }
        if (isset($data['is_active'])) {
             // If we are setting this session to active, deactivate others in same year
             if ($data['is_active'] == 1) {
                 $idStmt = $this->db->prepare("SELECT academic_year_id FROM exam_sessions WHERE id = ?");
                 $idStmt->execute([$sessionId]);
                 $ayId = $idStmt->fetchColumn();
                 if ($ayId) {
                     // Deactivate and Close all sessions in this year first
                     $this->db->prepare("UPDATE exam_sessions SET is_active = 0, is_open = 0 WHERE academic_year_id = ?")->execute([$ayId]);
                 }
             }
            $fields[] = "is_active = ?";
            $params[] = $data['is_active'];
        }

        if (empty($fields)) return false;

        $params[] = $sessionId;
        $sql = "UPDATE exam_sessions SET " . implode(', ', $fields) . " WHERE id = ?";
        return $this->db->prepare($sql)->execute($params);
    }

    public function getCommittee($sessionId) {
        $stmt = $this->db->prepare("
            SELECT u.id, u.nama, u.username 
            FROM users u
            JOIN exam_committees ec ON u.id = ec.user_id
            WHERE ec.exam_session_id = ?
        ");
        $stmt->execute([$sessionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateCommittee($sessionId, $userIds) {
        try {
            $this->db->beginTransaction();
            // Clear existing
            $this->db->prepare("DELETE FROM exam_committees WHERE exam_session_id = ?")->execute([$sessionId]);
            // Add new
            $stmt = $this->db->prepare("INSERT INTO exam_committees (exam_session_id, user_id) VALUES (?, ?)");
            foreach ($userIds as $uid) {
                if (!empty($uid)) {
                    $stmt->execute([$sessionId, $uid]);
                }
            }
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
