<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_rooms', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('grade_level'); // 7 - 12
            $table->string('major_code', 50)->default('Umum'); // IPA, IPS, TKJ, Akuntansi, Umum
            $table->string('name', 50); // X-IPA-1, XII-TKJ-1
            $table->foreignId('homeroom_teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_rooms');
    }
};
