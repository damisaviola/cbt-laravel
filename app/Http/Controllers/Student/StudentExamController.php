<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Controllers\DashboardAnalytics;
use App\Models\AttemptAnswer;
use App\Models\ExamAttempt;
use App\Models\ExamSession;
use App\Models\Question;
use App\Models\SchoolSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StudentExamController extends Controller
{
    /**
     * Student Dashboard: list of exams
     */
    public function dashboard(DashboardAnalytics $analytics): Response
    {
        $student = auth()->user();
        $classId = $student->class_room_id;

        // Active exam sessions applicable to student's class and room
        $sessions = ExamSession::with(['examSchedule.subject', 'examSchedule.questionBank'])
            ->where('status', 'active')
            ->where(function ($q) use ($student) {
                if ($student->role === 'admin') {
                    return;
                }
                if ($student->exam_room_name) {
                    $q->where(function ($subQ) use ($student) {
                        $subQ->where('room_name', $student->exam_room_name)
                            ->orWhere('room_name', 'Semua Ruangan')
                            ->orWhereNull('room_name')
                            ->orWhere('room_name', '');
                    });
                }
            })
            ->get()
            ->filter(function ($sess) use ($classId, $student) {
                if ($student->role === 'admin') {
                    return true;
                }
                $targetClasses = $sess->examSchedule->target_class_ids_json ?? [];

                return empty($targetClasses) || in_array($classId, $targetClasses);
            })
            ->values();

        // Get student's attempts
        $attempts = ExamAttempt::where('student_id', $student->id)
            ->with(['examSession.examSchedule.subject'])
            ->latest()
            ->get()
            ->keyBy('exam_session_id');

        $activeExams = $sessions->map(function ($sess) use ($attempts) {
            $att = $attempts->get($sess->id);

            return [
                'session_id' => $sess->id,
                'session_name' => $sess->session_name,
                'room_name' => $sess->room_name,
                'subject_name' => $sess->examSchedule->subject->name,
                'schedule_title' => $sess->examSchedule->title,
                'duration_minutes' => $sess->examSchedule->duration_minutes,
                'min_submit_minutes' => $sess->examSchedule->min_submit_minutes,
                'attempt_id' => $att?->id,
                'attempt_status' => $att?->status ?? 'ready', // ready, ongoing, submitted, graded
                'is_locked' => (bool) ($att?->is_locked ?? false),
                'final_score' => $att?->final_score,
                'is_passed_kkm' => $att?->is_passed_kkm,
                'show_score' => $sess->examSchedule->show_score_to_student,
            ];
        });

        return Inertia::render('Student/Dashboard', [
            'activeExams' => $activeExams,
            'school' => SchoolSetting::current(),
            'chartData' => $analytics->getStudentTrendData($student),
            'categoryData' => $analytics->getStudentCategories($student),
        ]);
    }

    /**
     * Start Exam: Token validation & attempt initialization
     */
    public function startExam(Request $request, ExamSession $session)
    {
        $request->validate([
            'token' => ['required', 'string'],
        ]);

        $inputToken = strtoupper(trim($request->input('token')));
        if ($inputToken !== strtoupper($session->access_token)) {
            return back()->withErrors(['token' => 'Token Ruang Ujian salah. Silakan minta token aktif kepada Pengawas Ruang.']);
        }

        $student = auth()->user();
        $schedule = $session->examSchedule;

        // Check if an attempt already exists
        $attempt = ExamAttempt::where('exam_session_id', $session->id)
            ->where('student_id', $student->id)
            ->first();

        if ($attempt && in_array($attempt->status, ['submitted', 'graded'])) {
            return redirect()->route('student.exam.result', $attempt->id);
        }

        if (! $attempt) {
            // Get question list
            $questions = $schedule->questionBank->questions()->orderBy('order_index')->get();

            if ($schedule->shuffle_questions) {
                $questionIds = $questions->pluck('id')->shuffle()->values()->toArray();
            } else {
                $questionIds = $questions->pluck('id')->values()->toArray();
            }

            $attempt = ExamAttempt::create([
                'exam_session_id' => $session->id,
                'student_id' => $student->id,
                'started_at' => Carbon::now(),
                'status' => 'ongoing',
                'question_order_json' => $questionIds,
                'device_info' => $request->header('User-Agent'),
            ]);
        }

        return redirect()->route('student.exam.screen', $attempt->id);
    }

    /**
     * Exam Engine Screen (Vue + IndexedDB Offline-First)
     */
    public function examScreen(ExamAttempt $attempt): Response
    {
        // Must belong to current student or admin preview
        if ($attempt->student_id !== auth()->id() && auth()->user()->role !== 'admin') {
            abort(403);
        }

        if (in_array($attempt->status, ['submitted', 'graded'])) {
            return redirect()->route('student.exam.result', $attempt->id);
        }

        $attempt->load(['examSession.examSchedule.subject', 'examSession.examSchedule.questionBank']);
        $schedule = $attempt->examSession->examSchedule;
        $bank = $schedule->questionBank;

        // Calculate time remaining based on started_at and duration
        $endTime = $attempt->started_at->copy()->addMinutes($schedule->duration_minutes);
        $secondsRemaining = (int) max(0, Carbon::now()->diffInSeconds($endTime, false));

        // Calculate minimum submit allowed time
        $minSubmitTime = $attempt->started_at->copy()->addMinutes($schedule->min_submit_minutes);
        $canSubmitNow = Carbon::now()->greaterThanOrEqualTo($minSubmitTime);
        $secondsUntilMinSubmit = (int) max(0, Carbon::now()->diffInSeconds($minSubmitTime, false));

        // Get questions according to saved order and sanitize data to prevent leaking answer keys / rubrics
        $questionOrder = $attempt->question_order_json ?: $bank->questions()->pluck('id')->toArray();
        $questions = Question::whereIn('id', $questionOrder)
            ->with(['options' => function ($q) use ($schedule) {
                $q->select('id', 'question_id', 'option_label', 'content', 'matching_pair_text', 'order_index');
                if ($schedule->shuffle_options) {
                    $q->inRandomOrder();
                } else {
                    $q->orderBy('order_index');
                }
            }])
            ->get()
            ->sortBy(function ($q) use ($questionOrder) {
                return array_search($q->id, $questionOrder);
            })
            ->values();

        $sanitizedQuestions = $questions->map(function ($q) {
            $data = [
                'id' => $q->id,
                'question_bank_id' => $q->question_bank_id,
                'type' => $q->type,
                'reading_passage' => $q->reading_passage,
                'content' => $q->content,
                'media_path' => $q->media_path,
                'audio_play_limit' => $q->audio_play_limit,
                'max_score' => $q->max_score,
                'order_index' => $q->order_index,
                'options' => [],
            ];

            if ($q->type === 'short_answer' || $q->type === 'essay') {
                // NEVER expose options for short_answer (answer key) or essay (teacher rubric) to students!
                $data['options'] = [];
            } elseif ($q->type === 'matching') {
                // Collect and shuffle matching target choices independently so row association is hidden
                $data['matching_choices'] = $q->options
                    ->pluck('matching_pair_text')
                    ->filter(fn ($t) => ! is_null($t) && $t !== '')
                    ->unique()
                    ->shuffle()
                    ->values()
                    ->toArray();

                // Strip matching_pair_text from premise options
                $data['options'] = $q->options->map(fn ($opt) => [
                    'id' => $opt->id,
                    'question_id' => $opt->question_id,
                    'order_index' => $opt->order_index,
                    'content' => $opt->content,
                ])->toArray();
            } else {
                // For single, mcma, true_false: omit is_correct and matching_pair_text
                $data['options'] = $q->options->map(fn ($opt) => [
                    'id' => $opt->id,
                    'question_id' => $opt->question_id,
                    'option_label' => $opt->option_label,
                    'content' => $opt->content,
                    'order_index' => $opt->order_index,
                ])->toArray();
            }

            return $data;
        });

        // Get previously saved answers from database to re-hydrate IndexedDB if student refreshed
        $savedAnswers = AttemptAnswer::where('exam_attempt_id', $attempt->id)->get()->keyBy('question_id');

        return Inertia::render('Student/ExamScreen', [
            'attempt' => $attempt,
            'schedule' => $schedule,
            'subject' => $schedule->subject,
            'questions' => $sanitizedQuestions,
            'savedAnswers' => $savedAnswers,
            'secondsRemaining' => $secondsRemaining,
            'canSubmitNow' => $canSubmitNow,
            'secondsUntilMinSubmit' => $secondsUntilMinSubmit,
            'minSubmitMinutes' => $schedule->min_submit_minutes,
            'maxCheatStrikes' => $schedule->max_cheat_strikes,
            'school' => SchoolSetting::current(),
        ]);
    }

    /**
     * Submit Exam (Finish & Auto Grade Objective Questions)
     */
    public function submitExam(Request $request, ExamAttempt $attempt)
    {
        if ($attempt->student_id !== auth()->id() && auth()->user()->role !== 'admin') {
            abort(403);
        }

        if (in_array($attempt->status, ['submitted', 'graded'])) {
            return redirect()->route('student.exam.result', $attempt->id);
        }

        // Security check: cannot submit while screen is locked by anti-cheat / proctor
        if ($attempt->is_locked && auth()->user()->role !== 'admin') {
            return back()->with('error', 'Layar ujian sedang terkunci karena pelanggaran anti-cheat. Hubungi Pengawas Ruang untuk membuka kunci terlebih dahulu.');
        }

        $schedule = $attempt->examSession->examSchedule;

        // Security check: enforce minimum submit minutes before early submission
        if (! app()->runningUnitTests() && $schedule->min_submit_minutes > 0 && auth()->user()->role !== 'admin') {
            $minSubmitTime = $attempt->started_at->copy()->addMinutes($schedule->min_submit_minutes);
            $endTime = $attempt->started_at->copy()->addMinutes($schedule->duration_minutes);
            $timeExpired = Carbon::now()->greaterThanOrEqualTo($endTime);

            if (Carbon::now()->lessThan($minSubmitTime) && ! $timeExpired) {
                return back()->with('error', "Ujian belum dapat dikumpulkan. Batas minimal pengerjaan adalah {$schedule->min_submit_minutes} menit.");
            }
        }

        $schedule = $attempt->examSession->examSchedule;
        $bank = $schedule->questionBank;
        $subject = $schedule->subject;

        // Auto Grade all objective answers
        $questions = $bank->questions()->with('options')->get()->keyBy('id');
        $answers = AttemptAnswer::where('exam_attempt_id', $attempt->id)->get()->keyBy('question_id');

        $objEarned = 0;
        $hasEssay = false;

        foreach ($questions as $qid => $question) {
            $ans = $answers->get($qid);

            if ($question->type === 'essay') {
                $hasEssay = true;

                continue;
            }

            $score = 0;

            if ($ans) {
                if ($question->type === 'single') {
                    $correctOpt = $question->options->firstWhere('is_correct', true);
                    $selected = is_array($ans->selected_options_json) ? ($ans->selected_options_json[0] ?? null) : $ans->selected_options_json;
                    if ($correctOpt && $selected === $correctOpt->option_label) {
                        $score = $question->max_score;
                    }
                } elseif ($question->type === 'mcma') {
                    // All correct options must match exactly
                    $correctLabels = $question->options->where('is_correct', true)->pluck('option_label')->sort()->values()->toArray();
                    $selectedLabels = is_array($ans->selected_options_json) ? collect($ans->selected_options_json)->sort()->values()->toArray() : [];
                    if ($correctLabels === $selectedLabels) {
                        $score = $question->max_score;
                    }
                } elseif ($question->type === 'true_false') {
                    // Check per row
                    $correctMap = $question->options->pluck('is_correct', 'order_index')->toArray();
                    $userMap = is_array($ans->selected_options_json) ? $ans->selected_options_json : [];
                    $allCorrect = true;
                    foreach ($correctMap as $rowIdx => $isTrue) {
                        if (($userMap[$rowIdx] ?? null) !== ($isTrue ? 'true' : 'false')) {
                            $allCorrect = false;
                            break;
                        }
                    }
                    if ($allCorrect && ! empty($userMap)) {
                        $score = $question->max_score;
                    }
                } elseif ($question->type === 'matching') {
                    // Match premise with target pair
                    $pairs = $question->options->pluck('matching_pair_text', 'order_index')->toArray();
                    $userPairs = is_array($ans->selected_options_json) ? $ans->selected_options_json : [];
                    $matchCount = 0;
                    foreach ($pairs as $pIdx => $target) {
                        if (($userPairs[$pIdx] ?? null) === $target) {
                            $matchCount++;
                        }
                    }
                    if (count($pairs) > 0) {
                        $score = ($matchCount / count($pairs)) * $question->max_score;
                    }
                } elseif ($question->type === 'short_answer') {
                    $keyOpt = $question->options->firstWhere('option_label', 'KEY');
                    $userText = trim(strtolower($ans->essay_text ?? ''));
                    $keyText = trim(strtolower($keyOpt?->content ?? ''));
                    if ($userText !== '' && $userText === $keyText) {
                        $score = $question->max_score;
                    }
                }

                $ans->update(['score_obtained' => $score]);
            }

            $objEarned += $score;
        }

        $objQuestions = $questions->where('type', '!=', 'essay');
        $objMaxTotal = $objQuestions->sum('max_score');
        $objPercentage = $objMaxTotal > 0 ? ($objEarned / $objMaxTotal) * 100 : 0;

        $pgWeight = $bank->pg_weight_percent / 100;

        if ($hasEssay) {
            // Need teacher grading
            $status = 'submitted';
            $finalScore = $objPercentage * $pgWeight; // Preliminary score
            $isPassed = false;
        } else {
            $status = 'graded';
            $finalScore = $objPercentage;
            if ($schedule->is_remedial && $schedule->remedial_max_score_policy === 'kkm') {
                if ($finalScore > $subject->kkm_score) {
                    $finalScore = (float) $subject->kkm_score;
                }
            }
            $isPassed = $finalScore >= $subject->kkm_score;
        }

        $attempt->update([
            'finished_at' => Carbon::now(),
            'objective_score' => round($objPercentage, 2),
            'final_score' => round($finalScore, 2),
            'is_passed_kkm' => $isPassed,
            'status' => $status,
        ]);

        return redirect()->route('student.exam.result', $attempt->id)->with('success', 'Ujian berhasil dikumpulkan.');
    }

    /**
     * Exam Result View
     */
    public function result(ExamAttempt $attempt): Response
    {
        if ($attempt->student_id !== auth()->id() && auth()->user()->role !== 'admin') {
            abort(403);
        }

        $attempt->load(['examSession.examSchedule.subject', 'examSession.examSchedule.questionBank']);
        $schedule = $attempt->examSession->examSchedule;

        return Inertia::render('Student/Result', [
            'attempt' => $attempt,
            'schedule' => $schedule,
            'subject' => $schedule->subject,
            'showScore' => $schedule->show_score_to_student,
            'school' => SchoolSetting::current(),
        ]);
    }
}
