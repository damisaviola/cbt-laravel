# Product Requirements Document (PRD)

**Nama Proyek:** SmartCBT School Edition (Sistem Ujian Berbasis Komputer & Evaluasi Akademik Sekolah)

**Versi Dokumen:** 2.1 (Khusus Pendidikan / Sekolah + Standar UI/UX Anti-AI Slop)

**Tech Stack Utama:** Laravel 11/12, Vue.js 3, Inertia.js, Tailwind CSS, IndexedDB

**Model Bisnis:** Lisensi per Sekolah (*One-Time Purchase* / Jual Putus) + Kontrak Pemeliharaan Tahunan

---

## 1. Ringkasan Eksekutif (Executive Summary)

### 1.1 Latar Belakang Masalah

Sekolah (SMP, SMA, SMK, dan Madrasah/MA) rutin menyelenggarakan ujian berskala besar seperti Sumatif Tengah Semester (STS), Sumatif Akhir Semester (SAS), Ujian Sekolah (US), hingga simulasi ANBK/AKM. Namun, banyak sekolah mengalami kendala serius:

1. **Server Sekolah Tumbang di Menit Pertama:** Saat 500–1.000 siswa menekan tombol "Mulai Ujian" secara bersamaan di laboratorium komputer atau lewat HP masing-masing (*Bring Your Own Device*), aplikasi CBT konvensional mengalami *database bottleneck* dan macet.
2. **Jawaban Hilang Akibat Wi-Fi Kelas Tidak Stabil:** Jaringan Wi-Fi sekolah sering kali memiliki titik buta (*dead zone*). Ketika koneksi siswa terputus, jawaban yang sudah diketik hilang dan memicu kepanikan di ruang ujian.
3. **Beban Koreksi Guru & Administrasi Nilai:** Guru kesulitan menginput soal bergambar/rumus, mengoreksi soal esai bercampur pilihan ganda, memisahkan nilai berdasarkan KKM (Kriteria Ketuntasan Minimal), serta mengelola jadwal ujian susulan dan remedial.
4. **Antarmuka CBT Konvensional Kaku atau Terlihat "Murahan":** Banyak aplikasi sekolah memiliki desain jadul yang membingungkan siswa, atau sebaliknya menggunakan template AI generik (*AI Slop*) penuh gradien neon yang membuat mata cepat lelah saat membaca wacana soal panjang.

### 1.2 Solusi yang Ditawarkan

**SmartCBT School Edition** adalah platform ujian sekolah mandiri (*Self-Hosted* di server lokal sekolah maupun VPS Cloud) dengan arsitektur *Offline-First Client Engine* menggunakan **Vue.js 3 + IndexedDB** dan **Laravel**. Naskah soal diunduh sekali di awal sesi sehingga perpindahan nomor soal berlangsung instan (**0 ms latency**), jawaban tersimpan otomatis di perangkat siswa meskipun Wi-Fi mati, serta dibalut dengan desain antarmuka bergaya **High-Craft Editorial & Swiss Grid** yang bersih, fokus, dan menenangkan mata siswa.

### 1.3 Target Pasar & Harga Jual

* **Target Klien:** Sekolah Swasta (SMP/SMA/SMK), Sekolah Negeri (melalui dana BOS/Komite), Madrasah (MTs/MA), dan Yayasan Pendidikan.
* **Rentang Harga Lisensi:** Rp3.000.000 – Rp12.000.000 per sekolah (tergantung kapasitas siswa dan dukungan instalasi server lokal/VPS).

---

## 2. Peran dan Hak Akses Pengguna (User Roles)

