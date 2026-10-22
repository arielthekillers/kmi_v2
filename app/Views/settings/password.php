<?php renderHeader($title); ?>

<main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Breadcrumb & Header -->
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
            <a href="<?= url('/') ?>" class="hover:text-indigo-600 transition-colors">
                <span class="hidden sm:inline">Dashboard</span>
                <i class="ri-home-4-line sm:hidden text-base"></i>
            </a>
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="<?= url('/settings/general') ?>" class="hover:text-indigo-600 transition-colors">Settings</a>
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-indigo-600 font-semibold">Ganti Password</span>
        </div>
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-indigo-500 flex items-center justify-center text-white shadow-md shadow-indigo-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Ganti Password Admin</h2>
                <p class="text-gray-500 text-sm mt-0.5">Kelola dan perbarui kata sandi akun administrator untuk menjaga keamanan sistem.</p>
            </div>
        </div>
    </div>

    <div class="flex flex-col md:flex-row gap-6">
        <?php $active_settings_tab = 'password'; ?>
        <?php include __DIR__ . '/_sidebar.php'; ?>

        <div class="flex-1 space-y-6">
            <!-- Info Akun Saat Ini -->
            <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm p-5 sm:p-6">
                <div class="flex items-center justify-between flex-wrap gap-4 pb-4 border-b border-gray-100">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-lg">
                            <?= mb_strtoupper(mb_substr($currentUser['nama'] ?? 'A', 0, 1)) ?>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-gray-900"><?= htmlspecialchars($currentUser['nama'] ?? 'Administrator') ?></h3>
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                    <?= htmlspecialchars(strtoupper($currentUser['role'] ?? 'ADMIN')) ?>
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Username: <span class="font-mono font-medium text-gray-700"><?= htmlspecialchars($currentUser['username'] ?? '-') ?></span>
                            </p>
                        </div>
                    </div>
                    <?php if (!empty($currentUser['updated_at'])): ?>
                    <div class="text-xs text-gray-400">
                        Terakhir diubah: <span class="text-gray-600 font-medium"><?= date('d M Y, H:i', strtotime($currentUser['updated_at'])) ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Form Ganti Password -->
                <form action="<?= url('/settings/password/update') ?>" method="POST" class="mt-6 space-y-5" id="formChangePassword">
                    <?= csrf_token_field() ?>

                    <!-- Jika ada lebih dari 1 admin di sistem -->
                    <?php if (count($allAdmins ?? []) > 1): ?>
                    <div>
                        <label for="target_user_id" class="block text-sm font-semibold text-gray-700 mb-1">Pilih Akun Admin</label>
                        <select name="target_user_id" id="target_user_id" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                            <?php foreach ($allAdmins as $adm): ?>
                                <option value="<?= $adm['id'] ?>" <?= ($adm['id'] == ($currentUser['id'] ?? null)) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($adm['nama']) ?> (<?= htmlspecialchars($adm['username']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Pilih akun administrator yang ingin Anda ganti password-nya.</p>
                    </div>
                    <?php else: ?>
                    <input type="hidden" name="target_user_id" value="<?= htmlspecialchars($currentUser['id'] ?? 1) ?>">
                    <?php endif; ?>

                    <!-- Password Lama -->
                    <div>
                        <label for="current_password" class="block text-sm font-semibold text-gray-700 mb-1">
                            Password Saat Ini (Password Lama) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" 
                                   id="current_password" 
                                   name="current_password" 
                                   required 
                                   autocomplete="current-password"
                                   placeholder="Masukkan password yang sedang digunakan"
                                   class="w-full px-4 py-2.5 pr-11 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                            <button type="button" 
                                    onclick="togglePassVisibility('current_password', this)" 
                                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-indigo-600 transition-colors focus:outline-none"
                                    title="Tampilkan / sembunyikan password">
                                <i class="ri-eye-line text-lg"></i>
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Dibutuhkan untuk verifikasi identitas administrator.</p>
                    </div>

                    <div class="border-t border-gray-100 pt-2"></div>

                    <!-- Password Baru -->
                    <div>
                        <label for="new_password" class="block text-sm font-semibold text-gray-700 mb-1">
                            Password Baru <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" 
                                   id="new_password" 
                                   name="new_password" 
                                   required 
                                   minlength="6"
                                   autocomplete="new-password"
                                   placeholder="Minimal 6 karakter"
                                   class="w-full px-4 py-2.5 pr-11 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                            <button type="button" 
                                    onclick="togglePassVisibility('new_password', this)" 
                                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-indigo-600 transition-colors focus:outline-none"
                                    title="Tampilkan / sembunyikan password">
                                <i class="ri-eye-line text-lg"></i>
                            </button>
                        </div>
                        
                        <!-- Password Strength Bar -->
                        <div class="mt-2">
                            <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                                <span>Kekuatan Password:</span>
                                <span id="strengthText" class="font-semibold text-gray-400">Belum diisi</span>
                            </div>
                            <div class="w-full h-1.5 bg-gray-200 rounded-full overflow-hidden flex">
                                <div id="strengthBar" class="h-full w-0 transition-all duration-300 bg-red-400"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Konfirmasi Password Baru -->
                    <div>
                        <label for="confirm_password" class="block text-sm font-semibold text-gray-700 mb-1">
                            Konfirmasi Password Baru <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" 
                                   id="confirm_password" 
                                   name="confirm_password" 
                                   required 
                                   minlength="6"
                                   autocomplete="new-password"
                                   placeholder="Ketik ulang password baru Anda"
                                   class="w-full px-4 py-2.5 pr-11 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                            <button type="button" 
                                    onclick="togglePassVisibility('confirm_password', this)" 
                                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-indigo-600 transition-colors focus:outline-none"
                                    title="Tampilkan / sembunyikan password">
                                <i class="ri-eye-line text-lg"></i>
                            </button>
                        </div>
                        <div id="matchNotice" class="text-xs mt-1 hidden"></div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="pt-3 flex items-center justify-end gap-3">
                        <a href="<?= url('/settings/general') ?>" class="px-4 py-2.5 rounded-xl border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                            Batal
                        </a>
                        <button type="submit" 
                                id="btnSubmit"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-100 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-200">
                            <i class="ri-check-line text-lg"></i>
                            Simpan Password Baru
                        </button>
                    </div>
                </form>
            </div>

            <!-- Petunjuk Keamanan -->
            <div class="bg-indigo-50/60 border border-indigo-100 rounded-2xl p-5">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600 flex-shrink-0 mt-0.5">
                        <i class="ri-shield-check-line text-lg"></i>
                    </div>
                    <div class="text-xs text-indigo-950 space-y-1.5 leading-relaxed">
                        <h4 class="font-bold text-sm text-indigo-900">Tips Keamanan Akun Administrator</h4>
                        <ul class="list-disc list-inside space-y-1 text-indigo-800/90">
                            <li>Gunakan kombinasi minimal 6-8 karakter yang terdiri dari huruf besar, huruf kecil, dan angka.</li>
                            <li>Hindari menggunakan kata yang mudah ditebak seperti tanggal lahir atau urutan angka sederhana.</li>
                            <li>Jangan bagikan password akun Administrator kepada pihak yang tidak berwenang.</li>
                            <li>Jika lupa password dan terkunci dari sistem, Anda dapat meresetnya melalui terminal server dengan perintah: <code class="bg-white/80 px-1.5 py-0.5 rounded font-mono text-indigo-700 text-[11px]">php reset_admin.php [password_baru]</code>.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
function togglePassVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (!input) return;
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'ri-eye-off-line text-lg';
    } else {
        input.type = 'password';
        icon.className = 'ri-eye-line text-lg';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const newPass = document.getElementById('new_password');
    const confirmPass = document.getElementById('confirm_password');
    const strengthBar = document.getElementById('strengthBar');
    const strengthText = document.getElementById('strengthText');
    const matchNotice = document.getElementById('matchNotice');
    const btnSubmit = document.getElementById('btnSubmit');

    function calculateStrength(pwd) {
        if (!pwd) return { score: 0, label: 'Belum diisi', color: 'bg-gray-300', width: '0%' };
        let score = 0;
        if (pwd.length >= 6) score += 1;
        if (pwd.length >= 8) score += 1;
        if (/[A-Z]/.test(pwd)) score += 1;
        if (/[0-9]/.test(pwd)) score += 1;
        if (/[^A-Za-z0-9]/.test(pwd)) score += 1;

        if (score <= 1) {
            return { score, label: 'Sangat Lemah', color: 'bg-red-500', width: '20%' };
        } else if (score === 2) {
            return { score, label: 'Lemah', color: 'bg-orange-400', width: '40%' };
        } else if (score === 3) {
            return { score, label: 'Cukup', color: 'bg-yellow-400', width: '65%' };
        } else if (score === 4) {
            return { score, label: 'Kuat', color: 'bg-blue-500', width: '85%' };
        } else {
            return { score, label: 'Sangat Kuat', color: 'bg-emerald-500', width: '100%' };
        }
    }

    function checkMatch() {
        const val1 = newPass.value;
        const val2 = confirmPass.value;
        if (!val2) {
            matchNotice.classList.add('hidden');
            return;
        }

        matchNotice.classList.remove('hidden');
        if (val1 === val2) {
            matchNotice.innerHTML = '<span class="text-emerald-600 font-medium flex items-center gap-1"><i class="ri-checkbox-circle-line"></i> Password konfirmasi cocok.</span>';
        } else {
            matchNotice.innerHTML = '<span class="text-red-500 font-medium flex items-center gap-1"><i class="ri-close-circle-line"></i> Konfirmasi password tidak cocok!</span>';
        }
    }

    if (newPass) {
        newPass.addEventListener('input', function() {
            const res = calculateStrength(this.value);
            strengthBar.style.width = res.width;
            strengthBar.className = 'h-full transition-all duration-300 ' + res.color;
            strengthText.innerText = res.label;
            strengthText.className = 'font-semibold ' + (res.score <= 2 ? 'text-red-500' : (res.score === 3 ? 'text-yellow-600' : 'text-emerald-600'));
            checkMatch();
        });
    }

    if (confirmPass) {
        confirmPass.addEventListener('input', checkMatch);
    }
});
</script>

<?php renderFooter(); ?>
