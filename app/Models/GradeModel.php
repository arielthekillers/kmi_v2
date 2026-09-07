<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class GradeModel extends Model {
    protected $table = 'exams';

    public function getAllExams($filters = []) {
        $sql = "SELECT e.*, 
                       k.tingkat, k.abjad, 
                       (SELECT COUNT(*) FROM student_enrollments se JOIN students s ON se.student_id = s.id WHERE se.kelas_id = k.id AND se.status IN ('Active', 'Graduated') AND se.academic_year_id = e.academic_year_id AND s.deleted_at IS NULL) as jumlah_murid,
                       sub.nama as mapel_nama,
                       CASE 
                           WHEN tp.gender = 'Laki-laki' THEN CONCAT('Al-Ustadz ', u.nama)
                           WHEN tp.gender = 'Perempuan' THEN CONCAT('Al-Ustadzah ', u.nama)
                           ELSE u.nama
                       END as pengajar_nama,
                       es.type as exam_type, es.is_open as session_is_open, COALESCE(es.use_bayanat, 1) as use_bayanat, e.has_oral,
                       (SELECT COUNT(*) FROM grades g WHERE g.exam_id = e.id AND (g.score_raw IS NOT NULL)) as graded_count,
                       (SELECT COUNT(*) FROM grades g WHERE g.exam_id = e.id AND (g.score_oral IS NOT NULL)) as graded_oral_count,
                       (SELECT COUNT(*) FROM grades g WHERE g.exam_id = e.id AND (g.no_bayanat IS NOT NULL)) as bayanat_count,
                       (SELECT AVG(g.score_final) FROM grades g WHERE g.exam_id = e.id AND g.score_final IS NOT NULL) as average_score
                FROM exams e
                LEFT JOIN kelas k ON e.kelas_id = k.id
                LEFT JOIN subjects sub ON e.subject_id = sub.id
                LEFT JOIN users u ON e.teacher_id = u.id
                LEFT JOIN teacher_profiles tp ON u.id = tp.user_id
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
        if (isset($filters['has_oral']) && $filters['has_oral'] !== '') {
            $sql .= " AND e.has_oral = ?";
            $params[] = (int)$filters['has_oral'];
        }
        if (!empty($filters['exclude_oral_only'])) {
            $sql .= " AND e.has_oral != 2";
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
                   (SELECT COUNT(*) FROM student_enrollments se JOIN students s ON se.student_id = s.id WHERE se.kelas_id = k.id AND se.status IN ('Active', 'Graduated') AND se.academic_year_id = e.academic_year_id AND s.deleted_at IS NULL) as jumlah_murid,
                   sub.nama as mapel_nama, sub.skala, 
                   CASE 
                       WHEN tp.gender = 'Laki-laki' THEN CONCAT('Al-Ustadz ', u.nama)
                       WHEN tp.gender = 'Perempuan' THEN CONCAT('Al-Ustadzah ', u.nama)
                       ELSE u.nama
                   END as pengajar_nama,
                   es.type as exam_type, es.is_open as session_is_open, COALESCE(es.use_bayanat, 1) as use_bayanat, e.has_oral,
                   (SELECT COUNT(*) FROM grades g WHERE g.exam_id = e.id AND (g.no_bayanat IS NOT NULL)) as bayanat_count
            FROM exams e
            JOIN kelas k ON e.kelas_id = k.id
            JOIN subjects sub ON e.subject_id = sub.id
            LEFT JOIN users u ON e.teacher_id = u.id
            LEFT JOIN teacher_profiles tp ON u.id = tp.user_id
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
            WHERE se.kelas_id = ? AND se.academic_year_id = ? AND se.status IN ('Active', 'Graduated') AND s.deleted_at IS NULL
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

            // Check for duplicate (active records)
            $stmtCheck = $this->db->prepare("SELECT id FROM exams WHERE subject_id = ? AND kelas_id = ? AND academic_year_id = ? AND exam_session_id = ? AND is_deleted = 0");
            $stmtCheck->execute([$data['subject_id'], $data['kelas_id'], $this->academic_year_id, $session['id']]);
            if ($stmtCheck->fetch()) {
                throw new \Exception("Pelajaran ini sudah ada di daftar koreksi untuk kelas tersebut pada sesi ini.");
            }

            // Check for duplicate in trash (soft-deleted records)
            $stmtTrash = $this->db->prepare("SELECT id FROM exams WHERE subject_id = ? AND kelas_id = ? AND academic_year_id = ? AND exam_session_id = ? AND is_deleted = 1");
            $stmtTrash->execute([$data['subject_id'], $data['kelas_id'], $this->academic_year_id, $session['id']]);
            if ($stmtTrash->fetch()) {
                throw new \Exception("Koreksi ujian ini sebelumnya telah dihapus dan masih tersimpan di Tong Sampah. Silakan pulihkan (restore) data tersebut daripada membuat entri baru.");
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

            // (The legacy code that hijacked grades here has been intentionally removed to prevent soft-deleted or previous session grades from being stolen into the new exam)

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

            // Get current or new has_oral setting
            $stmtExam = $this->db->prepare("SELECT has_oral FROM exams WHERE id = ?");
            $stmtExam->execute([$examId]);
            $hasOral = (int)$stmtExam->fetchColumn();
            if (isset($data['has_oral'])) {
                $hasOral = (int)$data['has_oral'];
            }

            $sql = "INSERT INTO grades (student_id, subject_id, exam_id, score_raw, score_final, score_oral, no_bayanat, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
                    ON DUPLICATE KEY UPDATE 
                        score_raw = VALUES(score_raw), 
                        score_final = VALUES(score_final),
                        score_oral = VALUES(score_oral),
                        no_bayanat = VALUES(no_bayanat),
                        updated_at = VALUES(updated_at)";
            $stmt = $this->db->prepare($sql);

            // If studentIds is empty (Admin case), fetch all existing grades to recalculate them with new config
            $submittedNilais = $data['nilai'] ?? [];
            if (empty($studentIds)) {
                $studentIds = [];
                $skors = [];
                $skorsLisan = [];
                $noBayanats = [];
                $submittedNilais = [];
                $stmtGet = $this->db->prepare("SELECT student_id, score_raw, score_oral, no_bayanat, score_final FROM grades WHERE exam_id = ?");
                $stmtGet->execute([$examId]);
                while ($row = $stmtGet->fetch(PDO::FETCH_ASSOC)) {
                    $studentIds[] = $row['student_id'];
                    $skors[] = $row['score_raw'] ?? '';
                    $skorsLisan[] = $row['score_oral'] ?? null;
                    $noBayanats[] = $row['no_bayanat'] ?? '';
                    $submittedNilais[] = $row['score_final'] ?? null;
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
                
                $skorLisan = isset($skorsLisan[$i]) ? trim($skorsLisan[$i]) : null;
                if ($skorLisan === '') $skorLisan = null;

                // If oral score is not provided in current request (e.g. Admin update),
                // restore existing oral score from DB to prevent overwriting to NULL.
                if ($skorLisan === null) {
                    $stmtGetOral = $this->db->prepare("SELECT score_oral FROM grades WHERE exam_id = ? AND student_id = ?");
                    $stmtGetOral->execute([$examId, $studentId]);
                    $rowOral = $stmtGetOral->fetch(PDO::FETCH_ASSOC);
                    $skorLisan = isset($rowOral['score_oral']) ? $rowOral['score_oral'] : null;
                }

                $nilai_akhir = null;
                $score_raw_db = null;

                // ----------------------------------------------------------------
                // Nilai Tulis = konversi skor tulis ke skala rapor.
                // Formula: round((skor / skor_maks) * (max_val - min_val) + min_val)
                // Nilai Lisan disimpan apa adanya (score_oral), TIDAK digabungkan di sini.
                // Penggabungan tulis+lisan untuk nilai akhir rapor dilakukan di modul rapor.
                // ----------------------------------------------------------------

                if ($hasOral == 0) {
                    // Tulis Saja: hitung nilai tulis dari skor tulis
                    if ($skor_input === '-') {
                        $nilai_akhir = 0;       // Absen
                        $score_raw_db = '-';
                    } elseif ($skor_input === '0' || $skor_input === 0 || $skor_input === '0.0') {
                        $nilai_akhir = $min_val; // Salah semua → nilai minimum
                        $score_raw_db = '0';
                    } elseif (is_numeric($skor_input)) {
                        $skor = (float) $skor_input;
                        if ($skor < 0) $skor = 0;
                        $nilai_akhir = round(($skor / $skor_maks) * ($max_val - $min_val) + $min_val);
                        if ($nilai_akhir < $min_val) $nilai_akhir = $min_val;
                        if ($nilai_akhir > $max_val) $nilai_akhir = $max_val;
                        $score_raw_db = $skor;
                    }

                } elseif ($hasOral == 2) {
                    // Lisan Saja: nilai lisan disimpan apa adanya di score_oral,
                    // score_final (nilai tulis) tidak berlaku → null.
                    // Tidak ada konversi di sini; score_oral disimpan langsung.
                    $score_raw_db = null;
                    $nilai_akhir  = null; // Nilai akhir rapor dihitung di modul rapor

                } elseif ($hasOral == 1) {
                    // Tulis & Lisan: hitung nilai tulis dari skor tulis.
                    // Nilai lisan tetap disimpan apa adanya di score_oral.
                    // TIDAK ada penggabungan di sini.
                    if ($skor_input === '-') {
                        $nilai_akhir  = 0;   // Absen tulis
                        $score_raw_db = '-';
                    } elseif ($skor_input === '0' || $skor_input === 0 || $skor_input === '0.0') {
                        $nilai_akhir  = $min_val;
                        $score_raw_db = '0';
                    } elseif (is_numeric($skor_input)) {
                        $tRaw = (float)$skor_input;
                        if ($tRaw < 0) $tRaw = 0;
                        $score_raw_db = $tRaw;
                        $nilai_akhir  = round(($tRaw / $skor_maks) * ($max_val - $min_val) + $min_val);
                        if ($nilai_akhir < $min_val) $nilai_akhir = $min_val;
                        if ($nilai_akhir > $max_val) $nilai_akhir = $max_val;
                    }
                }

                // If no_bayanat is not provided in the array (e.g. teacher updates grades without bayanat inputs),
                // fallback to the existing no_bayanat in the DB to prevent overwriting to NULL.
                if (empty($noBayanats)) {
                    $stmtGetBayanat = $this->db->prepare("SELECT no_bayanat FROM grades WHERE exam_id = ? AND student_id = ?");
                    $stmtGetBayanat->execute([$examId, $studentId]);
                    $valBayanat = $stmtGetBayanat->fetchColumn();
                    $noBayanat = ($valBayanat !== false && $valBayanat !== null) ? (int)$valBayanat : null;
                } else {
                    $noBayanat = !empty($noBayanats[$i]) ? (int)$noBayanats[$i] : null;
                    if ($noBayanat !== null && $noBayanat < 1) $noBayanat = null; // Sanity check: must be >= 1
                }

                if ($score_raw_db !== null || $skorLisan !== null) {
                    $stmt->execute([$studentId, $subjectId, $examId, $score_raw_db, $nilai_akhir, $skorLisan, $noBayanat]);
                } else {
                    $stmt->execute([$studentId, $subjectId, $examId, null, null, null, $noBayanat]);
                }
            }

            // Update Status, Skor Maks, and has_oral in one go
            if (isset($data['has_oral'])) {
                $stmtUpd = $this->db->prepare("UPDATE exams SET status = ?, skor_maks = ?, has_oral = ? WHERE id = ?");
                $stmtUpd->execute([$status, $skor_maks, $hasOral, $examId]);
            } else {
                $stmtUpd = $this->db->prepare("UPDATE exams SET status = ?, skor_maks = ? WHERE id = ?");
                $stmtUpd->execute([$status, $skor_maks, $examId]);
            }

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

    public function getDeletedExams($academicYearId = null) {
        $ayId = $academicYearId ?: $this->academic_year_id;
        $sql = "SELECT e.*, 
                       k.tingkat, k.abjad, 
                       sub.nama as mapel_nama,
                       CASE 
                           WHEN tp.gender = 'Laki-laki' THEN CONCAT('Al-Ustadz ', u.nama)
                           WHEN tp.gender = 'Perempuan' THEN CONCAT('Al-Ustadzah ', u.nama)
                           ELSE u.nama
                       END as pengajar_nama,
                       es.type as exam_type, es.is_open as session_is_open
                FROM exams e
                LEFT JOIN kelas k ON e.kelas_id = k.id
                LEFT JOIN subjects sub ON e.subject_id = sub.id
                LEFT JOIN users u ON e.teacher_id = u.id
                LEFT JOIN teacher_profiles tp ON u.id = tp.user_id
                LEFT JOIN exam_sessions es ON e.exam_session_id = es.id
                WHERE e.is_deleted = 1 AND e.academic_year_id = ?
                ORDER BY e.updated_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ayId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function restoreExam($id) {
        $stmt = $this->db->prepare("UPDATE exams SET is_deleted = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function hardDeleteExam($id) {
        $this->db->beginTransaction();
        try {
            $this->db->prepare("DELETE FROM grades WHERE exam_id = ?")->execute([$id]);
            $this->db->prepare("DELETE FROM exams WHERE id = ?")->execute([$id]);
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
        $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($sessions)) {
            // Seed default sessions for this academic year
            $defaultTypes = ['UUPT', 'UPT', 'UUAT', 'UAT'];
            foreach ($defaultTypes as $type) {
                $insert = $this->db->prepare("INSERT INTO exam_sessions (academic_year_id, type, is_open, is_active) VALUES (?, ?, 0, 0)");
                $insert->execute([$academicYearId, $type]);
            }
            
            // Re-fetch
            $stmt->execute([$academicYearId]);
            $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $sessions;
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
        if (isset($data['use_bayanat'])) {
            $fields[] = "use_bayanat = ?";
            $params[] = $data['use_bayanat'];
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

    public function getClassLeger($classId, $sessionId, $academicYearId) {
        // Fetch all exams for this class and session
        $stmtExams = $this->db->prepare("
            SELECT e.id as exam_id, sub.nama as subject_name, sub.nama_ar, sub.category, sub.urutan, sub.id as subject_id, e.skor_maks, sub.skala, e.has_oral, e.status
            FROM exams e
            JOIN subjects sub ON e.subject_id = sub.id
            WHERE e.kelas_id = ? AND e.exam_session_id = ? AND e.academic_year_id = ? AND e.is_deleted = 0
            ORDER BY sub.category ASC, sub.urutan ASC, sub.nama ASC
        ");
        $stmtExams->execute([$classId, $sessionId, $academicYearId]);
        $exams = $stmtExams->fetchAll(\PDO::FETCH_ASSOC);

        // Fetch all students
        $stmtStudents = $this->db->prepare("
            SELECT s.id as student_id, s.nama, s.nis
            FROM students s
            INNER JOIN student_enrollments se ON s.id = se.student_id
            WHERE se.kelas_id = ? AND se.academic_year_id = ? AND se.status IN ('Active', 'Graduated') AND s.deleted_at IS NULL
            ORDER BY s.nama ASC
        ");
        $stmtStudents->execute([$classId, $academicYearId]);
        $students = $stmtStudents->fetchAll(\PDO::FETCH_ASSOC);

        // Fetch all grades for these exams
        $examIds = array_column($exams, 'exam_id');
        $gradesByStudent = [];
        if (!empty($examIds)) {
            $examsById = [];
            foreach ($exams as $exam) {
                $examsById[$exam['exam_id']] = $exam;
            }

            $inQuery = implode(',', array_fill(0, count($examIds), '?'));
            $stmtGrades = $this->db->prepare("
                SELECT g.student_id, g.exam_id, g.score_raw, g.score_final, g.score_oral, g.no_bayanat
                FROM grades g
                WHERE g.exam_id IN ($inQuery)
            ");
            $stmtGrades->execute($examIds);
            while ($row = $stmtGrades->fetch(\PDO::FETCH_ASSOC)) {
                $examId = $row['exam_id'];
                $exam = $examsById[$examId] ?? null;
                $hasOral = $exam ? (int)$exam['has_oral'] : 0;
                
                // Calculate dynamic merged grade
                $row['score_final'] = calculate_merged_grade($row['score_final'], $row['score_oral'], $hasOral);
                
                $gradesByStudent[$row['student_id']][$examId] = $row;
            }
        }

        return [
            'exams' => $exams,
            'students' => $students,
            'grades' => $gradesByStudent
        ];
    }
}
