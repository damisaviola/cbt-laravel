<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subject_id',
        'question_bank_id',
        'target_class_ids_json',
        'duration_minutes',
        'min_submit_minutes',
        'shuffle_questions',
        'shuffle_options',
        'max_cheat_strikes',
        'show_score_to_student',
        'is_remedial',
        'remedial_max_score_policy',
    ];

    protected function casts(): array
    {
        return [
            'target_class_ids_json' => 'array',
            'shuffle_questions' => 'boolean',
            'shuffle_options' => 'boolean',
            'show_score_to_student' => 'boolean',
            'is_remedial' => 'boolean',
            'duration_minutes' => 'integer',
            'min_submit_minutes' => 'integer',
            'max_cheat_strikes' => 'integer',
        ];
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function questionBank(): BelongsTo
    {
        return $this->belongsTo(QuestionBank::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(ExamSession::class);
    }
}
