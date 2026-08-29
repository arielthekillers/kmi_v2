<?php

namespace App\Modules\StudentDevelopment\Models;

use App\Core\Database;
use PDO;

class DevelopmentModel {
    protected $db;
    protected $academic_year_id;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $year = $this->db->query("SELECT id FROM academic_years WHERE is_active = 1 LIMIT 1")->fetch();
        $this->academic_year_id = $year ? (int)$year['id'] : null;
    }

    public function getAcademicYearId() {
        return $this->academic_year_id;
    }

    /**
     * Get list of categories
     */
    public function getCategories() {
        return $this->db->query("SELECT * FROM student_observation_categories ORDER BY CASE WHEN name = 'Lainnya' THEN 1 ELSE 0 END ASC, name ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCategoryById($id) {
        $stmt = $this->db->prepare("SELECT * FROM student_observation_categories WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function storeCategory($data) {
        $stmt = $this->db->prepare("INSERT INTO student_observation_categories (name, description, color) VALUES (?, ?, ?)");
        return $stmt->execute([$data['name'], $data['description'] ?? null, $data['color'] ?? '#64748b']);
    }

    public function updateCategory($id, $data) {
        $stmt = $this->db->prepare("UPDATE student_observation_categories SET name = ?, description = ?, color = ? WHERE id = ?");
        return $stmt->execute([$data['name'], $data['description'] ?? null, $data['color'] ?? '#64748b', $id]);
    }

    public function deleteCategory($id) {
        $stmt = $this->db->prepare("DELETE FROM student_observation_categories WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Get list of students for select option (active only)
     */
    public function getStudentsForSelect() {
        $sql = "SELECT s.id, s.nama, s.nis, se.kelas_id, k.tingkat, k.abjad 
                FROM students s
                INNER JOIN student_enrollments se ON s.id = se.student_id
                LEFT JOIN kelas k ON se.kelas_id = k.id
                WHERE se.academic_year_id = ? AND se.status = 'Active' AND s.deleted_at IS NULL
                ORDER BY k.tingkat ASC, k.abjad ASC, s.nama ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->academic_year_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get active classes with student counts
     */
    public function getClassesWithStats() {
        $sql = "SELECT k.id, k.tingkat, k.abjad, k.teacher_id, u.nama as wali_kelas,
                (SELECT COUNT(*) FROM student_enrollments se JOIN students s ON se.student_id = s.id 
                 WHERE se.kelas_id = k.id AND se.status = 'Active' AND s.deleted_at IS NULL) as total_students,
                (SELECT COUNT(*) FROM student_observations so WHERE so.kelas_id = k.id AND so.academic_year_id = ?) as total_observations,
                (SELECT COUNT(*) FROM student_observations so WHERE so.kelas_id = k.id AND so.academic_year_id = ? AND so.type = 'Positif') as total_positif,
                (SELECT COUNT(*) FROM student_observations so WHERE so.kelas_id = k.id AND so.academic_year_id = ? AND so.type = 'Perhatian') as total_perhatian,
                (SELECT COUNT(*) FROM student_observations so WHERE so.kelas_id = k.id AND so.academic_year_id = ? AND so.type = 'Informasi') as total_informasi
                FROM kelas k
                LEFT JOIN users u ON k.teacher_id = u.id
                WHERE k.academic_year_id = ?
                ORDER BY k.tingkat ASC, k.abjad ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->academic_year_id, $this->academic_year_id, $this->academic_year_id, $this->academic_year_id, $this->academic_year_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get students in a specific class with summary statistics of observations
     */
    public function getClassStudentsWithStats($kelasId) {
        $sql = "SELECT s.id, s.nama, s.nis, s.gender,
                       SUM(CASE WHEN so.type = 'Positif' THEN 1 ELSE 0 END) as count_positif,
                       SUM(CASE WHEN so.type = 'Perhatian' THEN 1 ELSE 0 END) as count_perhatian,
                       SUM(CASE WHEN so.type = 'Informasi' THEN 1 ELSE 0 END) as count_informasi
                FROM students s
                INNER JOIN student_enrollments se ON s.id = se.student_id
                LEFT JOIN student_observations so ON s.id = so.student_id AND so.academic_year_id = ?
                WHERE se.kelas_id = ? AND se.academic_year_id = ? AND se.status = 'Active' AND s.deleted_at IS NULL
                GROUP BY s.id, s.nama, s.nis, s.gender
                ORDER BY s.nama ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->academic_year_id, $kelasId, $this->academic_year_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get observations with filters
     */
    public function getObservations($filters = [], $limit = 10, $offset = 0) {
        $params = [];
        $whereClauses = ["so.academic_year_id = ?"];
        $params[] = $this->academic_year_id;

        if (!empty($filters['teacher_id'])) {
            $whereClauses[] = "so.teacher_id = ?";
            $params[] = $filters['teacher_id'];
        }

        if (!empty($filters['kelas_id'])) {
            $whereClauses[] = "so.kelas_id = ?";
            $params[] = $filters['kelas_id'];
        }

        if (!empty($filters['student_id'])) {
            $whereClauses[] = "so.student_id = ?";
            $params[] = $filters['student_id'];
        }

        if (!empty($filters['type'])) {
            $whereClauses[] = "so.type = ?";
            $params[] = $filters['type'];
        }

        if (!empty($filters['category_id'])) {
            $whereClauses[] = "so.category_id = ?";
            $params[] = $filters['category_id'];
        }

        if (!empty($filters['date_from'])) {
            $whereClauses[] = "so.observation_date >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $whereClauses[] = "so.observation_date <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['q'])) {
            $whereClauses[] = "(s.nama LIKE ? OR so.content LIKE ?)";
            $params[] = '%' . $filters['q'] . '%';
            $params[] = '%' . $filters['q'] . '%';
        }

        $where = implode(" AND ", $whereClauses);

        $sql = "SELECT so.*, s.nama as student_name, s.nis as student_nis, 
                       u.nama as teacher_name, c.name as category_name, c.color as category_color,
                       k.tingkat, k.abjad, sub.nama as subject_name,
                       (SELECT COUNT(*) FROM student_observation_responses sor WHERE sor.observation_id = so.id) as response_count
                FROM student_observations so
                LEFT JOIN students s ON so.student_id = s.id
                INNER JOIN users u ON so.teacher_id = u.id
                INNER JOIN student_observation_categories c ON so.category_id = c.id
                LEFT JOIN kelas k ON so.kelas_id = k.id
                LEFT JOIN subjects sub ON so.subject_id = sub.id
                WHERE $where
                ORDER BY so.observation_date DESC, so.created_at DESC";

        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = (int)$limit;
            $params[] = (int)$offset;
        }

        $stmt = $this->db->prepare($sql);
        // Explicitly bind limit and offset as integers if emulator is off, 
        // but PDO execution is fine with standard execute() in standard CI wrappers
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countObservations($filters = []) {
        $params = [];
        $whereClauses = ["so.academic_year_id = ?"];
        $params[] = $this->academic_year_id;

        if (!empty($filters['teacher_id'])) {
            $whereClauses[] = "so.teacher_id = ?";
            $params[] = $filters['teacher_id'];
        }

        if (!empty($filters['kelas_id'])) {
            $whereClauses[] = "so.kelas_id = ?";
            $params[] = $filters['kelas_id'];
        }

        if (!empty($filters['student_id'])) {
            $whereClauses[] = "so.student_id = ?";
            $params[] = $filters['student_id'];
        }

        if (!empty($filters['type'])) {
            $whereClauses[] = "so.type = ?";
            $params[] = $filters['type'];
        }

        if (!empty($filters['category_id'])) {
            $whereClauses[] = "so.category_id = ?";
            $params[] = $filters['category_id'];
        }

        if (!empty($filters['date_from'])) {
            $whereClauses[] = "so.observation_date >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $whereClauses[] = "so.observation_date <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['q'])) {
            $whereClauses[] = "(s.nama LIKE ? OR so.content LIKE ?)";
            $params[] = '%' . $filters['q'] . '%';
            $params[] = '%' . $filters['q'] . '%';
        }

        $where = implode(" AND ", $whereClauses);

        $sql = "SELECT COUNT(*) FROM student_observations so
                LEFT JOIN students s ON so.student_id = s.id
                WHERE $where";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function getObservationById($id) {
        $sql = "SELECT so.*, s.nama as student_name, s.nis as student_nis, 
                       u.nama as teacher_name, c.name as category_name, c.color as category_color,
                       k.tingkat, k.abjad, sub.nama as subject_name
                FROM student_observations so
                LEFT JOIN students s ON so.student_id = s.id
                INNER JOIN users u ON so.teacher_id = u.id
                INNER JOIN student_observation_categories c ON so.category_id = c.id
                LEFT JOIN kelas k ON so.kelas_id = k.id
                LEFT JOIN subjects sub ON so.subject_id = sub.id
                WHERE so.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getResponsesForObservation($observationId) {
        $sql = "SELECT sor.*, u.nama as user_name, u.role as user_role
                FROM student_observation_responses sor
                INNER JOIN users u ON sor.user_id = u.id
                WHERE sor.observation_id = ?
                ORDER BY sor.created_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$observationId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function storeObservation($data) {
        // Find student class if not explicitly passed (only if student is specified)
        if (empty($data['kelas_id']) && !empty($data['student_id'])) {
            $stmt = $this->db->prepare("SELECT kelas_id FROM student_enrollments WHERE student_id = ? AND academic_year_id = ? AND status = 'Active' LIMIT 1");
            $stmt->execute([$data['student_id'], $this->academic_year_id]);
            $enrollment = $stmt->fetch();
            $data['kelas_id'] = $enrollment ? $enrollment['kelas_id'] : null;
        }

        $sql = "INSERT INTO student_observations (student_id, teacher_id, type, category_id, content, context, kelas_id, subject_id, academic_year_id, observation_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $data['student_id'],
            $data['teacher_id'],
            $data['type'],
            $data['category_id'],
            $data['content'],
            $data['context'] ?? null,
            $data['kelas_id'] ?? null,
            $data['subject_id'] ?? null,
            $this->academic_year_id,
            $data['observation_date'] ?? date('Y-m-d')
        ]);
        return $result ? $this->db->lastInsertId() : false;
    }

    public function updateObservation($id, $data) {
        // Find student class if not explicitly passed (only if student is specified)
        if (empty($data['kelas_id']) && !empty($data['student_id'])) {
            $stmt = $this->db->prepare("SELECT kelas_id FROM student_enrollments WHERE student_id = ? AND academic_year_id = ? AND status = 'Active' LIMIT 1");
            $stmt->execute([$data['student_id'], $this->academic_year_id]);
            $enrollment = $stmt->fetch();
            $data['kelas_id'] = $enrollment ? $enrollment['kelas_id'] : null;
        }

        $sql = "UPDATE student_observations SET 
                student_id = ?, 
                type = ?, 
                category_id = ?, 
                content = ?, 
                context = ?, 
                kelas_id = ?, 
                subject_id = ?, 
                observation_date = ? 
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['student_id'] ?: null,
            $data['type'],
            $data['category_id'],
            $data['content'],
            $data['context'] ?? null,
            $data['kelas_id'] ?? null,
            $data['subject_id'] ?? null,
            $data['observation_date'],
            $id
        ]);
    }

    public function updateContext($id, $context) {
        $stmt = $this->db->prepare("UPDATE student_observations SET context = ? WHERE id = ?");
        return $stmt->execute([$context, $id]);
    }

    public function addResponse($observationId, $userId, $content) {
        $stmt = $this->db->prepare("INSERT INTO student_observation_responses (observation_id, user_id, content) VALUES (?, ?, ?)");
        return $stmt->execute([$observationId, $userId, $content]);
    }

    public function getStudentProfile($studentId) {
        $sql = "SELECT s.*, k.tingkat, k.abjad, k.id as kelas_id, u.nama as wali_kelas
                FROM students s
                INNER JOIN student_enrollments se ON s.id = se.student_id
                LEFT JOIN kelas k ON se.kelas_id = k.id
                LEFT JOIN users u ON k.teacher_id = u.id
                WHERE s.id = ? AND se.academic_year_id = ? AND s.deleted_at IS NULL LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$studentId, $this->academic_year_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getStudentStats($studentId) {
        $sql = "SELECT 
                SUM(CASE WHEN type = 'Positif' THEN 1 ELSE 0 END) as count_positif,
                SUM(CASE WHEN type = 'Perhatian' THEN 1 ELSE 0 END) as count_perhatian,
                SUM(CASE WHEN type = 'Informasi' THEN 1 ELSE 0 END) as count_informasi
                FROM student_observations
                WHERE student_id = ? AND academic_year_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$studentId, $this->academic_year_id]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'positif' => (int)($stats['count_positif'] ?? 0),
            'perhatian' => (int)($stats['count_perhatian'] ?? 0),
            'informasi' => (int)($stats['count_informasi'] ?? 0),
            'total' => (int)($stats['count_positif'] ?? 0) + (int)($stats['count_perhatian'] ?? 0) + (int)($stats['count_informasi'] ?? 0)
        ];
    }

    public function getStudentCategoryDistribution($studentId) {
        $sql = "SELECT c.name as category, COUNT(so.id) as count
                FROM student_observation_categories c
                LEFT JOIN student_observations so ON so.category_id = c.id AND so.student_id = ? AND so.academic_year_id = ?
                GROUP BY c.id
                ORDER BY count DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$studentId, $this->academic_year_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStudentTimeline($studentId) {
        $sql = "SELECT so.*, u.nama as teacher_name, u.role as teacher_role, 
                       c.name as category_name, c.color as category_color, sub.nama as subject_name
                FROM student_observations so
                INNER JOIN users u ON so.teacher_id = u.id
                INNER JOIN student_observation_categories c ON so.category_id = c.id
                LEFT JOIN subjects sub ON so.subject_id = sub.id
                WHERE so.student_id = ? AND so.academic_year_id = ?
                ORDER BY so.observation_date DESC, so.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$studentId, $this->academic_year_id]);
        $observations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($observations)) {
            $obsIds = array_column($observations, 'id');
            $inClause = implode(',', array_fill(0, count($obsIds), '?'));
            
            $respSql = "SELECT sor.*, u.nama as user_name, u.role as user_role
                        FROM student_observation_responses sor
                        INNER JOIN users u ON sor.user_id = u.id
                        WHERE sor.observation_id IN ($inClause)
                        ORDER BY sor.created_at ASC";
            $respStmt = $this->db->prepare($respSql);
            $respStmt->execute($obsIds);
            $responses = $respStmt->fetchAll(PDO::FETCH_ASSOC);

            $groupedResponses = [];
            foreach ($responses as $resp) {
                $groupedResponses[$resp['observation_id']][] = $resp;
            }

            foreach ($observations as &$obs) {
                $obs['responses'] = $groupedResponses[$obs['id']] ?? [];
            }
        }
        return $observations;
    }

    /**
     * Guess current subject & class for the teacher based on academic schedule
     */
    public function getContextualSchedule($teacherId) {
        $days = [
            'Sun' => 'Ahad', 'Mon' => 'Senin', 'Tue' => 'Selasa',
            'Wed' => 'Rabu', 'Thu' => 'Kamis', 'Fri' => 'Jumat', 'Sat' => 'Sabtu'
        ];
        $currentDayName = $days[date('D')];
        
        // Find all schedules for today
        $sql = "SELECT s.kelas_id, s.subject_id, s.hour, k.tingkat, k.abjad, sub.nama as subject_name
                FROM schedules s
                INNER JOIN kelas k ON s.kelas_id = k.id
                INNER JOIN subjects sub ON s.subject_id = sub.id
                WHERE s.teacher_id = ? AND s.day = ? AND s.academic_year_id = ?
                ORDER BY s.hour ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$teacherId, $currentDayName, $this->academic_year_id]);
        $todaySchedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($todaySchedules)) {
            return null;
        }

        // Try to match the current hour to a schedule hour config or just return the first/recent one.
        // For simplicity, we return the schedules for today so the UI can let the user pick quickly,
        // or pre-fill with the first one.
        return $todaySchedules;
    }

    /**
     * Get subjects list for a specific class in the active academic year
     */
    public function getSubjectsByClass($kelasId) {
        $sql = "SELECT DISTINCT sub.id, sub.nama 
                FROM schedules s 
                INNER JOIN subjects sub ON s.subject_id = sub.id 
                WHERE s.kelas_id = ? AND s.academic_year_id = ? 
                ORDER BY sub.nama ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$kelasId, $this->academic_year_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteObservation($id) {
        $stmt = $this->db->prepare("DELETE FROM student_observation_responses WHERE observation_id = ?");
        $stmt->execute([$id]);
        
        $stmt2 = $this->db->prepare("DELETE FROM student_observations WHERE id = ?");
        return $stmt2->execute([$id]);
    }
}
