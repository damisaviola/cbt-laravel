<?php

namespace App\Filament\Resources\ExamSchedules\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ExamScheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Nama Jadwal Pelaksanaan Ujian')
                    ->placeholder('Contoh: Asesmen Sumatif Matematika - Sesi Pagi')
                    ->required(),
                Select::make('question_bank_id')
                    ->label('Naskah Soal Ujian')
                    ->relationship('questionBank', 'title')
                    ->required()
                    ->searchable()
                    ->preload(),
                DateTimePicker::make('start_time')
                    ->label('Waktu Mulai Akses')
                    ->default(now())
                    ->required(),
                DateTimePicker::make('end_time')
                    ->label('Waktu Selesai Akses')
                    ->default(now()->addDays(2))
                    ->required(),
                TextInput::make('duration_minutes')
                    ->label('Durasi Pengerjaan (Menit)')
                    ->numeric()
                    ->default(90)
                    ->required(),
                TextInput::make('min_submit_minutes')
                    ->label('Kunci Waktu Minimal Submit (Menit)')
                    ->helperText('Siswa tidak dapat mengumpulkan ujian sebelum durasi menit ini terpenuhi')
                    ->numeric()
                    ->default(30)
                    ->required(),
                Toggle::make('is_active')
                    ->label('Jadwal Ujian Aktif')
                    ->default(true),
            ]);
    }
}
