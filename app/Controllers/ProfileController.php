<?php

namespace App\Controllers;

use App\Core\Controller;

class ProfileController extends Controller {
    public function index() {
        require_login();

        if (auth_get_role() !== 'pengajar') {
            $this->redirect('/');
        }
        
        $this->view('profile/index', []);
    }

    public function update() {
        require_login();
        
        if (auth_get_role() !== 'pengajar') {
            $this->redirect('/');
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/profil');
        }

        csrf_validate_token();

        $teacherId = auth_get_user_id();
        require_once __DIR__ . '/../../helpers/profile_helper.php';
        $teacher = get_teacher_biodata($teacherId);

        if (!$teacher) {
            add_flash('Data pengajar tidak ditemukan.', 'error');
            $this->redirect('/');
        }

        $profileData = [
            'jenis_kelamin' => trim($_POST['jenis_kelamin'] ?? ''),
            'tempat_lahir' => trim($_POST['tempat_lahir'] ?? ''),
            'tanggal_lahir' => trim($_POST['tanggal_lahir'] ?? ''),
            'nik' => trim($_POST['nik'] ?? ''),
            'pendidikan_terakhir' => trim($_POST['pendidikan_terakhir'] ?? ''),
            'tahun_lulus' => trim($_POST['tahun_lulus'] ?? ''),
            'nama_ayah' => trim($_POST['nama_ayah'] ?? ''),
            'nama_ibu' => trim($_POST['nama_ibu'] ?? ''),
            'alamat' => trim($_POST['alamat'] ?? ''),
        ];

        if (!empty($profileData['nik']) && !preg_match('/^[0-9]{16}$/', $profileData['nik'])) {
            add_flash('NIK harus 16 digit angka.', 'error');
            $this->redirect('/profil');
        }

        $uploadDir = __DIR__ . '/../../uploads/profiles/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['profile_picture'];
            
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mimeType, $allowedTypes)) {
                add_flash('Format file tidak didukung. Gunakan JPG, PNG, atau WebP.', 'error');
                $this->redirect('/profil');
            }
            
            if ($file['size'] > 2 * 1024 * 1024) {
                add_flash('Ukuran file terlalu besar. Maksimal 2MB.', 'error');
                $this->redirect('/profil');
            }
            
            $extension = '';
            switch ($mimeType) {
                case 'image/jpeg': $extension = 'jpg'; break;
                case 'image/png': $extension = 'png'; break;
                case 'image/webp': $extension = 'webp'; break;
            }
            
            $extensions = ['jpg', 'jpeg', 'png', 'webp'];
            foreach ($extensions as $ext) {
                $oldFile = $uploadDir . $teacherId . '.' . $ext;
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }
            
            $targetFile = $uploadDir . $teacherId . '.' . $extension;
            if (!move_uploaded_file($file['tmp_name'], $targetFile)) {
                add_flash('Gagal meng-upload foto profil.', 'error');
                $this->redirect('/profil');
            }
            
            $profileData['profile_picture'] = 'uploads/profiles/' . $teacherId . '.' . $extension;
        }

        if (!empty($_POST['current_password']) && !empty($_POST['new_password'])) {
            $currentPassword = $_POST['current_password'];
            $newPassword = $_POST['new_password'];
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            if (!password_verify($currentPassword, $teacher['password'])) {
                add_flash('Password lama tidak sesuai.', 'error');
                $this->redirect('/profil');
            }
            
            if (strlen($newPassword) < 6) {
                add_flash('Password baru minimal 6 karakter.', 'error');
                $this->redirect('/profil');
            }
            
            if ($newPassword !== $confirmPassword) {
                add_flash('Konfirmasi password tidak cocok.', 'error');
                $this->redirect('/profil');
            }
            
            $profileData['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
            $profileData['password_plain'] = $newPassword;
        }

        if (update_teacher_biodata($teacherId, $profileData)) {
            add_flash('Profil berhasil diperbarui!', 'success');
        } else {
            add_flash('Gagal memperbarui profil.', 'error');
        }

        $this->redirect('/profil');
    }

    public function changePassword() {
        require_admin();
        $this->view('profile/change_password', []);
    }

    public function updatePassword() {
        require_admin();
        
        $username = $_POST['username'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        $result = null;
        if (empty($username) || empty($newPassword)) {
            $result = ['success' => false, 'message' => 'Username dan password tidak boleh kosong'];
        } elseif ($newPassword !== $confirmPassword) {
            $result = ['success' => false, 'message' => 'Password dan konfirmasi tidak sama'];
        } elseif (strlen($newPassword) < 4) {
            $result = ['success' => false, 'message' => 'Password minimal 4 karakter'];
        } else {
            require_once __DIR__ . '/../../helpers/file_helper.php';
            $usersFile = __DIR__ . '/../../data/users.json';
            $found = false;
            
            $updated = update_json_file($usersFile, function(&$users) use ($username, $newPassword, &$found) {
                foreach ($users as $key => &$user) {
                    if (isset($user['username']) && $user['username'] === $username) {
                        $user['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
                        $user['updated_at'] = date('Y-m-d H:i:s');
                        $found = true;
                        break;
                    }
                }
            });

            if (!$updated) {
                $result = ['success' => false, 'message' => 'Gagal menyimpan file (Locked)'];
            } elseif (!$found) {
                $result = ['success' => false, 'message' => "User dengan username '$username' tidak ditemukan"];
            } else {
                $result = ['success' => true, 'message' => 'Password berhasil diubah!'];
            }
        }
        
        session_start();
        $_SESSION['password_change_result'] = $result;
        $this->redirect('/change-password');
    }
}
