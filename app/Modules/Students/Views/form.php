<main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 flex items-center gap-4">
        <a href="<?= url('/students?q=' . urlencode($q) . '&kelas_id=' . $selected_kelas . '&page=' . $page) ?>" class="p-2 bg-white border border-gray-200 rounded-lg text-gray-500 hover:text-indigo-600 transition-colors">
            <i class="ri-arrow-left-line text-xl"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= $title ?></h1>
            <p class="text-sm text-gray-500">Lengkapi informasi detail santri di bawah ini.</p>
        </div>
    </div>

    <form action="<?= $action ?>" method="POST" id="studentForm" class="space-y-8">
        <?php if (isset($student['id'])): ?>
            <input type="hidden" name="id" value="<?= $student['id'] ?>">
        <?php endif; ?>
        <input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>">
        <input type="hidden" name="selected_kelas" value="<?= htmlspecialchars($selected_kelas) ?>">
        <input type="hidden" name="page" value="<?= htmlspecialchars($page) ?>">

        <!-- Section 1: Identitas Dasar -->
        <div class="bg-white shadow-sm border border-gray-200 rounded-xl p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-6 flex items-center gap-2">
                <i class="ri-fingerprint-line text-indigo-500"></i> Identitas Dasar
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">NIS <span class="text-red-500">*</span></label>
                    <input type="text" name="nis" value="<?= $student['nis'] ?? '' ?>" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">NISN</label>
                    <input type="text" name="nisn" value="<?= $student['nisn'] ?? '' ?>" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">NIK (Nomor Induk Kependudukan)</label>
                    <input type="text" name="nik" value="<?= $student['nik'] ?? '' ?>" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" value="<?= $student['nama'] ?? '' ?>" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Jenis Kelamin <span class="text-red-500">*</span></label>
                    <select name="gender" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border">
                        <option value="L" <?= ($student['gender'] ?? '') === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                        <option value="P" <?= ($student['gender'] ?? '') === 'P' ? 'selected' : '' ?>>Perempuan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" value="<?= $student['tempat_lahir'] ?? '' ?>" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" value="<?= $student['tanggal_lahir'] ?? '' ?>" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Kelas <span class="text-red-500">*</span></label>
                    <select name="kelas_id" required class="tom-select mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border">
                        <option value="">Pilih Kelas...</option>
                        <?php foreach($kelas as $k): ?>
                            <option value="<?= $k['id'] ?>" <?= ($student['kelas_id'] ?? '') == $k['id'] ? 'selected' : '' ?>><?= $k['tingkat'] . ' ' . $k['abjad'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tahun Masuk</label>
                    <input type="number" name="tahun_masuk" value="<?= $student['tahun_masuk'] ?? date('Y') ?>" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border">
                </div>
            </div>
        </div>

        <!-- Section 2: Alamat & Wilayah -->
        <div class="bg-white shadow-sm border border-gray-200 rounded-xl p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-6 flex items-center gap-2">
                <i class="ri-map-pin-2-line text-indigo-500"></i> Alamat & Wilayah
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Alamat Lengkap</label>
                    <textarea name="alamat" rows="2" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border"><?= $student['alamat'] ?? '' ?></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Provinsi</label>
                    <select id="prov_id" name="prov_id" class="tom-select-region mt-1 block w-full border">
                        <option value="">Pilih Provinsi...</option>
                        <?php if(!empty($student['prov_id'])): ?>
                            <option value="<?= $student['prov_id'] ?>" selected><?= $student['provinsi'] ?></option>
                        <?php endif; ?>
                    </select>
                    <input type="hidden" id="provinsi" name="provinsi" value="<?= $student['provinsi'] ?? '' ?>">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Kabupaten / Kota</label>
                    <select id="kab_id" name="kab_id" class="tom-select-region mt-1 block w-full border">
                        <option value="">Pilih Kabupaten...</option>
                        <?php if(!empty($student['kab_id'])): ?>
                            <option value="<?= $student['kab_id'] ?>" selected><?= $student['kabupaten'] ?></option>
                        <?php endif; ?>
                    </select>
                    <input type="hidden" id="kabupaten" name="kabupaten" value="<?= $student['kabupaten'] ?? '' ?>">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Kecamatan</label>
                    <select id="kec_id" name="kec_id" class="tom-select-region mt-1 block w-full border">
                        <option value="">Pilih Kecamatan...</option>
                        <?php if(!empty($student['kec_id'])): ?>
                            <option value="<?= $student['kec_id'] ?>" selected><?= $student['kecamatan'] ?></option>
                        <?php endif; ?>
                    </select>
                    <input type="hidden" id="kecamatan" name="kecamatan" value="<?= $student['kecamatan'] ?? '' ?>">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Kelurahan / Desa</label>
                    <select id="desa_id" name="desa_id" class="tom-select-region mt-1 block w-full border">
                        <option value="">Pilih Kelurahan...</option>
                        <?php if(!empty($student['desa_id'])): ?>
                            <option value="<?= $student['desa_id'] ?>" selected><?= $student['kelurahan'] ?></option>
                        <?php endif; ?>
                    </select>
                    <input type="hidden" id="kelurahan" name="kelurahan" value="<?= $student['kelurahan'] ?? '' ?>">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">RT / RW</label>
                    <input type="text" name="rt_rw" value="<?= $student['rt_rw'] ?? '' ?>" placeholder="Contoh: 001/002" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Kode Pos</label>
                    <input type="text" name="kode_pos" value="<?= $student['kode_pos'] ?? '' ?>" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border">
                </div>
            </div>
        </div>

        <!-- Section 3: Data Orang Tua / Wali -->
        <div class="bg-white shadow-sm border border-gray-200 rounded-xl p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-6 flex items-center gap-2">
                <i class="ri-parent-line text-indigo-500"></i> Orang Tua / Wali
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nama Kepala Keluarga</label>
                    <input type="text" name="nama_kk" value="<?= $student['nama_kk'] ?? '' ?>" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nama Wali (Optional)</label>
                    <input type="text" name="nama_wali" value="<?= $student['nama_wali'] ?? '' ?>" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border">
                </div>
                <div class="border-t border-gray-100 pt-4 md:col-span-2">
                    <h3 class="text-sm font-bold text-gray-500 uppercase mb-4">Data Ayah</h3>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Pekerjaan Ayah</label>
                    <input type="text" name="pekerjaan_ayah" value="<?= $student['pekerjaan_ayah'] ?? '' ?>" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nomor HP Ayah</label>
                    <input type="text" name="no_hp_ayah" value="<?= $student['no_hp_ayah'] ?? '' ?>" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border">
                </div>
                <div class="border-t border-gray-100 pt-4 md:col-span-2">
                    <h3 class="text-sm font-bold text-gray-500 uppercase mb-4">Data Ibu</h3>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nama Ibu Kandung</label>
                    <input type="text" name="nama_ibu" value="<?= $student['nama_ibu'] ?? '' ?>" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Pekerjaan Ibu</label>
                    <input type="text" name="pekerjaan_ibu" value="<?= $student['pekerjaan_ibu'] ?? '' ?>" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nomor HP Ibu</label>
                    <input type="text" name="no_hp_ibu" value="<?= $student['no_hp_ibu'] ?? '' ?>" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border">
                </div>
            </div>
        </div>

        <!-- Section 4: Riwayat Pendidikan -->
        <?php if (!empty($history)): ?>
        <div class="bg-white shadow-sm border border-gray-200 rounded-xl p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-6 flex items-center gap-2">
                <i class="ri-history-line text-indigo-500"></i> Riwayat Pendidikan
            </h2>
            <div class="relative pl-8 border-l-2 border-indigo-100 space-y-8">
                <?php foreach ($history as $h): ?>
                <div class="relative">
                    <div class="absolute -left-[41px] top-1 w-4 h-4 rounded-full border-4 border-white bg-indigo-500 shadow-sm"></div>
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-2">
                        <div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800 mb-1">
                                <?= htmlspecialchars($h['year_name']) ?>
                            </span>
                            <div class="text-sm font-bold text-gray-900">Kelas <?= htmlspecialchars($h['tingkat'] . ' ' . $h['abjad']) ?></div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs font-bold uppercase tracking-wider <?= $h['status'] === 'Active' ? 'text-green-500' : 'text-gray-400' ?>">
                                <?= $h['status'] ?>
                            </div>
                            <div class="text-[10px] text-gray-500 font-mono">
                                <?= date('d M Y', strtotime($h['start_date'])) ?> 
                                <?= $h['end_date'] ? ' — ' . date('d M Y', strtotime($h['end_date'])) : '' ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="pt-5 border-t border-gray-200 flex justify-end gap-3">
            <a href="<?= url('/students?q=' . urlencode($q) . '&kelas_id=' . $selected_kelas . '&page=' . $page) ?>" class="bg-white py-2.5 px-6 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                Batal
            </a>
            <button type="submit" class="inline-flex justify-center py-2.5 px-8 border border-transparent shadow-lg text-sm font-bold rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-200 transition-all">
                Simpan Data Santri
            </button>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const regionSelects = {};
            const regionNames = {
                'prov_id': 'provinsi',
                'kab_id': 'kabupaten',
                'kec_id': 'kecamatan',
                'desa_id': 'kelurahan'
            };

            // Initialize TomSelect for regions
            document.querySelectorAll('.tom-select-region').forEach(el => {
                regionSelects[el.id] = new TomSelect(el, {
                    valueField: 'code',
                    labelField: 'name',
                    searchField: 'name',
                    create: false,
                    onChange: function(val) {
                        const nextId = getNextRegionId(el.id);
                        if (nextId && val) loadRegions(nextId, val);
                        
                        // Update hidden name input
                        const nameInput = document.getElementById(regionNames[el.id]);
                        if (nameInput && this.options[val]) {
                            nameInput.value = this.options[val].name;
                        }
                    }
                });
            });

            function getNextRegionId(currentId) {
                const flow = ['prov_id', 'kab_id', 'kec_id', 'desa_id'];
                const idx = flow.indexOf(currentId);
                return idx < flow.length - 1 ? flow[idx+1] : null;
            }

            async function loadRegions(type, parentId = '', selectedValue = '') {
                const select = regionSelects[type];
                if (!select) return;

                const typeMap = {
                    'prov_id': 'provinces',
                    'kab_id': 'regencies',
                    'kec_id': 'districts',
                    'desa_id': 'villages'
                };

                // Show loading state
                select.clear(true);
                select.clearOptions();
                select.addOption({code: '', name: 'Mohon tunggu...'});
                select.addItem('');

                const apiType = typeMap[type];
                const url = `<?= url('/api/regions') ?>?type=${apiType}${parentId ? '&parent_id='+parentId : ''}`;
                
                try {
                    const response = await fetch(url);
                    const result = await response.json();
                    
                    select.clear(true);
                    select.clearOptions();
                    
                    if (result.data && result.data.length > 0) {
                        select.addOptions(result.data);
                        if (selectedValue) {
                            select.addItem(selectedValue);
                        } else {
                            // select.addItem(''); // Keep it blank
                        }
                    } else {
                        select.addOption({code: '', name: 'Tidak ada data'});
                        select.addItem('');
                    }
                } catch (e) {
                    console.error('Failed to load regions', e);
                    select.clear(true);
                    select.clearOptions();
                    select.addOption({code: '', name: 'Gagal memuat data'});
                    select.addItem('');
                }
            }

            // Initial load sequence for new and edit modes
            (async () => {
                const initProv = "<?= $student['prov_id'] ?? '' ?>";
                const initKab  = "<?= $student['kab_id'] ?? '' ?>";
                const initKec  = "current_kec_id" in window ? window.current_kec_id : "<?= $student['kec_id'] ?? '' ?>"; // Safety check
                const initDesa = "<?= $student['desa_id'] ?? '' ?>";

                // Step 1: Provinces
                await loadRegions('prov_id', '', initProv);

                // Sequential load for Edit Mode
                if (initProv) {
                    await loadRegions('kab_id', initProv, initKab);
                    if (initKab) {
                        await loadRegions('kec_id', initKab, initKec);
                        if (initKec) {
                            await loadRegions('desa_id', initKec, initDesa);
                        }
                    }
                }
            })();
        });
    </script>

</main>
