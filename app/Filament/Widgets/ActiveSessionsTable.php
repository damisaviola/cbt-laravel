<?php

namespace App\Filament\Widgets;

use App\Models\ExamSession;
use Filament\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class ActiveSessionsTable extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Sesi Ujian & Token Berlangsung';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => ExamSession::query()->with(['examSchedule', 'proctor'])->latest())
            ->columns([
                TextColumn::make('examSchedule.title')
                    ->label('Nama Ujian')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('session_name')
                    ->label('Sesi')
                    ->badge()
                    ->color('info'),
                TextColumn::make('room_name')
                    ->label('Ruang Ujian')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('access_token')
                    ->label('Token')
                    ->badge()
                    ->color('success')
                    ->copyable()
                    ->copyMessage('Token disalin ke clipboard!'),
                TextColumn::make('proctor.name')
                    ->label('Pengawas')
                    ->default('-'),
                IconColumn::make('is_active')
                    ->label('Status Aktif')
                    ->boolean(),
            ])
            ->recordActions([
                Action::make('control_room')
                    ->label('Buka Ruang Pengawas')
                    ->icon('heroicon-m-tv')
                    ->url(fn (ExamSession $record): string => url('/admin/control-room?session_id='.$record->id)),
            ]);
    }
}
