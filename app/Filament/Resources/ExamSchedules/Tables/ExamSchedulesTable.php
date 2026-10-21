<?php

namespace App\Filament\Resources\ExamSchedules;

namespace App\Filament\Resources\ExamSchedules\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExamSchedulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Nama Jadwal')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('questionBank.title')
                    ->label('Naskah Soal Ujian')
                    ->badge()
                    ->color('info')
                    ->searchable(),
                TextColumn::make('duration_minutes')
                    ->label('Durasi')
                    ->suffix(' mnt')
                    ->badge()
                    ->color('warning')
                    ->sortable(),
                TextColumn::make('sessions_count')
                    ->label('Sesi Ruang')
                    ->counts('sessions')
                    ->badge()
                    ->color('success'),
                TextColumn::make('start_time')
                    ->label('Waktu Mulai')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('end_time')
                    ->label('Waktu Selesai')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
