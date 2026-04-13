<?php

namespace App\Controllers;

use App\Core\Controller;

class SettingsController extends Controller {

    public function general() {
        require_admin();
        $this->view('settings/general', [
            'title' => 'Settings - General'
        ]);
    }

    public function tvshowcase() {
        require_admin();

        // Check if a BGM file already exists
        $bgmPath = __DIR__ . '/../../sound/bgm.mp3';
        $bgmExists = file_exists($bgmPath);
        $bgmSize   = $bgmExists ? round(filesize($bgmPath) / 1024 / 1024, 2) . ' MB' : null;
        $bgmMtime  = $bgmExists ? date('d M Y, H:i', filemtime($bgmPath)) : null;

        $this->view('settings/tvshowcase', [
            'title'     => 'Settings - TV Showcase',
            'bgmExists' => $bgmExists,
            'bgmSize'   => $bgmSize,
            'bgmMtime'  => $bgmMtime,
        ]);
    }

    public function uploadAudio() {
        require_admin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/settings/tvshowcase');
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
            $this->redirect('/settings/tvshowcase');
        }

        $file = $_FILES['audio'];

        // Validate MIME
        $allowedMimes = ['audio/mpeg', 'audio/mp3', 'audio/ogg', 'audio/wav', 'audio/x-wav', 'audio/mp4', 'audio/x-m4a'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowedMimes)) {
            add_flash('Format tidak didukung. Gunakan MP3, OGG, WAV, atau M4A.', 'error');
            $this->redirect('/settings/tvshowcase');
        }

        // Max 15 MB (server ceiling is 16M)
        if ($file['size'] > 15 * 1024 * 1024) {
            add_flash('File terlalu besar. Maksimal 15 MB.', 'error');
            $this->redirect('/settings/tvshowcase');
        }

        $targetDir = __DIR__ . '/../../sound/';
        if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

        if (!move_uploaded_file($file['tmp_name'], $targetDir . 'bgm.mp3')) {
            add_flash('Gagal menyimpan file. Periksa permission folder sound/.', 'error');
            $this->redirect('/settings/tvshowcase');
        }

        add_flash('BGM TV Showcase berhasil diperbarui!', 'success');
        $this->redirect('/settings/tvshowcase');
    }
}
