<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttemptAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_attempt_id',
        'question_id',
        'selected_options_json',
        'essay_text',
        'is_doubt',
        'score_obtained',
        'is_graded_by_teacher',
        'teacher_feedback',
    ];

    protected function casts(): array
    {
        return [
            'selected_options_json' => 'array',
            'is_doubt' => 'boolean',
            'is_graded_by_teacher' => 'boolean',
            'score_obtained' => 'decimal:2',
        ];
    }

    public function examAttempt(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