| Role | Tanggung Jawab & Hak Akses Utama |
| :--- | :--- |
| **Admin Sekolah (Operator / Tim IT)** | Mengatur identitas sekolah (logo, kop kartu ujian, tahun ajaran, semester), mengelola data master (Jurusan, Tingkat, Rombel/Kelas, Mapel, Guru, Siswa), menjadwalkan sesi ujian, serta memantau beban server. |
| **Guru Mata Pelajaran** | Mengelola Bank Soal sesuai mata pelajaran yang diampu, mengimpor soal dari Word/Excel, mengatur KKM, mengoreksi jawaban **Esai/Uraian**, melihat analisis butir soal, dan mengunduh leger nilai per kelas. |
| **Pengawas Ruang (Proctor)** | Mengelola sesi di ruang ujian tertentu, merilis **Token Ruang**, memantau kehadiran dan status layar siswa secara *real-time*, melakukan *reset login* siswa yang HP/komputernya mati, dan mencetak Berita Acara Ujian. |
| **Wali Kelas** | Memantau rekap nilai seluruh mata pelajaran untuk siswa di kelas perwaliannya, melihat daftar siswa yang belum mengikuti ujian (butuh susulan) atau di bawah KKM (butuh remedial). |
| **Siswa** | Mengikuti ujian harian/semester, mengerjakan ujian remedial, serta melihat riwayat nilai dan pembahasan (jika diizinkan oleh Guru/Admin). |

---

## 3. Spesifikasi Kebutuhan Fungsional (Functional Requirements)

### 3.1 Modul Manajemen Akademik Sekolah (Master Data)

* **\[FR-01\] Struktur Akademik Berjenjang:**
  * Pengelolaan **Tahun Ajaran & Semester** aktif (misal: $2026/2027$ Ganjil/Genap).
  * Pengelolaan **Tingkat Kelas** (Kelas $7, 8, 9$ atau $10, 11, 12$), **Jurusan/Peminatan** (IPA, IPS, TKJ, Akuntansi, dll.), dan **Rombongan Belajar / Rombel** (misal: `XII-TKJ-1`).
* **\[FR-02\] Sinkronisasi Siswa & Cetak Kartu Massal:**
  * Import data siswa massal menggunakan template Excel (mencakup NIS, NISN, Nama, Kelas, Ruang Ujian, dan Sesi).
  * Generator otomatis **Kartu Peserta Ujian** (dilengkapi pasfoto, QR Code, NISN, username/password, dan jadwal) serta **Daftar Hadir Ruang** siap cetak (PDF ukuran A4/F4).

### 3.2 Modul Mesin Ujian Anti-Lag (Exam Engine - Vue.js)

* **\[FR-03\] Pre-fetching Naskah Soal:** Saat siswa memasukkan Token Ujian, seluruh soal mata pelajaran tersebut diunduh ke memori browser dalam format JSON ringan (tanpa menyertakan kunci jawaban).
* **\[FR-04\] Navigasi Soal Instan (Zero Reload):** Perpindahan antar nomor soal maupun antar tipe soal (Pilihan Ganda ke Esai) berjalan $0\text{ ms}$ di sisi klien tanpa membebani server sekolah.
* **\[FR-05\] Penyimpanan Lokal Persisten (IndexedDB):**
  * Setiap pilihan jawaban atau ketikan teks esai siswa langsung tersimpan ke **IndexedDB** (via Dexie.js) dalam waktu $< 10\text{ ms}$.
  * Jika browser tertutup, komputer laboratorium mati mendadak, atau baterai HP habis, seluruh jawaban tetap aman saat perangkat dinyalakan kembali.
* **\[FR-06\] Sinkronisasi Latar Belakang (*Background Batch Sync*):**
  * Jawaban dikirim ke server Laravel secara *batch* setiap **60 detik** atau setiap siswa menjawab **5 soal**, serta wajib terkirim saat klik tombol "Kumpulkan Ujian".
  * Jika Wi-Fi ruang kelas putus di tengah ujian, siswa tetap bisa mengerjakan soal hingga nomor terakhir dan cukup mencari sinyal saat akan menekan tombol kumpulkan.
