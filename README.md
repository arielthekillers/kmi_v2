# KMI App v2 — Sistem Manajemen Pengajaran

Aplikasi web modern untuk mengelola kegiatan akademik di PM. Darussalam Bogor. Dibangun ulang dengan arsitektur **MVC (Model-View-Controller)** menggunakan PHP native + MySQL (PDO), menggantikan sistem lama berbasis JSON flat-file.

---

## ✨ Fitur Utama

### 🔐 Autentikasi & Profil
- **Dual Role Authentication** — Admin dan Pengajar dengan akses berbeda
- **Profil Pengajar** — Upload foto, biodata lengkap (NIK, TTL, pendidikan, orang tua, alamat)
- **Ganti Password** — Pengajar bisa ganti sendiri; Admin bisa reset password user mana saja

### 📚 Master Data (Admin Only)
- **Manajemen Pelajaran** — Tambah, edit, hapus mata pelajaran beserta skala nilai
- **Manajemen Kelas** — Kelola tingkat & abjad kelas dengan jumlah santri
- **Manajemen Pengajar** — CRUD data pengajar dengan search & paginasi; username otomatis dari nomor HP

### 📅 Jadwal Mengajar
- **Jadwal Kelas** — Admin dapat mengatur jadwal per kelas (per jam & hari) dengan assign pengajar dan mapel
- **Jadwal Saya** — Pengajar melihat jadwal mengajar pribadi dalam tampilan tabel mingguan

### ✅ Koreksi Nilai (Grading)
- **Assignment Koreksi** — Admin membuat tugas koreksi per pelajaran, kelas, dan pengajar
- **Input Nilai** — Pengajar input skor santri; konversi otomatis ke nilai berdasarkan skala
  - Ketik angka (0–100) → nilai otomatis
  - Ketik `-` → absen (nilai 0)
  - Ketik `0` → salah semua (nilai minimum skala)
- **Status Koreksi** — Belum → Proses → Selesai (read-only)
- **Unlock (Admin)** — Admin bisa buka kembali koreksi yang sudah selesai

### 🛡️ Tanqih Idad (Verifikasi Kehadiran Pengajar)
- **Cek Kehadiran Per Jam** — Piket Syeikh Diwan verifikasi kehadiran pengajar secara real-time
- **Status Verifikasi** — Hadir / Justified (izin/sakit) dengan pencatatan nama verifikator & waktu
- **Batasan Waktu** — Verifikasi hanya pukul 06:30–14:15 (dikecualikan untuk admin)
- **Anti Self-Verify** — Pengajar tidak bisa memverifikasi diri sendiri
- **Laporan Tanqih** — Filter berdasarkan rentang tanggal, statistik global per pengajar

### 📋 Absensi Pengajar (Piket Keliling)
- **Input Absensi** — Piket Keliling mencatat status kehadiran pengajar per jam per kelas
- **Status**: Hadir (tepat waktu / terlambat), Tidak Hadir, Diganti (dengan pengajar pengganti)
- **Laporan Lengkap** — Filter berdasarkan tanggal, kelas, pengajar; statistik agregat

### 📌 Jadwal Piket
- **Jadwal Syeikh Diwan** — Admin mengatur siapa yang bertugas sebagai Syeikh Diwan per hari
- **Jadwal Piket Keliling** — Admin mengatur petugas Piket Keliling per hari

### 📺 TV Showcase
- **Display Layar TV** — Tampilan real-time jadwal hari ini, status kehadiran, dan tanqih
- **Live Stats** — Statistik terverifikasi / pending otomatis diperbarui
- **Verifikasi Terbaru** — Feed 10 verifikasi terakhir dengan foto profil pengajar
- **Piket Hari Ini** — Tampilkan Syeikh Diwan & Piket Keliling bertugas
- **Inspirational Quotes** — Rotating motto & pancajiwa pondok

---

## 🏗️ Arsitektur

Aplikasi menggunakan arsitektur **MVC kustom** dengan autoloading berbasis namespace.

