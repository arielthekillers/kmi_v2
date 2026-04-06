<?php

namespace App\Modules\TeachingLogs\Controllers;

use App\Core\Controller;
use App\Core\Database;

class TeachingLogController extends Controller {

    public function index() {
        $db = Database::getInstance();
        
        $date = $_GET['date'] ?? date('Y-m-d');

        // Fetch Logs for the date
        $sql = "SELECT tl.*, 
                       k.tingkat, k.abjad, 
                       t.nama as teacher_name, 
                       v.nama as verifier_name
                FROM teaching_logs tl
                JOIN kelas k ON tl.kelas_id = k.id
                LEFT JOIN users t ON tl.teacher_id = t.id
                LEFT JOIN users v ON tl.verifier_id = v.id
                WHERE tl.date = ?
                ORDER BY tl.hour ASC, k.tingkat ASC, k.abjad ASC";
        
        $logs = $db->query($sql, [$date])->fetchAll();

        $this->view('TeachingLogs/Views/index', [
            'logs' => $logs,
            'selectedDate' => $date
        ]);
    }
}
