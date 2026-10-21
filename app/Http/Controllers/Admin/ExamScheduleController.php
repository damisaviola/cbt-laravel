<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\ExamSchedule;
use App\Models\ExamSession;
use App\Models\QuestionBank;
use App\Models\Subject;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ExamScheduleController extends Controller
{
    public function index(): Response
    {
        $schedules = ExamSchedule::with(['subject', 'questionBank', 'sessions.proctor'])
            ->withCount('sessions')
            ->latest()
            ->get();

        $subjects = Subject::orderBy('name')->get(['id', 'name', 'code']);
        $questionBanks = QuestionBank::with('subject')->latest()->get(['id', 'title', 'subject_id']);
        $classes = ClassRoom::orderBy('name')->get(['id', 'name', 'grade_level']);
        $proctors = User::whereIn('role', ['proctor', 'teacher'])->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/ExamSchedules', [
            'schedules' => $schedules,
            'subjects' => $subjects,
            'questionBanks' => $questionBanks,
            'classes' => $classes,
            'proctors' => $proctors,
        ]);
    }

    public function storeSchedule(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'question_bank_id' => ['required', 'exists:question_banks,id'],
            'target_class_ids_json' => ['required', 'array'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:360'],
            'min_submit_minutes' => ['required', 'integer', 'min:0', 'max:360'],
            'shuffle_questions' => ['boolean'],
            'shuffle_options' => ['boolean'],
            'max_cheat_strikes' => ['required', 'integer', 'min:1', 'max:20'],
            'show_score_to_student' => ['boolean'],
            'is_remedial' => ['boolean'],
            'auto_create_session' => ['nullable', 'boolean'],
            'session_name' => ['nullable', 'string', 'max:50'],
            'room_name' => ['nullable', 'string', 'max:100'],
            'start_time' => ['nullable', 'date'],
            'end_time' => ['nullable', 'date'],
            'proctor_id' => ['nullable', 'exists:users,id'],
            'access_token' => ['nullable', 'string', 'max:10'],
        ]);

        $schedule = ExamSchedule::create([
            'title' => $validated['title'],
            'subject_id' => $validated['subject_id'],
            'question_bank_id' => $validated['question_bank_id'],
            'target_class_ids_json' => $validated['target_class_ids_json'],
            'duration_minutes' => $validated['duration_minutes'],
            'min_submit_minutes' => $validated['min_submit_minutes'],
            'shuffle_questions' => $request->boolean('shuffle_questions', true),
            'shuffle_options' => $request->boolean('shuffle_options', true),
            'max_cheat_strikes' => $validated['max_cheat_strikes'],
            'show_score_to_student' => $request->boolean('show_score_to_student', false),
            'is_remedial' => $request->boolean('is_remedial', false),
        ]);

        $message = 'Jadwal Ujian berhasil dibuat.';

        if ($request->boolean('auto_create_session', true)) {
            $token = ! empty($validated['access_token'])
                ? strtoupper(trim($validated['access_token']))
                : strtoupper(Str::random(6));

            $startTime = ! empty($validated['start_time'])
                ? Carbon::parse($validated['start_time'])
                : Carbon::now()->startOfHour();

            $endTime = ! empty($validated['end_time'])
                ? Carbon::parse($validated['end_time'])
                : $startTime->copy()->addHours(6);

            ExamSession::create([
                'exam_schedule_id' => $schedule->id,
                'session_name' => $validated['session_name'] ?? 'Sesi 1 (Utama)',
                'room_name' => $validated['room_name'] ?? 'Lab Komputer 1',
                'access_token' => $token,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'proctor_id' => $validated['proctor_id'] ?? null,
                'status' => 'active',
            ]);

            $message = "Jadwal Ujian & Sesi 1 berhasil dibuat (Token: {$token}). Ujian langsung aktif dan siap dikerjakan!";
        }

        return back()->with('success', $message);
    }

    public function updateSchedule(Request $request, ExamSchedule $schedule)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'question_bank_id' => ['required', 'exists:question_banks,id'],
            'target_class_ids_json' => ['required', 'array'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:360'],
            'min_submit_minutes' => ['required', 'integer', 'min:0', 'max:360'],
            'shuffle_questions' => ['boolean'],
            'shuffle_options' => ['boolean'],
            'max_cheat_strikes' => ['required', 'integer', 'min:1', 'max:20'],
            'show_score_to_student' => ['boolean'],
            'is_remedial' => ['boolean'],
        ]);

        $schedule->update([
            ...$validated,
            'shuffle_questions' => $request->boolean('shuffle_questions', true),
            'shuffle_options' => $request->boolean('shuffle_options', true),
            'show_score_to_student' => $request->boolean('show_score_to_student', false),
            'is_remedial' => $request->boolean('is_remedial', false),
        ]);

        return back()->with('success', 'Jadwal Ujian berhasil diperbarui.');
    }

    public function destroySchedule(ExamSchedule $schedule)
    {
        $schedule->delete();

        return back()->with('success', 'Jadwal Ujian berhasil dihapus.');
    }

    public function storeSession(Request $request, ExamSchedule $schedule)
    {
        $validated = $request->validate([
            'session_name' => ['required', 'string', 'max:50'],
            'room_name' => ['required', 'string', 'max:100'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'proctor_id' => ['nullable', 'exists:users,id'],
            'access_token' => ['nullable', 'string', 'max:10'],
        ]);

        // Generate token if not specified
        $token = ! empty($validated['access_token'])
            ? strtoupper(trim($validated['access_token']))
            : strtoupper(Str::random(6));

        ExamSession::create([
            'exam_schedule_id' => $schedule->id,
            'session_name' => $validated['session_name'],
            'room_name' => $validated['room_name'],
            'access_token' => $token,
            'start_time' => Carbon::parse($validated['start_time']),
            'end_time' => Carbon::parse($validated['end_time']),
            'proctor_id' => $validated['proctor_id'] ?? null,
            'status' => 'active',
        ]);

        return back()->with('success', "Sesi ruang berhasil dibuat dengan Token: {$token}");
    }

    public function updateSession(Request $request, ExamSession $session)
    {
        $validated = $request->validate([
            'session_name' => ['required', 'string', 'max:50'],
            'room_name' => ['required', 'string', 'max:100'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'proctor_id' => ['nullable', 'exists:users,id'],
            'access_token' => ['required', 'string', 'max:10'],
            'status' => ['required', 'in:upcoming,active,finished'],
        ]);

        $session->update([
            'session_name' => $validated['session_name'],
            'room_name' => $validated['room_name'],
            'access_token' => strtoupper(trim($validated['access_token'])),
            'start_time' => Carbon::parse($validated['start_time']),
            'end_time' => Carbon::parse($validated['end_time']),
            'proctor_id' => $validated['proctor_id'] ?? null,
            'status' => $validated['status'],
        ]);

        return back()->with('success', 'Sesi ujian berhasil diperbarui.');
    }

    public function regenerateToken(ExamSession $session)
    {
        $newToken = strtoupper(Str::random(6));
        $session->update(['access_token' => $newToken]);

        return back()->with('success', "Token baru untuk {$session->session_name} ({$session->room_name}): {$newToken}");
    }

    public function destroySession(ExamSession $session)
    {
        $session->delete();

        return back()->with('success', 'Sesi ruang ujian berhasil dihapus.');
    }
}
