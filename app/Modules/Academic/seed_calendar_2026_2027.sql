-- ============================================================
-- Seed Data: Kalender Akademik 2026/2027
-- Jalankan SETELAH create_calendar_table.sql
-- Ganti @year_id sesuai id tahun ajaran 2026/2027 di database Anda
-- ============================================================

-- Ambil ID tahun ajaran 2026/2027 (sesuaikan jika perlu)
SET @year_id = (SELECT id FROM academic_years WHERE name LIKE '%2026%2027%' OR name LIKE '%2026/2027%' LIMIT 1);

INSERT INTO `academic_calendar_events` (`academic_year_id`, `tanggal_mulai`, `tanggal_selesai`, `keterangan`, `kategori`) VALUES
-- Juli - Agustus 2026
(@year_id, '2026-07-27', NULL,         'Pembukaan Tahun Ajaran 2026-2027',                             'Kegiatan'),
(@year_id, '2026-08-02', NULL,         'Apel Tahunan',                                                 'Kegiatan'),
(@year_id, '2026-08-03', '2026-08-05', 'Kuliah Umum Khutbatul Arsy',                                   'Akademik'),
(@year_id, '2026-08-09', '2026-11-19', 'Periode Efektif KBM',                                          'Akademik'),
(@year_id, '2026-08-15', NULL,         'Pengarahan Pengajara Sore & Awal Muwajahah',                   'Akademik'),
-- September 2026
(@year_id, '2026-09-10', NULL,         'Penetapan Panitia Ulangan Umum Pertengahan Tahun',             'Ujian'),
(@year_id, '2026-09-26', NULL,         'Malam Pentas Seni AKSARA',                                     'Kegiatan'),
(@year_id, '2026-09-29', NULL,         'Taftiys Kutub Siswa Kelas I-VI',                               'Akademik'),
-- Oktober 2026
(@year_id, '2026-10-03', '2026-10-07', 'Ulangan Umum Pertengahan Tahun & Ta\'hil Siswa Kelas Akhir KMI', 'Ujian'),
(@year_id, '2026-10-29', NULL,         'Penetapan Panitia Ujian Pertengahan Tahun',                    'Ujian'),
(@year_id, '2026-10-31', '2026-11-03', 'Lomba Cerdas Cermat (Malam)',                                  'Kegiatan'),
-- November 2026
(@year_id, '2026-11-11', NULL,         'Penilaian An-Nisaiyyah',                                       'Ujian'),
(@year_id, '2026-11-12', NULL,         'Penilaian Kepramukaan',                                        'Kegiatan'),
(@year_id, '2026-11-16', NULL,         'Ujian Bahasa',                                                 'Ujian'),
(@year_id, '2026-11-21', NULL,         'Pengarahan dan Pembagian Tugas Ujian Lisan Pertengahan Tahun & Penetapan Panitia Siswa Akhir KMI', 'Ujian'),
(@year_id, '2026-11-22', NULL,         'Tanqihi\'dan Penguji Ujian Lisan Pertengahan Tahun',           'Ujian'),
(@year_id, '2026-11-23', '2026-12-01', 'Masa Ujian Lisan Pertengahan Tahun',                           'Ujian'),
(@year_id, '2026-12-02', NULL,         'Pengarahan dan Pembagian Tugas Ujian Tulis Pertengahan Tahun', 'Ujian'),
-- Desember 2026
(@year_id, '2026-12-03', '2026-12-14', 'Masa Ujian Tulis Pertengahan Tahun',                           'Ujian'),
(@year_id, '2026-12-19', '2026-12-24', 'Masa Ujian Tulis Siswa Akhir KMI Gelombang I',                 'Ujian'),
(@year_id, '2026-12-28', NULL,         'Upacara Pembukaan Akhir Tahun Ajaran dan Absen Disiplin',      'Kegiatan'),
(@year_id, '2026-12-30', '2027-05-20', 'Periode Efektif KBM',                                          'Akademik'),
-- Januari 2027
(@year_id, '2027-01-02', '2027-01-07', 'Kursus Mahir Tingkat Dasar (KMD) Kelas V',                    'Akademik'),
(@year_id, '2027-01-02', '2027-01-05', 'Ujian Kompetensi Qira\'ah dan Ibadah Siswa Akhir KMI',        'Ujian'),
(@year_id, '2027-01-16', '2027-01-19', 'Darussalam Cup Season X',                                      'Kegiatan'),
(@year_id, '2027-01-21', NULL,         'Penetapan Panitia Ulangan Umum Akhir Tahun',                   'Ujian'),
(@year_id, '2027-01-21', '2027-01-26', 'Laporan Pertanggungjawaban Pelantikan Pengurus OSADA',         'Kegiatan'),
(@year_id, '2027-01-27', NULL,         'Pengarahan Pengajara Sore Kelas 5 (Pagi)',                     'Akademik'),
(@year_id, '2027-01-30', '2027-02-06', 'Masa Fathul Kutub Siswa Akhir KMI',                            'Akademik'),
-- Februari 2027
(@year_id, '2027-02-23', NULL,         'Akhir KBM Siswa Akhir KMI',                                    'Akademik'),
-- Maret 2027
(@year_id, '2027-03-21', NULL,         'Taftiys Kutub Siswa Akhir KMI',                                'Akademik'),
(@year_id, '2027-03-22', '2027-03-30', 'Masa Ujian Lisan Siswa Akhir KMI Gelombang II',               'Ujian'),
(@year_id, '2027-03-27', '2027-03-31', 'Ulangan Umum Akhir Tahun',                                     'Ujian'),
(@year_id, '2027-03-31', '2027-04-03', 'Taujihat Amaliyah Tadris',                                     'Akademik'),
-- April 2027
(@year_id, '2027-04-04', '2027-04-20', 'Amaliyah Tadris',                                              'Akademik'),
(@year_id, '2027-04-08', NULL,         'Penetapan Panitia Ujian Akhir Tahun dan Panitia Qurban',       'Ujian'),
(@year_id, '2027-04-21', NULL,         'Penutupan dan Kesan-kesan Amaliyah Tadris',                    'Kegiatan'),
(@year_id, '2027-04-22', '2027-05-08', 'Masa Ujian Tulis Siswa Akhir KMI Gelombang II',               'Ujian'),
-- Mei 2027
(@year_id, '2027-05-07', NULL,         'Perfotoan Santri Darussalam Kelas I-V KMI',                    'Kegiatan'),
(@year_id, '2027-05-09', '2027-05-13', 'Pembekalan Siswa Akhir KMI',                                   'Akademik'),
(@year_id, '2027-05-11', NULL,         'Ujian Tulis Tahfidz',                                          'Ujian'),
(@year_id, '2027-05-13', NULL,         'Penilaian Kepramukaan',                                        'Kegiatan'),
(@year_id, '2027-05-18', NULL,         'Ujian Bahasa',                                                 'Ujian'),
(@year_id, '2027-05-19', NULL,         'Penilaian An-Nisaiyyah',                                       'Ujian'),
(@year_id, '2027-05-22', NULL,         'Pengarahan dan Pembagian Tugas Ujian Lisan Akhir Tahun',       'Ujian'),
(@year_id, '2027-05-23', NULL,         'Tanqihi\'dan Penguji Ujian Lisan Akhir Tahun',                 'Ujian'),
(@year_id, '2027-05-24', '2027-06-01', 'Masa Ujian Lisan Akhir Tahun',                                 'Ujian'),
(@year_id, '2027-05-27', NULL,         'Pengarahan dan Pembagian Tugas Ujian Tulis Akhir Tahun',       'Ujian'),
-- Juni 2027
(@year_id, '2027-06-03', '2027-06-14', 'Masa Ujian Tulis Akhir Tahun',                                 'Ujian'),
(@year_id, '2027-06-14', '2027-06-16', 'Khutsanul Wada\'',                                             'Kegiatan'),
(@year_id, '2027-06-19', NULL,         'Yudisium dan Khataman Siswa Akhir KMI',                        'Kegiatan'),
(@year_id, '2027-06-21', NULL,         'Rapat Penentuan Kenaikan Kelas Siswa KMI',                     'Akademik');
