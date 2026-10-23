<?php

namespace App\Http\Controllers;

use App\Models\ExamAttempt;
use App\Models\Question;
use App\Models\Subject;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DashboardAnalytics
{
    /**
     * Color palettes matching EduPro aesthetic in contoh.jpg
     *
     * @var array<int, array{color: string, iconBg: string, iconColor: string, defaultIcon: string}>
     */
    private const PALETTES = [
        [
            'color' => 'bg-blue-600',
            'iconBg' => 'bg-blue-50 dark:bg-blue-950/60',
            'iconColor' => 'text-blue-600 dark:text-blue-400',
            'defaultIcon' => 'pen',
        ],
        [
            'color' => 'bg-amber-400',
            'iconBg' => 'bg-amber-50 dark:bg-amber-950/60',
            'iconColor' => 'text-amber-600 dark:text-amber-400',
            'defaultIcon' => 'code',
        ],
        [
            'color' => 'bg-emerald-500',
            'iconBg' => 'bg-emerald-50 dark:bg-emerald-950/60',
            'iconColor' => 'text-emerald-600 dark:text-emerald-400',
            'defaultIcon' => 'briefcase',
        ],
        [
            'color' => 'bg-rose-500',
            'iconBg' => 'bg-rose-50 dark:bg-rose-950/60',
            'iconColor' => 'text-rose-500 dark:text-rose-400',
            'defaultIcon' => 'megaphone',
        ],
        [
            'color' => 'bg-indigo-500',
            'iconBg' => 'bg-indigo-50 dark:bg-indigo-950/60',
            'iconColor' => 'text-indigo-600 dark:text-indigo-400',
            'defaultIcon' => 'book',
        ],
    ];

    /**
     * Get chart data sets for Admin (total exam participation / attempts).
     *
     * @return array<string, array<int, array{label: string, value: int}>>
     */
    public function getAdminTrendData(): array
    {
        $since = Carbon::now()->subMonths(6)->startOfMonth()->min(Carbon::now()->startOfYear());

        $attempts = ExamAttempt::query()
            ->where('created_at', '>=', $since)
            ->get(['created_at', 'final_score', 'status']);

        return $this->formatTrendBuckets($attempts);
    }

    /**
     * Get top categories for Admin based on subject KKM pass rate or question distribution.
     *
     * @return array<int, array{name: string, percent: int, color: string, iconBg: string, iconColor: string, iconType: string}>
     */
    public function getAdminCategories(): array
    {
        $subjects = Subject::all();
        if ($subjects->isEmpty()) {
            return [];
        }

        $subjectStats = ExamAttempt::query()
            ->join('exam_sessions', 'exam_attempts.exam_session_id', '=', 'exam_sessions.id')
            ->join('exam_schedules', 'exam_sessions.exam_schedule_id', '=', 'exam_schedules.id')
            ->selectRaw('exam_schedules.subject_id, count(*) as total_attempts, sum(case when exam_attempts.is_passed_kkm = 1 then 1 else 0 end) as passed_attempts')
            ->groupBy('exam_schedules.subject_id')
            ->get()
            ->keyBy('subject_id');

        $totalAttempts = $subjectStats->sum('total_attempts');

        $questionCounts = null;
        if ($totalAttempts === 0) {
            $questionCounts = Question::query()
                ->join('question_banks', 'questions.question_bank_id', '=', 'question_banks.id')
                ->selectRaw('question_banks.subject_id, count(*) as total_questions')
                ->groupBy('question_banks.subject_id')
                ->pluck('total_questions', 'subject_id');
        }

        $totalQuestions = $questionCounts?->sum() ?? 0;

        $items = $subjects->map(function (Subject $subject) use ($subjectStats, $totalAttempts, $questionCounts, $totalQuestions) {
            $percent = 0;
            if ($totalAttempts > 0) {
                $stat = $subjectStats->get($subject->id);
                if ($stat && $stat->total_attempts > 0) {
                    $percent = (int) round(($stat->passed_attempts / $stat->total_attempts) * 100);
                }
            } elseif ($totalQuestions > 0) {
                $qCount = $questionCounts->get($subject->id, 0);
                $percent = (int) round(($qCount / $totalQuestions) * 100);
            }

            return [
                'subject' => $subject,
                'percent' => min(max($percent, 0), 100),
            ];
        });

        $sorted = $items->sortByDesc('percent')->values()->take(4);

        return $this->formatCategoryItems($sorted);
    }

    /**
     * Get chart data sets for Teacher.
     *
     * @return array<string, array<int, array{label: string, value: int}>>
     */
    public function getTeacherTrendData(User $teacher): array
    {
        $since = Carbon::now()->subMonths(6)->startOfMonth()->min(Carbon::now()->startOfYear());

        $query = ExamAttempt::query()
            ->where('exam_attempts.created_at', '>=', $since);

        if ($teacher->role === 'teacher') {
            $query->join('exam_sessions', 'exam_attempts.exam_session_id', '=', 'exam_sessions.id')
                ->join('exam_schedules', 'exam_sessions.exam_schedule_id', '=', 'exam_schedules.id')
                ->join('question_banks', 'exam_schedules.question_bank_id', '=', 'question_banks.id')
                ->where('question_banks.teacher_id', $teacher->id);
        }

        $attempts = $query->get(['exam_attempts.created_at', 'exam_attempts.final_score', 'exam_attempts.status']);

        return $this->formatTrendBuckets($attempts);
    }

    /**
     * Get categories for Teacher.
     *
     * @return array<int, array{name: string, percent: int, color: string, iconBg: string, iconColor: string, iconType: string}>
     */
    public function getTeacherCategories(User $teacher): array
    {
        $subjectsQuery = Subject::query();
        if ($teacher->role === 'teacher') {
            $subjectsQuery->whereHas('questionBanks', fn ($q) => $q->where('teacher_id', $teacher->id));
        }

        $subjects = $subjectsQuery->get();
        if ($subjects->isEmpty()) {
            $subjects = Subject::take(4)->get();
        }

        if ($subjects->isEmpty()) {
            return [];
        }

        $subjectStatsQuery = ExamAttempt::query()
            ->join('exam_sessions', 'exam_attempts.exam_session_id', '=', 'exam_sessions.id')
            ->join('exam_schedules', 'exam_sessions.exam_schedule_id', '=', 'exam_schedules.id')
            ->selectRaw('exam_schedules.subject_id, count(*) as total_attempts, sum(case when exam_attempts.is_passed_kkm = 1 then 1 else 0 end) as passed_attempts')
            ->groupBy('exam_schedules.subject_id');

        if ($teacher->role === 'teacher') {
            $subjectStatsQuery->join('question_banks', 'exam_schedules.question_bank_id', '=', 'question_banks.id')
                ->where('question_banks.teacher_id', $teacher->id);
        }

        $subjectStats = $subjectStatsQuery->get()->keyBy('subject_id');
        $totalAttempts = $subjectStats->sum('total_attempts');

        $questionCounts = null;
        if ($totalAttempts === 0) {
            $questionCounts = Question::query()
                ->join('question_banks', 'questions.question_bank_id', '=', 'question_banks.id')
                ->when($teacher->role === 'teacher', fn ($q) => $q->where('question_banks.teacher_id', $teacher->id))
                ->selectRaw('question_banks.subject_id, count(*) as total_questions')
                ->groupBy('question_banks.subject_id')
                ->pluck('total_questions', 'subject_id');
        }

        $totalQuestions = $questionCounts?->sum() ?? 0;

        $items = $subjects->map(function (Subject $subject) use ($subjectStats, $totalAttempts, $questionCounts, $totalQuestions) {
            $percent = 0;
            if ($totalAttempts > 0) {
                $stat = $subjectStats->get($subject->id);
                if ($stat && $stat->total_attempts > 0) {
                    $percent = (int) round(($stat->passed_attempts / $stat->total_attempts) * 100);
                }
            } elseif ($totalQuestions > 0) {
                $qCount = $questionCounts->get($subject->id, 0);
                $percent = (int) round(($qCount / $totalQuestions) * 100);
            }

            return [
                'subject' => $subject,
                'percent' => min(max($percent, 0), 100),
            ];
        });

        $sorted = $items->sortByDesc('percent')->values()->take(4);

        return $this->formatCategoryItems($sorted);
    }

    /**
     * Get chart data sets for Student (score trends or participation).
     *
     * @return array<string, array<int, array{label: string, value: int}>>
     */
    public function getStudentTrendData(User $student): array
    {
        $since = Carbon::now()->subMonths(6)->startOfMonth()->min(Carbon::now()->startOfYear());

        $attempts = ExamAttempt::query()
            ->where('student_id', $student->id)
            ->whereIn('status', ['submitted', 'graded'])
            ->where('created_at', '>=', $since)
            ->get(['created_at', 'final_score', 'status']);

        return $this->formatTrendBuckets($attempts, scoreMode: true);
    }

    /**
     * Get categories for Student (subject score averages).
     *
     * @return array<int, array{name: string, percent: int, color: string, iconBg: string, iconColor: string, iconType: string}>
     */
    public function getStudentCategories(User $student): array
    {
        $subjects = Subject::all();
        if ($subjects->isEmpty()) {
            return [];
        }

        $subjectScores = ExamAttempt::query()
            ->where('student_id', $student->id)
            ->whereIn('exam_attempts.status', ['submitted', 'graded'])
            ->join('exam_sessions', 'exam_attempts.exam_session_id', '=', 'exam_sessions.id')
            ->join('exam_schedules', 'exam_sessions.exam_schedule_id', '=', 'exam_schedules.id')
            ->selectRaw('exam_schedules.subject_id, avg(exam_attempts.final_score) as avg_score')
            ->groupBy('exam_schedules.subject_id')
            ->pluck('avg_score', 'subject_id');

        $items = $subjects->map(function (Subject $subject) use ($subjectScores) {
            $avg = $subjectScores->get($subject->id);
            $percent = $avg !== null ? (int) round((float) $avg) : 0;

            return [
                'subject' => $subject,
                'percent' => min(max($percent, 0), 100),
            ];
        });

        $sorted = $items->sortByDesc('percent')->values()->take(4);

        return $this->formatCategoryItems($sorted);
    }

    /**
     * Format attempts collection into This Year, Last 6 Months, and This Month buckets.
     *
     * @param  Collection<int, ExamAttempt>  $attempts
     * @return array<string, array<int, array{label: string, value: int}>>
     */
    private function formatTrendBuckets(Collection $attempts, bool $scoreMode = false): array
    {
        $now = Carbon::now();
        $currentYear = $now->year;

        // 1. This Year (12 months: Jan to Dec)
        $thisYear = [];
        for ($m = 1; $m <= 12; $m++) {
            $date = Carbon::create($currentYear, $m, 1);
            $bucket = $attempts->filter(function ($att) use ($currentYear, $m) {
                return $att->created_at && $att->created_at->year === $currentYear && $att->created_at->month === $m;
            });

            $value = $scoreMode
                ? ($bucket->isNotEmpty() ? (int) round((float) $bucket->avg('final_score')) : 0)
                : $bucket->count();

            $thisYear[] = [
                'label' => $date->format('M'),
                'value' => $value,
            ];
        }

        // 2. Last 6 Months
        $last6Months = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $targetYear = $date->year;
            $targetMonth = $date->month;

            $bucket = $attempts->filter(function ($att) use ($targetYear, $targetMonth) {
                return $att->created_at && $att->created_at->year === $targetYear && $att->created_at->month === $targetMonth;
            });

            $value = $scoreMode
                ? ($bucket->isNotEmpty() ? (int) round((float) $bucket->avg('final_score')) : 0)
                : $bucket->count();

            $last6Months[] = [
                'label' => $date->format('M'),
                'value' => $value,
            ];
        }

        // 3. This Month (4 Weeks: W1, W2, W3, W4)
        $thisMonthAttempts = $attempts->filter(function ($att) use ($currentYear, $now) {
            return $att->created_at && $att->created_at->year === $currentYear && $att->created_at->month === $now->month;
        });

        $weeks = [
            'W1' => [1, 7],
            'W2' => [8, 14],
            'W3' => [15, 21],
            'W4' => [22, 31],
        ];

        $thisMonth = [];
        foreach ($weeks as $label => [$startDay, $endDay]) {
            $bucket = $thisMonthAttempts->filter(function ($att) use ($startDay, $endDay) {
                $day = $att->created_at?->day;

                return $day >= $startDay && $day <= $endDay;
            });

            $value = $scoreMode
                ? ($bucket->isNotEmpty() ? (int) round((float) $bucket->avg('final_score')) : 0)
                : $bucket->count();

            $thisMonth[] = [
                'label' => $label,
                'value' => $value,
            ];
        }

        return [
            'This Year' => $thisYear,
            'Last 6 Months' => $last6Months,
            'This Month' => $thisMonth,
        ];
    }

    /**
     * Map subject list to styled category widget format with palette and icons.
     *
     * @param  Collection<int, array{subject: Subject, percent: int}>  $items
     * @return array<int, array{name: string, percent: int, color: string, iconBg: string, iconColor: string, iconType: string}>
     */
    private function formatCategoryItems(Collection $items): array
    {
        return $items->values()->map(function ($item, int $index) {
            $palette = self::PALETTES[$index % count(self::PALETTES)];
            $subject = $item['subject'];
            $iconType = $this->resolveIconType($subject->name, $subject->code, $palette['defaultIcon']);

            return [
                'name' => $subject->name,
                'percent' => $item['percent'],
                'color' => $palette['color'],
                'iconBg' => $palette['iconBg'],
                'iconColor' => $palette['iconColor'],
                'iconType' => $iconType,
            ];
        })->all();
    }

    private function resolveIconType(string $name, ?string $code, string $default): string
    {
        $haystack = strtoupper($name.' '.($code ?? ''));

        if (preg_match('/(MAT|MATH|IPA|KIM|FIS|BIO|KOM|TIK|PROG|WEB)/', $haystack)) {
            return 'code';
        }
        if (preg_match('/(IND|ENG|BHS|LIT|SENI|ART|TULIS)/', $haystack)) {
            return 'pen';
        }
        if (preg_match('/(EKO|IPS|BIS|MNJ|ACC|KEU)/', $haystack)) {
            return 'briefcase';
        }
        if (preg_match('/(PKN|AGM|PAI|SEJ|PANCASILA|HUKUM)/', $haystack)) {
            return 'megaphone';
        }

        return $default;
    }
}