* **\[FR-07\] Server-Authoritative Timer & Batas Minimal Kumpul:**
  * Hitung mundur waktu ujian mengacu pada jam server sekolah.
  * Fitur **Batas Waktu Minimal Kumpul**: Admin/Guru dapat mengunci tombol "Selesai Ujian" hingga waktu berjalan minimal sekian menit (misal: tombol selesai baru aktif $15\text{ menit}$ sebelum waktu habis agar siswa tidak asal menjawab cepat lalu keluar kelas).

### 3.3 Modul Bank Soal Kurikulum Merdeka & AKM

* **\[FR-08\] 5 Tipe Soal Standar Sekolah & ANBK:**
  1. **Pilihan Ganda Biasa:** 1 jawaban benar (opsi A–D untuk SMP, A–E untuk SMA/SMK).
  2. **Pilihan Ganda Kompleks (MCMA):** Siswa dapat memilih lebih dari satu jawaban benar (kotak centang).
  3. **Benar / Salah atau Sesuai / Tidak Sesuai:** Tabel pernyataan majemuk di mana siswa menentukan status tiap baris pernyataan.
  4. **Menjodohkan (Matching):** Menghubungkan premis di kolom kiri dengan jawaban di kolom kanan menggunakan interaksi klik-pasangkan yang bersih.
  5. **Esai / Uraian (Terbuka & Singkat):**
     * *Isian Singkat:* Dikoreksi otomatis oleh sistem (pencocokan kata kunci/angka).
     * *Uraian Panjang:* Siswa mengetik jawaban paragraf (atau mengunggah foto rumus/coretan kertas jika diizinkan), yang kemudian dinilai manual oleh Guru melalui **Meja Koreksi Esai**.
* **\[FR-09\] Editor Rumus Matematika, Arab & Audio Listening:**
  * Editor WYSIWYG (TipTap) dengan dukungan rumus $\text{KaTeX}$ (Matematika, Fisika, Kimia).
  * Dukungan font dan arah teks *Right-to-Left* (RTL) yang rapi untuk soal **Bahasa Arab / Al-Qur'an Hadits / PAI** (menggunakan font khusus seperti *Amiri* atau *Scheherazade New* dengan ukuran baris yang proporsional).
  * Pemutar audio untuk ujian *Listening* Bahasa Inggris dengan batas jumlah putar (*play limit*).
* **\[FR-10\] Import Soal Word & Excel:** Guru dapat mengunggah naskah soal langsung dari template Microsoft Word (`.docx`) berformat tabel maupun Excel (`.xlsx`) beserta gambar di dalamnya.

### 3.4 Modul Keamanan Ruang Ujian (Anti-Cheat & Proctoring)

* **\[FR-11\] Dukungan Safe Exam Browser (SEB) & Exam Browser Android:**
  * Sistem dapat diatur agar hanya bisa dibuka menggunakan aplikasi **Safe Exam Browser (SEB)** atau aplikasi *CBT Exam Browser* (Kiosk Mode) melalui validasi `User-Agent` dan `Course Key / Config Key` header.
* **\[FR-12\] Deteksi Pindah Aplikasi (Mode Browser Biasa):**
  * Jika sekolah tidak memakai SEB, sistem mewajibkan *Fullscreen Mode* dan mendeteksi jika siswa membuka tab baru, *split-screen*, atau membuka aplikasi chat/AI melalui event `visibilitychange` dan `blur`.
* **\[FR-13\] Penguncian Layar & Reset oleh Pengawas:**
  * Jika siswa melanggar batas pindah tab (misal $3\text{ kali}$), layar ujian terkunci otomatis. Siswa harus mengangkat tangan dan meminta Pengawas Ruang menekan tombol **Buka Kunci (Unlock)** dari *Proctor Dashboard*.
* **\[FR-14\] Berita Acara & Absensi Digital Ruang:**
  * Pengawas dapat mengisi catatan kejadian selama ujian berlangsung (siswa sakit, terlambat, kendala teknis) dan mencetak **Berita Acara Pelaksanaan Ujian** yang langsung ditandatangani.

### 3.5 Modul Penilaian, Remedial & Leger Rapor

