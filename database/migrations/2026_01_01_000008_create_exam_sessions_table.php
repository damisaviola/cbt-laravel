<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_schedule_id')->constrained('exam_schedules')->cascadeOnDelete();
            $table->string('session_name', 50)->default('Sesi 1');
            $table->string('room_name', 100)->default('Lab Komputer 1');
            $table->string('access_token', 10)->index(); // Token Ruang e.g. "SMART1"
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->foreignId('proctor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('proctor_notes')->nullable();
            $table->string('status', 20)->default('active'); // upcoming, active, finished
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_sessions');
    }
};
