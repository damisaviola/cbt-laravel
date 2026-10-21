<?php

namespace App\Filament\Resources\ClassRooms;

use App\Filament\Resources\ClassRooms\Pages\ManageClassRooms;
use App\Models\ClassRoom;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class ClassRoomResource extends Resource
{
    protected static ?string $model = ClassRoom::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static UnitEnum|string|null $navigationGroup = 'Data Master';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Kelas / Rombel';

    protected static ?string $pluralModelLabel = 'Data Kelas';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('grade_level')
                    ->label('Tingkat')
                    ->required()
                    ->numeric()
                    ->default(10),
                TextInput::make('major_code')
                    ->label('Kode Jurusan / Peminatan')
                    ->required()
                    ->default('Umum'),
                TextInput::make('name')
                    ->label('Nama Rombel')
                    ->placeholder('Contoh: X-IPA-1 / XII-RPL')
                    ->required(),
                Select::make('homeroom_teacher_id')
                    ->label('Wali Kelas')
                    ->relationship('homeroomTeacher', 'name'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Rombel')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('grade_level')
                    ->label('Tingkat')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('major_code')
                    ->label('Jurusan')
                    ->badge()
                    ->color('info')
                    ->searchable(),
                TextColumn::make('homeroomTeacher.name')
                    ->label('Wali Kelas')
                    ->default('-')
                    ->searchable(),
                TextColumn::make('students_count')
                    ->label('Jumlah Siswa')
                    ->counts('students')
                    ->badge()
                    ->color('success'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageClassRooms::route('/'),
        ];
    }
}
