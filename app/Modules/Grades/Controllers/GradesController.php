<?php

namespace App\Modules\Grades\Controllers;

use App\Core\Controller;
use App\Core\Database;

class GradesController extends Controller {

    public function index() {
        $db = Database::getInstance();
        
        // 1. Get Filters
        $kelasId = $_GET['kelas_id'] ?? null;
        $subjectId = $_GET['subject_id'] ?? null;

        // 2. Fetch Options for Dropdowns
        $classes = $db->query("SELECT * FROM kelas ORDER BY tingkat, abjad")->fetchAll();
        $subjects = $db->query("SELECT * FROM subjects ORDER BY nama")->fetchAll();

        $rows = [];
        $classInfo = null;
        $subjectInfo = null;

        if ($kelasId && $subjectId) {
            // Fetch Class & Subject Details
            $classInfo = $db->query("SELECT * FROM kelas WHERE id = ?", [$kelasId])->fetch();
            $subjectInfo = $db->query("SELECT * FROM subjects WHERE id = ?", [$subjectId])->fetch();

            // Fetch Students and Grades
            // Left Join Grades to show empty if missing
            $sql = "SELECT s.id as student_id, s.nis, s.nama, 
                           g.score_raw, g.score_final
                    FROM students s
                    LEFT JOIN grades g ON s.id = g.student_id AND g.subject_id = ?
                    WHERE s.kelas_id = ?
                    ORDER BY s.nama ASC";
            
            $rows = $db->query($sql, [$subjectId, $kelasId])->fetchAll();
        }

        $this->view('Grades/Views/index', [
            'classes' => $classes,
            'subjects' => $subjects,
            'selectedClass' => $kelasId,
            'selectedSubject' => $subjectId,
            'rows' => $rows,
            'classInfo' => $classInfo,
            'subjectInfo' => $subjectInfo
        ]);
    }
}