* **\[FR-15\] Pembobotan Nilai PG & Esai:** Guru dapat mengatur komposisi nilai akhir secara otomatis, misalnya: $\text{Bobot Pilihan Ganda } 70\% + \text{Bobot Esai } 30\% = \text{Nilai Akhir } (0\text{–}100)$.
* **\[FR-16\] Manajemen KKM, Ujian Susulan & Remedial:**
  * Sistem otomatis menandai siswa dengan status **Tuntas ($\ge \text{KKM}$)** atau **Belum Tuntas ($< \text{KKM}$)**.
  * Guru/Admin dapat membuat jadwal **Sesi Remedial** dengan 1 klik yang hanya dapat diakses oleh siswa yang nilainya di bawah KKM, dengan aturan nilai akhir remedial (misal: nilai maksimal dibatasi sebesar nilai KKM atau diambil nilai tertinggi).
* **\[FR-17\] Analisis Butir Soal Akademik:** Menghasilkan laporan tingkat kesukaran soal (*Mudah, Sedang, Sukar*) dan persentase distribusi jawaban per opsi untuk bahan evaluasi guru dan akreditasi sekolah.
* **\[FR-18\] Ekspor Leger Nilai per Kelas:** Unduh rekap nilai format Excel yang sudah terurut rapi berdasarkan Kelas/Rombel dan NIS siswa, siap disalin ke aplikasi **e-Rapor**.

---

## 4. Standar Desain UI/UX: Pedoman "Anti-AI Slop" (High-Craft Design System)

Agar aplikasi ini terlihat seperti produk perangkat lunak B2B bernilai belasan juta rupiah (setara *Linear*, *Notion*, atau *Stripe*) dan **tidak terlihat seperti template hasil generate AI murahan (*AI Slop*)**, seluruh komponen Vue.js + Tailwind CSS wajib mematuhi aturan desain berikut:

### 4.1 Daftar Larangan Keras (Anti-AI Slop Guardrails)

1. **DILARANG Menggunakan Gradien Ungu/Biru Neon Generik:** Hindari sepenuhnya penggunaan `bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500` pada tombol, header, maupun latar belakang kartu.
2. **DILARANG Menggunakan *Glassmorphism* & *Blur* Berlebihan:** Hindari efek `backdrop-blur` berlapis-lapis dengan bayangan melayang (`shadow-2xl`) yang tidak fungsional dan memberatkan GPU HP Android siswa.
3. **DILARANG Membuat Kartu Statistik Berjejer 4 dengan Ikon Bulat Warna-Warni:** Jangan gunakan pola basi "4 kartu di atas dashboard dengan ikon di dalam lingkaran pastel dan teks *+12% dari bulan lalu*". Gunakan **Bento Grid fungsional** atau *inline metric bar* yang padat informasi.
4. **DILARANG Menggunakan Sudut Terlalu Membulat (`rounded-3xl`):** Gunakan radius sudut yang tegas dan profesional (`rounded-md` atau `rounded-lg` maksimal $6\text{px}$–$8\text{px}$) dengan garis tepi tipis presisi (`border border-slate-200`).
5. **DILARANG Menggunakan Ilustrasi Kartun 3D Generik atau Emoji di UI:** Gunakan ikon garis vektor yang tajam dan konsisten (**Lucide Icons** atau **Phosphor Icons** dengan `stroke-width: 1.75`).

### 4.2 Palet Warna "Paper-First" (Ramah Mata Saat Ujian Panjang)

Siswa menatap layar selama 90–120 menit. Warna putih murni (`#FFFFFF`) dengan kontras ekstrem menyebabkan mata cepat lelah (*visual fatigue*).

