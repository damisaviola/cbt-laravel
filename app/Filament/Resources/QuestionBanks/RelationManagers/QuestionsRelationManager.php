<?php

namespace App\Filament\Resources\QuestionBanks\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';

    protected static ?string $title = 'Butir Soal & Kunci Jawaban';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->label('Tipe Soal')
                    ->options([
                        'single' => 'Pilihan Ganda Biasa',
                        'multiple_choice_multiple_answer' => 'Pilihan Ganda Kompleks (MCMA)',
                        'boolean' => 'Benar / Salah (Grid Matrix)',
                        'matching' => 'Menjodohkan (Matching Pairs)',
                        'essay' => 'Uraian / Isian Singkat',
                    ])
                    ->required()
                    ->default('single'),

                TextInput::make('order_index')
                    ->label('Nomor Urut')
                    ->numeric()
                    ->default(1)
                    ->required(),

                TextInput::make('max_score')
                    ->label('Bobot Nilai')
                    ->numeric()
                    ->default(1)
                    ->required(),

                Textarea::make('reading_passage')
                    ->label('Stimulus Bacaan / Wacana (Split-Pane Kiri)')
                    ->placeholder('Isi jika soal ini menggunakan teks stimulus panjang/wacana/dialog')
                    ->rows(3)
                    ->columnSpanFull(),

                Textarea::make('content')
                    ->label('Teks Pertanyaan / Soal (Mendukung KaTeX: $formula$ dan Teks Arab)')
                    ->placeholder('Contoh: Tentukan himpunan penyelesaian dari $x^2 - 4x + 3 = 0$...')
                    ->rows(3)
                    ->required()
                    ->columnSpanFull(),

                Repeater::make('options')
                    ->relationship('options')
                    ->label('Pilihan Jawaban / Kunci Jawaban')
                    ->schema([
                        TextInput::make('label')
                            ->label('Label')
                            ->placeholder('A / B / C / D / E')
                            ->required(),
                        TextInput::make('content')
                            ->label('Teks Pilihan / Pasangan')
                            ->required(),
                        Toggle::make('is_correct')
                            ->label('Kunci Benar')
                            ->default(false),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('content')
            ->defaultSort('order_index')
            ->columns([
                TextColumn::make('order_index')
                    ->label('No.')
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('type')
                    ->label('Tipe Soal')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'single' => 'primary',
                        'multiple_choice_multiple_answer' => 'info',
                        'boolean' => 'warning',
                        'matching' => 'gray',
                        'essay' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'single' => 'PG Biasa',
                        'multiple_choice_multiple_answer' => 'PG Kompleks',
                        'boolean' => 'Benar / Salah',
                        'matching' => 'Menjodohkan',
                        'essay' => 'Uraian',
                        default => $state,
                    }),
                TextColumn::make('content')
                    ->label('Isi Pertanyaan')
                    ->limit(60)
                    ->searchable(),
                TextColumn::make('options_count')
                    ->label('Jml Opsi')
                    ->counts('options')
                    ->badge()
                    ->color('info'),
                TextColumn::make('max_score')
                    ->label('Bobot')
                    ->numeric()
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Butir Soal'),
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
}
