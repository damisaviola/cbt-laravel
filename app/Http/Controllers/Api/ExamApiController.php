<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttemptAnswer;
use App\Models\ExamAttempt;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamApiController extends Controller
{
    /**
     * Background Batch Sync: Upsert student answers
     * POST /api/exam/{attempt}/sync
     */
    public function sync(Request $request, ExamAttempt $attempt): JsonResponse
    {
        // Security check: must be student's own attempt and ongoing (or admin preview)
        if ($attempt->student_id !== auth()->id() && auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (in_array($attempt->status, ['submitted', 'graded'])) {
            return response()->json(['error' => 'Exam already submitted', 'status' => $attempt->status], 400);
        }

        $answers = $request->input('answers', []);
        $syncedQuestionIds = [];

        foreach ($answers as $item) {
            $questionId = $item['question_id'] ?? null;
            if (! $questionId) {
                continue;
            }

            AttemptAnswer::updateOrCreate(
                [
                    'exam_attempt_id' => $attempt->id,
                    'question_id' => $questionId,
                ],
                [
                    'selected_options_json' => $item['selected_options'] ?? null,
                    'essay_text' => $item['essay_text'] ?? null,
                    'is_doubt' => (bool) ($item['is_doubt'] ?? false),
                ]
            );

            $syncedQuestionIds[] = $questionId;
        }

        // Return server authoritative time remaining & lock status
        $schedule = $attempt->examSession->examSchedule;
        $endTime = $attempt->started_at->copy()->addMinutes($schedule->duration_minutes);
        $secondsRemaining = (int) max(0, Carbon::now()->diffInSeconds($endTime, false));

        return response()->json([
            'success' => true,
            'synced_question_ids' => $syncedQuestionIds,
            'seconds_remaining' => $secondsRemaining,
            'is_locked' => (bool) $attempt->is_locked,
            'cheat_strikes' => $attempt->cheat_strikes,
            'server_time' => Carbon::now()->toIso8601String(),
        ]);
    }

    /**
     * Anti-Cheat: Record strike when student switches tab / leaves fullscreen
     * POST /api/exam/{attempt}/strike
     */
    public function recordStrike(Request $request, ExamAttempt $attempt): JsonResponse
    {
        if ($attempt->student_id !== auth()->id() && auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $schedule = $attempt->examSession->examSchedule;
        $attempt->increment('cheat_strikes');

        $isLocked = false;
        if ($attempt->cheat_strikes >= $schedule->max_cheat_strikes) {
            $attempt->update(['is_locked' => true]);
            $isLocked = true;
        }

        return response()->json([
            'success' => true,
            'cheat_strikes' => $attempt->cheat_strikes,
            'max_strikes' => $schedule->max_cheat_strikes,
            'is_locked' => $isLocked,
        ]);
    }

    /**
     * Check attempt heartbeat and status
     * GET /api/exam/{attempt}/status
     */
    public function status(ExamAttempt $attempt): JsonResponse
    {
        if ($attempt->student_id !== auth()->id() && auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $schedule = $attempt->examSession->examSchedule;
        $endTime = $attempt->started_at->copy()->addMinutes($schedule->duration_minutes);
        $secondsRemaining = (int) max(0, Carbon::now()->diffInSeconds($endTime, false));

        return response()->json([
            'status' => $attempt->status,
            'is_locked' => (bool) $attempt->is_locked,
            'cheat_strikes' => $attempt->cheat_strikes,
            'seconds_remaining' => $secondsRemaining,
        ]);
    }

    /**
     * Unlock student attempt via API
     * POST /api/exam/{attempt}/unlock
     */
    public function unlock(Request $request, ExamAttempt $attempt): JsonResponse
    {
        $user = auth()->user();
        if (! in_array($user->role, ['admin', 'teacher', 'proctor'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $attempt->update([
            'is_locked' => false,
            'cheat_strikes' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Layar ujian berhasil dibuka.',
            'is_locked' => false,
            'cheat_strikes' => 0,
        ]);
    }
}
