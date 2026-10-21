<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->required(),
                TextInput::make('username')
                    ->label('Username / NISN')
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('email')
                    ->label('Alamat Email')
                    ->email(),
                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create'),
                Select::make('role')
                    ->label('Peran (Role)')
                    ->options([
                        'student' => 'Siswa',
                        'teacher' => 'Guru',
                        'proctor' => 'Pengawas',
                        'admin' => 'Administrator',
                    ])
                    ->required()
                    ->default('student')
                    ->reactive(),
                Select::make('class_room_id')
                    ->label('Rombel / Kelas')
                    ->relationship('classRoom', 'name')
                    ->searchable()
                    ->preload(),
                TextInput::make('exam_room_name')
                    ->label('Ruang Ujian')
                    ->placeholder('Contoh: Ruang-1, Lab-Komputer'),
                Toggle::make('is_active')
                    ->label('Akun Aktif')
                    ->default(true)
                    ->required(),
            ]);
    }
}
