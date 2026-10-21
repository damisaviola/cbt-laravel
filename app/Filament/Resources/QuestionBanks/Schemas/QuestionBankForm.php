<?php

namespace App\Filament\Resources\QuestionBanks\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class QuestionBankForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Judul Soal Ujian')
                    ->placeholder('Contoh: Asesmen Sumatif Akhir Semester Matematika X')
                    ->required(),
                Select::make('subject_id')
                    ->label('Mata Pelajaran')
                    ->relationship('subject', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('teacher_id')
                    ->label('Guru Pengampu')
                    ->relationship('teacher', 'name', fn ($query) => $query->whereIn('role', ['teacher', 'admin']))
                    ->required()
                    ->searchable()
                    ->preload(),
                Textarea::make('description')
                    ->label('Keterangan / Kisi-kisi')
                    ->rows(2),
                TextInput::make('kkm_score')
                    ->label('Nilai KKM')
                    ->numeric()
                    ->default(75)
                    ->required(),
                TextInput::make('pg_weight_percent')
                    ->label('Bobot Soal Obyektif / PG (%)')
                    ->numeric()
                    ->default(70)
                    ->required(),
                TextInput::make('essay_weight_percent')
                    ->label('Bobot Soal Uraian / Isian (%)')
                    ->numeric()
                    ->default(30)
                    ->required(),
                Toggle::make('randomize_questions')
                    ->label('Acak Urutan Soal Siswa')
                    ->default(true),
                Toggle::make('randomize_options')
                    ->label('Acak Urutan Pilihan Opsi')
                    ->default(true),
                Toggle::make('is_active')
                    ->label('Soal Ujian Aktif')
                    ->default(true),
            ]);
    }
}
