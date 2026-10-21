<?php

namespace App\Filament\Pages;

use App\Models\AttemptAnswer;
use App\Models\ExamAttempt;
use App\Models\ExamSchedule;
use App\Models\ExamSession;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use UnitEnum;

class GradingWorkbench extends Page
{
    protected string $view = 'filament.pages.grading-workbench';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static UnitEnum|string|null $navigationGroup = 'Pengawasan & Koreksi';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Speed-Grading & Penilaian Uraian';

    public ?int $scheduleId = null;

    public ?int $selectedAttemptId = null;

    public function mount(): void
    {
        $this->scheduleId = request()->query('schedule_id')
            ? (int) request()->query('schedule_id')
            : ExamSchedule::latest()->value('id');

        $this->selectedAttemptId = $this->attempts->first()?->id;
    }

    public function getSchedulesProperty()
    {
        return ExamSchedule::with('questionBank')->latest()->get();
    }

    public function getSelectedScheduleProperty()
    {
        return $this->scheduleId ? ExamSchedule::with(['questionBank.questions.options', 'sessions'])->find($this->scheduleId) : null;
    }

    public function getAttemptsProperty()
    {
        if (! $this->scheduleId) {
            return collect();
        }

        return ExamAttempt::whereHas('examSession', fn ($q) => $q->where('exam_schedule_id', $this->scheduleId))
            ->with(['student.classRoom', 'answers.question'])
            ->latest()
            ->get();
    }

    public function getSelectedAttemptProperty()
    {
        return $this->selectedAttemptId
            ? ExamAttempt::with(['student.classRoom', 'answers.question.options'])->find($this->selectedAttemptId)
            : null;
    }

    public function selectAttempt(int $attemptId): void
    {
        $this->selectedAttemptId = $attemptId;
    }

    public function saveScore(int $answerId, float $score): void
    {
        $answer = AttemptAnswer::find($answerId);
        if ($answer) {
            $answer->update([
                'score_obtained' => $score,
                'is_graded_by_teacher' => true,
            ]);

            $attempt = $answer->examAttempt;
            $bank = $attempt->examSession->examSchedule->questionBank;

            // Recalculate essay totals
            $essayQuestions = $bank->questions()->where('type', 'essay')->pluck('id');
            $maxEssayTotal = $bank->questions()->where('type', 'essay')->sum('max_score') ?: 1;
            $earnedEssay = AttemptAnswer::where('exam_attempt_id', $attempt->id)
                ->whereIn('question_id', $essayQuestions)
                ->sum('score_obtained');

            $scaledEssay = ($earnedEssay / $maxEssayTotal) * 100;
            $attempt->essay_score = round($scaledEssay, 2);

            $pgWeight = $bank->pg_weight_percent / 100;
            $essayWeight = $bank->essay_weight_percent / 100;

            $attempt->final_score = round(($attempt->objective_score * $pgWeight) + ($attempt->essay_score * $essayWeight), 2);
            $attempt->is_passed_kkm = $attempt->final_score >= $bank->kkm_score;
            $attempt->status = 'graded';
            $attempt->save();

            Notification::make()
                ->title('Nilai Disimpan!')
                ->body("Nilai akhir {$attempt->student->name} adalah {$attempt->final_score}")
                ->success()
                ->send();
        }
    }

    public function generateRemedial(): void
    {
        if (! $this->selectedSchedule) {
            return;
        }

        $belowKkmAttempts = $this->attempts->filter(fn ($a) => $a->final_score !== null && ! $a->is_passed_kkm);

        if ($belowKkmAttempts->isEmpty()) {
            Notification::make()
                ->title('Tidak Ada Siswa Remedial')
                ->body('Semua siswa yang telah dinilai telah melampaui batas KKM.')
                ->info()
                ->send();

            return;
        }

        $session = ExamSession::create([
            'exam_schedule_id' => $this->selectedSchedule->id,
            'session_name' => 'Sesi Remedial '.now()->format('d/m'),
            'room_name' => 'Ruang Remedial',
            'access_token' => strtoupper(Str::random(5)),
            'start_time' => now(),
            'end_time' => now()->addDays(2),
            'status' => 'active',
        ]);

        Notification::make()
            ->title('Sesi Remedial Dibuat!')
            ->body("Token baru: {$session->access_token} untuk siswa di bawah KKM.")
            ->success()
            ->send();
    }
}
