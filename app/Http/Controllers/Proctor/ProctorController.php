<?php

namespace App\Http\Controllers\Proctor;

use App\Http\Controllers\Controller;
use App\Models\ExamAttempt;
use App\Models\ExamSession;
use App\Models\SchoolSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProctorController extends Controller
{
    /**
     * Proctor Control Room Dashboard
     */
    public function index(Request $request): Response
    {
        $proctor = auth()->user();

        // Active sessions proctored by this user or all active if admin
        $sessions = ExamSession::with(['examSchedule.subject', 'examSchedule.questionBank'])
            ->where('status', 'active')
            ->orderBy('start_time')
            ->get();

        $selectedSessionId = $request->query('session_id', $sessions->first()?->id);
        $selectedSession = $sessions->firstWhere('id', $selectedSessionId) ?? $sessions->first();

        $studentsMatrix = [];
        if ($selectedSession) {
            $schedule = $selectedSession->examSchedule;
            $totalQuestions = $schedule->questionBank ? $schedule->questionBank->questions()->count() : 0;

            // Target students for this exam session
            $targetClassIds = $schedule->target_class_ids_json ?? [];
            $allEligibleStudents = User::where('role', 'student')
                ->where('exam_room_name', $selectedSession->room_name)
                ->when(! empty($targetClassIds), fn ($q) => $q->whereIn('class_room_id', $targetClassIds))
                ->with('classRoom')
                ->orderBy('name')
                ->get();

            // 1. Attempts in this session (ensure any active/locked student is never missed)
            $attempts = ExamAttempt::where('exam_session_id', $selectedSession->id)
                ->with(['student.classRoom', 'answers'])
                ->latest()
                ->get();

            $handledStudentIds = [];

            foreach ($attempts as $att) {
                $student = $att->student;
                if (! $student) {
                    continue;
                }
                $handledStudentIds[] = $student->id;
                $answeredCount = $att->answers->count();

                $studentsMatrix[] = [
                    'student_id' => $student->id,
                    'name' => $student->name,
                    'username' => $student->username,
                    'class_name' => $student->classRoom ? $student->classRoom->name : ($student->role === 'admin' ? 'Administrator' : '-'),
                    'attempt_id' => $att->id,
                    'status' => $att->status, // ongoing, submitted, graded
                    'started_at' => $att->started_at?->format('H:i:s'),
                    'finished_at' => $att->finished_at?->format('H:i:s'),
                    'answered_count' => $answeredCount,
                    'total_questions' => $totalQuestions,
                    'cheat_strikes' => $att->cheat_strikes,
                    'max_cheat_strikes' => $schedule->max_cheat_strikes,
                    'is_locked' => (bool) $att->is_locked,
                    'final_score' => $att->final_score,
                    'device_info' => $att->device_info,
                    'active_session_id' => $student->active_session_id,
                ];
            }

            // 2. Add eligible students who haven't started yet
            foreach ($allEligibleStudents as $student) {
                if (in_array($student->id, $handledStudentIds)) {
                    continue;
                }

                $studentsMatrix[] = [
                    'student_id' => $student->id,
                    'name' => $student->name,
                    'username' => $student->username,
                    'class_name' => $student->classRoom ? $student->classRoom->name : '-',
                    'attempt_id' => null,
                    'status' => 'not_started',
                    'started_at' => null,
                    'finished_at' => null,
                    'answered_count' => 0,
                    'total_questions' => $totalQuestions,
                    'cheat_strikes' => 0,
                    'max_cheat_strikes' => $schedule->max_cheat_strikes,
                    'is_locked' => false,
                    'final_score' => null,
                    'device_info' => null,
                    'active_session_id' => $student->active_session_id,
                ];
            }
        }

        return Inertia::render('Proctor/Dashboard', [
            'sessions' => $sessions,
            'selectedSession' => $selectedSession,
            'studentsMatrix' => $studentsMatrix,
            'school' => SchoolSetting::current(),
        ]);
    }

    /**
     * Unlock student exam screen
     */
    public function unlockStudent(ExamAttempt $attempt)
    {
        $attempt->update([
            'is_locked' => false,
            'cheat_strikes' => 0, // Reset strikes upon proctor unlock
        ]);

        return back()->with('success', "Layar ujian siswa {$attempt->student->name} berhasil dibuka kuncinya.");
    }

    /**
     * Unlock all locked students in this session
     */
    public function unlockAll(ExamSession $session)
    {
        $count = ExamAttempt::where('exam_session_id', $session->id)
            ->where('is_locked', true)
            ->update([
                'is_locked' => false,
                'cheat_strikes' => 0,
            ]);

        return back()->with('success', "Berhasil membuka kunci {$count} siswa yang terkunci.");
    }

    /**
     * Reset login for student (if device died or restarted)
     */
    public function resetStudentLogin(User $student)
    {
        $student->update(['active_session_id' => null]);

        return back()->with('success', "Sesi login siswa {$student->name} telah di-reset. Siswa dapat login kembali di perangkat baru.");
    }

    /**
     * Save proctor notes during exam
     */
    public function saveNotes(Request $request, ExamSession $session)
    {
        $validated = $request->validate([
            'proctor_notes' => ['nullable', 'string'],
        ]);

        $session->update($validated);

        return back()->with('success', 'Catatan pengawas berhasil disimpan.');
    }

    /**
     * Print Berita Acara Pelaksanaan Ujian (A4/F4)
     */
    public function printBeritaAcara(ExamSession $session)
    {
        $session->load(['examSchedule.subject', 'proctor']);
        $school = SchoolSetting::current();

        $attempts = ExamAttempt::where('exam_session_id', $session->id)->with('student.classRoom')->get();
        $totalPresent = $attempts->count();
        $targetClassIds = $session->examSchedule->target_class_ids_json ?? [];
        $totalTarget = User::where('role', 'student')
            ->where('exam_room_name', $session->room_name)
            ->when(! empty($targetClassIds), fn ($q) => $q->whereIn('class_room_id', $targetClassIds))
            ->count();

        $totalAbsent = max(0, $totalTarget - $totalPresent);

        return view('print.berita_acara', [
            'session' => $session,
            'school' => $school,
            'totalPresent' => $totalPresent,
            'totalAbsent' => $totalAbsent,
            'totalTarget' => $totalTarget,
        ]);
    }
}
