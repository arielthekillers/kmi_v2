<?php

namespace App\Controllers;

use App\Core\Database;

class MessagingController
{
    public function index()
    {
        require_admin(); // Secure the settings menu

        $db = Database::getInstance();
        $settingModel = new \App\Models\SettingModel();
        $sendMethod = $settingModel->get('wa_send_method', 'direct');
        
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 50;
        $offset = ($page - 1) * $limit;
        
        $q = $_GET['q'] ?? '';
        $status = $_GET['status'] ?? '';
        
        $where = [];
        $params = [];
        
        if ($q !== '') {
            $where[] = "(wq.recipient_number LIKE ? OR wq.message LIKE ? OR u.nama LIKE ?)";
            $params[] = "%$q%";
            $params[] = "%$q%";
            $params[] = "%$q%";
        }
        
        if ($status !== '') {
            $where[] = "wq.status = ?";
            $params[] = $status;
        }
        
        $whereSql = '';
        if (!empty($where)) {
            $whereSql = "WHERE " . implode(' AND ', $where);
        }

        // Get total for pagination first
        $totalStmt = $db->query("
            SELECT COUNT(*) as count 
            FROM whatsapp_queues wq
            LEFT JOIN users u ON u.username = wq.recipient_number OR u.username = CONCAT('0', SUBSTRING(wq.recipient_number, 3))
            $whereSql
        ", $params);
        $total = $totalStmt->fetch()['count'];
        $totalPages = max(1, (int)ceil($total / $limit));

        // Adjust page and offset if requested page is greater than available pages
        if ($page > $totalPages) {
            $page = $totalPages;
            $offset = ($page - 1) * $limit;
        }

        // Fetch queued messages
        $stmt = $db->query("
            SELECT wq.*, u.nama as recipient_name, u.id as recipient_user_id 
            FROM whatsapp_queues wq 
            LEFT JOIN users u ON u.username = wq.recipient_number OR u.username = CONCAT('0', SUBSTRING(wq.recipient_number, 3))
            $whereSql 
            ORDER BY wq.id DESC 
            LIMIT $limit OFFSET $offset
        ", $params);
        $messages = $stmt->fetchAll();

        require_once __DIR__ . '/../Views/settings/messaging.php';
    }

    public function searchUsers()
    {
        require_admin();
        header('Content-Type: application/json');

        $query = $_GET['q'] ?? '';
        $query = ltrim($query, '@'); // Strip @ prefix if user types it
        
        $db = Database::getInstance();
        
        // Search by username (phone number) or name
        $stmt = $db->query("SELECT username as id, nama, role FROM users WHERE (nama LIKE ? OR username LIKE ?) AND deleted_at IS NULL LIMIT 20", ["%$query%", "%$query%"]);
        $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        echo json_encode($users);
    }

    public function sendManualMessage()
    {
        require_admin();
        csrf_validate_token();

        $recipients = $_POST['recipients'] ?? []; // Array from Tom Select
        $message = $_POST['message'] ?? '';
        $everyone = $_POST['everyone'] ?? '0';

        $settingModel = new \App\Models\SettingModel();
        $sendMethod = $settingModel->get('wa_send_method', 'direct');

        if ($sendMethod === 'direct') {
            if ($everyone === '1') {
                add_flash("Broadcast ke semua pengguna dinonaktifkan pada mode Direct Send.", "error");
                header('Location: ' . url('/settings/messaging'));
                exit;
            }
            if (is_array($recipients) && count($recipients) > 1) {
                add_flash("Pengiriman ke lebih dari 1 orang tidak diizinkan pada mode Direct Send.", "error");
                header('Location: ' . url('/settings/messaging'));
                exit;
            }
        }

        if (empty($message)) {
            add_flash("Isi pesan tidak boleh kosong", "error");
            header('Location: ' . url('/settings/messaging'));
            exit;
        }

        $db = Database::getInstance();
        $count = 0;

        if ($everyone === '1') {
            // Queue for everyone
            $stmt = $db->query("SELECT username FROM users WHERE deleted_at IS NULL");
            $allUsers = $stmt->fetchAll();
            
            foreach ($allUsers as $u) {
                // Validate if it is numeric and looks like a phone number
                $phone = preg_replace('/[^0-9]/', '', $u['username']);
                if (strlen($phone) >= 9) {
                    if (substr($phone, 0, 1) === '0') {
                        $phone = '62' . substr($phone, 1);
                    }
                    if (queue_whatsapp_message($phone, $message, 'Admin')) {
                        $count++;
                    }
                }
            }
        } else {
            // Specific recipients
            if (!empty($recipients)) {
                if (!is_array($recipients)) {
                    $recipients = explode(',', $recipients);
                }
                foreach ($recipients as $recipient) {
                    $phone = preg_replace('/[^0-9]/', '', $recipient);
                    if (strlen($phone) >= 9) {
                        if (substr($phone, 0, 1) === '0') {
                            $phone = '62' . substr($phone, 1);
                        }
                        if (queue_whatsapp_message($phone, $message, 'Admin')) {
                            $count++;
                        }
                    }
                }
            }
        }

        if ($count > 0) {
            add_flash("Berhasil memasukkan $count pesan ke antrean.", "success");
        } else {
            add_flash("Tidak ada pesan yang dimasukkan ke antrean. Pastikan nomor tujuan valid.", "error");
        }

        header('Location: ' . url('/settings/messaging'));
        exit;
    }

    public function deleteMessage()
    {
        require_admin();
        $db = Database::getInstance();
        
        $id = $_POST['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
            return;
        }

        $stmt = $db->query("DELETE FROM whatsapp_queues WHERE id = ?", [$id]);
        
        header('Content-Type: application/json');
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Pesan berhasil dihapus dari antrean']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menghapus pesan atau pesan tidak ditemukan']);
        }
    }

    public function bulkDelete()
    {
        require_admin();
        $db = Database::getInstance();
        
        $ids = $_POST['ids'] ?? [];
        if (empty($ids) || !is_array($ids)) {
            echo json_encode(['success' => false, 'message' => 'Tidak ada pesan yang dipilih']);
            return;
        }

        // Create placeholders for the IN clause
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        
        $stmt = $db->query("DELETE FROM whatsapp_queues WHERE id IN ($placeholders)", $ids);
        
        header('Content-Type: application/json');
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => $stmt->rowCount() . ' pesan berhasil dihapus']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menghapus pesan']);
        }
    }

    public function bulkResend()
    {
        require_admin();
        $db = Database::getInstance();
        
        $ids = $_POST['ids'] ?? [];
        if (empty($ids) || !is_array($ids)) {
            echo json_encode(['success' => false, 'message' => 'Tidak ada pesan yang dipilih']);
            return;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        
        // Reset status to 'pending'
        $stmt = $db->query("UPDATE whatsapp_queues SET status = 'pending', response = NULL WHERE id IN ($placeholders)", $ids);
        
        header('Content-Type: application/json');
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => $stmt->rowCount() . ' pesan berhasil diubah menjadi antrean (pending)']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal mengubah status pesan (mungkin pesan sudah berstatus pending)']);
        }
    }

    public function getStatuses()
    {
        // Require admin access, but return JSON instead of redirecting
        if (!is_logged_in() || auth_get_role() !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $db = Database::getInstance();
        $ids = $_POST['ids'] ?? [];
        if (empty($ids) || !is_array($ids)) {
            echo json_encode(['success' => true, 'data' => []]);
            return;
        }

        // Filter valid integers
        $ids = array_filter($ids, 'is_numeric');
        if (empty($ids)) {
            echo json_encode(['success' => true, 'data' => []]);
            return;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $db->query("SELECT id, status, response FROM whatsapp_queues WHERE id IN ($placeholders)", $ids);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $data = [];
        foreach ($results as $row) {
            $data[$row['id']] = [
                'status' => $row['status'],
                'response' => $row['response']
            ];
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $data]);
    }
}
