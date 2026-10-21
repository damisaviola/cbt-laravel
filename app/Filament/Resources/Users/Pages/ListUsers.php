<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\ClassRoom;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua Pengguna'),
            'students' => Tab::make('Siswa')
                ->badge(User::where('role', 'student')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('role', 'student')),
            'teachers' => Tab::make('Guru')
                ->badge(User::where('role', 'teacher')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('role', 'teacher')),
            'proctors' => Tab::make('Pengawas')
                ->badge(User::where('role', 'proctor')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('role', 'proctor')),
            'admins' => Tab::make('Administrator')
                ->badge(User::where('role', 'admin')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('role', 'admin')),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Pengguna Baru'),

            Action::make('import_excel')
                ->label('Import Excel Siswa')
                ->icon('heroicon-m-arrow-up-tray')
                ->color('success')
                ->form([
                    FileUpload::make('file')
                        ->label('Pilih File Excel (.xlsx / .xls)')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                        ->disk('local')
                        ->directory('imports')
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $filePath = storage_path('app/'.$data['file']);
                    if (! file_exists($filePath)) {
                        $filePath = storage_path('app/private/'.$data['file']);
                    }
                    if (! file_exists($filePath)) {
                        $filePath = storage_path('app/public/'.$data['file']);
                    }

                    try {
                        $spreadsheet = IOFactory::load($filePath);
                        $sheet = $spreadsheet->getActiveSheet();
                        $rows = $sheet->toArray();

                        $imported = 0;
                        foreach ($rows as $index => $row) {
                            if ($index === 0) {
                                continue;
                            } // Header row
                            $nisn = trim((string) ($row[0] ?? ''));
                            $name = trim((string) ($row[1] ?? ''));
                            $className = trim((string) ($row[2] ?? ''));
                            $examRoom = trim((string) ($row[3] ?? 'Ruang-1'));
                            $password = trim((string) ($row[4] ?? 'siswa123'));

                            if (empty($nisn) || empty($name)) {
                                continue;
                            }

                            $classRoom = ClassRoom::firstOrCreate(
                                ['name' => $className ?: 'X-Umum'],
                                ['grade_level' => 10, 'major_code' => 'Umum']
                            );

                            User::updateOrCreate(
                                ['username' => $nisn],
                                [
                                    'name' => $name,
                                    'email' => $nisn.'@smartcbt.local',
                                    'password' => Hash::make($password),
                                    'role' => 'student',
                                    'class_room_id' => $classRoom->id,
                                    'exam_room_name' => $examRoom,
                                    'is_active' => true,
                                ]
                            );
                            $imported++;
                        }

                        Notification::make()
                            ->title("Berhasil mengimpor {$imported} siswa!")
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Gagal mengimpor file: '.$e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('template_excel')
                ->label('Template Siswa')
                ->icon('heroicon-m-arrow-down-tray')
                ->color('gray')
                ->url(route('admin.students.template'), shouldOpenInNewTab: true),

            Action::make('print_cards')
                ->label('Cetak Kartu Ujian')
                ->icon('heroicon-m-printer')
                ->color('info')
                ->url(route('admin.students.printCards'), shouldOpenInNewTab: true),

            Action::make('print_attendance')
                ->label('Cetak Daftar Hadir')
                ->icon('heroicon-m-document-text')
                ->color('gray')
                ->url(route('admin.students.printAttendance'), shouldOpenInNewTab: true),
        ];
    }
}
