# SmartCBT School Edition

> **Sistem Ujian Berbasis Komputer & Evaluasi Akademik Sekolah (Self-Hosted / Cloud Ready)**  
> Dibangun sesuai spesifikasi resmi **Product Requirements Document (PRD) v2.1**.

---

## 🌟 Keunggulan Arsitektur & Fitur Utama

1. **Mesin Ujian Anti-Lag (Offline-First Client Engine):**
   - Didukung **Vue.js 3 + Pinia + Dexie.js (IndexedDB)**.
   - Naskah soal diunduh sekali di awal sesi (*Pre-fetching*), membuat navigasi antar nomor soal instan (**0 ms latency**).
   - Jawaban tersimpan otomatis di perangkat siswa dalam waktu **< 10 ms**.
   - Jawaban tetap aman jika Wi-Fi putus, komputer laboratorium mati mendadak, atau baterai habis.
   - Sinkronisasi latar belakang (*Background Batch Sync*) ke server Laravel setiap 60 detik atau setiap 5 soal dijawab.

2. **Standar Desain UI/UX "Anti-AI Slop" (High-Craft Editorial & Swiss Grid):**
   - **Paper-First Palette:** Kanvas baca soal berlatar `#FAF9F5` (*Warm Alabaster*) dan teks *Deep Ink* `#0F172A` untuk kenyamanan mata selama ujian 90–120 menit.
   - **Tactile Keycaps:** Tombol pilihan ganda `[A]`, `[B]`, `[C]`, `[D]`, `[E]` dengan navigasi *keyboard shortcuts*.
   - **Tabular Countdown Timer:** Angka hitung mundur berbasis `font-mono tabular-nums` yang tidak bergeser setiap detik.
   - **Measure Limit:** Lebar teks bacaan dibatasi `max-w-prose` (65–75 karakter) agar mata tidak lelah.

3. **5 Tipe Soal Kurikulum Merdeka & AKM:**
   - **Pilihan Ganda Biasa:** 1 jawaban benar (opsi A–E).
   - **Pilihan Ganda Kompleks (MCMA):** Kotak centang dengan lebih dari satu jawaban benar.
   - **Benar / Salah:** Tabel pernyataan majemuk.
   - **Menjodohkan (Matching):** Menghubungkan premis di kolom kiri dengan jawaban di kolom kanan.
   - **Esai / Uraian:** Isian singkat (*auto-graded*) dan uraian panjang dengan rubrik penskoran.
   - **Split-Pane AKM View:** Layar terbagi dua kolom mandiri untuk soal wacana teks panjang / stimulus literasi.
   - **Dukungan KaTeX & Teks Arab (RTL):** Render rumus matematika/sains lokal dan teks Arab berharakat proporsional.
   - **Import Soal Word (.docx) & Excel (.xlsx):** Dilengkapi template unduh.

4. **Keamanan Ruang Ujian & Pengawasan (Proctor Control Room):**
   - **Dashboard Pengawas Berdensitas Tinggi:** Kotak Token Ruang besar untuk proyektor kelas & Matriks Kursi Siswa Real-Time.
   - **Aksi 1-Klik:** *Reset Login* siswa dan *Buka Kunci (Unlock)* layar siswa.
   - **Deteksi Kecurangan:** Deteksi keluar fullscreen dan pindah tab (`visibilitychange`, `blur`) dengan penghitung pelanggaran (*cheat strikes*) dan penguncian otomatis (*Auto-Lock*).
   - **Dukungan Safe Exam Browser (SEB):** Validasi header aplikasi resmi SEB.
   - **Cetak Dokumen Ujian:** Berita Acara Pelaksanaan Ujian, Daftar Hadir Ruang, dan Kartu Peserta Ujian massal.

5. **Meja Koreksi Esai Guru (Speed-Grading Workbench) & Leger e-Rapor:**
   - Meja koreksi esai cepat: daftar siswa di kiri, jawaban dan rubrik di tengah, input skor angka di kanan (ketik skor &rarr; tekan `Enter` &rarr; otomatis lanjut ke siswa berikutnya).
   - Pembobotan otomatis: $\text{Bobot PG } (70\%) + \text{Bobot Esai } (30\%) = \text{Nilai Akhir } (0\text{–}100)$.
   - Otomasi KKM: Status Tuntas / Belum Tuntas.
   - **Sesi Remedial 1-Klik:** Membuat jadwal ujian susulan khusus bagi siswa di bawah KKM dengan aturan batas nilai KKM.
   - **Analisis Butir Soal:** Laporan indeks kesukaran (*Mudah, Sedang, Sukar*) dan sebaran opsi.
   - **Ekspor Leger Nilai:** Format Excel terstruktur siap disalin ke aplikasi **e-Rapor**.

