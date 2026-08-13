# Panduan Kontribusi (Contributing Guidelines)

Terima kasih telah berkontribusi! Untuk menjaga kerapian dan konsistensi riwayat perubahan (*commit history*), kami menggunakan standar penulisan pesan *commit* (berdasarkan *Conventional Commits*).

## Format Pesan Commit
Pastikan Anda menggunakan format berikut setiap kali melakukan commit, dan **pesan commit HARUS ditulis dalam bahasa Inggris (English)**:

`<tipe>(<opsional-cakupan>): <pesan_singkat_dalam_bahasa_inggris>`

### Daftar Tipe Commit yang Diizinkan:
* **`feat`** : Fitur baru
* **`fix`** : Perbaikan bug
* **`security`** : Perubahan terkait keamanan sistem
* **`style`** : Perubahan UI, CSS, tampilan (tidak mengubah logika)
* **`refactor`** : Merapikan struktur kode tanpa mengubah fungsionalitas / perilaku
* **`perf`** : Optimasi performa
* **`docs`** : Penambahan/pembaruan dokumentasi (contoh: README)
* **`test`** : Penambahan/perbaikan *unit test* atau *integration test*
* **`build`** : Perubahan pada sistem *build* atau dependensi eksternal
* **`ci`** : Perubahan pada konfigurasi CI/CD (contoh: GitHub Actions)
* **`chore`** : Pekerjaan pendukung teknis (pembaruan `.gitignore`, pembersihan rutin)

### Contoh Penulisan:
* `feat(workspace): drag & drop`
* `feat(database): add PostgreSQL support`
* `feat(database): add SQLite support`
* `fix(reminder): delete bug`
* `style(ui): redesign dashboard`
* `security: improve AES encryption`
* `docs: update README`

Dengan mengikuti standar ini, proyek akan lebih mudah ditelusuri riwayat perubahannya dan mempermudah kerja sama dalam tim.

## Aturan Khusus (AI/Agent)
**PENTING**: Dilarang keras melakukan `git commit` (ataupun `git push`) sebelum ada perintah/instruksi eksplisit dari *User*. Hal ini bertujuan untuk menghindari jumlah *commit* yang berlebihan, *history* yang kotor, serta risiko *force push* ketika suatu masalah (*bug/feature*) belum benar-benar selesai secara tuntas.
