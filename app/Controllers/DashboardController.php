<?php

namespace App\Controllers;

use App\Core\Controller;

class DashboardController extends Controller {
    public function index() {
        // Just include the legacy dashboard file which is now in root
        // Since this class is in app/Controllers, we go up two levels
        require __DIR__ . '/../Views/dashboard.php';
    }
}
