<?php

namespace App\Filament\Pages;

use App\Models\ExamAttempt;
use App\Models\ExamSession;
use App\Models\User;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use UnitEnum;

class ControlRoom extends Page
{
    protected string $view = 'filament.pages.control-room';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTv;

    protected static UnitEnum|string|null $navigationGroup = 'Pengawasan & Koreksi';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Control Room Pengawas Ujian';

    public ?int $sessionId = null;

    public ?string $proctorNotes = '';

    public function mount(): void
    {
        $this->sessionId = request()->query('session_id')
            ? (int) request()->query('session_id')
            : ExamSession::latest()->value('id');

        if ($this->sessionId) {
            $session = ExamSession::find($this->sessionId);
            $this->proctorNotes = $session?->proctor_notes ?? '';
        }
    }

    public function getSessionsProperty()
    {
        return ExamSession::with(['examSchedule', 'proctor'])->latest()->get();
    }

    public function getSelectedSessionProperty()
    {
        return $this->sessionId ? ExamSession::with(['examSchedule.questionBank', 'proctor'])->find($this->sessionId) : null;
    }

    public function getAttemptsProperty()
    {
        if (! $this->sessionId) {
            return collect();
        }

        return ExamAttempt::with(['student.classRoom', 'answers'])
            ->where('exam_session_id', $this->sessionId)
            ->latest()
            ->get();
    }

    public function regenerateToken(): void
    {
        if (! $this->selectedSession) {
            return;
        }

        $newToken = strtoupper(Str::random(5));
        $this->selectedSession->update(['access_token' => $newToken]);

        Notification::make()
            ->title('Token Berhasil Diperbarui!')
            ->body("Token baru untuk {$this->selectedSession->room_name} adalah: {$newToken}")
            ->success()
            ->send();
    }

    public function unlockStudent(int $attemptId): void
    {
        $attempt = ExamAttempt::with('student')->find($attemptId);
        if ($attempt) {
            $attempt->update([
                'is_locked' => false,
                'cheat_strikes' => 0,
            ]);

            Notification::make()
                ->title('Akses Ujian Dibuka!')
                ->body("Siswa {$attempt->student->name} telah di-unlock.")
                ->success()
                ->send();
        }
    }

    public function unlockAllStudents(): void
    {
        if (! $this->sessionId) {
            return;
        }

        $count = ExamAttempt::where('exam_session_id', $this->sessionId)
            ->where('is_locked', true)
            ->update([
                'is_locked' => false,
                'cheat_strikes' => 0,
            ]);

        Notification::make()
            ->title('Semua Siswa Terkunci Berhasil Dibuka!')
            ->body("{$count} siswa yang terkunci telah dibuka akses ujiannya.")
            ->success()
            ->send();
    }

    public function resetStudentLogin(int $userId): void
    {
        $user = User::find($userId);
        if ($user) {
            $user->update(['active_session_id' => null]);

            Notification::make()
                ->title('Sesi Login Direset!')
                ->body("Siswa {$user->name} dapat login kembali.")
                ->success()
                ->send();
        }
    }

    public function saveProctorNotes(): void
    {
        if (! $this->selectedSession) {
            return;
        }

        $this->selectedSession->update(['proctor_notes' => $this->proctorNotes]);

        Notification::make()
            ->title('Catatan Berita Acara Disimpan')
            ->success()
            ->send();
    }
}
