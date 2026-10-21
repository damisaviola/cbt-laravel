<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Top Toolbar: Select Schedule & Actions -->
        <div class="p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <label for="scheduleSelect" class="text-sm font-semibold text-gray-700 dark:text-gray-200">Pilih Jadwal Ujian:</label>
                <select id="scheduleSelect" wire:model.live="scheduleId" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm font-medium focus:ring-primary-500 focus:border-primary-500">
                    @foreach($this->schedules as $sch)
                        <option value="{{ $sch->id }}">{{ $sch->title }} ({{ $sch->questionBank->title ?? '-' }})</option>
                    @endforeach
                </select>
            </div>

            @if($this->selectedSchedule)
                <div class="flex items-center gap-2">
                    <button type="button" wire:click="generateRemedial" wire:confirm="Buat sesi ujian remedial otomatis untuk seluruh siswa dengan nilai di bawah KKM?" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold rounded-lg bg-amber-500 hover:bg-amber-600 text-white transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        1-Klik Buat Sesi Remedial
                    </button>

                    <a href="{{ route('teacher.grading.exportLeger', $this->selectedSchedule->id) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Ekspor Leger Nilai e-Rapor (.xlsx)
                    </a>
                </div>
            @endif
        </div>

        @if($this->selectedSchedule)
            <!-- Schedule Meta Summary -->
            @php
                $bank = $this->selectedSchedule->questionBank;
                $passedCount = $this->attempts->where('is_passed_kkm', true)->count();
                $remedialCount = $this->attempts->where('final_score', '!=', null)->where('is_passed_kkm', false)->count();
            @endphp
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700">
                    <span class="text-xs text-gray-500">Standar KKM</span>
                    <p class="text-xl font-black text-gray-900 dark:text-white mt-1">{{ $bank->kkm_score ?? 75 }}</p>
                </div>
                <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700">
                    <span class="text-xs text-gray-500">Bobot Nilai</span>
                    <p class="text-sm font-bold text-gray-800 dark:text-gray-200 mt-1">PG: {{ $bank->pg_weight_percent }}% &bull; Uraian: {{ $bank->essay_weight_percent }}%</p>
                </div>
                <div class="p-4 bg-emerald-50 dark:bg-emerald-950/20 rounded-xl border border-emerald-200 dark:border-emerald-800/40">
                    <span class="text-xs text-emerald-700 dark:text-emerald-400 font-medium">Tuntas KKM</span>
                    <p class="text-xl font-black text-emerald-700 dark:text-emerald-400 mt-1">{{ $passedCount }} Siswa</p>
                </div>
                <div class="p-4 bg-amber-50 dark:bg-amber-950/20 rounded-xl border border-amber-200 dark:border-amber-800/40">
                    <span class="text-xs text-amber-700 dark:text-amber-400 font-medium">Perlu Remedial</span>
                    <p class="text-xl font-black text-amber-700 dark:text-amber-400 mt-1">{{ $remedialCount }} Siswa</p>
                </div>
            </div>

            <!-- Main Speed-Grading Workbench Split View -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Left: Student Attempts List -->
                <div class="lg:col-span-5 p-4 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-3 flex items-center justify-between">
                        <span>Daftar Siswa Peserta Ujian</span>
                        <span class="text-xs text-gray-400 font-normal">{{ count($this->attempts) }} Siswa</span>
                    </h3>

                    <div class="space-y-2 max-h-[650px] overflow-y-auto pr-1">
                        @forelse($this->attempts as $att)
                            <div wire:click="selectAttempt({{ $att->id }})" class="p-3 rounded-xl border cursor-pointer transition text-left {{ $this->selectedAttemptId === $att->id ? 'border-primary-500 bg-primary-50/40 dark:bg-primary-950/20 shadow-sm' : 'border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50' }}">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <h4 class="text-xs font-bold text-gray-900 dark:text-white truncate">{{ $att->student->name }}</h4>
                                        <p class="text-[11px] text-gray-500">{{ $att->student->username }} &bull; {{ $att->student->classRoom->name ?? '-' }}</p>
                                    </div>
                                    @if($att->final_score !== null)
                                        <span class="px-2 py-0.5 text-xs font-black rounded-lg {{ $att->is_passed_kkm ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                            {{ $att->final_score }}
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-gray-100 text-gray-600">Belum Koreksi</span>
                                    @endif
                                </div>

                                <div class="mt-2 flex items-center justify-between text-[11px] text-gray-500 pt-2 border-t border-gray-100 dark:border-gray-700">
                                    <span>Skor PG: <strong>{{ $att->objective_score ?? 0 }}</strong></span>
                                    <span>Skor Uraian: <strong>{{ $att->essay_score ?? '-' }}</strong></span>
                                    <span class="text-xs">{{ $att->status === 'submitted' ? 'Submited' : ($att->status === 'graded' ? 'Dinilai' : 'Mengerjakan') }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 text-center py-6">Belum ada data pengerjaan ujian.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Right: Speed-Grading Answer Sheet & Rubric -->
                <div class="lg:col-span-7 p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                    @if($this->selectedAttempt)
                        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-4 mb-4">
                            <div>
                                <h3 class="text-base font-bold text-gray-900 dark:text-white">{{ $this->selectedAttempt->student->name }}</h3>
                                <p class="text-xs text-gray-500">NISN: {{ $this->selectedAttempt->student->username }} &bull; Kelas: {{ $this->selectedAttempt->student->classRoom->name ?? '-' }}</p>
                            </div>
                            <div class="text-right">
                                <span class="text-xs text-gray-400">Total Nilai Akhir:</span>
                                <p class="text-2xl font-black text-primary-600">{{ $this->selectedAttempt->final_score ?? '-' }}</p>
                            </div>
                        </div>

                        <!-- Essay Questions & Answers List -->
                        @php
                            $essayAnswers = $this->selectedAttempt->answers->filter(fn ($a) => $a->question && $a->question->type === 'essay');
                        @endphp

                        @if($essayAnswers->isEmpty())
                            <div class="p-12 text-center text-gray-400 border border-dashed rounded-xl">
                                <p class="text-sm font-medium">Ujian ini tidak memiliki butir soal uraian, atau siswa belum menjawab soal uraian.</p>
                                <p class="text-xs mt-1">Skor Obyektif / PG telah dihitung otomatis oleh sistem: <strong>{{ $this->selectedAttempt->objective_score }}</strong></p>
                            </div>
                        @else
                            <div class="space-y-6 max-h-[600px] overflow-y-auto pr-1">
                                @foreach($essayAnswers as $index => $answer)
                                    <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 space-y-3">
                                        <div class="flex items-start justify-between">
                                            <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                Soal Uraian #{{ $answer->question->order_index ?? ($index + 1) }} (Maks: {{ $answer->question->max_score }} Poin)
                                            </span>
                                            <span class="text-xs font-bold {{ $answer->is_graded_by_teacher ? 'text-emerald-600' : 'text-amber-500' }}">
                                                {{ $answer->is_graded_by_teacher ? 'Sudah Dinilai (' . $answer->score_obtained . ' Poin)' : 'Belum Dinilai' }}
                                            </span>
                                        </div>

                                        <!-- Question Content -->
                                        <div class="text-xs text-gray-700 dark:text-gray-300 font-medium bg-white dark:bg-gray-800 p-3 rounded-lg border border-gray-100 dark:border-gray-700">
                                            {!! nl2br(e($answer->question->content)) !!}
                                        </div>

                                        <!-- Student Answer Text -->
                                        <div>
                                            <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Jawaban Siswa:</span>
                                            <div class="mt-1 p-3 rounded-lg bg-white dark:bg-gray-800 border border-primary-200 dark:border-primary-800/40 text-sm text-gray-900 dark:text-white font-mono whitespace-pre-wrap">
                                                {{ $answer->essay_text ?: '(Siswa tidak memberikan jawaban teks)' }}
                                            </div>
                                        </div>

                                        <!-- Speed Scoring Workbench Buttons (0 .. max_score) -->
                                        <div>
                                            <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Speed-Scoring Rubrik:</span>
                                            <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                                                @php
                                                    $max = (float) ($answer->question->max_score ?: 10);
                                                    $steps = [0, round($max * 0.25, 1), round($max * 0.5, 1), round($max * 0.75, 1), $max];
                                                @endphp
                                                @foreach(array_unique($steps) as $s)
                                                    <button type="button" wire:click="saveScore({{ $answer->id }}, {{ $s }})" class="px-3 py-1.5 text-xs font-bold rounded-lg border transition {{ (float) $answer->score_obtained === (float) $s && $answer->is_graded_by_teacher ? 'bg-primary-600 border-primary-600 text-white shadow-sm' : 'bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200 border-gray-300 dark:border-gray-600' }}">
                                                        {{ $s }} Poin
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @else
                        <div class="p-16 text-center text-gray-400">
                            <p class="text-sm">Silakan pilih peserta ujian dari daftar di sebelah kiri untuk mulai melakukan speed-grading.</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
