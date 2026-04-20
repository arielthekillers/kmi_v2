<?php

namespace App\Core;

class Controller {
    protected $currentYear;

    public function __construct() {
        $yearModel = new \App\Models\AcademicYearModel();
        $this->currentYear = $yearModel->getActive();
    }

    public function view($view, $data = []) {
        $data['currentYear'] = $this->currentYear;
        extract($data);
        // Assuming views are in specific module folders or a shared view folder
        // For this refactor, let's support absolute path or relative to Module
        
        // This is a simple implementation. 
        // We will refine how we find view files.
        // E.g. view("Auth/Views/login")
        
        // Check Modules first
        $viewPath = __DIR__ . '/../Modules/' . $view . '.php';
        
        if (!file_exists($viewPath)) {
            // Check global Views
            $viewPath = __DIR__ . '/../Views/' . $view . '.php';
        }
        
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            echo "View not found: " . $viewPath;
        }
    }

    public function redirect($url) {
        // Use the global url() helper if available, otherwise just use header
        if (function_exists('url')) {
            header("Location: " . url($url));
        } else {
            header("Location: " . $url);
        }
        exit;
    }
}
