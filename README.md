# KMI App - Sistem Koreksi Nilai Ujian

Aplikasi web untuk mengelola koreksi nilai ujian di PM. Darussalam Bogor. Sistem ini memungkinkan admin untuk membuat assignment koreksi dan pengoreksi untuk input nilai dengan formula konversi otomatis.

## Features

- 🔐 **Dual Role Authentication** - Admin dan Pengoreksi dengan akses berbeda
- 📚 **Master Data Management** - Kelola Pelajaran, Kelas, dan Pengoreksi  
- ✅ **Koreksi Assignment** - Assign tugas koreksi ke pengoreksi tertentu
- 📊 **Formula Konversi Nilai** - Otomatis konversi skor ke nilai dengan skala custom
- 📈 **Dashboard & Statistics** - Monitoring progress koreksi real-time
- 🔒 **Security Features** - CSRF protection, input validation, role-based access control

## Requirements

- **PHP** 7.4+ (recommended 8.0+)
- **Apache** with mod_rewrite enabled
- **Extensions**: 
  - `json`
  - `session`
  - `mbstring`

## Installation

### 1. Clone/Upload Project

```bash
# Via Git
git clone https://github.com/yourusername/nilaiujian.git
cd nilaiujian

# Atau upload manual via FTP ke document root
```

### 2. Set Permissions

Pastikan folder `data/` memiliki permission write untuk PHP:

```bash
chmod 755 data/
chmod 644 data/*.json
chmod 644 data/.htaccess
```

### 3. Apache Configuration

Pastikan `.htaccess` di root dan di folder `data/` sudah ter-upload dengan benar.

**Verify `.htaccess` aktif:**
- Coba akses `http://yourdomain.com/data/users.json`
- Harus return `403 Forbidden` atau `404 Not Found`

### 4. PHP Configuration (Production)

Edit `php.ini` atau gunakan `.htaccess` untuk set:

```ini
display_errors = Off
log_errors = On
error_reporting = E_ALL
```

## Default Credentials

**Admin Default:**
- Username: `admin`
- Password: `admin`

> ⚠️ **PENTING**: Ganti password default setelah login pertama kali!

> 💡 **Note**: Default credentials hanya ditampilkan di localhost. Di production (non-localhost), info ini hidden otomatis.

## Usage

### Login Roles

1. **Admin** - Login dengan username `admin`
   - Kelola master data (Pelajaran, Kelas, Pengoreksi)
   - Create assignment koreksi
   - View semua data koreksi
   - Unlock koreksi yang sudah selesai

2. **Pengoreksi** - Login dengan nomor HP (tercatat di data pengoreksi)
   - View assignment yang di-assign ke diri sendiri saja
   - Input nilai untuk assignment yang belum selesai
   - Dashboard dengan progress tracking

### Workflow Koreksi

1. **Admin** membuat data master:
   - **Pelajaran**: Nama, Skor Max, Skala Nilai (contoh: 80-30)
   - **Kelas**: Tingkat, Abjad, Jumlah Santri
   - **Pengoreksi**: Nama, No HP (akan jadi username)

2. **Admin** create assignment koreksi:
   - Pilih Pelajaran
   - Pilih Kelas
   - Pilih Pengoreksi
   - Status awal: "Belum Diperiksa"

3. **Pengoreksi** login dan input nilai:
   - Buka "Koreksi Ujian" → Akan melihat assignment sendiri
   - Klik "Input Nilai"
   - Masukkan skor:
     - Ketik angka normal (0-100) → Auto convert ke nilai
     - Ketik `-` untuk absen → Nilai 0
     - Ketik `0` untuk salah semua → Nilai minimum dari skala
   - Simpan Draft atau Selesai Diperiksa

4. **Status Monitoring**:
   - 🔵 **Belum**: Belum ada nilai yang diinput
   - 🟡 **Proses**: Ada draft nilai tapi belum selesai
   - 🟢 **Selesai**: Sudah ditandai selesai, read-only

5. **Unlock (Admin Only)**:
   - Jika perlu edit nilai yang sudah selesai
   - Admin bisa "Buka Akses" untuk kembalikan ke status Proses

## File Structure

```
nilaiujian/
├── .htaccess              # Apache config & security
├── data/                  # JSON database
│   ├── .htaccess          # Block direct access
│   ├── pelajaran.json    
│   ├── kelas.json
│   ├── pengoreksi.json
│   ├── koreksi.json
│   ├── users.json         # Admin users
│   └── nilai/             # Grade files
├── helpers/               
│   ├── auth.php           # Auth & CSRF functions
│   └── layout.php         # Layout helper
├── *.php                  # Pages & handlers
└── README.md
```

## Security Features

✅ **CSRF Protection** - All POST forms protected with CSRF tokens  
✅ **Input Validation** - GET/POST parameters validated  
✅ **Access Control** - Role-based access (require_admin, require_login)  
✅ **Data Protection** - `.htaccess` blocks direct access to `/data` folder  
✅ **Session Security** - PHP session with httponly cookies  
✅ **Password Hashing** - bcrypt for all passwords  
✅ **Error Handling** - Production mode hides errors from users  

## Deployment Checklist

Before deploying to production:

- [ ] Change default admin password
- [ ] Verify `.htaccess` files uploaded correctly
- [ ] Test `/data/users.json` returns 403/404
- [ ] Set PHP `display_errors = Off`
- [ ] Enable `log_errors = On` with proper error_log path
- [ ] Verify file permissions (755 for dirs, 644 for files)
- [ ] Test CSRF protection on all forms
- [ ] Backup `data/` folder regularly

## Troubleshooting

**Problem: "Invalid CSRF token"**
- Solution: Refresh halaman sebelum submit form
- Cause: Session expired atau token mismatch

**Problem: Bisa akses `data/users.json` via browser**
- Solution: Check `.htaccess` di folder `data/` exists
- Verify Apache `AllowOverride All` di vhost config

**Problem: "Permission denied" error**
- Solution: `chmod 755 data/ && chmod 644 data/*.json`
- Pastikan web server bisa write ke `data/`

**Problem: Default credentials masih muncul di production**
- Check: `$_SERVER['HTTP_HOST']` value harus bukan localhost/127.0.0.1
- Note: Ini feature, bukan bug. Hidden otomatis di non-localhost.

## License

Copyright © 2025. All rights reserved.

## Support

Untuk pertanyaan atau issue, hubungi administrator sistem.
