<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\SettingModel;

class SettingsController extends Controller {

    public function general() {
        require_admin();
        $this->view('settings/general', [
            'title' => 'Settings - General'
        ]);
    }

    public function whatsappApi() {
        require_admin();
        $settingModel = new SettingModel();
        
        $deviceKey = $settingModel->get('wa_device_key', '');
        $apiKey = $settingModel->get('wa_api_key', '');
        $sendMethod = $settingModel->get('wa_send_method', 'direct');

        $this->view('settings/whatsapp_api', [
            'title' => 'Settings - WhatsApp API',
            'deviceKey' => $deviceKey,
            'apiKey' => $apiKey,
            'sendMethod' => $sendMethod
        ]);
    }

    public function updateWhatsappApi() {
        require_admin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/settings/whatsapp');
        }

        csrf_validate_token();

        $settingModel = new SettingModel();
        $settingModel->set('wa_device_key', trim($_POST['device_key'] ?? ''));
        $settingModel->set('wa_api_key', trim($_POST['api_key'] ?? ''));
        $settingModel->set('wa_send_method', ($_POST['send_method'] ?? '') === 'queue' ? 'queue' : 'direct');

        add_flash('Konfigurasi WhatsApp API berhasil disimpan!', 'success');
        $this->redirect('/settings/whatsapp');
    }

    public function tvShowcaseLayout() {
        require_admin();

        $settingModel = new SettingModel();
        $showcaseMode = $settingModel->get('tv_showcase_mode', 'normal');

        $this->view('settings/tvshowcase_layout', [
            'title'       => 'Settings - TV Layout',
            'showcaseMode'=> $showcaseMode
        ]);
    }

    public function tvShowcaseBgm() {
        require_admin();

        $settingModel = new SettingModel();
        $bgmYoutube = $settingModel->get('tv_showcase_bgm_youtube', '');

        // Check if a BGM file already exists (fallback)
        $bgmPath = __DIR__ . '/../../uploads/bgm.mp3';
        $bgmExists = file_exists($bgmPath);
        $bgmSize   = $bgmExists ? round(filesize($bgmPath) / 1024 / 1024, 2) . ' MB' : null;
        $bgmMtime  = $bgmExists ? date('d M Y, H:i', filemtime($bgmPath)) : null;

        $this->view('settings/tvshowcase_bgm', [
            'title'       => 'Settings - TV BGM',
            'bgmYoutube'  => $bgmYoutube,
            'bgmExists'   => $bgmExists,
            'bgmSize'     => $bgmSize,
            'bgmMtime'    => $bgmMtime
        ]);
    }

    public function updateShowcaseMode() {
        require_admin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/settings/tv/layout');
        }

        csrf_validate_token();

        $mode = $_POST['tv_showcase_mode'] ?? 'normal';
        if (!in_array($mode, ['normal', 'exam', 'orientation'])) {
            $mode = 'normal';
        }

        $settingModel = new SettingModel();
        $settingModel->set('tv_showcase_mode', $mode);

        add_flash('Mode Tampilan TV Showcase berhasil diperbarui!', 'success');
        $this->redirect('/settings/tv/layout');
    }

    public function updateYoutubeBgm() {
        require_admin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/settings/tv/bgm');
        }

        csrf_validate_token();

        $youtubeUrl = $_POST['youtube_url'] ?? '';
        
        $settingModel = new SettingModel();
        $settingModel->set('tv_showcase_bgm_youtube', trim($youtubeUrl));

        add_flash('YouTube BGM berhasil diperbarui!', 'success');
        $this->redirect('/settings/tv/bgm');
    }

    public function tvShowcaseHours() {
        require_admin();
        $settingModel = new SettingModel();
        $hoursConfig  = $settingModel->getTvHours();

        $this->view('settings/tvshowcase_hours', [
            'title'       => 'Settings - Jam Pelajaran',
            'hoursConfig' => $hoursConfig
        ]);
    }

    public function tvShowcaseQuotes() {
        require_admin();
        $settingModel = new SettingModel();
        $quotes = $settingModel->get('tv_showcase_quotes', [
            "Pancajiwa Pondok: Keikhlasan, Kesederhanaan, Berdikari, Ukhuwah Islamiyah, dan Kebebasan.",
            "Motto Pondok: Berbudi tinggi, Berbadan sehat, Berpengetahuan luas, dan Berpikiran bebas.",
            "إنّ تنفيذ التربية الخلقية والعقلية لا يكفي بمjرد الكلام، بل لا بدّ أن يكون بالقدوة الصالحة...",
            "الوعي مبعث كلّ النجاح",
            "من وعى انتبه",
            "Think globally, act locally!"
        ]);

        $this->view('settings/tvshowcase_quotes', [
            'title'  => 'Settings - Motto/Quotes',
            'quotes' => $quotes
        ]);
    }

    public function uploadAudio() {
        require_admin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/settings/tv/bgm');
        }

        csrf_validate_token();

        if (!isset($_FILES['audio']) || $_FILES['audio']['error'] !== UPLOAD_ERR_OK) {
            $errCodes = [
                UPLOAD_ERR_INI_SIZE   => 'File melebihi upload_max_filesize di php.ini.',
                UPLOAD_ERR_FORM_SIZE  => 'File melebihi MAX_FILE_SIZE di form.',
                UPLOAD_ERR_PARTIAL    => 'File hanya terupload sebagian.',
                UPLOAD_ERR_NO_FILE    => 'Tidak ada file yang dipilih.',
            ];
            $code = $_FILES['audio']['error'] ?? UPLOAD_ERR_NO_FILE;
            add_flash($errCodes[$code] ?? 'Terjadi kesalahan upload.', 'error');
            $this->redirect('/settings/tv/bgm');
        }

        $file = $_FILES['audio'];

        // Validate MIME
        $allowedMimes = ['audio/mpeg', 'audio/mp3', 'audio/ogg', 'audio/wav', 'audio/x-wav', 'audio/mp4', 'audio/x-m4a'];
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);

        if (!in_array($mime, $allowedMimes)) {
            add_flash('Format tidak didukung. Gunakan MP3, OGG, WAV, atau M4A.', 'error');
            $this->redirect('/settings/tv/bgm');
        }

        // Max 15 MB (server ceiling is 16M)
        if ($file['size'] > 15 * 1024 * 1024) {
            add_flash('File terlalu besar. Maksimal 15 MB.', 'error');
            $this->redirect('/settings/tv/bgm');
        }

        $targetDir = __DIR__ . '/../../uploads/';
        if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

        if (!move_uploaded_file($file['tmp_name'], $targetDir . 'bgm.mp3')) {
            add_flash('Gagal menyimpan file. Periksa permission folder uploads/.', 'error');
            $this->redirect('/settings/tv/bgm');
        }

        add_flash('BGM TV Showcase berhasil diperbarui!', 'success');
        $this->redirect('/settings/tv/bgm');
    }

    public function updateHours() {
        require_admin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/settings/tv/hours');
        }

        csrf_validate_token();

        $types  = $_POST['type'] ?? [];
        $labels = $_POST['label'] ?? [];
        $starts = $_POST['start'] ?? [];
        $ends   = $_POST['end'] ?? [];
        $values = $_POST['value'] ?? [];

        $newHours = [];
        for ($i = 0; $i < count($types); $i++) {
            if (empty($labels[$i]) || empty($starts[$i]) || empty($ends[$i])) continue;
            
            $newHours[] = [
                'type'  => $types[$i],
                'label' => $labels[$i],
                'start' => $starts[$i],
                'end'   => $ends[$i],
                'value' => $types[$i] === 'jam' ? (int)$values[$i] : $values[$i]
            ];
        }

        // Basic sort by start time to ensure consistency
        usort($newHours, function($a, $b) {
            return strcmp($a['start'], $b['start']);
        });

        $settingModel = new SettingModel();
        $settingModel->set('tv_showcase_hours', $newHours);

        add_flash('Pengaturan jam pelajaran berhasil diperbarui!', 'success');
        $this->redirect('/settings/tv/hours');
    }

    public function updateQuotes() {
        require_admin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/settings/tv/quotes');
        }

        csrf_validate_token();

        $rawQuotes = $_POST['quotes'] ?? [];
        $cleanQuotes = [];

        foreach ($rawQuotes as $q) {
            $trimmed = trim($q);
            if (!empty($trimmed)) {
                $cleanQuotes[] = $trimmed;
            }
        }

        $settingModel = new SettingModel();
        $settingModel->set('tv_showcase_quotes', $cleanQuotes);

        add_flash('Daftar motto/quotes berhasil diperbarui!', 'success');
        $this->redirect('/settings/tv/quotes');
    }

    public function adminPassword() {
        require_admin();
        $db = \App\Core\Database::getInstance()->getConnection();
        
        $currentUserId = auth_get_user_id();
        $stmt = $db->prepare("SELECT id, username, nama, role, updated_at FROM users WHERE id = ?");
        $stmt->execute([$currentUserId]);
        $currentUser = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Fetch all admin accounts in case there are multiple
        $adminStmt = $db->query("SELECT id, username, nama, updated_at FROM users WHERE role = 'admin' AND deleted_at IS NULL ORDER BY id ASC");
        $allAdmins = $adminStmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->view('settings/password', [
            'title' => 'Settings - Ganti Password Admin',
            'currentUser' => $currentUser,
            'allAdmins' => $allAdmins
        ]);
    }

    public function updateAdminPassword() {
        require_admin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/settings/password');
        }

        csrf_validate_token();

        $targetUserId = (int)($_POST['target_user_id'] ?? auth_get_user_id());
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $db = \App\Core\Database::getInstance()->getConnection();
        
        // Target admin user
        $stmt = $db->prepare("SELECT id, username, nama, password, role FROM users WHERE id = ? AND role = 'admin' AND deleted_at IS NULL");
        $stmt->execute([$targetUserId]);
        $targetUser = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$targetUser) {
            add_flash('Akun admin tidak ditemukan.', 'error');
            $this->redirect('/settings/password');
        }

        // Verify current password of the logged-in admin
        $loggedInUserId = auth_get_user_id();
        $loggedStmt = $db->prepare("SELECT password FROM users WHERE id = ?");
        $loggedStmt->execute([$loggedInUserId]);
        $loggedInUser = $loggedStmt->fetch(\PDO::FETCH_ASSOC);

        if (!$loggedInUser || !password_verify($currentPassword, $loggedInUser['password'])) {
            add_flash('Password saat ini (password lama) tidak sesuai.', 'error');
            $this->redirect('/settings/password');
        }

        if (strlen($newPassword) < 6) {
            add_flash('Password baru minimal 6 karakter.', 'error');
            $this->redirect('/settings/password');
        }

        if ($newPassword !== $confirmPassword) {
            add_flash('Konfirmasi password baru tidak cocok.', 'error');
            $this->redirect('/settings/password');
        }

        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $upd = $db->prepare("UPDATE users SET password = ?, password_plain = ?, updated_at = NOW() WHERE id = ?");
        $upd->execute([$hashed, $newPassword, $targetUser['id']]);

        // If the logged-in admin changed their own password, keep THIS computer logged in by updating its session hash.
        // All other computers holding the old hash will be invalidated and logged out automatically.
        if ($targetUser['id'] === $loggedInUserId && isset($_SESSION['user']) && is_array($_SESSION['user'])) {
            $_SESSION['user']['password_hash'] = $hashed;
        }

        log_activity("Admin mengubah password akun: {$targetUser['username']} (ID: {$targetUser['id']})");
        add_flash("Password untuk akun {$targetUser['username']} berhasil diperbarui!", 'success');
        $this->redirect('/settings/password');
    }
}
