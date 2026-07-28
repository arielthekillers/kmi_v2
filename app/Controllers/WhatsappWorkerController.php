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
        
        $apiUrl = getenv('RUANGWA_URL') ?: 'https://ruangwa.id/api-app/waba/messages/simple';
        $deviceKey = getenv('RUANGWA_DEVICE_KEY');
        $apiKey = getenv('RUANGWA_API_KEY');
        
        if (empty($deviceKey) || empty($apiKey)) {
            echo "Ruang WA credentials not configured in .env.\n";
            return;
        }
        
        $successCount = 0;
        $failedCount = 0;
        
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Content-Type: application/json'
        ]);
        
        $msgCount = count($messages);
        $i = 0;

        foreach ($messages as $msg) {
            $i++;
            $payload = [
                'phone' => $msg['recipient_number'],
                'device_key' => $deviceKey,
                'api_key' => $apiKey,
                'message' => $msg['message'],
                'url' => null
            ];
            
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            
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
        
        curl_close($ch);
        
        echo "Processed " . count($messages) . " messages. Success: $successCount, Failed: $failedCount\n";
    }
}
