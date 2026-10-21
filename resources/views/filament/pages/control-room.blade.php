<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Session Selector & Info Bar -->
        <div class="p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <label for="sessionSelect" class="text-sm font-semibold text-gray-700 dark:text-gray-200">Pilih Ruang & Sesi:</label>
                <select id="sessionSelect" wire:model.live="sessionId" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm font-medium focus:ring-primary-500 focus:border-primary-500">
                    @foreach($this->sessions as $s)
                        <option value="{{ $s->id }}">{{ $s->room_name }} - {{ $s->session_name }} ({{ $s->examSchedule->title ?? '-' }})</option>
                    @endforeach
                </select>
            </div>

            @if($this->selectedSession)
                <div class="flex items-center gap-2">
                    <a href="{{ route('proctor.printBeritaAcara', $this->selectedSession->id) }}" target="_blank" class="inline-flex items-center gap-1 px-3 py-2 text-xs font-semibold rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        Cetak Berita Acara
                    </a>
                </div>
            @endif
        </div>

        @if($this->selectedSession)
            <!-- Projector Token & Stats Card -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Big Token Display (Projector Friendly) -->
                <div class="p-6 bg-gradient-to-br from-blue-700 to-indigo-900 rounded-2xl text-white shadow-lg flex flex-col justify-between items-center text-center">
                    <div>
                        <span class="px-3 py-1 bg-white/20 rounded-full text-xs font-bold uppercase tracking-wider">Token Akses Ruang</span>
                        <h2 class="text-xs text-blue-200 mt-2 font-mono">Tampilkan di Layar Proyektor</h2>
                    </div>

                    <div class="my-4">
                        <div class="text-5xl lg:text-6xl font-black font-mono tracking-widest bg-white/10 px-6 py-3 rounded-2xl border border-white/20 shadow-inner">
                            {{ $this->selectedSession->access_token }}
                        </div>
                    </div>

                    <div class="flex items-center gap-3 w-full">
                        <button type="button" wire:click="regenerateToken" wire:confirm="Yakin ingin mengganti token baru untuk ruangan ini?" class="flex-1 py-2 px-3 text-xs font-bold rounded-lg bg-white/20 hover:bg-white/30 transition flex items-center justify-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            Ganti Token Baru
                        </button>
                    </div>
                </div>

                <!-- Session Meta Information -->
                <div class="lg:col-span-2 p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $this->selectedSession->examSchedule->title ?? '-' }}</h3>
                                <p class="text-xs text-gray-500">{{ $this->selectedSession->room_name }} &bull; {{ $this->selectedSession->session_name }}</p>
                            </div>
                            <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300">
                                Sesi Aktif
                            </span>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-4">
                            <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl text-center">
                                <span class="text-xs text-gray-500 dark:text-gray-400">Total Siswa</span>
                                <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ count($this->attempts) }}</p>
                            </div>
                            <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl text-center">
                                <span class="text-xs text-blue-600 dark:text-blue-400">Mengerjakan</span>
                                <p class="text-xl font-bold text-blue-700 dark:text-blue-300 mt-1">
                                    {{ $this->attempts->where('status', 'ongoing')->count() }}
                                </p>
                            </div>
                            <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-xl text-center">
                                <span class="text-xs text-green-600 dark:text-green-400">Selesai Submit</span>
                                <p class="text-xl font-bold text-green-700 dark:text-green-300 mt-1">
                                    {{ $this->attempts->where('status', 'submitted')->count() }}
                                </p>
                            </div>
                            <div class="p-3 bg-red-50 dark:bg-red-900/20 rounded-xl text-center">
                                <span class="text-xs text-red-600 dark:text-red-400">Terkunci Cheat</span>
                                <p class="text-xl font-bold text-red-700 dark:text-red-300 mt-1">
                                    {{ $this->attempts->where('is_locked', true)->count() }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between text-xs text-gray-500">
                        <span>Pengawas: <strong>{{ $this->selectedSession->proctor->name ?? '-' }}</strong></span>
                        <span>Durasi: <strong>{{ $this->selectedSession->examSchedule->duration_minutes ?? 90 }} Menit</strong></span>
                    </div>
                </div>
            </div>

            <!-- Student Seat Matrix -->
            <div class="p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        Matriks Pemantauan Siswa Real-Time
                    </h3>
                    <div class="flex items-center gap-3">
                        @if($this->attempts->where('is_locked', true)->count() > 0)
                            <button type="button" wire:click="unlockAllStudents" wire:confirm="Buka kunci semua siswa yang terkunci di sesi ini?" class="px-3 py-1.5 text-xs font-bold rounded-lg bg-red-600 hover:bg-red-700 text-white flex items-center gap-1.5 shadow-sm">
                                Buka Kunci Semua ({{ $this->attempts->where('is_locked', true)->count() }})
                            </button>
                        @endif
                        <span class="text-xs text-gray-400">Total: {{ count($this->attempts) }} Peserta</span>
                    </div>
                </div>

                @if(count($this->attempts) === 0)
                    <div class="p-12 text-center text-gray-400 border border-dashed rounded-xl">
                        <p class="text-sm">Belum ada siswa yang mulai mengerjakan di sesi ini.</p>
                        <p class="text-xs mt-1">Siswa dapat masuk menggunakan token: <strong class="font-mono text-primary-600">{{ $this->selectedSession->access_token }}</strong></p>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                        @foreach($this->attempts as $attempt)
                            <div class="p-4 rounded-xl border {{ $attempt->is_locked ? 'border-red-400 bg-red-50/50 dark:bg-red-950/20' : ($attempt->status === 'submitted' ? 'border-green-300 bg-green-50/30' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800') }} flex flex-col justify-between shadow-sm">
                                <div>
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <h4 class="text-sm font-bold text-gray-900 dark:text-white truncate">{{ $attempt->student->name }}</h4>
                                            <p class="text-xs font-mono text-gray-500">{{ $attempt->student->username }} &bull; {{ $attempt->student->classRoom->name ?? '-' }}</p>
                                        </div>
                                        @if($attempt->is_locked)
                                            <span class="px-2 py-0.5 text-xs font-bold rounded bg-red-600 text-white animate-pulse">LOCKED</span>
                                        @elseif($attempt->status === 'submitted')
                                            <span class="px-2 py-0.5 text-xs font-bold rounded bg-green-100 text-green-700">SELESAI</span>
                                        @else
                                            <span class="px-2 py-0.5 text-xs font-bold rounded bg-blue-100 text-blue-700">AKTIF</span>
                                        @endif
                                    </div>

                                    <!-- Cheating / Strikes indicator -->
                                    <div class="mt-3 flex items-center justify-between text-xs">
                                        <span class="text-gray-500">Peringatan Cheat:</span>
                                        <div class="flex items-center gap-1">
                                            @for($i = 1; $i <= 3; $i++)
                                                <span class="w-2.5 h-2.5 rounded-full {{ $i <= $attempt->cheat_strikes ? 'bg-red-500' : 'bg-gray-200 dark:bg-gray-600' }}"></span>
                                            @endfor
                                        </div>
                                    </div>

                                    @if($attempt->final_score !== null)
                                        <div class="mt-2 text-xs font-semibold text-gray-700 dark:text-gray-300">
                                            Nilai Akhir: <span class="text-primary-600 font-bold text-sm">{{ $attempt->final_score }}</span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Proctor Quick Actions -->
                                <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700 flex items-center gap-2">
                                    @if($attempt->is_locked)
                                        <button type="button" wire:click="unlockStudent({{ $attempt->id }})" class="flex-1 py-1.5 px-2 text-xs font-bold rounded-lg bg-red-600 hover:bg-red-700 text-white transition text-center shadow-sm">
                                            Buka Kunci
                                        </button>
                                    @endif

                                    <button type="button" wire:click="resetStudentLogin({{ $attempt->student_id }})" title="Reset jika siswa perlu login ulang di perangkat lain" class="py-1.5 px-2 text-xs font-medium rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 transition">
                                        Reset Login
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Proctor Notes & Berita Acara -->
            <div class="p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 space-y-3">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Catatan Berita Acara Pelaksanaan Ruang</h3>
                <textarea wire:model="proctorNotes" rows="3" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500" placeholder="Tuliskan catatan kejadian penting selama ujian berlangsung di ruangan ini..."></textarea>
                <div class="flex justify-end">
                    <button type="button" wire:click="saveProctorNotes" class="px-4 py-2 text-xs font-bold rounded-lg bg-primary-600 hover:bg-primary-700 text-white transition">
                        Simpan Catatan Berita Acara
                    </button>
                </div>
            </div>
        @else
            <div class="p-12 text-center bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 text-gray-500">
                <p>Belum ada sesi ujian yang dibuat. Silakan tambahkan jadwal ujian dan sesi ruangan terlebih dahulu di menu <strong>Jadwal Ujian</strong>.</p>
            </div>
        @endif
    </div>
</x-filament-panels::page>
