<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_settings', function (Blueprint $table) {
            $table->id();
            $table->string('school_name')->default('SMA Negeri 1 Digital');
            $table->string('npsn', 20)->default('10203040');
            $table->string('logo_path')->nullable();
            $table->string('headmaster_name')->default('Dr. H. Ahmad Fauzi, M.Pd.');
            $table->string('headmaster_nip')->default('197505122000031002');
            $table->string('active_academic_year')->default('2026/2027');
            $table->string('active_semester')->default('Ganjil'); // Ganjil / Genap
            $table->string('primary_color')->default('#0F52BA');
            $table->boolean('seb_required')->default(false);
            $table->string('seb_key_hash')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_settings');
    }
};
