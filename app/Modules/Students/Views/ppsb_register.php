<div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8 bg-gradient-to-tr from-slate-900 via-indigo-950 to-slate-950 flex flex-col justify-center">
    <div class="sm:mx-auto sm:w-full sm:max-w-3xl">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center p-3 bg-indigo-500/10 rounded-2xl border border-indigo-500/20 mb-4 shadow-inner">
                <i class="ri-user-add-line text-indigo-400 text-3xl"></i>
            </div>
            <h2 class="text-3xl font-extrabold text-white tracking-tight sm:text-4xl">
                Pendaftaran Santri Baru (PPSB)
            </h2>
            <p class="mt-2 text-sm text-slate-400">
                Silakan isi data calon santri dengan lengkap dan benar untuk memulai proses seleksi penerimaan siswa baru.
            </p>
        </div>
    </div>

    <div class="sm:mx-auto sm:w-full sm:max-w-3xl">
        <div class="bg-white/5 backdrop-blur-xl border border-white/10 shadow-2xl rounded-3xl overflow-hidden">
            <form action="<?= url('/ppsb/store') ?>" method="POST" class="p-8 sm:p-10 space-y-8">
                
                <!-- Section 1: Data Diri Calon Santri -->
                <div>
                    <h3 class="text-lg font-bold text-white flex items-center gap-2.5 pb-3 border-b border-white/10">
                        <span class="w-6 h-6 rounded-lg bg-indigo-500/20 flex items-center justify-center text-xs text-indigo-400">1</span>
                        Identitas Calon Santri
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-6">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Nama Lengkap <span class="text-red-400">*</span></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500">
                                    <i class="ri-user-line"></i>
                                </span>
                                <input type="text" name="nama" required placeholder="Masukkan nama lengkap sesuai ijazah/akta..." 
                                       class="block w-full pl-10 pr-4 py-3 bg-slate-900/50 border border-white/10 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-white placeholder-slate-500 text-sm transition-all">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Jenis Kelamin <span class="text-red-400">*</span></label>
                            <select name="gender" required 
                                    class="block w-full px-3.5 py-3 bg-slate-900 border border-white/10 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-slate-300 text-sm transition-all">
                                <option value="" class="text-slate-900">-- Pilih Jenis Kelamin --</option>
                                <option value="L" class="text-slate-900">Laki-laki</option>
                                <option value="P" class="text-slate-900">Perempuan</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Tempat Lahir <span class="text-red-400">*</span></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500">
                                    <i class="ri-map-pin-line"></i>
                                </span>
                                <input type="text" name="tempat_lahir" required placeholder="Contoh: Jakarta" 
                                       class="block w-full pl-10 pr-4 py-3 bg-slate-900/50 border border-white/10 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-white placeholder-slate-500 text-sm transition-all">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Tanggal Lahir <span class="text-red-400">*</span></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500">
                                    <i class="ri-calendar-line"></i>
                                </span>
                                <input type="date" name="tanggal_lahir" required 
                                       class="block w-full pl-10 pr-4 py-3 bg-slate-900/50 border border-white/10 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-white placeholder-slate-500 text-sm transition-all">
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Alamat Lengkap <span class="text-red-400">*</span></label>
                            <textarea name="alamat" rows="3" required placeholder="Masukkan nama jalan, RT/RW, kelurahan, kecamatan, kota/kabupaten..." 
                                      class="block w-full px-4 py-3 bg-slate-900/50 border border-white/10 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-white placeholder-slate-500 text-sm transition-all"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Data Orang Tua / Wali -->
                <div>
                    <h3 class="text-lg font-bold text-white flex items-center gap-2.5 pb-3 border-b border-white/10">
                        <span class="w-6 h-6 rounded-lg bg-indigo-500/20 flex items-center justify-center text-xs text-indigo-400">2</span>
                        Data Wali / Orang Tua
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Nama Wali / Orang Tua <span class="text-red-400">*</span></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500">
                                    <i class="ri-parent-line"></i>
                                </span>
                                <input type="text" name="nama_wali" required placeholder="Nama wali penanggung jawab..." 
                                       class="block w-full pl-10 pr-4 py-3 bg-slate-900/50 border border-white/10 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-white placeholder-slate-500 text-sm transition-all">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Nomor HP Wali <span class="text-red-400">*</span></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500">
                                    <i class="ri-phone-line"></i>
                                </span>
                                <input type="tel" name="no_hp_wali" required placeholder="Contoh: 081234567890" 
                                       class="block w-full pl-10 pr-4 py-3 bg-slate-900/50 border border-white/10 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-white placeholder-slate-500 text-sm transition-all">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submission Button -->
                <div class="pt-6 border-t border-white/10 flex justify-end">
                    <button type="submit" 
                            class="px-8 py-3.5 bg-gradient-to-r from-indigo-500 to-indigo-600 hover:from-indigo-600 hover:to-indigo-700 text-white text-sm font-bold rounded-xl shadow-lg hover:shadow-indigo-500/20 hover:shadow-xl active:scale-95 transition-all flex items-center gap-2">
                        <i class="ri-send-plane-fill"></i> Kirim Pendaftaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
