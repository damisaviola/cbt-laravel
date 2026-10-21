<?php

namespace App\Filament\Pages;

use App\Models\SchoolSetting;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class SchoolSettings extends Page
{
    protected string $view = 'filament.pages.school-settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static UnitEnum|string|null $navigationGroup = 'Pengaturan';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Identitas & Pengaturan Sekolah';

    public string $school_name = '';

    public string $npsn = '';

    public string $headmaster_name = '';

    public string $headmaster_nip = '';

    public string $active_academic_year = '';

    public string $active_semester = '';

    public bool $seb_required = false;

    public string $seb_key_hash = '';

    public function mount(): void
    {
        $setting = SchoolSetting::current();
        $this->school_name = $setting->school_name ?? '';
        $this->npsn = $setting->npsn ?? '';
        $this->headmaster_name = $setting->headmaster_name ?? '';
        $this->headmaster_nip = $setting->headmaster_nip ?? '';
        $this->active_academic_year = $setting->active_academic_year ?? '2026/2027';
        $this->active_semester = $setting->active_semester ?? 'Ganjil';
        $this->seb_required = (bool) $setting->seb_required;
        $this->seb_key_hash = $setting->seb_key_hash ?? '';
    }

    public function save(): void
    {
        $setting = SchoolSetting::current();
        $setting->update([
            'school_name' => $this->school_name,
            'npsn' => $this->npsn,
            'headmaster_name' => $this->headmaster_name,
            'headmaster_nip' => $this->headmaster_nip,
            'active_academic_year' => $this->active_academic_year,
            'active_semester' => $this->active_semester,
            'seb_required' => $this->seb_required,
            'seb_key_hash' => $this->seb_key_hash,
        ]);

        Notification::make()
            ->title('Pengaturan Berhasil Disimpan!')
            ->body('Identitas dan konfigurasi ujian sekolah telah diperbarui.')
            ->success()
            ->send();
    }
}
