<?php

namespace Database\Seeders;

use App\Models\ClassRoom;
use App\Models\ExamSchedule;
use App\Models\ExamSession;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\QuestionOption;
use App\Models\SchoolSetting;
use App\Models\Subject;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. School Settings
        $school = SchoolSetting::create([
            'school_name' => 'SMA Negeri 1 Digital Excellence',
            'npsn' => '20104050',
            'headmaster_name' => 'Dr. H. Ahmad Fauzi, M.Pd.',
            'headmaster_nip' => '197505122000031002',
            'active_academic_year' => '2026/2027',
            'active_semester' => 'Ganjil',
            'primary_color' => '#0F52BA',
            'seb_required' => false,
            'seb_key_hash' => null,
        ]);

        // 2. Users: Admin, Teacher, Proctor
        $admin = User::create([
            'name' => 'Administrator Sekolah',
            'username' => 'admin',
            'email' => 'admin@smartcbt.sch.id',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $teacher = User::create([
            'name' => 'Budi Hartono, S.Pd., M.Si.',
            'username' => 'guru',
            'email' => 'budi@smartcbt.sch.id',
            'password' => Hash::make('guru123'),
            'role' => 'teacher',
            'is_active' => true,
        ]);

        $proctor = User::create([
            'name' => 'Siti Rahmawati, S.Kom.',
            'username' => 'pengawas',
            'email' => 'siti@smartcbt.sch.id',
            'password' => Hash::make('pengawas123'),
            'role' => 'proctor',
            'is_active' => true,
        ]);

        // 3. Classes (Rombel)
        $classX1 = ClassRoom::create([
            'grade_level' => 10,
            'major_code' => 'MIPA',
            'name' => 'X-MIPA-1',
            'homeroom_teacher_id' => $teacher->id,
        ]);

        $classX2 = ClassRoom::create([
            'grade_level' => 10,
            'major_code' => 'MIPA',
            'name' => 'X-MIPA-2',
            'homeroom_teacher_id' => null,
        ]);

        $classXI1 = ClassRoom::create([
            'grade_level' => 11,
            'major_code' => 'IPS',
            'name' => 'XI-IPS-1',
            'homeroom_teacher_id' => null,
        ]);

        // 4. Students
        $studentsData = [
            ['name' => 'Ahmad Pratama', 'nisn' => '2026001', 'class_id' => $classX1->id, 'room' => 'Lab Komputer 1'],
            ['name' => 'Budi Santoso', 'nisn' => '2026002', 'class_id' => $classX1->id, 'room' => 'Lab Komputer 1'],
            ['name' => 'Citra Lestari', 'nisn' => '2026003', 'class_id' => $classX1->id, 'room' => 'Lab Komputer 1'],
            ['name' => 'Doni Setiawan', 'nisn' => '2026004', 'class_id' => $classX1->id, 'room' => 'Lab Komputer 1'],
            ['name' => 'Eka Wulandari', 'nisn' => '2026005', 'class_id' => $classX1->id, 'room' => 'Lab Komputer 1'],
            ['name' => 'Fajar Ramadhan', 'nisn' => '2026006', 'class_id' => $classX1->id, 'room' => 'Lab Komputer 1'],
            ['name' => 'Gita Permata', 'nisn' => '2026007', 'class_id' => $classX1->id, 'room' => 'Lab Komputer 1'],
            ['name' => 'Hendra Wijaya', 'nisn' => '2026008', 'class_id' => $classX1->id, 'room' => 'Lab Komputer 1'],
        ];

        foreach ($studentsData as $std) {
            User::create([
                'name' => $std['name'],
                'username' => $std['nisn'],
                'email' => $std['nisn'].'@siswa.smartcbt.sch.id',
                'password' => Hash::make('siswa123'),
                'role' => 'student',
                'class_room_id' => $std['class_id'],
                'exam_room_name' => $std['room'],
                'is_active' => true,
            ]);
        }

        // 5. Subjects
        $subMat = Subject::create([
            'code' => 'MAT-10',
            'name' => 'Matematika Terapan & AKM',
            'grade_level' => 10,
            'kkm_score' => 75.00,
        ]);

        $subInd = Subject::create([
            'code' => 'BIN-10',
            'name' => 'Bahasa Indonesia (Literasi)',
            'grade_level' => 10,
            'kkm_score' => 75.00,
        ]);

        $subPAI = Subject::create([
            'code' => 'PAI-10',
            'name' => 'Pendidikan Agama Islam & Bahasa Arab',
            'grade_level' => 10,
            'kkm_score' => 78.00,
        ]);

        // 6. Question Bank (Bank Soal SAS Matematika & AKM)
        $bank = QuestionBank::create([
            'subject_id' => $subMat->id,
            'teacher_id' => $teacher->id,
            'title' => 'Bank Soal Sumatif Akhir Semester (SAS) Ganjil - Matematika & Numerasi AKM',
            'pg_weight_percent' => 70,
            'essay_weight_percent' => 30,
        ]);

        // Question 1: Single Choice (PG Biasa) with KaTeX math formula
        $q1 = Question::create([
            'question_bank_id' => $bank->id,
            'type' => 'single',
            'reading_passage' => null,
            'content' => 'Diketahui fungsi kuadrat f(x) = x^2 - 6x + 8. Tentukan titik potong grafik fungsi tersebut terhadap sumbu-X (di mana f(x) = 0).',
            'max_score' => 10.00,
            'order_index' => 1,
        ]);
        QuestionOption::create(['question_id' => $q1->id, 'option_label' => 'A', 'content' => '(2, 0) dan (4, 0)', 'is_correct' => true, 'order_index' => 1]);
        QuestionOption::create(['question_id' => $q1->id, 'option_label' => 'B', 'content' => '(-2, 0) dan (-4, 0)', 'is_correct' => false, 'order_index' => 2]);
        QuestionOption::create(['question_id' => $q1->id, 'option_label' => 'C', 'content' => '(1, 0) dan (8, 0)', 'is_correct' => false, 'order_index' => 3]);
        QuestionOption::create(['question_id' => $q1->id, 'option_label' => 'D', 'content' => '(3, 0) dan (5, 0)', 'is_correct' => false, 'order_index' => 4]);
        QuestionOption::create(['question_id' => $q1->id, 'option_label' => 'E', 'content' => '(0, 2) dan (0, 4)', 'is_correct' => false, 'order_index' => 5]);

        // Question 2: Single Choice with Reading Passage (Split-Pane AKM View)
        $q2 = Question::create([
            'question_bank_id' => $bank->id,
            'type' => 'single',
            'reading_passage' => '<h3>TRANSISI ENERGI BERSIH DI INDONESIA</h3><p>Indonesia memiliki potensi energi terbarukan yang sangat melimpah, mencapai lebih dari 3.600 Gigawatt (GW), terdiri dari energi surya, angin, hidro, panas bumi, dan biomassa.</p><p>Pemerintah menargetkan bauran energi baru terbarukan (EBT) sebesar 23% pada tahun 2025 dan emisi nol bersih (Net Zero Emission) pada tahun 2060. Pada panel surya atap skala rumah tangga dengan daya 2 kWp, rata-rata produksi listrik per hari di wilayah khatulistiwa adalah sekitar 8 kWh.</p><p>Jika satu keluarga rata-rata mengonsumsi 12 kWh per hari, panel surya mampu menutupi sekitar dua pertiga dari kebutuhan listrik harian tersebut, sekaligus mengurangi emisi karbon sebesar 2,4 kg CO2 setiap harinya.</p>',
            'content' => 'Berdasarkan wacana di samping, berapakah perkiraan pengurangan emisi karbon yang dihasilkan oleh sistem panel surya atap 2 kWp dalam waktu 30 hari (1 bulan)?',
            'max_score' => 10.00,
            'order_index' => 2,
        ]);
        QuestionOption::create(['question_id' => $q2->id, 'option_label' => 'A', 'content' => '48 kg CO2', 'is_correct' => false, 'order_index' => 1]);
        QuestionOption::create(['question_id' => $q2->id, 'option_label' => 'B', 'content' => '72 kg CO2', 'is_correct' => true, 'order_index' => 2]);
        QuestionOption::create(['question_id' => $q2->id, 'option_label' => 'C', 'content' => '120 kg CO2', 'is_correct' => false, 'order_index' => 3]);
        QuestionOption::create(['question_id' => $q2->id, 'option_label' => 'D', 'content' => '240 kg CO2', 'is_correct' => false, 'order_index' => 4]);
        QuestionOption::create(['question_id' => $q2->id, 'option_label' => 'E', 'content' => '360 kg CO2', 'is_correct' => false, 'order_index' => 5]);

        // Question 3: Multiple Choice Multiple Answer (MCMA / Pilihan Ganda Kompleks)
        $q3 = Question::create([
            'question_bank_id' => $bank->id,
            'type' => 'mcma',
            'reading_passage' => null,
            'content' => 'Pilihlah seluruh pernyataan berikut yang BENAR mengenai sifat-sifat bangun datar jajargenjang! (Jawaban benar lebih dari satu)',
            'max_score' => 10.00,
            'order_index' => 3,
        ]);
        QuestionOption::create(['question_id' => $q3->id, 'option_label' => 'A', 'content' => 'Sisi-sisi yang berhadapan sejajar dan sama panjang.', 'is_correct' => true, 'order_index' => 1]);
        QuestionOption::create(['question_id' => $q3->id, 'option_label' => 'B', 'content' => 'Sudut-sudut yang berhadapan sama besar.', 'is_correct' => true, 'order_index' => 2]);
        QuestionOption::create(['question_id' => $q3->id, 'option_label' => 'C', 'content' => 'Kedua diagonalnya selalu berpotongan tegak lurus membentuk 90 derajat.', 'is_correct' => false, 'order_index' => 3]);
        QuestionOption::create(['question_id' => $q3->id, 'option_label' => 'D', 'content' => 'Jumlah sudut yang berdekatan adalah 180 derajat.', 'is_correct' => true, 'order_index' => 4]);

        // Question 4: True / False (Benar / Salah atau Sesuai / Tidak Sesuai)
        $q4 = Question::create([
            'question_bank_id' => $bank->id,
            'type' => 'true_false',
            'reading_passage' => null,
            'content' => 'Tentukan kebenaran dari masing-masing pernyataan matematis berikut (Benar atau Salah):',
            'max_score' => 10.00,
            'order_index' => 4,
        ]);
        QuestionOption::create(['question_id' => $q4->id, 'option_label' => '1', 'content' => 'Bilangan prima genap satu-satunya di dunia adalah angka 2.', 'is_correct' => true, 'order_index' => 1]);
        QuestionOption::create(['question_id' => $q4->id, 'option_label' => '2', 'content' => 'Nilai dari sin(90°) adalah 0.', 'is_correct' => false, 'order_index' => 2]);
        QuestionOption::create(['question_id' => $q4->id, 'option_label' => '3', 'content' => 'Jika a^2 = 25, maka a harus selalu bernilai positif 5 saja.', 'is_correct' => false, 'order_index' => 3]);
        QuestionOption::create(['question_id' => $q4->id, 'option_label' => '4', 'content' => 'Sudut lancip adalah sudut yang ukurannya lebih kecil dari 90 derajat.', 'is_correct' => true, 'order_index' => 4]);

        // Question 5: Matching (Menjodohkan)
        $q5 = Question::create([
            'question_bank_id' => $bank->id,
            'type' => 'matching',
            'reading_passage' => null,
            'content' => 'Pasangkanlah konsep bangun ruang di sebelah kiri dengan rumus volume yang tepat di sebelah kanan:',
            'max_score' => 10.00,
            'order_index' => 5,
        ]);
        QuestionOption::create(['question_id' => $q5->id, 'option_label' => '1', 'content' => 'Kubus', 'matching_pair_text' => 'sisi x sisi x sisi (s^3)', 'is_correct' => true, 'order_index' => 1]);
        QuestionOption::create(['question_id' => $q5->id, 'option_label' => '2', 'content' => 'Balok', 'matching_pair_text' => 'panjang x lebar x tinggi (p x l x t)', 'is_correct' => true, 'order_index' => 2]);
        QuestionOption::create(['question_id' => $q5->id, 'option_label' => '3', 'content' => 'Tabung (Silinder)', 'matching_pair_text' => 'pi x r^2 x tinggi', 'is_correct' => true, 'order_index' => 3]);
        QuestionOption::create(['question_id' => $q5->id, 'option_label' => '4', 'content' => 'Kerucut', 'matching_pair_text' => '1/3 x pi x r^2 x tinggi', 'is_correct' => true, 'order_index' => 4]);

        // Question 6: Short Answer (Isian Singkat)
        $q6 = Question::create([
            'question_bank_id' => $bank->id,
            'type' => 'short_answer',
            'reading_passage' => null,
            'content' => 'Berapakah nilai dari 15% dari 240? (Ketikkan angka saja)',
            'max_score' => 10.00,
            'order_index' => 6,
        ]);
        QuestionOption::create(['question_id' => $q6->id, 'option_label' => 'KEY', 'content' => '36', 'is_correct' => true, 'order_index' => 1]);

        // Question 7: Arabic RTL Text Support (PAI / Bahasa Arab)
        $q7 = Question::create([
            'question_bank_id' => $bank->id,
            'type' => 'single',
            'reading_passage' => "<div class='arabic-text'>بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ <br/> اقْرَأْ بِاسْمِ رَبِّكَ الَّذِي خَلَقَ</div><p class='mt-3 text-sm text-slate-600'>Ayat di atas merupakan wahyu pertama yang diturunkan kepada Nabi Muhammad SAW di Gua Hira.</p>",
            'content' => 'Surah apakah dan ayat ke berapakah kutipan teks ayat Al-Qur\'an di atas?',
            'max_score' => 10.00,
            'order_index' => 7,
        ]);
        QuestionOption::create(['question_id' => $q7->id, 'option_label' => 'A', 'content' => 'Surah Al-\'Alaq ayat 1', 'is_correct' => true, 'order_index' => 1]);
        QuestionOption::create(['question_id' => $q7->id, 'option_label' => 'B', 'content' => 'Surah Al-Muddatstsir ayat 1', 'is_correct' => false, 'order_index' => 2]);
        QuestionOption::create(['question_id' => $q7->id, 'option_label' => 'C', 'content' => 'Surah Al-Fatihah ayat 1', 'is_correct' => false, 'order_index' => 3]);
        QuestionOption::create(['question_id' => $q7->id, 'option_label' => 'D', 'content' => 'Surah Al-Baqarah ayat 1', 'is_correct' => false, 'order_index' => 4]);

        // Question 8: Long Essay / Uraian (Meja Koreksi Esai)
        $q8 = Question::create([
            'question_bank_id' => $bank->id,
            'type' => 'essay',
            'reading_passage' => null,
            'content' => 'Jelaskan perbedaan mendasar antara data kuantitatif diskrit dan data kuantitatif kontinu dalam ilmu statistika! Berikan masing-masing 2 contoh konkret dalam kehidupan sehari-hari siswa di sekolah.',
            'max_score' => 30.00,
            'order_index' => 8,
        ]);
        QuestionOption::create([
            'question_id' => $q8->id,
            'option_label' => 'RUBRIC',
            'content' => 'Kunci Rubrik Penilaian Guru: Data diskrit diperoleh dari hasil membilang/menghitung (bilangan bulat, misal: jumlah siswa di kelas, jumlah buku). Data kontinu diperoleh dari hasil pengukuran (bisa bernilai desimal/pecahan, misal: tinggi badan siswa 165.5 cm, berat badan, waktu tempuh lari). Nilai maksimal 30 jika penjelasan tepat dan kedua contoh ada.',
            'is_correct' => true,
            'order_index' => 1,
        ]);

        // 7. Exam Schedule
        $schedule = ExamSchedule::create([
            'title' => 'Sumatif Akhir Semester (SAS) Ganjil - Matematika & Literasi Numerasi',
            'subject_id' => $subMat->id,
            'question_bank_id' => $bank->id,
            'target_class_ids_json' => [$classX1->id, $classX2->id],
            'duration_minutes' => 60,
            'min_submit_minutes' => 5, // 5 menit batas minimal kumpul
            'shuffle_questions' => false,
            'shuffle_options' => false,
            'max_cheat_strikes' => 3,
            'show_score_to_student' => true,
            'is_remedial' => false,
            'remedial_max_score_policy' => 'kkm',
        ]);

        // 8. Exam Session (Room & Token)
        $now = Carbon::now();
        $session = ExamSession::create([
            'exam_schedule_id' => $schedule->id,
            'session_name' => 'Sesi 1 (Pagi)',
            'room_name' => 'Lab Komputer 1',
            'access_token' => 'SMART1', // Token Ruang Ujian
            'start_time' => $now->copy()->subMinutes(10),
            'end_time' => $now->copy()->addMinutes(120),
            'proctor_id' => $proctor->id,
            'proctor_notes' => 'Sesi berjalan tertib. Jaringan Wi-Fi lab stabil.',
            'status' => 'active',
        ]);
    }
}
