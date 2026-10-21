<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_session_id')->constrained('exam_sessions')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('started_at');
            $table->dateTime('finished_at')->nullable();
            $table->unsignedTinyInteger('cheat_strikes')->default(0);
            $table->boolean('is_locked')->default(false);
            $table->decimal('objective_score', 8, 2)->default(0.00);
            $table->decimal('essay_score', 8, 2)->default(0.00);
            $table->decimal('final_score', 8, 2)->default(0.00);
            $table->boolean('is_passed_kkm')->default(false);
            $table->string('status', 20)->default('ongoing')->index(); // ongoing, submitted, graded
            $table->json('question_order_json')->nullable();
            $table->text('device_info')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_attempts');
    }
};
