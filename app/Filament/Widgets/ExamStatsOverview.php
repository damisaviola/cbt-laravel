<?php

namespace App\Filament\Widgets;

use App\Models\ExamAttempt;
use App\Models\ExamSchedule;
use App\Models\QuestionBank;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ExamStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalStudents = User::where('role', 'student')->where('is_active', true)->count();
        $totalBanks = QuestionBank::count();
        $todaySchedules = ExamSchedule::where('is_active', true)->count();
        $inProgressAttempts = ExamAttempt::where('status', 'in_progress')->count();

        return [
            Stat::make('Total Siswa Aktif', number_format($totalStudents))
                ->description('Terdaftar dalam rombel')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('success'),
            Stat::make('Soal Ujian Siap', number_format($totalBanks))
                ->description('Mata pelajaran aktif')
                ->descriptionIcon('heroicon-m-folder')
                ->color('info'),
            Stat::make('Jadwal Ujian Aktif', number_format($todaySchedules))
                ->description('Siap / sedang diujikan')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('warning'),
            Stat::make('Siswa Sedang Ujian', number_format($inProgressAttempts))
                ->description('Live di ruang ujian')
                ->descriptionIcon('heroicon-m-computer-desktop')
                ->color('primary'),
        ];
    }
}
