<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // SAS Ganjil Matematika Kelas X
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('question_bank_id')->constrained('question_banks')->cascadeOnDelete();
            $table->json('target_class_ids_json')->nullable(); // [1, 2, 3]
            $table->unsignedSmallInteger('duration_minutes')->default(90);
            $table->unsignedSmallInteger('min_submit_minutes')->default(15);
            $table->boolean('shuffle_questions')->default(true);
            $table->boolean('shuffle_options')->default(true);
            $table->unsignedTinyInteger('max_cheat_strikes')->default(3);
            $table->boolean('show_score_to_student')->default(false);
            $table->boolean('is_remedial')->default(false);
            $table->string('remedial_max_score_policy', 30)->default('kkm'); // kkm, highest, actual
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_schedules');
    }
};
