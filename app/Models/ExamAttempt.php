<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_session_id',
        'student_id',
        'started_at',
        'finished_at',
        'cheat_strikes',
        'is_locked',
        'objective_score',
        'essay_score',
        'final_score',
        'is_passed_kkm',
        'status',
        'question_order_json',
        'device_info',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'is_locked' => 'boolean',
            'is_passed_kkm' => 'boolean',
            'objective_score' => 'decimal:2',
            'essay_score' => 'decimal:2',
            'final_score' => 'decimal:2',
            'question_order_json' => 'array',
            'cheat_strikes' => 'integer',
        ];
    }

    public function examSession(): BelongsTo
    {
        return $this->belongsTo(ExamSession::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(AttemptAnswer::class);
    }
}
