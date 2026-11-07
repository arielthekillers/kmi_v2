<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class SmartTapPermissionModel extends Model
{
    protected $table = 'smart_tap_permissions';

    public function getPermissions($filters = [])
    {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['person_type'])) {
            $where[] = "p.person_type = ?";
            $params[] = $filters['person_type'];
        }

        if (!empty($filters['date'])) {
            $where[] = "? BETWEEN p.start_date AND p.end_date";
            $params[] = $filters['date'];
        }

        if (!empty($filters['permission_type'])) {
            $where[] = "p.permission_type = ?";
            $params[] = $filters['permission_type'];
        }

        $whereClause = implode(" AND ", $where);

        $sql = "SELECT p.*,
                CASE 
                    WHEN p.person_type = 'student' THEN s.nama
                    WHEN p.person_type = 'teacher' THEN u.nama
                    ELSE '-'
                END as person_name,
                CASE 
                    WHEN p.person_type = 'student' THEN CONCAT(COALESCE(k.tingkat, ''), ' ', COALESCE(k.abjad, ''))
                    WHEN p.person_type = 'teacher' THEN 'Pengajar / Guru'
                    ELSE '-'
                END as person_subinfo,
                CASE 
                    WHEN p.person_type = 'student' THEN s.nis
                    WHEN p.person_type = 'teacher' THEN u.username
                    ELSE '-'
                END as person_identifier
                FROM smart_tap_permissions p
                LEFT JOIN students s ON p.person_type = 'student' AND p.person_id = s.id
                LEFT JOIN student_enrollments se ON s.id = se.student_id AND se.academic_year_id = ?
                LEFT JOIN kelas k ON se.kelas_id = k.id
                LEFT JOIN users u ON p.person_type = 'teacher' AND p.person_id = u.id
                WHERE $whereClause
                ORDER BY p.id DESC";

        $execParams = array_merge([$this->academic_year_id], $params);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($execParams);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getActivePermissionsForDate($dateStr)
    {
        $sql = "SELECT p.*, 
                CASE 
                    WHEN p.person_type = 'student' THEN s.nama
                    WHEN p.person_type = 'teacher' THEN u.nama
                END as person_name
                FROM smart_tap_permissions p
                LEFT JOIN students s ON p.person_type = 'student' AND p.person_id = s.id
                LEFT JOIN users u ON p.person_type = 'teacher' AND p.person_id = u.id
                WHERE ? BETWEEN p.start_date AND p.end_date";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$dateStr]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createPermission($data)
    {
        $sql = "INSERT INTO smart_tap_permissions (
            person_type, person_id, permission_type, start_date, end_date, event_ids, reason, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['person_type'],
            $data['person_id'],
            $data['permission_type'],
            $data['start_date'],
            $data['end_date'],
            !empty($data['event_ids']) ? (is_array($data['event_ids']) ? implode(',', $data['event_ids']) : $data['event_ids']) : null,
            $data['reason'] ?? null,
            $data['created_by'] ?? null
        ]);
        return $this->db->lastInsertId();
    }

    public function getPermissionById($id)
    {
        $sql = "SELECT p.*,
                CASE 
                    WHEN p.person_type = 'student' THEN s.nama
                    WHEN p.person_type = 'teacher' THEN u.nama
                    ELSE '-'
                END as person_name,
                CASE 
                    WHEN p.person_type = 'student' THEN CONCAT(COALESCE(k.tingkat, ''), ' ', COALESCE(k.abjad, ''))
                    WHEN p.person_type = 'teacher' THEN 'Pengajar / Guru'
                    ELSE '-'
                END as person_subinfo,
                CASE 
                    WHEN p.person_type = 'student' THEN s.nis
                    WHEN p.person_type = 'teacher' THEN u.username
                    ELSE '-'
                END as person_identifier
                FROM smart_tap_permissions p
                LEFT JOIN students s ON p.person_type = 'student' AND p.person_id = s.id
                LEFT JOIN student_enrollments se ON s.id = se.student_id AND se.academic_year_id = ?
                LEFT JOIN kelas k ON se.kelas_id = k.id
                LEFT JOIN users u ON p.person_type = 'teacher' AND p.person_id = u.id
                WHERE p.id = ?
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->academic_year_id, (int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updatePermission($id, $data)
    {
        $sql = "UPDATE smart_tap_permissions SET
                    person_type = ?,
                    person_id = ?,
                    permission_type = ?,
                    start_date = ?,
                    end_date = ?,
                    event_ids = ?,
                    reason = ?
                WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['person_type'],
            $data['person_id'],
            $data['permission_type'],
            $data['start_date'],
            $data['end_date'],
            !empty($data['event_ids']) ? (is_array($data['event_ids']) ? implode(',', $data['event_ids']) : $data['event_ids']) : null,
            $data['reason'] ?? null,
            (int)$id
        ]);
    }

    public function deletePermission($id)
    {
        $stmt = $this->db->prepare("DELETE FROM smart_tap_permissions WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