```
kmi_v2/
├── index.php               # Entry point
├── .htaccess               # URL rewriting ke index.php
│
├── app/
│   ├── Core/               # Framework inti
│   │   ├── App.php         # Router registration & dispatcher
│   │   ├── Router.php      # HTTP routing (GET/POST)
│   │   ├── Controller.php  # Base controller (view, redirect)
│   │   ├── Model.php       # Base model
│   │   └── Database.php    # PDO singleton connection
│   │
│   ├── Config/
│   │   └── database.php    # Konfigurasi koneksi MySQL
│   │
│   ├── Controllers/        # Controller utama aplikasi
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── ProfileController.php
│   │   ├── TeacherController.php
│   │   ├── SubjectController.php
│   │   ├── KelasController.php
│   │   ├── ScheduleController.php
│   │   ├── GradeController.php
│   │   ├── PiketController.php
│   │   ├── TanqihController.php
│   │   ├── AttendanceController.php
│   │   ├── TvShowcaseController.php
│   │   └── MediaController.php
│   │
│   ├── Models/             # Model query database
│   │   ├── TeacherModel.php
│   │   ├── SubjectModel.php
│   │   ├── KelasModel.php
│   │   ├── ScheduleModel.php
│   │   ├── GradeModel.php
│   │   ├── PiketModel.php
│   │   ├── TanqihModel.php
│   │   └── AttendanceModel.php
│   │
│   ├── Views/              # Template PHP halaman
│   │   ├── dashboard.php
│   │   ├── tvshowcase.php
│   │   ├── auth/           login, logout
│   │   ├── grades/         index, edit
│   │   ├── teachers/       index
│   │   ├── subjects/       index
│   │   ├── kelas/          index
│   │   ├── schedule/       index, my_schedule
│   │   ├── piket/          index
│   │   ├── tanqih/         index, report
│   │   ├── attendance/     index, report
│   │   └── profile/        index, change_password
│   │
│   └── Modules/            # Modul fitur terpisah (opsional/migrasi)
│       ├── Auth/
│       ├── Attendance/
│       ├── Classes/
│       ├── Dashboard/
│       ├── Duties/
│       ├── Grades/
│       ├── Students/
│       ├── Subjects/
│       ├── Teachers/
│       └── TeachingLogs/
│
├── helpers/                # Fungsi global
│   ├── auth.php            # Auth, CSRF, role check, piket checker
│   ├── layout.php          # renderHeader/Footer, UI components
│   ├── sidebar_layout.php  # Layout sidebar untuk halaman dalam
│   ├── profile_helper.php  # Fungsi biodata & update pengajar
│   ├── file_helper.php     # Baca/tulis JSON helper (legacy)
│   └── utilities.php       # url(), redirect(), flash messages
│
├── uploads/
│   └── profiles/           # Foto profil pengajar (JPG/PNG/WebP, max 2MB)
│
└── public/                 # Aset publik
    ├── img/
    └── sound/
```

---

## ⚙️ Requirements

| Komponen | Versi |
|---|---|
| PHP | 7.4+ (rekomendasi 8.0+) |
| MySQL / MariaDB | 5.7+ |
| Apache | dengan `mod_rewrite` enabled |
| PHP Extensions | `pdo_mysql`, `json`, `session`, `mbstring`, `fileinfo` |

---

## 🚀 Instalasi

### 1. Upload / Clone Project

```bash
git clone https://github.com/yourusername/kmi_v2.git
cd kmi_v2
```

Atau upload manual ke document root Apache (misalnya `htdocs/kmi_v2`).

### 2. Buat Database MySQL

