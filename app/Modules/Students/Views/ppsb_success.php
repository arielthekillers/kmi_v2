<div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8 bg-gradient-to-tr from-slate-900 via-indigo-950 to-slate-950 flex flex-col justify-center print:bg-white print:py-0 print:min-h-0">
    <div class="sm:mx-auto sm:w-full sm:max-w-xl print:max-w-full print:w-full">
        
        <!-- Registration Card Container -->
        <div id="receipt-card" class="bg-white text-slate-800 shadow-2xl rounded-3xl overflow-hidden border border-slate-100 p-8 sm:p-10 print:border-none print:shadow-none print:p-0">
            
            <!-- Header for Receipt -->
            <div class="text-center pb-6 border-b-2 border-dashed border-slate-200">
                <div class="inline-flex items-center justify-center p-2.5 bg-green-50 text-green-600 rounded-full mb-3 print:hidden">
                    <i class="ri-checkbox-circle-fill text-4xl"></i>
                </div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">KARTU PENDAFTARAN PPSB</h2>
                <p class="text-xs font-bold text-slate-400 mt-1 uppercase tracking-widest">Kulliyatu-l-Mu'allimin Al-Islamiyyah (KMI)</p>
            </div>

            <!-- Card Details -->
            <div class="py-8 space-y-6">
                <!-- Registration No. -->
                <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-4 text-center">
                    <span class="block text-[10px] font-bold text-indigo-500 uppercase tracking-widest mb-0.5">NOMOR REGISTRASI</span>
                    <span class="text-2xl font-mono font-black text-indigo-900 tracking-wider">
                        <?= htmlspecialchars($registration['registration_no']) ?>
                    </span>
                </div>

                <div class="grid grid-cols-1 gap-y-4 text-sm">
                    <div class="flex justify-between border-b border-slate-100 pb-2.5">
                        <span class="text-slate-400 font-medium">Nama Calon Santri</span>
                        <span class="font-bold text-slate-900 text-right"><?= htmlspecialchars($registration['nama']) ?></span>
                    </div>
                    <div class="flex justify-between border-b border-slate-100 pb-2.5">
                        <span class="text-slate-400 font-medium">Jenis Kelamin</span>
                        <span class="font-bold text-slate-900"><?= $registration['gender'] === 'L' ? 'Laki-laki' : 'Perempuan' ?></span>
                    </div>
                    <div class="flex justify-between border-b border-slate-100 pb-2.5">
                        <span class="text-slate-400 font-medium">Tempat, Tanggal Lahir</span>
                        <span class="font-bold text-slate-900 text-right"><?= htmlspecialchars($registration['tempat_lahir']) . ', ' . date('d M Y', strtotime($registration['tanggal_lahir'])) ?></span>
                    </div>
                    <div class="flex justify-between border-b border-slate-100 pb-2.5">
                        <span class="text-slate-400 font-medium">Nama Wali / Orang Tua</span>
                        <span class="font-bold text-slate-900 text-right"><?= htmlspecialchars($registration['nama_wali']) ?></span>
                    </div>
                    <div class="flex justify-between border-b border-slate-100 pb-2.5">
                        <span class="text-slate-400 font-medium">Nomor HP Wali</span>
                        <span class="font-bold text-slate-900 font-mono"><?= htmlspecialchars($registration['no_hp_wali']) ?></span>
                    </div>
                    <div class="flex flex-col gap-1.5 border-b border-slate-100 pb-2.5">
                        <span class="text-slate-400 font-medium">Alamat Lengkap</span>
                        <span class="font-semibold text-slate-700 leading-relaxed"><?= htmlspecialchars($registration['alamat']) ?></span>
                    </div>
                </div>
            </div>

            <!-- Steps & Instructions -->
            <div class="bg-slate-50 border border-slate-100 rounded-2xl p-5 mb-8 print:hidden">
                <h4 class="text-xs font-black text-slate-500 uppercase tracking-widest mb-3 flex items-center gap-1.5">
                    <i class="ri-information-line text-indigo-500 text-base"></i> Langkah Selanjutnya:
                </h4>
                <ul class="space-y-2.5 text-xs text-slate-600 leading-relaxed font-medium">
                    <li class="flex gap-2">
                        <span class="w-5 h-5 rounded-full bg-slate-200 flex items-center justify-center font-bold text-[10px] flex-shrink-0 text-slate-700">1</span>
                        <span>Cetak atau screenshot kartu bukti pendaftaran ini.</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="w-5 h-5 rounded-full bg-slate-200 flex items-center justify-center font-bold text-[10px] flex-shrink-0 text-slate-700">2</span>
                        <span>Tunggu informasi jadwal ujian seleksi masuk yang akan dikirim via WhatsApp.</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="w-5 h-5 rounded-full bg-slate-200 flex items-center justify-center font-bold text-[10px] flex-shrink-0 text-slate-700">3</span>
                        <span>Membawa kartu pendaftaran ini saat menghadiri ujian di kampus pesantren.</span>
                    </li>
                </ul>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-4 print:hidden">
                <button onclick="window.print()" 
                        class="flex-1 px-5 py-3.5 bg-slate-900 hover:bg-slate-800 text-white text-sm font-bold rounded-xl flex items-center justify-center gap-2 transition-all shadow-lg active:scale-95">
                    <i class="ri-printer-line text-lg"></i> Cetak Kartu
                </button>
                <a href="<?= url('/ppsb/daftar') ?>" 
                   class="flex-1 px-5 py-3.5 border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-bold rounded-xl flex items-center justify-center gap-2 transition-all active:scale-95">
                    Daftar Baru
                </a>
            </div>
        </div>

    </div>
</div>
