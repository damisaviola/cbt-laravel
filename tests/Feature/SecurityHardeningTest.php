<?php

namespace Tests\Feature;

use App\Models\AttemptAnswer;
use App\Models\ClassRoom;
use App\Models\ExamAttempt;
use App\Models\ExamSchedule;
use App\Models\ExamSession;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\QuestionOption;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_security_headers_are_present(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_student_exam_screen_does_not_leak_short_answer_key_or_essay_rubric(): void
    {
        $student = User::where('username', '2026001')->first();
        $bank = QuestionBank::first();

        // Add a short_answer question with secret key
        $shortQ = Question::create([
            'question_bank_id' => $bank->id,
            'type' => 'short_answer',
            'content' => 'Berapa hasil dari 5 x 5?',
            'max_score' => 10,
            'order_index' => 98,
        ]);
        QuestionOption::create([
            'question_id' => $shortQ->id,
            'option_label' => 'KEY',
            'content' => 'SECRET_KEY_25',
            'is_correct' => true,
            'order_index' => 1,
        ]);

        // Add an essay question with secret rubric
        $essayQ = Question::create([
            'question_bank_id' => $bank->id,
            'type' => 'essay',
            'content' => 'Jelaskan konsep gravitasi!',
            'max_score' => 20,
            'order_index' => 99,
        ]);
        QuestionOption::create([
            'question_id' => $essayQ->id,
            'option_label' => 'RUBRIC',
            'content' => 'SECRET_TEACHER_RUBRIC_GRAVITY',
            'is_correct' => true,
            'order_index' => 1,
        ]);

        $session = ExamSession::first();
        $attempt = ExamAttempt::create([
            'exam_session_id' => $session->id,
            'student_id' => $student->id,
            'started_at' => now(),
            'status' => 'ongoing',
            'question_order_json' => [$shortQ->id, $essayQ->id],
        ]);

        $this->actingAs($student);
        $response = $this->get("/student/exam/{$attempt->id}");
        $response->assertStatus(200);

        $response->assertInertia(function (Assert $page) {
            $page->component('Student/ExamScreen')
                ->has('questions', 2)
                ->where('questions.0.options', [])
                ->where('questions.1.options', []);
        });

        // Ensure raw response content does NOT contain the secret keys/rubrics
        $this->assertStringNotContainsString('SECRET_KEY_25', $response->getContent());
        $this->assertStringNotContainsString('SECRET_TEACHER_RUBRIC_GRAVITY', $response->getContent());
    }

    public function test_teacher_cannot_modify_or_delete_other_teachers_question_bank(): void
    {
        $teacherA = User::where('username', 'guru')->first();
        $teacherB = User::create([
            'name' => 'Guru Lain',
            'username' => 'guru2',
            'email' => 'guru2@sekolah.sch.id',
            'password' => Hash::make('password'),
            'role' => 'teacher',
            'is_active' => true,
        ]);

        $bankA = QuestionBank::where('teacher_id', $teacherA->id)->first();
        $this->assertNotNull($bankA);

        // Teacher B attempts to view Teacher A's bank
        $this->actingAs($teacherB);
        $responseShow = $this->get("/teacher/banks/{$bankA->id}");
        $responseShow->assertStatus(403);

        // Teacher B attempts to update Teacher A's bank
        $responseUpdate = $this->put("/teacher/banks/{$bankA->id}", [
            'title' => 'Hacked Title',
            'pg_weight_percent' => 50,
            'essay_weight_percent' => 50,
        ]);
        $responseUpdate->assertStatus(403);

        // Teacher B attempts to delete Teacher A's bank
        $responseDelete = $this->delete("/teacher/banks/{$bankA->id}");
        $responseDelete->assertStatus(403);

        // Verify bank still exists intact
        $this->assertDatabaseHas('question_banks', [
            'id' => $bankA->id,
            'title' => $bankA->title,
        ]);
    }

    public function test_essay_grading_rejects_score_higher_than_max_score(): void
    {
        $teacher = User::where('username', 'guru')->first();
        $this->actingAs($teacher);

        $bank = QuestionBank::first();
        $q = Question::create([
            'question_bank_id' => $bank->id,
            'type' => 'essay',
            'content' => 'Pertanyaan Esai',
            'max_score' => 15.00,
            'order_index' => 1,
        ]);

        $student = User::where('username', '2026001')->first();
        $session = ExamSession::first();
        $attempt = ExamAttempt::create([
            'exam_session_id' => $session->id,
            'student_id' => $student->id,
            'started_at' => now(),
            'status' => 'submitted',
        ]);

        $answer = AttemptAnswer::create([
            'exam_attempt_id' => $attempt->id,
            'question_id' => $q->id,
            'essay_text' => 'Jawaban Siswa',
        ]);

        // Attempting to submit a score of 999 when max_score is 15
        $response = $this->post("/teacher/grading/answer/{$answer->id}", [
            'score_obtained' => 999,
        ]);
        $response->assertSessionHasErrors('score_obtained');

        // Valid score within max_score succeeds
        $validResponse = $this->post("/teacher/grading/answer/{$answer->id}", [
            'score_obtained' => 12.5,
        ]);
        $validResponse->assertSessionHasNoErrors();

        $answer->refresh();
        $this->assertEquals(12.5, $answer->score_obtained);
    }

    public function test_sis_api_sync_requires_valid_api_key(): void
    {
        // Unauthenticated request without token gets 401
        $unauth = $this->postJson('/api/v1/students/sync', [
            'students' => [
                ['username' => 'TEST999', 'name' => 'Siswa Test'],
            ],
        ]);
        $unauth->assertStatus(401);

        // With invalid token gets 401
        $invalid = $this->withHeader('X-API-KEY', 'wrong-key')->postJson('/api/v1/students/sync', [
            'students' => [
                ['username' => 'TEST999', 'name' => 'Siswa Test'],
            ],
        ]);
        $invalid->assertStatus(401);

        // With valid token succeeds
        $token = env('SIS_API_TOKEN', 'cbt-sis-sync-secret-key');
        $valid = $this->withHeader('X-API-KEY', $token)->postJson('/api/v1/students/sync', [
            'students' => [
                ['username' => 'TEST999', 'name' => 'Siswa Test Sync', 'class_name' => 'X-1'],
            ],
        ]);
        $valid->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'username' => 'TEST999',
            'name' => 'Siswa Test Sync',
        ]);
    }

    public function test_locked_attempt_rejects_answer_synchronization(): void
    {
        $student = User::where('username', '2026001')->first();
        $session = ExamSession::first();

        $attempt = ExamAttempt::create([
            'exam_session_id' => $session->id,
            'student_id' => $student->id,
            'started_at' => now(),
            'status' => 'ongoing',
            'is_locked' => true, // Student locked for cheating
            'cheat_strikes' => 3,
        ]);

        $this->actingAs($student);
        $response = $this->postJson("/api/exam/{$attempt->id}/sync", [
            'answers' => [
                ['question_id' => 1, 'selected_options' => ['A']],
            ],
        ]);

        $response->assertStatus(423); // 423 Locked
    }
}
