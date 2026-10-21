<?php

namespace Tests\Feature;

use App\Models\ClassRoom;
use App\Models\ExamAttempt;
use App\Models\ExamSchedule;
use App\Models\ExamSession;
use App\Models\QuestionBank;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmartCbtTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_root_redirects_to_login_for_guests(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    public function test_admin_can_login_and_access_dashboard(): void
    {
        $response = $this->post('/login', [
            'username' => 'admin',
            'password' => 'admin123',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticated();

        $dashResponse = $this->get('/admin/dashboard');
        $dashResponse->assertStatus(200);

        $adminIndex = $this->get('/admin');
        $adminIndex->assertStatus(200);
    }

    public function test_admin_can_download_student_template_and_print_cards(): void
    {
        $admin = User::where('username', 'admin')->first();
        $this->actingAs($admin);

        // Download Template
        $templateResponse = $this->get('/admin/students/template');
        $templateResponse->assertStatus(200);
        $templateResponse->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // Print Kartu Peserta Ujian
        $cardsResponse = $this->get('/admin/students/print-cards');
        $cardsResponse->assertStatus(200);

        // Print Daftar Hadir Ruang
        $attendanceResponse = $this->get('/admin/students/print-attendance?room=Lab+Komputer+1');
        $attendanceResponse->assertStatus(200);
    }

    public function test_teacher_can_access_question_banks_and_grading(): void
    {
        $teacher = User::where('username', 'guru')->first();
        $this->actingAs($teacher);

        $dashResponse = $this->get('/teacher/dashboard');
        $dashResponse->assertStatus(200);

        $bankResponse = $this->get('/teacher/banks');
        $bankResponse->assertStatus(200);

        $gradingResponse = $this->get('/teacher/grading');
        $gradingResponse->assertStatus(200);

        // Download Question Template
        $qTemplateResponse = $this->get('/teacher/template/excel');
        $qTemplateResponse->assertStatus(200);
        $qTemplateResponse->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // Export Leger Nilai Excel
        $schedule = ExamSchedule::first();
        $legerResponse = $this->get("/teacher/grading/{$schedule->id}/export-leger");
        $legerResponse->assertStatus(200);
        $legerResponse->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_proctor_can_access_control_room_and_print_berita_acara(): void
    {
        $proctor = User::where('username', 'pengawas')->first();
        $this->actingAs($proctor);

        $response = $this->get('/proctor/dashboard');
        $response->assertStatus(200);

        $session = ExamSession::first();
        $baResponse = $this->get("/proctor/session/{$session->id}/print-berita-acara");
        $baResponse->assertStatus(200);
    }

    public function test_student_can_start_exam_with_token_and_sync_answers(): void
    {
        $student = User::where('username', '2026001')->first();
        $this->actingAs($student);

        // 1. Visit Student Dashboard
        $dashResponse = $this->get('/student/dashboard');
        $dashResponse->assertStatus(200);

        // 2. Start Exam with Token
        $session = ExamSession::where('access_token', 'SMART1')->first();
        $startResponse = $this->post("/student/exam/{$session->id}/start", [
            'token' => 'SMART1',
        ]);

        $attempt = ExamAttempt::where('student_id', $student->id)->where('exam_session_id', $session->id)->first();
        $this->assertNotNull($attempt);
        $startResponse->assertRedirect("/student/exam/{$attempt->id}");

        // 3. Open Exam Screen
        $screenResponse = $this->get("/student/exam/{$attempt->id}");
        $screenResponse->assertStatus(200);

        // 4. Test Background Batch Sync API
        $syncResponse = $this->postJson("/api/exam/{$attempt->id}/sync", [
            'answers' => [
                [
                    'question_id' => 1,
                    'selected_options' => ['A'],
                    'essay_text' => null,
                    'is_doubt' => false,
                ],
            ],
        ]);
        $syncResponse->assertStatus(200);
        $syncResponse->assertJson(['success' => true]);

        $this->assertDatabaseHas('attempt_answers', [
            'exam_attempt_id' => $attempt->id,
            'question_id' => 1,
        ]);

        // 5. Test Anti-Cheat Strike API
        $strikeResponse = $this->postJson("/api/exam/{$attempt->id}/strike");
        $strikeResponse->assertStatus(200);
        $strikeResponse->assertJson(['success' => true, 'cheat_strikes' => 1]);

        // 6. Submit Exam
        $submitResponse = $this->post("/student/exam/{$attempt->id}/submit");
        $submitResponse->assertRedirect("/student/exam/{$attempt->id}/result");

        $attempt->refresh();
        $this->assertContains($attempt->status, ['submitted', 'graded']);
    }

    public function test_filament_admin_panel_pages_and_resources(): void
    {
        $admin = User::where('username', 'admin')->first();
        $this->actingAs($admin);

        // Filament Main Dashboard
        $this->get('/filament')->assertStatus(200);

        // Filament Resources
        $this->get('/filament/class-rooms')->assertStatus(200);
        $this->get('/filament/subjects')->assertStatus(200);
        $this->get('/filament/users')->assertStatus(200);
        $this->get('/filament/question-banks')->assertStatus(200);
        $this->get('/filament/exam-schedules')->assertStatus(200);

        // Filament Custom Pages
        $this->get('/filament/control-room')->assertStatus(200);
        $this->get('/filament/grading-workbench')->assertStatus(200);
        $this->get('/filament/school-settings')->assertStatus(200);
    }

    public function test_admin_creates_schedule_with_auto_created_session(): void
    {
        $admin = User::where('username', 'admin')->first();
        $this->actingAs($admin);

        $subject = Subject::first();
        $bank = QuestionBank::first();
        $class = ClassRoom::first();

        $response = $this->post('/admin/schedules', [
            'title' => 'Simulasi SAS Matematika',
            'subject_id' => $subject->id,
            'question_bank_id' => $bank->id,
            'target_class_ids_json' => [$class->id],
            'duration_minutes' => 60,
            'min_submit_minutes' => 10,
            'shuffle_questions' => true,
            'shuffle_options' => true,
            'max_cheat_strikes' => 3,
            'show_score_to_student' => true,
            'is_remedial' => false,
            'auto_create_session' => true,
            'session_name' => 'Sesi 1 (Utama)',
            'room_name' => 'Lab Komputer 1',
            'access_token' => 'ABC999',
        ]);

        $response->assertSessionHas('success');

        $schedule = ExamSchedule::where('title', 'Simulasi SAS Matematika')->first();
        $this->assertNotNull($schedule);

        $session = ExamSession::where('exam_schedule_id', $schedule->id)->first();
        $this->assertNotNull($session);
        $this->assertEquals('Sesi 1 (Utama)', $session->session_name);
        $this->assertEquals('Lab Komputer 1', $session->room_name);
        $this->assertEquals('ABC999', $session->access_token);
        $this->assertEquals('active', $session->status);
    }

    public function test_students_cannot_access_filament_admin_panel(): void
    {
        $student = User::where('username', '2026001')->first();
        $this->actingAs($student);

        $this->get('/filament')->assertStatus(403);
    }
}
