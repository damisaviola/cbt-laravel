<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_name',
        'npsn',
        'logo_path',
        'headmaster_name',
        'headmaster_nip',
        'active_academic_year',
        'active_semester',
        'primary_color',
        'seb_required',
        'seb_key_hash',
    ];

    protected function casts(): array
    {
        return [
            'seb_required' => 'boolean',
        ];
    }

    public static function current(): self
    {
        return static::firstOrCreate([], [
            'school_name' => 'SMA Negeri 1 Digital',
            'npsn' => '10203040',
            'headmaster_name' => 'Dr. H. Ahmad Fauzi, M.Pd.',
            'headmaster_nip' => '197505122000031002',
            'active_academic_year' => '2026/2027',
            'active_semester' => 'Ganjil',
            'primary_color' => '#0F52BA',
            'seb_required' => false,
        ]);
    }
}
