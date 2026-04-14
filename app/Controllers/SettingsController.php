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

    public function tvShowcaseBgm() {
        require_admin();

        // Check if a BGM file already exists
        $bgmPath = __DIR__ . '/../../uploads/bgm.mp3';
        $bgmExists = file_exists($bgmPath);
        $bgmSize   = $bgmExists ? round(filesize($bgmPath) / 1024 / 1024, 2) . ' MB' : null;
        $bgmMtime  = $bgmExists ? date('d M Y, H:i', filemtime($bgmPath)) : null;

        $this->view('settings/tvshowcase_bgm', [
            'title'       => 'Settings - TV BGM',
            'bgmExists'   => $bgmExists,
            'bgmSize'     => $bgmSize,
            'bgmMtime'    => $bgmMtime
        ]);
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
            "إنّ تنفيذ التربية الخلقية والعقلية لا يكفي بمجرد الكلام، بل لا بدّ أن يكون بالقدوة الصالحة...",
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
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

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
}
