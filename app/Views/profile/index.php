<?php
require_once __DIR__ . '/../../../helpers/profile_helper.php';

$teacherId = auth_get_user_id();
$teacher = get_teacher_biodata($teacherId);

if (!$teacher) {
    echo "Data pengajar tidak ditemukan.";
    exit;
}

$profilePicture = get_profile_picture_url($teacherId, $teacher['nama']);

renderHeader("Profil Saya");
?>

<main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Profil Saya</h2>
        <p class="text-gray-500 text-sm">Kelola informasi biodata Anda</p>
    </div>

    <form action="<?= url('/profil/simpan') ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
        <?= csrf_token_field() ?>
        
        <!-- Profile Picture Section -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Foto Profil</h3>
            
            <div class="flex items-center gap-6">
                <div class="relative">
                    <img id="profilePreview" src="<?= htmlspecialchars($profilePicture) ?>" alt="Profile Picture" class="w-32 h-32 rounded-full object-cover border-4 border-gray-100 shadow-md">
                    <div class="absolute bottom-0 right-0 bg-indigo-600 rounded-full p-2 shadow-lg cursor-pointer hover:bg-indigo-700 transition" onclick="document.getElementById('profilePictureInput').click()">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                </div>
                
                <div class="flex-1">
                    <input type="file" id="profilePictureInput" name="profile_picture" accept="image/jpeg,image/png,image/webp" class="hidden">
                    <label for="profilePictureInput" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 cursor-pointer">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Pilih Foto
                    </label>
                    <p class="mt-2 text-xs text-gray-500">Format: JPG, PNG, WebP. Maksimal 2MB.</p>
                </div>
            </div>
        </div>

        <!-- Personal Information -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Data Pribadi</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                    <input type="text" value="<?= htmlspecialchars($teacher['nama']) ?>" readonly class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 bg-gray-50 text-gray-600">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                        <option value="">- Pilih -</option>
                        <option value="Laki-laki" <?= ($teacher['jenis_kelamin'] ?? '') === 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
                        <option value="Perempuan" <?= ($teacher['jenis_kelamin'] ?? '') === 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" value="<?= htmlspecialchars($teacher['tempat_lahir'] ?? '') ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" value="<?= htmlspecialchars($teacher['tanggal_lahir'] ?? '') ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">NIK</label>
                    <input type="text" name="nik" value="<?= htmlspecialchars($teacher['nik'] ?? '') ?>" maxlength="16" pattern="[0-9]{16}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">No. HP</label>
                    <input type="text" value="<?= htmlspecialchars($teacher['hp']) ?>" readonly class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 bg-gray-50 text-gray-600">
                </div>
            </div>
        </div>

        <!-- Education Information -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Pendidikan</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Pendidikan Terakhir</label>
                    <select name="pendidikan_terakhir" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                        <option value="">- Pilih -</option>
                        <?php
                        $pendidikanOptions = ['SMA/MA', 'D3', 'S1', 'S2', 'S3'];
                        foreach ($pendidikanOptions as $option) {
                            $selected = ($teacher['pendidikan_terakhir'] ?? '') === $option ? 'selected' : '';
                            echo "<option value=\"$option\" $selected>$option</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tahun Lulus</label>
                    <input type="number" name="tahun_lulus" value="<?= htmlspecialchars($teacher['tahun_lulus'] ?? '') ?>" min="1950" max="<?= date('Y') ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                </div>
            </div>
        </div>

        <!-- Family Information -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Data Keluarga</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nama Ayah</label>
                    <input type="text" name="nama_ayah" value="<?= htmlspecialchars($teacher['nama_ayah'] ?? '') ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nama Ibu Kandung</label>
                    <input type="text" name="nama_ibu" value="<?= htmlspecialchars($teacher['nama_ibu'] ?? '') ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                </div>
            </div>
        </div>

        <!-- Address Information -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Alamat</h3>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Alamat Lengkap</label>
                <textarea name="alamat" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"><?= htmlspecialchars($teacher['alamat'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- Password Change Section -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Ganti Password</h3>
            
            <div class="bg-blue-50 border border-blue-200 rounded-md p-3 mb-4">
                <p class="text-sm text-blue-800">
                    Kosongkan jika tidak ingin mengganti password
                </p>
            </div>
            
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Password Lama</label>
                    <input type="password" name="current_password" autocomplete="current-password" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Password Baru</label>
                    <input type="password" name="new_password" autocomplete="new-password" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Konfirmasi Password Baru</label>
                    <input type="password" name="confirm_password" autocomplete="new-password" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end gap-3">
            <a href="<?= url('/') ?>" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition shadow-sm">
                Simpan Perubahan
            </button>
        </div>
    </form>

</main>

<script>
document.getElementById('profilePictureInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profilePreview').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});
</script>

<?php renderFooter(); ?>