| Elemen | Kode Warna (Hex / Tailwind) | Fungsi & Keterangan |
| :--- | :--- | :--- |
| **Canvas Utama (Layar Baca Soal)** | `#FAF9F5` / `bg-[#FAF9F5]` | *Warm Alabaster / Paper White* — memberikan sensasi tenang seperti membaca kertas ujian asli. |
| **Permukaan Kartu & Sidebar** | `#FFFFFF` / `bg-white` | Kontras lembut terhadap kanvas utama, dipisahkan dengan *hairline border* `#E2E8F0` (`border-slate-200`). |
| **Teks Utama (Naskah Soal)** | `#0F172A` / `text-slate-900` | *Deep Ink* — sangat tajam dan mudah dibaca tanpa silau. |
| **Teks Sekunder & Label** | `#475569` / `text-slate-600` | Untuk instruksi soal, label nomor, dan metadata jadwal. |
| **Aksen Primer (Default Sekolah)** | `#0F52BA` atau `#047857` | *Academic Sapphire Blue* atau *Madrasah Emerald Green* (warna solid yang berwibawa, dapat dikustomisasi via CSS Variables `--brand-primary`). |
| **Status Jawaban: Terjawab** | `#15803D` (`bg-emerald-700 text-white`) | Hijau tegas pada kotak navigasi nomor soal yang sudah dijawab. |
| **Status Jawaban: Ragu-ragu** | `#D97706` (`bg-amber-500 text-white`) | Kuning amber kontras tinggi untuk penanda soal ragu-ragu. |
| **Status Bahaya / Waktu Hampir Habis** | `#B91C1C` (`text-red-700 bg-red-50`) | Muncul pada *timer* saat sisa waktu ujian $< 5\text{ menit}$. |

### 4.3 Tipografi & Keterbacaan Naskah (Editorial Typography)

* **Font UI & Navigasi:** **Plus Jakarta Sans** atau **Inter** (dibundel lokal `.woff2`).
* **Font Naskah Soal Panjang (Opsional Toggle):** Menyediakan opsi font serif editorial (**Source Serif 4** atau **Newsreader**) khusus untuk wacana soal Bahasa Indonesia/Inggris agar nyaman dibaca.
* **Font Angka, Timer, Token & NISN:** **JetBrains Mono** atau aturan wajib `font-variant-numeric: tabular-nums` (`tabular-nums` di Tailwind). Ini memastikan angka hitung mundur *timer* (`01:24:59`) **tidak bergoyang-goyang kiri-kanan** setiap detiknya.
* **Batas Lebar Baris Baca (*Measure*):** Teks cerita soal wajib dibatasi maksimal `65ch–75ch` (`max-w-prose`) agar mata siswa tidak lelah menyapu layar monitor lebar dari ujung kiri ke ujung kanan.

### 4.4 Arsitektur Tata Letak (Layout Architecture)

1. **Layar Ujian Siswa (*Distraction-Free Swiss Split Layout*):**
   * **Top Bar Tipis ($56\text{px}$ Height):** Hanya memuat Logo & Nama Sekolah di kiri, indikator status koneksi (*"Tersimpan di Perangkat"* dengan titik hijau tenang) di tengah, serta **Tabular Countdown Timer** dan tombol *Font Size (`A-` `A` `A+`)* di kanan.
   * **Area Split-Pane (Untuk Soal Wacana AKM):** Jika soal memiliki teks bacaan panjang, layar PC otomatis terbagi dua kolom (*Split View*): kolom kiri untuk wacana yang bisa di-*scroll* tersendiri, kolom kanan untuk pertanyaan dan opsi jawaban.
   * **Desain Opsi Pilihan Ganda (*Tactile Keycaps*):** Setiap pilihan jawaban (A, B, C, D, E) berbentuk baris kartu berbatas tegas (`border-slate-200`) dengan kotak huruf bergaya tombol *keyboard* (`[A]`, `[B]`). Siswa dapat memilih jawaban baik dengan klik *mouse*/sentuhan jari maupun menekan tombol keyboard `A`, `B`, `C`, `D`, `E`, serta panah `←` / `→` untuk pindah nomor.
   * **Drawer Navigasi Nomor Soal:** Di layar PC tampil sebagai panel kanan yang rapi (Grid $5\text{ kolom}$ kotak bernomor dengan `tabular-nums`); di layar HP tampil sebagai *Bottom Sheet* yang mudah dijangkau ibu jari.
