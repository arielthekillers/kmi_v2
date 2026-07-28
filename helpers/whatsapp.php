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
            $sql = "INSERT INTO whatsapp_queues (recipient_number, message, status, sender, created_at) VALUES (?, ?, 'pending', ?, NOW())";
            $db->query($sql, [$recipient_number, $message, $sender]);
            return true;
        } catch (\Exception $e) {
            error_log("Failed to queue WhatsApp message: " . $e->getMessage());
            return false;
        }
    }
}