---

## 🔑 Akun Uji Coba & Demo (Tersedia Tombol 1-Klik Masuk)

Aplikasi telah dilengkapi seeder akun dengan kata sandi terstandarisasi:

| Peran (Role) | Username / NISN | Kata Sandi | Tanggung Jawab / Akses |
| :--- | :--- | :--- | :--- |
| **Admin Sekolah** | `admin` | `admin123` | Master Akademik (Rombel, Mapel, Siswa, Guru), Jadwal & Pengaturan SEB |
| **Guru Mapel** | `guru` | `guru123` | Bank Soal (5 Tipe), Meja Koreksi Esai, Analisis Butir Soal, Ekspor Leger |
| **Pengawas Ruang** | `pengawas` | `pengawas123` | Rilis Token Proyektor, Matriks Kursi Siswa, Buka Kunci, Berita Acara |
| **Siswa 1** | `2026001` | `siswa123` | Ahmad Pratama (Kelas X-MIPA-1, Token Sesi: `SMART1`) |
| **Siswa 2** | `2026002` | `siswa123` | Budi Santoso (Kelas X-MIPA-1, Token Sesi: `SMART1`) |
| **Siswa 3–8** | `2026003` s.d. `2026008` | `siswa123` | Siswa kelas X-MIPA-1 lainnya |

---

## 🚀 Panduan Instalasi & Menjalankan Aplikasi

### 1. Prasyarat Sistem
- PHP 8.3+ (Telah dikonfigurasi menggunakan PHP 8.5 bawaan Laragon)
- Node.js v20+ & NPM v10+
- Composer 2.x

### 2. Menjalankan Server Aplikasi
Cukup klik ganda (double click) file **`start.bat`** atau jalankan perintah berikut di terminal:

```powershell
.\start.bat
```

Atau secara manual:
```powershell
.\artisan.bat serve --port=8000
```
Buka browser di: **`http://127.0.0.1:8000`**

### 3. Kompilasi Aset Frontend (Jika Ingin Memodifikasi Vue/Tailwind)
```powershell
npm run build
```
*(Aset CSS dan JS telah terkompilasi dan siap pakai di direktori `public/build/`).*

### 4. Menjalankan Pengujian Otomatis (Test Suite)
Untuk memverifikasi seluruh fungsionalitas (Login, Master Akademik, Bank Soal, Pengerjaan Siswa, Batch Sync, Anti-Cheat, Meja Koreksi, hingga Ekspor Excel):

```powershell
.\artisan.bat test
```
*(Seluruh 9 test case dengan 37 assertions terverifikasi 100% Passed).*

---

## 📂 Struktur Direktori Utama

```
cbt-laravel/
├── app/
│   ├── Http/Controllers/
│   │   ├── AuthController.php                   # Autentikasi terpadu & Single-Session
│   │   ├── Admin/                               # Master Data, Jadwal, Kartu & Siswa
│   │   ├── Teacher/                             # Bank Soal, Speed-Grading & Analisis
│   │   ├── Proctor/                             # Control Room, Token & Berita Acara
│   │   ├── Student/                             # Portal Siswa & Pengerjaan Ujian
│   │   └── Api/ExamApiController.php            # Endpoint Batch Sync & Anti-Cheat
│   └── Models/                                  # 11 Eloquent Models
├── database/
│   ├── migrations/                              # Skema lengkap 11 tabel utama
│   └── seeders/DatabaseSeeder.php               # Data realistis sekolah, soal, dan sesi
├── resources/
│   ├── css/app.css                              # Design Tokens Paper-First & KaTeX
│   ├── js/
│   │   ├── Layouts/AppLayout.vue                # Swiss Grid Sidebar & Workspace
│   │   ├── Pages/                               # Halaman Admin, Guru, Pengawas, Siswa
│   │   │   └── Student/ExamScreen.vue           # Core Exam Engine (Vue + Dexie.js)
│   │   ├── Stores/examLocalDb.js                # IndexedDB Engine via Dexie.js
│   │   └── Utils/katexRender.js                 # Parser Rumus Matematika KaTeX
│   └── views/                                   # Root layout & Print views (Kartu, Berita Acara)
├── start.bat                                    # Script 1-klik jalankan server
├── artisan.bat                                  # Wrapper Artisan PHP 8.5
└── composer.bat                                 # Wrapper Composer
```
