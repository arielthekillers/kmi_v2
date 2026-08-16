<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class StudentAttendanceModel extends Model {
    protected $table = 'attendance_sessions';

    public function getSessions($academicYearId) {
        $stmt = $this->db->prepare("SELECT * FROM attendance_sessions WHERE academic_year_id = ? ORDER BY semester ASC");
        $stmt->execute([$academicYearId]);
        $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($sessions) && $academicYearId) {
            // Automatically initialize Semester 1 and Semester 2 for this academic year
            try {
                $this->db->beginTransaction();
                $stmtInsert = $this->db->prepare("INSERT INTO attendance_sessions (academic_year_id, semester, is_active, is_open) VALUES (?, ?, 0, 0)");
                $stmtInsert->execute([$academicYearId, 1]);
                $stmtInsert->execute([$academicYearId, 2]);
                $this->db->commit();

                // Refetch
                $stmt->execute([$academicYearId]);
                $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (\Exception $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
            }
        }

        return $sessions;
    }

    public function getActiveSession($academicYearId) {
        $stmt = $this->db->prepare("SELECT * FROM attendance_sessions WHERE academic_year_id = ? AND is_active = 1 LIMIT 1");
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
            if ($data['is_active'] == 1) {
                $idStmt = $this->db->prepare("SELECT academic_year_id FROM attendance_sessions WHERE id = ?");
                $idStmt->execute([$sessionId]);
                $ayId = $idStmt->fetchColumn();
                if ($ayId) {
                    // Deactivate and Close all attendance sessions in this year first
                    $this->db->prepare("UPDATE attendance_sessions SET is_active = 0, is_open = 0 WHERE academic_year_id = ?")->execute([$ayId]);
                }
            }
            $fields[] = "is_active = ?";
            $params[] = $data['is_active'];
        }

        if (empty($fields)) return false;

        $params[] = $sessionId;
        $sql = "UPDATE attendance_sessions SET " . implode(', ', $fields) . " WHERE id = ?";
        return $this->db->prepare($sql)->execute($params);
    }

    public function getCommittee($sessionId) {
        $stmt = $this->db->prepare("
            SELECT u.id, u.nama, u.username 
            FROM users u
            JOIN attendance_committees ac ON u.id = ac.user_id
            WHERE ac.attendance_session_id = ?
            ORDER BY u.nama ASC
        ");
        $stmt->execute([$sessionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateCommittee($sessionId, $userIds) {
        try {
            $this->db->beginTransaction();
            // Clear existing
            $this->db->prepare("DELETE FROM attendance_committees WHERE attendance_session_id = ?")->execute([$sessionId]);
            // Add new
            $stmt = $this->db->prepare("INSERT INTO attendance_committees (attendance_session_id, user_id) VALUES (?, ?)");
            foreach ($userIds as $uid) {
                if (!empty($uid)) {
                    $stmt->execute([$sessionId, $uid]);
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

    public function getAbsencesByClassAndDate($kelasId, $date, $sessionId) {
        $sql = "SELECT s.id as student_id, s.nama, s.nis, sa.type as status, sa.note 
                FROM students s 
                INNER JOIN student_enrollments se ON s.id = se.student_id
                LEFT JOIN student_absences sa ON s.id = sa.student_id AND sa.date = ? AND sa.attendance_session_id = ?
                WHERE se.kelas_id = ? AND se.academic_year_id = ? AND se.status IN ('Active', 'Graduated') AND s.deleted_at IS NULL
                ORDER BY s.nama ASC";
        return $this->query($sql, [$date, $sessionId, $kelasId, $this->academic_year_id])->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveAbsences($sessionId, $kelasId, $date, $absencesData, $createdBy) {
        try {
            $this->db->beginTransaction();

            $stmtDelete = $this->db->prepare("DELETE FROM student_absences WHERE student_id = ? AND date = ? AND attendance_session_id = ?");
            $stmtUpsert = $this->db->prepare("
                INSERT INTO student_absences (attendance_session_id, student_id, kelas_id, date, type, note, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE type = VALUES(type), note = VALUES(note), created_by = VALUES(created_by)
            ");

            foreach ($absencesData as $studentId => $data) {
                $status = $data['status'] ?? '';
                $note = isset($data['note']) ? trim($data['note']) : '';

                if (empty($status)) {
                    // Present / Hadir -> delete record from db
                    $stmtDelete->execute([$studentId, $date, $sessionId]);
                } else {
                    // Absent -> upsert record in db
                    $stmtUpsert->execute([
                        $sessionId,
                        $studentId,
                        $kelasId,
                        $date,
                        $status,
                        !empty($note) ? $note : null,
                        $createdBy
                    ]);
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

    public function getAbsenceSummaryByClass($kelasId, $sessionId) {
        $stmt = $this->db->prepare("
            SELECT student_id,
                   SUM(CASE WHEN type = 'sakit' THEN 1 ELSE 0 END) as sakit,
                   SUM(CASE WHEN type = 'izin' THEN 1 ELSE 0 END) as izin,
                   SUM(CASE WHEN type = 'alpha' THEN 1 ELSE 0 END) as alpa
            FROM student_absences
            WHERE kelas_id = ? AND attendance_session_id = ?
            GROUP BY student_id
        ");
        $stmt->execute([$kelasId, $sessionId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $summary = [];
        foreach ($rows as $row) {
            $summary[$row['student_id']] = $row;
        }
        return $summary;
    }

    public function getAbsentCountPerClass($date, $sessionId) {
        if (!$sessionId) return [];
        $stmt = $this->db->prepare("
            SELECT kelas_id, COUNT(*) as count 
            FROM student_absences 
            WHERE date = ? AND attendance_session_id = ? AND type IN ('sakit', 'izin', 'alpha')
            GROUP BY kelas_id
        ");
        $stmt->execute([$date, $sessionId]);
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }
}
