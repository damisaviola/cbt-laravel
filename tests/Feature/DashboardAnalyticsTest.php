<?php

namespace Tests\Feature;

use App\Http\Controllers\DashboardAnalytics;
use App\Models\ExamAttempt;
use App\Models\ExamSession;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_dashboard_provides_real_chart_and_category_data(): void
    {
        $admin = User::where('username', 'admin')->first();
        $this->actingAs($admin);

        $response = $this->get('/admin/dashboard');
        $response->assertStatus(200);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Dashboard')
            ->has('chartData.This Year')
            ->has('chartData.Last 6 Months')
            ->has('chartData.This Month')
            ->has('categoryData')
        );
    }

    public function test_teacher_dashboard_provides_real_chart_and_category_data(): void
    {
        $teacher = User::where('username', 'guru')->first();
        $this->actingAs($teacher);

        $response = $this->get('/teacher/dashboard');
        $response->assertStatus(200);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Teacher/Dashboard')
            ->has('chartData.This Year')
            ->has('chartData.Last 6 Months')
            ->has('chartData.This Month')
            ->has('categoryData')
        );
    }

    public function test_student_dashboard_provides_real_chart_and_category_data(): void
    {
        $student = User::where('username', '2026001')->first();
        $this->actingAs($student);

        $response = $this->get('/student/dashboard');
        $response->assertStatus(200);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Student/Dashboard')
            ->has('chartData.This Year')
            ->has('chartData.Last 6 Months')
            ->has('chartData.This Month')
            ->has('categoryData')
        );
    }

    public function test_analytics_service_accurately_reflects_real_database_records(): void
    {
        $student = User::where('username', '2026001')->first();
        $session = ExamSession::first();

        // Create a completed attempt with score 95.00
        ExamAttempt::create([
            'exam_session_id' => $session->id,
            'student_id' => $student->id,
            'started_at' => now()->subHour(),
            'finished_at' => now(),
            'final_score' => 95.00,
            'is_passed_kkm' => true,
            'status' => 'submitted',
            'cheat_strikes' => 0,
            'is_locked' => false,
        ]);

        $analytics = app(DashboardAnalytics::class);

        // 1. Admin Trend data should reflect at least 1 attempt
        $adminTrends = $analytics->getAdminTrendData();
        $thisYearSum = collect($adminTrends['This Year'])->sum('value');
        $this->assertGreaterThanOrEqual(1, $thisYearSum);

        // 2. Student Trend data should reflect average score 95
        $studentTrends = $analytics->getStudentTrendData($student);
        $thisMonthAvg = collect($studentTrends['This Month'])->max('value');
        $this->assertEquals(95, $thisMonthAvg);

        // 3. Category Data should list existing subjects
        $categories = $analytics->getAdminCategories();
        $this->assertNotEmpty($categories);
        $this->assertArrayHasKey('name', $categories[0]);
        $this->assertArrayHasKey('percent', $categories[0]);
        $this->assertArrayHasKey('color', $categories[0]);
        $this->assertArrayHasKey('iconBg', $categories[0]);
    }
}
