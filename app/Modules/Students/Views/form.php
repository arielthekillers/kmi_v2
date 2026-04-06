<main class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Tambah Data Santri</h1>
    </div>

    <form action="/students/store" method="POST" class="bg-white shadow-sm border border-gray-200 rounded-lg p-6 space-y-6">
        
        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
            <div class="sm:col-span-3">
                <label for="nis" class="block text-sm font-medium text-gray-700">NIS</label>
                <div class="mt-1">
                    <input type="text" name="nis" id="nis" required class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                </div>
            </div>

            <div class="sm:col-span-3">
                <label for="gender" class="block text-sm font-medium text-gray-700">Jenis Kelamin</label>
                <div class="mt-1">
                    <select id="gender" name="gender" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>
            </div>

            <div class="sm:col-span-6">
                <label for="nama" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                <div class="mt-1">
                    <input type="text" name="nama" id="nama" required class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                </div>
            </div>

            <div class="sm:col-span-6">
                 <label for="kelas_id" class="block text-sm font-medium text-gray-700">Kelas</label>
                 <div class="mt-1">
                     <select id="kelas_id" name="kelas_id" required class="tom-select block w-full p-2 border">
                        <option value="">Pilih Kelas...</option>
                        <?php foreach($kelas as $k): ?>
                            <option value="<?= $k['id'] ?>"><?= $k['tingkat'] . ' ' . $k['abjad'] ?></option>
                        <?php endforeach; ?>
                     </select>
                 </div>
            </div>

            <div class="sm:col-span-6">
                <label for="nama_wali" class="block text-sm font-medium text-gray-700">Nama Wali</label>
                <div class="mt-1">
                    <input type="text" name="nama_wali" id="nama_wali" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                </div>
            </div>
            
            <div class="sm:col-span-3">
                <label for="tempat_lahir" class="block text-sm font-medium text-gray-700">Tempat Lahir</label>
                <div class="mt-1">
                    <input type="text" name="tempat_lahir" id="tempat_lahir" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                </div>
            </div>
            
             <div class="sm:col-span-3">
                <label for="tanggal_lahir" class="block text-sm font-medium text-gray-700">Tanggal Lahir</label>
                <div class="mt-1">
                    <input type="date" name="tanggal_lahir" id="tanggal_lahir" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                </div>
            </div>

        </div>

        <div class="pt-5 flex justify-end">
            <a href="/students" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Batal
            </a>
            <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Simpan
            </button>
        </div>
    </form>

</main>
