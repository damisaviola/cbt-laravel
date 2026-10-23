<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\ExamAttempt;
use App\Models\ExamSchedule;
use App\Models\ExamSession;
use App\Models\QuestionBank;
use App\Models\SchoolSetting;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    public function index(): Response
    {
        $school = SchoolSetting::current();

        $stats = [
            'total_students' => User::where('role', 'student')->count(),
            'total_teachers' => User::where('role', 'teacher')->count(),
            'total_proctors' => User::where('role', 'proctor')->count(),
            'total_classes' => ClassRoom::count(),
            'total_subjects' => Subject::count(),
            'total_banks' => QuestionBank::count(),
            'active_schedules' => ExamSchedule::count(),
            'active_sessions' => ExamSession::where('status', 'active')->count(),
            'total_attempts' => ExamAttempt::count(),
            'ongoing_attempts' => ExamAttempt::where('status', 'ongoing')->count(),
            'server_php_version' => PHP_VERSION,
            'server_os' => PHP_OS,
        ];

        $recentSessions = ExamSession::with(['examSchedule.subject', 'proctor'])
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'school' => $school,
            'stats' => $stats,
            'recentSessions' => $recentSessions,
        ]);
    }

    public function settings(): Response
    {
        $school = SchoolSetting::current();

        return Inertia::render('Admin/Settings', [
            'school' => $school,
            'serverInfo' => [
                'php_version' => PHP_VERSION,
                'os' => PHP_OS,
                'laravel_version' => app()->version(),
            ],
        ]);
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'school_name' => ['required', 'string', 'max:255'],
            'npsn' => ['required', 'string', 'max:20'],
            'headmaster_name' => ['required', 'string', 'max:255'],
            'headmaster_nip' => ['nullable', 'string', 'max:50'],
            'active_academic_year' => ['required', 'string', 'max:50'],
            'active_semester' => ['required', 'string', 'in:Ganjil,Genap'],
            'primary_color' => ['required', 'string', 'max:20'],
            'seb_required' => ['boolean'],
            'seb_key_hash' => ['nullable', 'string', 'max:255'],
        ]);

        $school = SchoolSetting::current();
        $school->update($validated);

        return back()->with('success', 'Identitas dan pengaturan sekolah berhasil diperbarui.');
    }
}
