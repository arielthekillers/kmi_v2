<?php
namespace App\Controllers;

use App\Core\Database;

class WhatsappWorkerController {
    
    public function processQueue() {
        // This could be run via cronjob by hitting /whatsapp/process or from CLI if router supports it
        
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        // 1. Auto-cleanup: Delete sent messages older than 7 days
        $conn->exec("DELETE FROM whatsapp_queues WHERE status = 'sent' AND sent_at < DATE_SUB(NOW(), INTERVAL 7 DAY)");
        
        // Fetch up to 20 pending messages
        $stmt = $conn->query("SELECT id, recipient_number, message, retry_count FROM whatsapp_queues WHERE status = 'pending' ORDER BY created_at ASC LIMIT 20");
        $messages = $stmt->fetchAll();
        
        if (empty($messages)) {
            echo "No pending messages.\n";
            return;
        }
        
        $apiUrl = getenv('RUANGWA_URL') ?: 'https://app.ruangwa.id/api/send_message';
        $checkUrl = 'https://app.ruangwa.id/api/check_number';
        $token = getenv('RUANGWA_TOKEN') ?: getenv('RUANGWA_API_KEY');
        
        if (empty($token)) {
            echo "Ruang WA credentials not configured in .env.\n";
            return;
        }
        
        $successCount = 0;
        $failedCount = 0;
        
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        
        $msgCount = count($messages);
        $i = 0;

        foreach ($messages as $msg) {
            $i++;

            // Check if number is registered on WhatsApp
            $checkPayload = [
                'token' => $token,
                'number' => $msg['recipient_number']
            ];
            
            $chCheck = curl_init($checkUrl);
            curl_setopt($chCheck, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($chCheck, CURLOPT_POST, true);
            curl_setopt($chCheck, CURLOPT_POSTFIELDS, http_build_query($checkPayload));
            
            $checkResponseRaw = curl_exec($chCheck);
            
            $checkResponse = json_decode($checkResponseRaw, true);
            
            if (isset($checkResponse['onwhatsapp']) && $checkResponse['onwhatsapp'] === 'false') {
                $status = 'inactive';
                $updateStmt = $conn->prepare("UPDATE whatsapp_queues SET status = ?, sent_at = NOW(), response = ?, retry_count = ? WHERE id = ?");
                $updateStmt->execute([$status, 'Nomor tidak terdaftar di WhatsApp', $msg['retry_count'] ?? 0, $msg['id']]);
                
                // Throttling: 1 second delay between messages
                if ($i < $msgCount) {
                    sleep(1);
                }
                continue;
            }

            $payload = [
                'token' => $token,
                'number' => $msg['recipient_number'],
                'message' => $msg['message']
            ];
            
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if ($httpCode >= 200 && $httpCode < 300) {
                $status = 'sent';
                $successCount++;
                $retryCount = $msg['retry_count'] ?? 0;
            } else {
                $retryCount = ($msg['retry_count'] ?? 0) + 1;
                if ($retryCount >= 3) {
                    $status = 'failed';
                    $failedCount++;
                } else {
                    $status = 'pending'; // keep it pending to try again
                }
            }
            
            // Update database
            $updateStmt = $conn->prepare("UPDATE whatsapp_queues SET status = ?, sent_at = NOW(), response = ?, retry_count = ? WHERE id = ?");
            $updateStmt->execute([$status, $response, $retryCount, $msg['id']]);

            // Throttling: 1 second delay between messages
            if ($i < $msgCount) {
                sleep(1);
            }
        }
        
        echo "Processed " . count($messages) . " messages. Success: $successCount, Failed: $failedCount\n";
    }
}
