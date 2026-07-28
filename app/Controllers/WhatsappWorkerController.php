<?php
namespace App\Controllers;

use App\Core\Database;

class WhatsappWorkerController {
    
    public function processQueue() {
        // This could be run via cronjob by hitting /whatsapp/process or from CLI if router supports it
        
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        // Fetch up to 20 pending messages
        $stmt = $conn->query("SELECT id, recipient_number, message FROM whatsapp_queues WHERE status = 'pending' ORDER BY created_at ASC LIMIT 20");
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
        
        foreach ($messages as $msg) {
            $payload = [
                'phone' => $msg['recipient_number'],
                'device_key' => $deviceKey,
                'api_key' => $apiKey,
                'message' => $msg['message'],
                'url' => null
            ];
            
            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            $status = 'failed';
            if ($httpCode >= 200 && $httpCode < 300) {
                $status = 'sent';
                $successCount++;
            } else {
                $failedCount++;
            }
            
            // Update database
            $updateStmt = $conn->prepare("UPDATE whatsapp_queues SET status = ?, sent_at = NOW(), response = ? WHERE id = ?");
            $updateStmt->execute([$status, $response, $msg['id']]);
        }
        
        echo "Processed " . count($messages) . " messages. Success: $successCount, Failed: $failedCount\n";
    }
}
