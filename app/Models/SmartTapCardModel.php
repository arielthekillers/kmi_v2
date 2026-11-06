<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class SmartTapCardModel extends Model
{
    public function getStudentsList($search = '', $hasCard = null, $page = 1, $limit = 50, $kelasId = null)
    {
        $offset = ($page - 1) * $limit;
        $yearId = $this->academic_year_id ?? 1;

        $where = [
            "se.academic_year_id = ?",
            "se.status = 'Active'",
            "s.deleted_at IS NULL"
        ];
        $params = [$yearId];

        if (!empty($search)) {
            $where[] = "(s.nama LIKE ? OR s.nis LIKE ? OR s.rfid_uid LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if ($hasCard === 'yes') {
            $where[] = "(s.rfid_uid IS NOT NULL AND TRIM(s.rfid_uid) != '')";
        } elseif ($hasCard === 'no') {
            $where[] = "(s.rfid_uid IS NULL OR TRIM(s.rfid_uid) = '')";
        }

        if (!empty($kelasId)) {
            $where[] = "se.kelas_id = ?";
            $params[] = (int)$kelasId;
        }

        $whereClause = implode(" AND ", $where);

        $sql = "SELECT s.id, s.nis, s.nama, s.gender, s.rfid_uid,
                       k.id as kelas_id,
                       CONCAT(COALESCE(k.tingkat, ''), ' ', COALESCE(k.abjad, '')) as kelas_nama
                FROM student_enrollments se
                INNER JOIN students s ON se.student_id = s.id
                LEFT JOIN kelas k ON se.kelas_id = k.id
                WHERE $whereClause
                ORDER BY k.tingkat ASC, k.abjad ASC, s.nama ASC
                LIMIT $limit OFFSET $offset";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStudentsCount($search = '', $hasCard = null, $kelasId = null)
    {
        $yearId = $this->academic_year_id ?? 1;

        $where = [
            "se.academic_year_id = ?",
            "se.status = 'Active'",
            "s.deleted_at IS NULL"
        ];
        $params = [$yearId];

        if (!empty($search)) {
            $where[] = "(s.nama LIKE ? OR s.nis LIKE ? OR s.rfid_uid LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if ($hasCard === 'yes') {
            $where[] = "(s.rfid_uid IS NOT NULL AND TRIM(s.rfid_uid) != '')";
        } elseif ($hasCard === 'no') {
            $where[] = "(s.rfid_uid IS NULL OR TRIM(s.rfid_uid) = '')";
        }

        if (!empty($kelasId)) {
            $where[] = "se.kelas_id = ?";
            $params[] = (int)$kelasId;
        }

        $whereClause = implode(" AND ", $where);
        $sql = "SELECT COUNT(*)
                FROM student_enrollments se
                INNER JOIN students s ON se.student_id = s.id
                WHERE $whereClause";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function getCardStats()
    {
        $yearId = $this->academic_year_id ?? 1;

        $stmtS = $this->db->prepare("SELECT 
            COUNT(*) as total, 
            SUM(CASE WHEN s.rfid_uid IS NOT NULL AND TRIM(s.rfid_uid) != '' THEN 1 ELSE 0 END) as paired,
            SUM(CASE WHEN s.rfid_uid IS NULL OR TRIM(s.rfid_uid) = '' THEN 1 ELSE 0 END) as unpaired
        FROM student_enrollments se
        INNER JOIN students s ON se.student_id = s.id
        WHERE se.academic_year_id = ? AND se.status = 'Active' AND s.deleted_at IS NULL");
        $stmtS->execute([$yearId]);
        $studentStats = $stmtS->fetch(PDO::FETCH_ASSOC);

        $teacherStats = $this->db->query("SELECT 
            COUNT(*) as total, 
            SUM(CASE WHEN rfid_uid IS NOT NULL AND TRIM(rfid_uid) != '' THEN 1 ELSE 0 END) as paired,
            SUM(CASE WHEN rfid_uid IS NULL OR TRIM(rfid_uid) = '' THEN 1 ELSE 0 END) as unpaired
        FROM users WHERE role = 'pengajar' AND is_active = 1 AND deleted_at IS NULL")->fetch(PDO::FETCH_ASSOC);

        return [
            'students' => [
                'total' => (int)($studentStats['total'] ?? 0),
                'paired' => (int)($studentStats['paired'] ?? 0),
                'unpaired' => (int)($studentStats['unpaired'] ?? 0),
            ],
            'teachers' => [
                'total' => (int)($teacherStats['total'] ?? 0),
                'paired' => (int)($teacherStats['paired'] ?? 0),
                'unpaired' => (int)($teacherStats['unpaired'] ?? 0),
            ]
        ];
    }

    public function getTeachersList($search = '', $hasCard = null)
    {
        $where = ["u.role = 'pengajar'", "u.is_active = 1", "u.deleted_at IS NULL"];
        $params = [];

        if (!empty($search)) {
            $where[] = "(u.nama LIKE ? OR u.username LIKE ? OR u.rfid_uid LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if ($hasCard === 'yes') {
            $where[] = "(u.rfid_uid IS NOT NULL AND u.rfid_uid != '')";
        } elseif ($hasCard === 'no') {
            $where[] = "(u.rfid_uid IS NULL OR u.rfid_uid = '')";
        }

        $whereClause = implode(" AND ", $where);

        $sql = "SELECT u.id, u.nama, u.username, u.rfid_uid, u.is_active,
                       tp.nip, tp.phone, tp.profile_picture
                FROM users u
                LEFT JOIN teacher_profiles tp ON u.id = tp.user_id
                WHERE $whereClause
                ORDER BY u.nama ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function checkCardAvailability($cardUid, $ignorePersonType = null, $ignorePersonId = null)
    {
        $cardUid = trim($cardUid);
        if (empty($cardUid)) return true;

        // Check students
        $sqlS = "SELECT id, nama FROM students WHERE rfid_uid = ?";
        if ($ignorePersonType === 'student' && $ignorePersonId) {
            $sqlS .= " AND id != " . (int)$ignorePersonId;
        }
        $stmtS = $this->db->prepare($sqlS);
        $stmtS->execute([$cardUid]);
        $student = $stmtS->fetch(PDO::FETCH_ASSOC);
        if ($student) {
            return ['in_use' => true, 'type' => 'student', 'name' => $student['nama']];
        }

        // Check users
        $sqlU = "SELECT id, nama FROM users WHERE rfid_uid = ?";
        if ($ignorePersonType === 'teacher' && $ignorePersonId) {
            $sqlU .= " AND id != " . (int)$ignorePersonId;
        }
        $stmtU = $this->db->prepare($sqlU);
        $stmtU->execute([$cardUid]);
        $teacher = $stmtU->fetch(PDO::FETCH_ASSOC);
        if ($teacher) {
            return ['in_use' => true, 'type' => 'teacher', 'name' => $teacher['nama']];
        }

        return ['in_use' => false];
    }

    public function pairCard($personType, $personId, $cardUid)
    {
        $cardUid = trim($cardUid);
        if (empty($cardUid)) {
            return $this->unpairCard($personType, $personId);
        }

        $check = $this->checkCardAvailability($cardUid, $personType, $personId);
        if ($check['in_use']) {
            throw new \Exception("Kartu UID {$cardUid} sudah digunakan oleh " . ($check['type'] == 'student' ? 'Santri' : 'Pengajar') . ": " . $check['name']);
        }

        if ($personType === 'student') {
            $stmt = $this->db->prepare("UPDATE students SET rfid_uid = ? WHERE id = ?");
            return $stmt->execute([$cardUid, $personId]);
        } elseif ($personType === 'teacher') {
            $stmt = $this->db->prepare("UPDATE users SET rfid_uid = ? WHERE id = ?");
            return $stmt->execute([$cardUid, $personId]);
        }
        return false;
    }

    public function unpairCard($personType, $personId)
    {
        if ($personType === 'student') {
            $stmt = $this->db->prepare("UPDATE students SET rfid_uid = NULL WHERE id = ?");
            return $stmt->execute([$personId]);
        } elseif ($personType === 'teacher') {
            $stmt = $this->db->prepare("UPDATE users SET rfid_uid = NULL WHERE id = ?");
            return $stmt->execute([$personId]);
        }
        return false;
    }

    public function findPersonByCardUid($cardUid)
    {
        $cardUid = trim($cardUid);
        if (empty($cardUid)) return null;

        // Check student
        $stmtS = $this->db->prepare("
            SELECT s.id, s.nis, s.nama, 'student' as person_type, s.rfid_uid, s.gender,
                   CONCAT(COALESCE(k.tingkat, ''), ' ', COALESCE(k.abjad, '')) as info,
                   se.kelas_id
            FROM students s
            LEFT JOIN student_enrollments se ON s.id = se.student_id AND se.academic_year_id = ?
            LEFT JOIN kelas k ON se.kelas_id = k.id
            WHERE s.rfid_uid = ? AND s.deleted_at IS NULL
            LIMIT 1
        ");
        $stmtS->execute([$this->academic_year_id, $cardUid]);
        $student = $stmtS->fetch(PDO::FETCH_ASSOC);
        if ($student) return $student;

        // Check teacher
        $stmtT = $this->db->prepare("
            SELECT u.id, u.username as nis, u.nama, 'teacher' as person_type, u.rfid_uid,
                   CASE WHEN tp.gender = 'Perempuan' THEN 'P' ELSE 'L' END as gender,
                   'Pengajar / Guru' as info,
                   NULL as kelas_id,
                   tp.profile_picture
            FROM users u
            LEFT JOIN teacher_profiles tp ON u.id = tp.user_id
            WHERE u.rfid_uid = ? AND u.deleted_at IS NULL AND u.is_active = 1
            LIMIT 1
        ");
        $stmtT->execute([$cardUid]);
        $teacher = $stmtT->fetch(PDO::FETCH_ASSOC);
        if ($teacher) return $teacher;

        return null;
    }

    public function getAllMasterForSync()
    {
        // 1. All Active Students in Current Academic Year with RFID
        $stmtS = $this->db->prepare("
            SELECT s.id, s.nis, s.nama, 'student' as person_type, s.rfid_uid, s.gender,
                   CONCAT(COALESCE(k.tingkat, ''), ' ', COALESCE(k.abjad, '')) as info,
                   se.kelas_id
            FROM student_enrollments se
            INNER JOIN students s ON se.student_id = s.id
            LEFT JOIN kelas k ON se.kelas_id = k.id
            WHERE se.academic_year_id = ? AND se.status = 'Active' 
              AND s.deleted_at IS NULL AND s.rfid_uid IS NOT NULL AND s.rfid_uid != ''
        ");
        $stmtS->execute([$this->academic_year_id]);
        $students = $stmtS->fetchAll(PDO::FETCH_ASSOC);

        // 2. All Teachers with RFID
        $stmtT = $this->db->query("
            SELECT u.id, u.username as nis, u.nama, 'teacher' as person_type, u.rfid_uid,
                   CASE WHEN tp.gender = 'Perempuan' THEN 'P' ELSE 'L' END as gender,
                   'Pengajar / Guru' as info,
                   NULL as kelas_id,
                   tp.profile_picture
            FROM users u
            LEFT JOIN teacher_profiles tp ON u.id = tp.user_id
            WHERE u.deleted_at IS NULL AND u.is_active = 1 AND u.rfid_uid IS NOT NULL AND u.rfid_uid != ''
        ");
        $teachers = $stmtT->fetchAll(PDO::FETCH_ASSOC);

        return [
            'students' => $students,
            'teachers' => $teachers,
            'total_registered_cards' => count($students) + count($teachers)
        ];
    }
}