```sql
CREATE DATABASE kmi_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Kemudian jalankan file SQL migrasi (ada di tiap subfolder `Modules/*/`):

```
app/Modules/Auth/create_users_table.sql
app/Modules/Attendance/create_table.sql
app/Modules/Attendance/create_teacher_attendance.sql
app/Modules/Grades/create_table.sql
app/Modules/Students/create_tables.sql
app/Modules/Duties/create_table.sql
app/Modules/TeachingLogs/create_table.sql
```

### 3. Konfigurasi Database

Edit `app/Config/database.php`:

```php
return [
    'host'     => '127.0.0.1',
    'dbname'   => 'kmi_db',
    'username' => 'root',
    'password' => '',   // sesuaikan
    'charset'  => 'utf8mb4',
];
```

### 4. Set Permissions

```bash
chmod 755 uploads/
chmod 755 uploads/profiles/
```

### 5. Verifikasi Apache Rewrite

Pastikan `.htaccess` di root aktif dan Apache memiliki `AllowOverride All`. Coba akses `http://localhost/kmi_v2/` — harus menuju halaman login jika belum login.

### 6. PHP Configuration (Production)

```ini
display_errors = Off
log_errors = On
error_reporting = E_ALL
```

---

## 🔑 Default Credentials

**Admin Default:**
- Username: `admin`
- Password: `admin`

> ⚠️ **PENTING**: Ganti password default setelah login pertama kali!

> 💡 **Note**: Info default credentials hanya tampil di `localhost`. Di production tersembunyi otomatis.

---

## 🔗 Route Map

| Method | URL | Akses | Keterangan |
|--------|-----|-------|------------|
| GET | `/` | Login | Dashboard utama |
| GET/POST | `/login` `/authenticate` | Publik | Autentikasi |
| GET | `/logout` | Login | Logout |
| GET/POST | `/profil` `/profil/simpan` | Pengajar | Edit profil & foto |
| GET/POST | `/change-password` | Admin | Reset password user |
| GET | `/jadwal-saya` | Login | Jadwal mengajar pribadi |
| GET | `/avatar` | Login | Serve foto profil |
| GET | `/tvshowcase` | Publik | Tampilan layar TV |
| GET | `/api/tv-data` | Publik | JSON data untuk TV |
| GET/POST | `/subjects` | Admin | Master pelajaran |
| GET/POST | `/teachers` | Admin | Master pengajar |
| GET/POST | `/classes` | Admin | Master kelas |
| GET/POST | `/schedule` | Admin | Jadwal mengajar |
| GET/POST | `/grades` | Login | Koreksi nilai |
| GET/POST | `/piket/office` `/piket/roaming` | Admin | Jadwal piket |
| GET/POST | `/tanqih` `/tanqih/verify` `/tanqih/report` | Login | Tanqih idad |
| GET/POST | `/attendance` `/attendance/store` `/attendance/report` | Login | Absensi pengajar |

---

## 🔒 Security Features

| Fitur | Status |
|-------|--------|
| CSRF Protection | ✅ Semua form POST dilindungi token |
| Input Validation | ✅ GET/POST parameter divalidasi ketat |
| Role-Based Access Control | ✅ `require_admin()`, `require_login()` |
| Piket-Based Access | ✅ Hak akses tanqih & absensi per jadwal piket |
| Password Hashing | ✅ bcrypt (`password_hash`) |
| File Upload Validation | ✅ MIME type, ukuran max 2MB |
| Session Security | ✅ Httponly cookies, regenerasi session ID |
| Data Protection | ✅ `.htaccess` blokir akses langsung ke `/data` |
| Error Handling | ✅ Production mode menyembunyikan error dari user |

---

## 🚦 Status Workflow

### Koreksi Nilai
```
Belum Diperiksa → [Pengajar input nilai] → Proses → [Pengajar tandai selesai] → Selesai (read-only)
                                                                                      ↑
                                                              [Admin unlock] ← ───────┘
```

### Tanqih Idad
```
Jadwal hari ini → [Syeikh Diwan verifikasi] → Terverifikasi (Hadir / Justified)
                                             → Belum Terverifikasi (Pending)
```

### Absensi Pengajar
```
[Piket Keliling input] → Hadir (tepat waktu / terlambat) | Tidak Hadir | Diganti
```

---

## 🛠️ Troubleshooting

**Problem: Halaman 404 / routing tidak bekerja**
- Pastikan `mod_rewrite` aktif dan `.htaccess` terbaca
- Cek `AllowOverride All` di konfigurasi Apache vhost

**Problem: "Invalid CSRF token"**
- Refresh halaman sebelum submit form
- Cek bahwa sesi PHP aktif dan tidak expired

**Problem: Upload foto profil gagal**
- Pastikan folder `uploads/profiles/` ada dan writable
- Cek ukuran file (max 2MB) dan format (JPG, PNG, WebP)

**Problem: Koneksi database gagal**
- Verifikasi credentials di `app/Config/database.php`
- Pastikan service MySQL/MariaDB berjalan
- Cek nama database sesuai (`kmi_db`)

**Problem: Default credentials muncul di production**
- Ini by design: tersembunyi otomatis jika `HTTP_HOST` bukan `localhost` / `127.0.0.1`

---

## 📄 License

Copyright © 2025–2026 PM. Darussalam Bogor. All rights reserved.

## 💬 Support

Untuk pertanyaan atau issue, hubungi administrator sistem.
