<?php

namespace App\Filament\Resources\ExamSchedules\RelationManagers;

use App\Models\ExamSession;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class SessionsRelationManager extends RelationManager
{
    protected static string $relationship = 'sessions';

    protected static ?string $title = 'Sesi Ruang & Token Ujian';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('session_name')
                    ->label('Nama Sesi')
                    ->placeholder('Contoh: Sesi 1 / Sesi Pagi')
                    ->required()
                    ->default('Sesi 1'),

                TextInput::make('room_name')
                    ->label('Nama Ruangan')
                    ->placeholder('Contoh: Lab Komputer 1 / Ruang 05')
                    ->required()
                    ->default('Lab Komputer 1'),

                TextInput::make('access_token')
                    ->label('Token Akses Ujian')
                    ->default(fn () => strtoupper(Str::random(5)))
                    ->required()
                    ->maxLength(10),

                DateTimePicker::make('start_time')
                    ->label('Waktu Mulai')
                    ->default(now())
                    ->required(),

                DateTimePicker::make('end_time')
                    ->label('Waktu Selesai')
                    ->default(now()->addHours(2))
                    ->required(),

                Select::make('proctor_id')
                    ->label('Guru Pengawas Ruang')
                    ->relationship('proctor', 'name', fn ($query) => $query->whereIn('role', ['proctor', 'teacher', 'admin']))
                    ->searchable()
                    ->preload(),

                Select::make('status')
                    ->label('Status Sesi')
                    ->options([
                        'upcoming' => 'Belum Dimulai',
                        'active' => 'Sedang Berlangsung',
                        'finished' => 'Selesai',
                    ])
                    ->default('active')
                    ->required(),

                Textarea::make('proctor_notes')
                    ->label('Catatan Pengawas Ruang')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('room_name')
            ->columns([
                TextColumn::make('session_name')
                    ->label('Sesi')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('room_name')
                    ->label('Ruangan')
                    ->weight('bold')
                    ->searchable(),

                TextColumn::make('access_token')
                    ->label('Token Ujian')
                    ->badge()
                    ->color('success')
                    ->copyable()
                    ->copyMessage('Token disalin!')
                    ->searchable(),

                TextColumn::make('proctor.name')
                    ->label('Pengawas')
                    ->default('-')
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'upcoming' => 'warning',
                        'finished' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Aktif',
                        'upcoming' => 'Mendatang',
                        'finished' => 'Selesai',
                        default => $state,
                    }),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Sesi Ruang'),
            ])
            ->recordActions([
                Action::make('regenerate_token')
                    ->label('Reset Token')
                    ->icon('heroicon-m-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (ExamSession $record): void {
                        $newToken = strtoupper(Str::random(5));
                        $record->update(['access_token' => $newToken]);
                        Notification::make()
                            ->title('Token diperbarui')
                            ->body("Token baru untuk {$record->room_name} adalah: {$newToken}")
                            ->success()
                            ->send();
                    }),

                Action::make('control_room')
                    ->label('Control Room')
                    ->icon('heroicon-m-tv')
                    ->color('primary')
                    ->url(fn (ExamSession $record): string => url('/admin/control-room?session_id='.$record->id)),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
