<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('username')
                    ->label('Username / NISN')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('name')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('role')
                    ->label('Peran')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'teacher' => 'info',
                        'proctor' => 'warning',
                        'student' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'admin' => 'Admin',
                        'teacher' => 'Guru',
                        'proctor' => 'Pengawas',
                        'student' => 'Siswa',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('classRoom.name')
                    ->label('Kelas')
                    ->badge()
                    ->color('gray')
                    ->default('-')
                    ->searchable(),
                TextColumn::make('exam_room_name')
                    ->label('Ruang Ujian')
                    ->default('-')
                    ->badge()
                    ->color('info')
                    ->searchable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('Filter Peran')
                    ->options([
                        'student' => 'Siswa',
                        'teacher' => 'Guru',
                        'proctor' => 'Pengawas',
                        'admin' => 'Admin',
                    ]),
                SelectFilter::make('class_room_id')
                    ->label('Filter Kelas')
                    ->relationship('classRoom', 'name'),
            ])
            ->recordActions([
                Action::make('reset_login')
                    ->label('Reset Login')
                    ->icon('heroicon-m-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Reset Sesi Login?')
                    ->modalDescription('Tindakan ini akan mengosongkan active session ID siswa/pengguna ini sehingga mereka dapat login kembali pada perangkat lain.')
                    ->action(function (User $record): void {
                        $record->update(['active_session_id' => null]);
                        Notification::make()
                            ->title('Sesi login berhasil di-reset')
                            ->body("Pengguna {$record->name} sekarang dapat login kembali.")
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
