<?php

namespace App\Modules\Duties\Controllers;

use App\Core\Controller;
use App\Core\Database;

class DutiesController extends Controller {

    public function index() {
        $db = Database::getInstance();
        
        // Fetch All Duties joined with Users and Profiles for HP
        $sql = "SELECT d.*, u.nama, tp.phone as hp 
                FROM duties d 
                JOIN users u ON d.user_id = u.id 
                LEFT JOIN teacher_profiles tp ON u.id = tp.user_id
                ORDER BY d.id ASC";
        
        $rows = $db->query($sql)->fetchAll();

        // Organize by Day -> Type
        $schedule = [
            'Sabtu' => [], 'Ahad' => [], 'Senin' => [], 
            'Selasa' => [], 'Rabu' => [], 'Kamis' => [], 'Jumat' => []
        ];

        foreach ($rows as $row) {
            $day = $row['day'];
            $type = $row['type']; // 'diwan' or 'keliling'
            
            if (!isset($schedule[$day][$type])) {
                $schedule[$day][$type] = [];
            }
            $schedule[$day][$type][] = $row;
        }

        $this->view('Duties/Views/index', [
            'schedule' => $schedule
        ]);
    }
}
