<?php

namespace App\Modules\Duties\Controllers;

use App\Core\Controller;
use App\Core\Database;

class DutiesController extends Controller {

    public function index() {
        $db = Database::getInstance();
        
        // Fetch All Duties joined with Users
        $sql = "SELECT d.*, u.nama, u.hp 
                FROM duties d 
                JOIN users u ON d.user_id = u.id 
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
