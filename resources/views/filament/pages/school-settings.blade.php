<x-filament-panels::page>
    <form wire:submit.prevent="save" class="space-y-6 max-w-4xl">
        <!-- School Identity Card -->
        <div class="p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 space-y-4">
            <h3 class="text-base font-bold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-700 pb-3">
                Identitas Lembaga & Kop Cetak Ujian
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="school_name" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Nama Satuan Pendidikan</label>
                    <input id="school_name" type="text" wire:model="school_name" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500" required>
                </div>

                <div>
                    <label for="npsn" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Nomor Pokok Sekolah Nasional (NPSN)</label>
                    <input id="npsn" type="text" wire:model="npsn" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500" required>
                </div>

                <div>
                    <label for="headmaster_name" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Nama Kepala Sekolah</label>
                    <input id="headmaster_name" type="text" wire:model="headmaster_name" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500">
                </div>

                <div>
                    <label for="headmaster_nip" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">NIP Kepala Sekolah</label>
                    <input id="headmaster_nip" type="text" wire:model="headmaster_nip" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500">
                </div>

                <div>
                    <label for="active_academic_year" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Tahun Ajaran Aktif</label>
                    <input id="active_academic_year" type="text" wire:model="active_academic_year" placeholder="Contoh: 2026/2027" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500">
                </div>

                <div>
                    <label for="active_semester" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Semester Aktif</label>
                    <select id="active_semester" wire:model="active_semester" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500">
                        <option value="Ganjil">Semester Ganjil</option>
                        <option value="Genap">Semester Genap</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Security & Safe Exam Browser (SEB) Card -->
        <div class="p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 space-y-4">
            <h3 class="text-base font-bold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-700 pb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                Keamanan & Integrasi Safe Exam Browser (SEB)
            </h3>

            <div class="flex items-center gap-3">
                <input type="checkbox" id="seb_required" wire:model="seb_required" class="w-4 h-4 rounded text-primary-600 focus:ring-primary-500 border-gray-300">
                <label for="seb_required" class="text-sm font-semibold text-gray-800 dark:text-gray-200 cursor-pointer">
                    Wajibkan penggunaan Safe Exam Browser (SEB) untuk siswa
                </label>
            </div>

            @if($seb_required)
                <div>
                    <label for="seb_key_hash" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">SEB Config Key Hash (Opsional)</label>
                    <input id="seb_key_hash" type="text" wire:model="seb_key_hash" placeholder="Hash kunci enkripsi konfigurasi file .seb" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm font-mono focus:ring-primary-500 focus:border-primary-500">
                </div>
            @endif
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-6 py-2.5 text-sm font-bold rounded-xl bg-primary-600 hover:bg-primary-700 text-white shadow-md transition">
                Simpan Perubahan Pengaturan
            </button>
        </div>
    </form>
</x-filament-panels::page>