2. **Dashboard Pengawas Ruang (*High-Density Control Room*):**
   * Tanpa grafik hiasan yang tidak perlu. Fokus pada **Kotak Token Ruang** berukuran besar (mudah dilihat di proyektor kelas) dan **Tabel Matriks Kursi Siswa Real-Time** yang ringkas: menampilkan status tiap siswa (*Sedang Mengerjakan: 32/40 soal*, *Pindah Tab: 1x*, *Terkunci*, *Selesai*) beserta tombol aksi cepat satu klik (`Reset Login`, `Buka Kunci`).
3. **Meja Koreksi Esai Guru (*Speed-Grading Workbench*):**
   * Dirancang untuk kecepatan kerja guru: daftar nama siswa di panel kiri, jawaban esai siswa bersanding dengan rubrik kunci jawaban di tengah, dan input skor angka cepat di kanan (tekan angka nilai $\rightarrow$ tekan `Enter` $\rightarrow$ otomatis lanjut ke siswa berikutnya tanpa *reload*).

---

## 5. Kebutuhan Non-Fungsional (Non-Functional Requirements)

1. **Fleksibilitas Deployment (Lokal & Cloud):**
   * **Mode Server Lokal (On-Premise / LAN):** Aplikasi harus dapat berjalan tanpa koneksi internet luar sama sekali di dalam jaringan lokal sekolah (menggunakan PC Server Sekolah / Mini PC dengan Docker atau Laragon/Ubuntu Server), termasuk seluruh aset font (`.woff2`), ikon, dan library $\text{KaTeX}$ harus dibundel secara lokal (*zero CDN dependency*).
   * **Mode Cloud (VPS):** Siap di-deploy di VPS untuk ujian dari rumah atau BYOD menggunakan internet seluler.
2. **Performa Tinggi pada Spesifikasi Rendah:**
   * Antarmuka siswa bebas dari animasi berat dan berjalan mulus $60\text{ fps}$ pada smartphone Android entry-level (RAM $2\text{ GB}$) maupun PC laboratorium sekolah generasi lama.
   * Mampu menangani minimal **500–1.000 siswa serentak** dalam satu gelombang ujian dengan waktu respons API sinkronisasi $< 200\text{ ms}$.

---

## 6. Arsitektur Teknis & Tech Stack

### 6.1 Spesifikasi Teknologi

* **Backend:** Laravel 11 / 12 (PHP 8.3+)
* **Frontend:** Vue 3 (Composition API, `<script setup>`) + TypeScript
* **Full-Stack Adapter:** Inertia.js (untuk halaman Admin Sekolah, Guru, Wali Kelas, dan Pengawas)
* **Exam Engine Communication:** Axios REST API khusus pada layar ujian siswa untuk kontrol *offline queue* dan *background batch sync*.
* **State Management & Local DB:** Pinia + Dexie.js (IndexedDB Wrapper)
* **Styling & UI:** Tailwind CSS + Komponen *Headless* (**Radix Vue / Reka UI**) dengan desain kustom berstandar Swiss Grid (bukan template Bootstrap/AdminLTE).
* **Database & Cache:** MySQL 8.0+ / MariaDB + Redis (untuk caching naskah soal dan sesi token aktif).

### 6.2 Alur Data Pengerjaan Ujian Siswa

1. **Mulai Ujian:** Siswa login dengan NISN $\rightarrow$ Pilih jadwal mapel aktif $\rightarrow$ Input Token Ruang dari Pengawas $\rightarrow$ Laravel mengambil soal dari Cache $\rightarrow$ Mengirim JSON soal teracak ke browser siswa.
2. **Mengerjakan Soal:** Siswa memilih opsi PG atau mengetik jawaban Esai $\rightarrow$ Tersimpan instan di IndexedDB perangkat siswa (`status: unsynced`).
3. **Sinkronisasi Berkala:** Setiap 60 detik, Vue mengirim *batch* jawaban yang berubah ke `POST /api/exam/{attempt}/sync` $\rightarrow$ Laravel melakukan *Bulk Upsert* ke database.
4. **Koreksi & Nilai Akhir:**
   * Jika ujian 100% objektif (PG/MCMA/Benar-Salah/Isian Singkat), nilai langsung dihitung saat siswa klik "Selesai".
   * Jika terdapat soal **Esai Uraian**, status nilai menjadi *Menunggu Koreksi Guru*, dan nilai akhir terbit otomatis begitu Guru selesai memberi poin di Meja Koreksi.

