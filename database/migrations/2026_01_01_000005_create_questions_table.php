<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_bank_id')->constrained('question_banks')->cascadeOnDelete();
            $table->string('type', 30)->default('single'); // single, mcma, true_false, matching, short_answer, essay
            $table->longText('reading_passage')->nullable(); // Wacana kiri (Split-Pane AKM)
            $table->longText('content'); // Pertanyaan soal (KaTeX, HTML, Arabic)
            $table->string('media_path')->nullable(); // File gambar atau audio
            $table->unsignedSmallInteger('audio_play_limit')->default(2);
            $table->decimal('max_score', 8, 2)->default(1.00);
            $table->unsignedInteger('order_index')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
