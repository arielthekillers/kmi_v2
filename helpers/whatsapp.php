<?php

if (!function_exists('queue_whatsapp_message')) {
    /**
     * Menambahkan pesan ke antrian WhatsApp
     *
     * @param string $recipient_number Nomor tujuan
     * @param string $message Isi pesan
     * @return bool
     */
    function queue_whatsapp_message($recipient_number, $message, $sender = 'System') {
        try {
            $db = \App\Core\Database::getInstance();
            
            // Check setting
            $settingModel = new \App\Models\SettingModel();
            $sendMethod = $settingModel->get('wa_send_method', 'direct');
            
            if ($sendMethod === 'direct') {
                $apiUrl = getenv('RUANGWA_URL') ?: 'https://app.ruangwa.id/api/send_message';
                $token = getenv('RUANGWA_TOKEN') ?: getenv('RUANGWA_API_KEY');
                if (empty($token)) {
                    $token = $settingModel->get('wa_api_key', '');
                }

                if (empty($token)) {
                    $sql = "INSERT INTO whatsapp_queues (recipient_number, message, status, sender, created_at, response) VALUES (?, ?, 'failed', ?, NOW(), 'API Token tidak dikonfigurasi')";
                    $db->query($sql, [$recipient_number, $message, $sender]);
                    return false;
                }

                $payload = [
                    'token' => $token,
                    'number' => $recipient_number,
                    'message' => $message
                ];

                $ch = curl_init($apiUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
                curl_setopt($ch, CURLOPT_TIMEOUT, 15);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                $status = ($httpCode >= 200 && $httpCode < 300) ? 'sent' : 'failed';
                
                $sql = "INSERT INTO whatsapp_queues (recipient_number, message, status, sender, created_at, response) VALUES (?, ?, ?, ?, NOW(), ?)";
                $db->query($sql, [$recipient_number, $message, $status, $sender, $response]);
                
                return ($status === 'sent');
            } else {
                $sql = "INSERT INTO whatsapp_queues (recipient_number, message, status, sender, created_at) VALUES (?, ?, 'pending', ?, NOW())";
                $db->query($sql, [$recipient_number, $message, $sender]);
                return true;
            }
        } catch (\Exception $e) {
            error_log("Failed to queue WhatsApp message: " . $e->getMessage());
            return false;
        }
    }
}
