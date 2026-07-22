<main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 flex items-center gap-4">
        <a href="<?= url($return_url) ?>" class="p-2 bg-white border border-gray-200 rounded-lg text-gray-500 hover:text-indigo-600 transition-colors">
            <i class="ri-arrow-left-line text-xl"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= $title ?></h1>
            <p class="text-sm text-gray-500">Perbaiki dan lengkapi data pendaftar sebelum dimasukkan ke dalam daftar kelas santri aktif.</p>
        </div>
    </div>

    <form action="<?= url('/admin/ppsb/update') ?>" method="POST" id="ppsbEditForm" class="space-y-8">
        <?= csrf_input() ?>
        <input type="hidden" name="id" value="<?= $registration['id'] ?>">
        <input type="hidden" name="return_url" value="<?= htmlspecialchars($return_url) ?>">

        <!-- Section 1: Identitas Dasar -->
        <div class="bg-white shadow-sm border border-gray-200 rounded-xl p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-6 flex items-center gap-2">
                <i class="ri-fingerprint-line text-indigo-500"></i> Identitas Dasar
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">No Registrasi <span class="text-red-500">*</span></label>
                    <input type="text" name="registration_no" value="<?= $registration['registration_no'] ?? '' ?>" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" value="<?= $registration['nama'] ?? '' ?>" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">NISN</label>
                    <input type="text" name="nisn" value="<?= $registration['nisn'] ?? '' ?>" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">NIK (Nomor Induk Kependudukan)</label>
                    <input type="text" name="nik" value="<?= $registration['nik'] ?? '' ?>" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Jenis Kelamin <span class="text-red-500">*</span></label>
                    <select name="gender" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border">
                        <option value="L" <?= ($registration['gender'] ?? '') === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                        <option value="P" <?= ($registration['gender'] ?? '') === 'P' ? 'selected' : '' ?>>Perempuan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" value="<?= $registration['tempat_lahir'] ?? '' ?>" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" value="<?= $registration['tanggal_lahir'] ?? '' ?>" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border">
                </div>
            </div>
        </div>

        <!-- Section 2: Alamat & Wilayah -->
        <div class="bg-indigo-50 shadow-sm border border-indigo-200 rounded-xl p-6">
            <h2 class="text-lg font-semibold text-indigo-900 mb-2 flex items-center gap-2">
                <i class="ri-map-pin-2-line text-indigo-600"></i> Sinkronisasi Alamat & Wilayah
            </h2>
            <p class="text-xs text-indigo-700 mb-6 font-medium">Cocokkan data CSV dari form pendaftaran ke database wilayah.id untuk melengkapi alamat yang standar.</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap</label>
                    <textarea name="alamat" rows="2" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border bg-white"><?= htmlspecialchars($registration['alamat'] ?? '') ?></textarea>
                </div>
                <div>
                    <div class="flex justify-between items-end mb-1">
                        <label class="block text-sm font-medium text-gray-700">Provinsi</label>
                        <span class="text-[10px] bg-yellow-100 text-yellow-800 px-2 rounded font-mono font-bold">CSV: <?= htmlspecialchars($registration['provinsi'] ?: '(Kosong)') ?></span>
                    </div>
                    <select id="prov_id" name="prov_id" class="tom-select-region mt-1 block w-full border bg-white">
                        <option value="">Pilih Provinsi...</option>
                        <?php if(!empty($registration['prov_id'])): ?>
                            <option value="<?= $registration['prov_id'] ?>" selected><?= $registration['provinsi'] ?></option>
                        <?php endif; ?>
                    </select>
                    <input type="hidden" id="provinsi" name="provinsi" value="<?= $registration['provinsi'] ?? '' ?>">
                </div>
                <div>
                    <div class="flex justify-between items-end mb-1">
                        <label class="block text-sm font-medium text-gray-700">Kabupaten / Kota</label>
                        <span class="text-[10px] bg-yellow-100 text-yellow-800 px-2 rounded font-mono font-bold">CSV: <?= htmlspecialchars($registration['kabupaten'] ?: '(Kosong)') ?></span>
                    </div>
                    <select id="kab_id" name="kab_id" class="tom-select-region mt-1 block w-full border bg-white">
                        <option value="">Pilih Kabupaten...</option>
                        <?php if(!empty($registration['kab_id'])): ?>
                            <option value="<?= $registration['kab_id'] ?>" selected><?= $registration['kabupaten'] ?></option>
                        <?php endif; ?>
                    </select>
                    <input type="hidden" id="kabupaten" name="kabupaten" value="<?= $registration['kabupaten'] ?? '' ?>">
                </div>
                <div>
                    <div class="flex justify-between items-end mb-1">
                        <label class="block text-sm font-medium text-gray-700">Kecamatan</label>
                        <span class="text-[10px] bg-yellow-100 text-yellow-800 px-2 rounded font-mono font-bold">CSV: <?= htmlspecialchars($registration['kecamatan'] ?: '(Kosong)') ?></span>
                    </div>
                    <select id="kec_id" name="kec_id" class="tom-select-region mt-1 block w-full border bg-white">
                        <option value="">Pilih Kecamatan...</option>
                        <?php if(!empty($registration['kec_id'])): ?>
                            <option value="<?= $registration['kec_id'] ?>" selected><?= $registration['kecamatan'] ?></option>
                        <?php endif; ?>
                    </select>
                    <input type="hidden" id="kecamatan" name="kecamatan" value="<?= $registration['kecamatan'] ?? '' ?>">
                </div>
                <div>
                    <div class="flex justify-between items-end mb-1">
                        <label class="block text-sm font-medium text-gray-700">Kelurahan / Desa</label>
                        <span class="text-[10px] bg-yellow-100 text-yellow-800 px-2 rounded font-mono font-bold">CSV: <?= htmlspecialchars($registration['kelurahan'] ?: '(Kosong)') ?></span>
                    </div>
                    <select id="desa_id" name="desa_id" class="tom-select-region mt-1 block w-full border bg-white">
                        <option value="">Pilih Kelurahan...</option>
                        <?php if(!empty($registration['desa_id'])): ?>
                            <option value="<?= $registration['desa_id'] ?>" selected><?= $registration['kelurahan'] ?></option>
                        <?php endif; ?>
                    </select>
                    <input type="hidden" id="kelurahan" name="kelurahan" value="<?= $registration['kelurahan'] ?? '' ?>">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">RT / RW</label>
                    <input type="text" name="rt_rw" value="<?= $registration['rt_rw'] ?? '' ?>" placeholder="Contoh: 001/002" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Kode Pos <span id="kodepos-loading" class="text-xs text-indigo-500 hidden ml-2"><i class="ri-loader-4-line animate-spin"></i> Mencari...</span></label>
                    <input type="text" id="kode_pos" name="kode_pos" value="<?= $registration['kode_pos'] ?? '' ?>" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border transition-colors">
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
                    <div class="flex justify-between items-end mb-1">
                        <label class="block text-sm font-medium text-gray-700">Nama Kepala Keluarga</label>
                        <div class="flex gap-1">
                            <button type="button" onclick="document.getElementById('nama_kk').value = document.getElementById('nama_wali').value; document.getElementById('nama_kk').classList.add('bg-green-50', 'border-green-400'); setTimeout(()=>document.getElementById('nama_kk').classList.remove('bg-green-50', 'border-green-400'), 1000);" class="text-[10px] font-bold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 px-2 py-0.5 rounded border border-indigo-200 transition-colors" title="Gunakan Nama Ayah / Wali">Ayah</button>
                            <button type="button" onclick="document.getElementById('nama_kk').value = document.getElementById('nama_ibu').value; document.getElementById('nama_kk').classList.add('bg-green-50', 'border-green-400'); setTimeout(()=>document.getElementById('nama_kk').classList.remove('bg-green-50', 'border-green-400'), 1000);" class="text-[10px] font-bold bg-pink-50 text-pink-700 hover:bg-pink-100 px-2 py-0.5 rounded border border-pink-200 transition-colors" title="Gunakan Nama Ibu">Ibu</button>
                        </div>
                    </div>
                    <input type="text" id="nama_kk" name="nama_kk" value="<?= $registration['nama_kk'] ?? '' ?>" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border transition-colors">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nama Wali (Utama / Ayah)</label>
                    <input type="text" id="nama_wali" name="nama_wali" value="<?= $registration['nama_wali'] ?? '' ?>" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">No HP Wali (Utama)</label>
                    <input type="text" name="no_hp_wali" value="<?= $registration['no_hp_wali'] ?? '' ?>" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border">
                </div>
                <div class="md:col-span-1 border-b border-transparent"></div> <!-- Empty cell for grid layout -->

                <div class="border-t border-gray-100 pt-4 md:col-span-2">
                    <h3 class="text-sm font-bold text-gray-500 uppercase mb-4">Data Ayah</h3>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Pekerjaan Ayah</label>
                    <input type="text" name="pekerjaan_ayah" value="<?= $registration['pekerjaan_ayah'] ?? '' ?>" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nomor HP Ayah</label>
                    <input type="text" name="no_hp_ayah" value="<?= $registration['no_hp_ayah'] ?? '' ?>" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border">
                </div>
                <div class="border-t border-gray-100 pt-4 md:col-span-2">
                    <h3 class="text-sm font-bold text-gray-500 uppercase mb-4">Data Ibu</h3>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nama Ibu Kandung</label>
                    <input type="text" id="nama_ibu" name="nama_ibu" value="<?= $registration['nama_ibu'] ?? '' ?>" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Pekerjaan Ibu</label>
                    <input type="text" name="pekerjaan_ibu" value="<?= $registration['pekerjaan_ibu'] ?? '' ?>" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nomor HP Ibu</label>
                    <input type="text" name="no_hp_ibu" value="<?= $registration['no_hp_ibu'] ?? '' ?>" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5 border">
                </div>
            </div>
        </div>

        <div class="pt-5 border-t border-gray-200 flex justify-end gap-3">
            <a href="<?= url($return_url) ?>" class="bg-white py-2.5 px-6 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                Batal
            </a>
            <button type="submit" class="inline-flex justify-center py-2.5 px-8 border border-transparent shadow-lg text-sm font-bold rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-200 transition-all">
                Simpan & Perbarui Data
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

                        // Auto-fetch Postal Code when Village is selected
                        if (el.id === 'desa_id' && val && this.options[val]) {
                            fetchPostalCode(this.options[val].name);
                        }
                    }
                });
            });

            function getNextRegionId(currentId) {
                const flow = ['prov_id', 'kab_id', 'kec_id', 'desa_id'];
                const idx = flow.indexOf(currentId);
                return idx < flow.length - 1 ? flow[idx+1] : null;
            }

            async function fetchPostalCode(villageName) {
                const kodeposInput = document.getElementById('kode_pos');
                const loadingIndicator = document.getElementById('kodepos-loading');
                if (!kodeposInput || !villageName) return;

                const kecamatanName = document.getElementById('kecamatan').value.trim();
                if (loadingIndicator) loadingIndicator.classList.remove('hidden');
                console.log("Auto-fetch postal code for village:", villageName, ", kecamatan:", kecamatanName);
                
                try {
                    // Use local proxy to avoid CORS/Mixed-Content blocks
                    const url = `<?= url('/api/postal-codes') ?>?search=${encodeURIComponent(villageName.trim())}`;
                    const response = await fetch(url);
                    if (!response.ok) throw new Error("API error: " + response.status);
                    
                    const result = await response.json();
                    console.log("Carikodepos API result:", result);

                    if (result.success && result.data && result.data.postalCodes.length > 0) {
                        let matched = result.data.postalCodes.find(p => 
                            p.district && p.district.name.trim().toLowerCase() === kecamatanName.toLowerCase()
                        );
                        
                        if (!matched) {
                            matched = result.data.postalCodes.find(p => 
                                p.village && p.village.name.trim().toLowerCase() === villageName.trim().toLowerCase()
                            );
                        }
                        
                        if (!matched && result.data.postalCodes.length > 0) {
                            matched = result.data.postalCodes[0];
                        }

                        if (matched && matched.code) {
                            console.log("Matched postal code:", matched.code);
                            kodeposInput.value = matched.code;
                            kodeposInput.classList.add('bg-green-50', 'border-green-400');
                            setTimeout(() => {
                                kodeposInput.classList.remove('bg-green-50', 'border-green-400');
                            }, 2000);
                        } else {
                            console.log("No matching postal code found.");
                        }
                    }
                } catch (e) {
                    console.error('Failed to fetch postal code:', e);
                } finally {
                    if (loadingIndicator) loadingIndicator.classList.add('hidden');
                }
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

            // Initial load sequence for edit mode
            (async () => {
                const initProv = "<?= $registration['prov_id'] ?? '' ?>";
                const initKab  = "<?= $registration['kab_id'] ?? '' ?>";
                const initKec  = "<?= $registration['kec_id'] ?? '' ?>"; 
                const initDesa = "<?= $registration['desa_id'] ?? '' ?>";

                // Step 1: Provinces
                await loadRegions('prov_id', '', initProv);

                // Sequential load
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
