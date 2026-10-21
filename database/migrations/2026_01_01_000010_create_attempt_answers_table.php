<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attempt_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_attempt_id')->constrained('exam_attempts')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->json('selected_options_json')->nullable(); // misal ['A'] atau ['A', 'C'] atau pasangan menjodohkan {'1': 'A', '2': 'B'}
            $table->longText('essay_text')->nullable();
            $table->boolean('is_doubt')->default(false); // Penanda ragu-ragu
            $table->decimal('score_obtained', 8, 2)->default(0.00);
            $table->boolean('is_graded_by_teacher')->default(false);
            $table->text('teacher_feedback')->nullable();
            $table->timestamps();

            $table->unique(['exam_attempt_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attempt_answers');
    }
};