---

## 7. Rancangan Skema Database (Database Schema)

### 7.1 Tabel Utama

* **`school_settings`**: `id`, `school_name`, `npsn`, `logo_path`, `headmaster_name`, `headmaster_nip`, `active_academic_year`, `active_semester`, `primary_color`, `seb_required`, `seb_key_hash`
* **`class_rooms` (Rombel)**: `id`, `grade_level` (`7`–`12`), `major_code` (`IPA`, `IPS`, `TKJ`, dll.), `name` (`X-IPA-1`), `homeroom_teacher_id`
* **`users`**: `id`, `name`, `username` (NISN/NIP), `password`, `role` (`admin`, `teacher`, `proctor`, `student`), `class_room_id`, `exam_room_name`, `is_active`, `active_session_id`
* **`subjects` (Mata Pelajaran)**: `id`, `code`, `name`, `grade_level`, `kkm_score`
* **`question_banks`**: `id`, `subject_id`, `teacher_id`, `title`, `pg_weight_percent`, `essay_weight_percent`
* **`questions`**: `id`, `question_bank_id`, `type` (`single`, `mcma`, `true_false`, `matching`, `short_answer`, `essay`), `reading_passage` (wacana kiri opsional), `content`, `media_path`, `audio_play_limit`, `max_score`
* **`question_options`**: `id`, `question_id`, `option_label`, `content`, `matching_pair_text`, `is_correct`
* **`exam_schedules`**: `id`, `title` (`SAS Ganjil`, `Tryout ANBK`), `subject_id`, `question_bank_id`, `target_class_ids_json`, `duration_minutes`, `min_submit_minutes`, `shuffle_questions`, `shuffle_options`, `max_cheat_strikes`, `show_score_to_student`, `is_remedial`
* **`exam_sessions` (Ruang & Sesi)**: `id`, `exam_schedule_id`, `session_name` (`Sesi 1`, `Sesi 2`), `room_name` (`Lab Komputer 1`), `access_token`, `start_time`, `end_time`, `proctor_id`, `proctor_notes`
* **`exam_attempts`**: `id`, `exam_session_id`, `student_id`, `started_at`, `finished_at`, `cheat_strikes`, `is_locked`, `objective_score`, `essay_score`, `final_score`, `is_passed_kkm`, `status` (`ongoing`, `submitted`, `graded`)
* **`attempt_answers`**: `id`, `exam_attempt_id`, `question_id`, `selected_options_json`, `essay_text`, `is_doubt`, `score_obtained`, `is_graded_by_teacher`

---

## 8. Skema Paket Penjualan ke Sekolah (Pricing Tiers)

Aplikasi dapat dipasarkan ke sekolah melalui dana **BOS (Bantuan Operasional Sekolah)** di pos anggaran pengembangan perpustakaan/sistem informasi/evaluasi pembelajaran, maupun melalui Yayasan:

