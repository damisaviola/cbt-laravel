<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AttemptAnswer;
use App\Models\ExamAttempt;
use App\Models\ExamSchedule;
use App\Models\ExamSession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GradingController extends Controller
{
    /**
     * List exams available for grading / evaluation
     */
    public function index(): Response
    {
        $teacher = auth()->user();

        $schedules = ExamSchedule::with(['subject', 'questionBank'])
            ->withCount(['sessions'])
            ->latest()
            ->get()
            ->map(function ($sch) {
                $attempts = ExamAttempt::whereHas('examSession', fn ($q) => $q->where('exam_schedule_id', $sch->id))->get();
                $sch->total_students = $attempts->count();
                $sch->submitted_count = $attempts->whereIn('status', ['submitted', 'graded'])->count();
                $sch->needs_grading_count = $attempts->where('status', 'submitted')->count();
                $sch->passed_kkm_count = $attempts->where('is_passed_kkm', true)->count();

                return $sch;
            });

        return Inertia::render('Teacher/GradingIndex', [
            'schedules' => $schedules,
        ]);
    }

    /**
     * Speed-Grading Workbench for Essay answers
     */
    public function workbench(ExamSchedule $schedule): Response
    {
        $schedule->load(['subject', 'questionBank.questions.options']);

        // Get all essay questions for this exam
        $essayQuestions = $schedule->questionBank->questions->where('type', 'essay')->values();

        // Get all completed attempts for this schedule
        $attempts = ExamAttempt::whereHas('examSession', fn ($q) => $q->where('exam_schedule_id', $schedule->id))
            ->whereIn('status', ['submitted', 'graded'])
            ->with(['student.classRoom', 'answers' => function ($q) use ($essayQuestions) {
                $q->whereIn('question_id', $essayQuestions->pluck('id'));
            }])
            ->get();

        return Inertia::render('Teacher/SpeedGradingWorkbench', [
            'schedule' => $schedule,
            'essayQuestions' => $essayQuestions,
            'attempts' => $attempts,
        ]);
    }

    /**
     * Save rapid essay score for a student answer
     */
    public function saveEssayScore(Request $request, AttemptAnswer $answer)
    {
        $validated = $request->validate([
            'score_obtained' => ['required', 'numeric', 'min:0'],
            'teacher_feedback' => ['nullable', 'string'],
        ]);

        $answer->update([
            'score_obtained' => $validated['score_obtained'],
            'teacher_feedback' => $validated['teacher_feedback'] ?? null,
            'is_graded_by_teacher' => true,
        ]);

        // Recalculate student final score
        $this->recalculateAttemptScore($answer->examAttempt);

        return back()->with('success', 'Nilai esai tersimpan.');
    }

    /**
     * Recalculate objective, essay, and weighted final score for an attempt
     */
    public function recalculateAttemptScore(ExamAttempt $attempt): void
    {
        $schedule = $attempt->examSession->examSchedule;
        $bank = $schedule->questionBank;
        $subject = $schedule->subject;

        $pgWeight = $bank->pg_weight_percent / 100;
        $essayWeight = $bank->essay_weight_percent / 100;

        // Sum objective scores
        $objQuestions = $bank->questions->where('type', '!=', 'essay');
        $objMaxTotal = $objQuestions->sum('max_score');
        $objAnswers = $attempt->answers->whereIn('question_id', $objQuestions->pluck('id'));
        $objEarned = $objAnswers->sum('score_obtained');

        $objPercentage = $objMaxTotal > 0 ? ($objEarned / $objMaxTotal) * 100 : 0;

        // Sum essay scores
        $essayQuestions = $bank->questions->where('type', 'essay');
        $essayMaxTotal = $essayQuestions->sum('max_score');
        $essayAnswers = $attempt->answers->whereIn('question_id', $essayQuestions->pluck('id'));
        $essayEarned = $essayAnswers->sum('score_obtained');

        $essayPercentage = $essayMaxTotal > 0 ? ($essayEarned / $essayMaxTotal) * 100 : 0;

        // Final score (0 - 100)
        if ($essayQuestions->isEmpty()) {
            $finalScore = $objPercentage;
        } elseif ($objQuestions->isEmpty()) {
            $finalScore = $essayPercentage;
        } else {
            $finalScore = ($objPercentage * $pgWeight) + ($essayPercentage * $essayWeight);
        }

        // Apply remedial policy if schedule is remedial
        if ($schedule->is_remedial && $schedule->remedial_max_score_policy === 'kkm') {
            if ($finalScore > $subject->kkm_score) {
                $finalScore = (float) $subject->kkm_score;
            }
        }

        $isPassed = $finalScore >= $subject->kkm_score;

        $attempt->update([
            'objective_score' => round($objPercentage, 2),
            'essay_score' => round($essayPercentage, 2),
            'final_score' => round($finalScore, 2),
            'is_passed_kkm' => $isPassed,
            'status' => 'graded',
        ]);
    }

    /**
     * Item Difficulty Analysis (Analisis Butir Soal)
     */
    public function itemAnalysis(ExamSchedule $schedule): Response
    {
        $schedule->load(['subject', 'questionBank.questions.options']);
        $questions = $schedule->questionBank->questions;

        $attempts = ExamAttempt::whereHas('examSession', fn ($q) => $q->where('exam_schedule_id', $schedule->id))
            ->whereIn('status', ['submitted', 'graded'])
            ->with('answers')
            ->get();

        $totalStudents = $attempts->count();

        $analysis = $questions->map(function ($q) use ($attempts, $totalStudents) {
            $answers = AttemptAnswer::whereIn('exam_attempt_id', $attempts->pluck('id'))
                ->where('question_id', $q->id)
                ->get();

            $correctCount = 0;
            $optionDistribution = [];

            foreach ($answers as $ans) {
                if ($ans->score_obtained >= $q->max_score) {
                    $correctCount++;
                }

                // Check option choices
                if (is_array($ans->selected_options_json)) {
                    foreach ($ans->selected_options_json as $chosen) {
                        $optionDistribution[$chosen] = ($optionDistribution[$chosen] ?? 0) + 1;
                    }
                }
            }

            $difficultyIndex = $totalStudents > 0 ? round($correctCount / $totalStudents, 2) : 0;

            // Kategori Tingkat Kesukaran
            // P > 0.70 : Mudah | 0.30 <= P <= 0.70 : Sedang | P < 0.30 : Sukar
            $category = match (true) {
                $difficultyIndex > 0.70 => 'Mudah',
                $difficultyIndex >= 0.30 => 'Sedang',
                default => 'Sukar',
            };

            return [
                'id' => $q->id,
                'order_index' => $q->order_index,
                'content' => Str::limit(strip_tags($q->content), 80),
                'type' => $q->type,
                'max_score' => $q->max_score,
                'correct_count' => $correctCount,
                'total_answered' => $answers->count(),
                'difficulty_index' => $difficultyIndex,
                'difficulty_category' => $category,
                'option_distribution' => $optionDistribution,
            ];
        });

        return Inertia::render('Teacher/ItemAnalysis', [
            'schedule' => $schedule,
            'totalStudents' => $totalStudents,
            'analysis' => $analysis,
        ]);
    }

    /**
     * Create 1-Click Remedial Exam Session for students below KKM
     */
    public function createRemedialSession(Request $request, ExamSchedule $schedule)
    {
        $schedule->load(['subject', 'questionBank']);

        // Find failed students
        $failedAttempts = ExamAttempt::whereHas('examSession', fn ($q) => $q->where('exam_schedule_id', $schedule->id))
            ->where('is_passed_kkm', false)
            ->with('student')
            ->get();

        if ($failedAttempts->isEmpty()) {
            return back()->with('warning', 'Seluruh siswa telah tuntas KKM. Tidak ada siswa yang memerlukan remedial.');
        }

        // Create a new remedial ExamSchedule
        $remedialSchedule = ExamSchedule::create([
            'title' => '[REMEDIAL] '.$schedule->title,
            'subject_id' => $schedule->subject_id,
            'question_bank_id' => $schedule->question_bank_id,
            'target_class_ids_json' => $schedule->target_class_ids_json,
            'duration_minutes' => $schedule->duration_minutes,
            'min_submit_minutes' => $schedule->min_submit_minutes,
            'shuffle_questions' => true,
            'shuffle_options' => true,
            'max_cheat_strikes' => $schedule->max_cheat_strikes,
            'show_score_to_student' => true,
            'is_remedial' => true,
            'remedial_max_score_policy' => 'kkm',
        ]);

        $token = strtoupper(Str::random(6));
        $now = Carbon::now();

        ExamSession::create([
            'exam_schedule_id' => $remedialSchedule->id,
            'session_name' => 'Sesi Remedial',
            'room_name' => 'Lab Komputer / Remedial Online',
            'access_token' => $token,
            'start_time' => $now,
            'end_time' => $now->copy()->addDays(2),
            'proctor_id' => auth()->id(),
            'status' => 'active',
            'proctor_notes' => 'Sesi remedial khusus untuk '.$failedAttempts->count().' siswa di bawah KKM ('.$schedule->subject->kkm_score.').',
        ]);

        return redirect()->route('teacher.grading.index')->with('success', "Sesi remedial berhasil dibuat dengan Token: {$token} untuk {$failedAttempts->count()} siswa.");
    }

    /**
     * Export Leger Nilai per Class to Excel (ready for e-Rapor)
     */
    public function exportLeger(Request $request, ExamSchedule $schedule)
    {
        $schedule->load(['subject', 'questionBank']);

        $classId = $request->query('class_id');
        $query = ExamAttempt::whereHas('examSession', fn ($q) => $q->where('exam_schedule_id', $schedule->id))
            ->whereIn('status', ['submitted', 'graded'])
            ->with(['student.classRoom']);

        if ($classId) {
            $query->whereHas('student', fn ($q) => $q->where('class_room_id', $classId));
        }

        $attempts = $query->get()->sortBy(fn ($a) => $a->student->name);

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Leger Nilai e-Rapor');

        // Header Metadata
        $sheet->setCellValue('A1', 'LEGER NILAI HASIL UJIAN CBT');
        $sheet->setCellValue('A2', 'Mata Pelajaran: '.$schedule->subject->name.' (KKM: '.$schedule->subject->kkm_score.')');
        $sheet->setCellValue('A3', 'Ujian: '.$schedule->title);
        $sheet->setCellValue('A4', 'Tanggal Cetak: '.Carbon::now()->format('d/m/Y H:i'));

        // Table Header
        $headers = ['No', 'NISN', 'Nama Siswa', 'Kelas', 'Skor PG ('.$schedule->questionBank->pg_weight_percent.'%)', 'Skor Esai ('.$schedule->questionBank->essay_weight_percent.'%)', 'Nilai Akhir (0-100)', 'Status KKM'];
        $sheet->fromArray([$headers], null, 'A6');

        $rowNum = 7;
        $no = 1;

        foreach ($attempts as $att) {
            $sheet->fromArray([
                $no++,
                $att->student->username,
                $att->student->name,
                $att->student->classRoom ? $att->student->classRoom->name : '-',
                $att->objective_score,
                $att->essay_score,
                $att->final_score,
                $att->is_passed_kkm ? 'TUNTAS' : 'BELUM TUNTAS',
            ], null, 'A'.$rowNum);
            $rowNum++;
        }

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Leger_Nilai_'.Str::slug($schedule->title).'.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
