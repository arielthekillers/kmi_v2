<?php

namespace App\Controllers;

use App\Core\Controller;

class ActivityLogController extends Controller {

    public function index() {
        require_admin();
        
        $search = $_GET['q'] ?? '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 50;
        $offset = ($page - 1) * $limit;
        
        $db = \App\Core\Database::getInstance()->getConnection();
        
        $countSql = "SELECT COUNT(*) FROM activity_logs";
        $selectSql = "SELECT * FROM activity_logs";
        $params = [];
        
        if (!empty($search)) {
            $where = " WHERE username LIKE ? OR nama LIKE ? OR action LIKE ? OR role LIKE ? OR ip_address LIKE ?";
            $countSql .= $where;
            $selectSql .= $where;
            $searchTerm = "%{$search}%";
            $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm];
        }
        
        $selectSql .= " ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
        
        // Count total rows
        $stmtCount = $db->prepare($countSql);
        $stmtCount->execute($params);
        $totalData = (int)$stmtCount->fetchColumn();
        
        // Fetch rows
        $stmtSelect = $db->prepare($selectSql);
        $stmtSelect->execute($params);
        $logs = $stmtSelect->fetchAll(\PDO::FETCH_ASSOC);
        
        $totalPages = ceil($totalData / $limit);
        $page = max(1, min($page, max(1, $totalPages)));
        
        $this->view('activity_logs/index', [
            'title' => 'Log Aktivitas',
            'logs' => $logs,
            'q' => $search,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalData' => $totalData,
            'offset' => $offset,
            'perPage' => $limit
        ]);
    }
}