| Fitur & Layanan | Paket Sekolah Mandiri (Rp3,5 Juta) | Paket Sekolah Favorit (Rp6,5 Juta) | Paket Yayasan / Terpadu (Rp12 Juta) |
| :--- | :--- | :--- | :--- |
| **Target Pengguna** | SMP/SMA/SMK (< 300 Siswa) | SMP/SMA/SMK (300 – 1.000 Siswa) | Yayasan (Multi-Unit: SD + SMP + SMA) |
| **Mode Instalasi** | Server Lokal Sekolah (LAN) / 1 VPS | Server Lokal + VPS Cloud Hybrid | Multi-Subdomain per Unit Sekolah |
| **Mesin Ujian Anti-Lag (IndexedDB)** | ✅ | ✅ | ✅ |
| **Tipe Soal PG, AKM (MCMA/Menjodohkan) & Esai** | ✅ | ✅ | ✅ |
| **Cetak Kartu Ujian, Absensi & Berita Acara** | ✅ | ✅ | ✅ |
| **Proteksi Safe Exam Browser (SEB) & Anti-Cheat** | ✅ | ✅ | ✅ |
| **Manajemen KKM, Ujian Susulan & Sesi Remedial** | ❌ | ✅ | ✅ |
| **Analisis Butir Soal & Ekspor Format e-Rapor** | Standar (Excel) | Lengkap + Analisis Kesukaran | Lengkap + Dashboard Kepala Sekolah |
| **Aplikasi Android Exam Browser (APK Kiosk Khusus)** | ❌ | ✅ (Berlogo Sekolah) | ✅ (Berlogo Yayasan/Sekolah) |
| **Dukungan Teknis & Pendampingan Saat PAS/SAS** | Panduan Video & Dokumen | Gratis Setup Server + Pendampingan 1x | Garansi Maintenance 1 Tahun Penuh |

---

## 9. Roadmap Pengembangan (4-Week Sprint Plan)

* **Minggu 1: Design System Anti-AI Slop, Master Akademik & Bank Soal**
  * Setup Laravel, Inertia.js, Vue 3, TypeScript, dan konfigurasi *Design Tokens* Tailwind CSS (`#FAF9F5` paper canvas, font lokal Plus Jakarta Sans & JetBrains Mono, serta *hairline borders*).
  * CRUD Tahun Ajaran, Kelas/Rombel, Mata Pelajaran, Import Siswa Excel, dan Cetak Kartu Ujian PDF.
  * Pembuatan Bank Soal 5 tipe (PG, MCMA, Benar/Salah, Menjodohkan, Esai), dukungan wacana *Split-Pane*, rumus $\text{KaTeX}$ & teks Bahasa Arab, serta Import Soal dari Word/Excel.
* **Minggu 2: Mesin Ujian Siswa (Vue + IndexedDB) & Interaksi Keyboard**
  * Pembuatan antarmuka pengerjaan ujian *Distraction-Free* dengan navigasi *shortcut keyboard* (`A`–`E` dan panah) serta *bottom sheet* responsif untuk HP Android.
  * Integrasi Pinia + Dexie.js (IndexedDB) untuk *auto-save* pilihan ganda dan ketikan esai secara lokal.
  * Pembuatan endpoint *Batch Sync* (`upsert`) dan *Server-Authoritative Timer* (`tabular-nums`) beserta fitur batas waktu minimal kumpul.
* **Minggu 3: Pengawasan Ruang (Control Room UI), SEB & Speed-Grading Guru**
  * Integrasi validasi *Safe Exam Browser (SEB)* dan sensor deteksi pindah tab/aplikasi beserta fitur *Auto-Lock*.
  * Pembuatan *Dashboard Pengawas Ruang* berdensitas tinggi (rilis token proyektor, matriks status kursi siswa, *unlock* instan, dan cetak Berita Acara).
  * Pembuatan antarmuka **Meja Koreksi Esai (Speed-Grading Workbench)** bagi Guru dan kalkulasi pembobotan nilai akhir ($\text{PG} + \text{Esai}$) terhadap KKM.
* **Minggu 4: Remedial, Leger Nilai, Analisis Soal & Uji Beban**
  * Pembuatan fitur penjadwalan cepat **Ujian Remedial & Susulan**.
  * Pembuatan fitur ekspor Leger Nilai per Kelas (Excel siap masuk e-Rapor) dan Analisis Butir Soal.
  * Simulasi uji beban (*Stress Testing*) untuk skenario 800 siswa login dan mengumpulkan jawaban serentak.