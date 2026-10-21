<script setup>
import { Link, Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { 
    CheckCircle2, 
    Clock, 
    Award, 
    ArrowLeft, 
    Layers, 
    AlertCircle,
    RotateCcw
} from 'lucide-vue-next';

const props = defineProps({
    attempt: Object,
    schedule: Object,
    subject: Object,
    showScore: Boolean,
    school: Object,
});
</script>

<template>
    <AppLayout>
        <Head :title="'Hasil Ujian - ' + schedule.title" />

        <template #header>
            <div>
                <h1 class="text-sm font-bold text-slate-900 dark:text-white tracking-tight">Hasil Evaluasi Ujian Siswa</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ schedule.title }} &bull; {{ subject.name }}</p>
            </div>
        </template>

        <div class="max-w-2xl mx-auto py-6">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 p-8 sm:p-10 shadow-xs text-center space-y-6 transition-colors duration-150">
                <!-- Status Icon -->
                <div class="w-16 h-16 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto border border-emerald-200/60 dark:border-emerald-800 shadow-2xs">
                    <CheckCircle2 class="w-8 h-8" />
                </div>

                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white uppercase tracking-tight">
                        Ujian Berhasil Dikumpulkan!
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Seluruh naskah jawaban Anda telah tersimpan secara permanen di server sekolah.
                    </p>
                </div>

                <!-- Graded Score vs Waiting for Teacher -->
                <div v-if="attempt.status === 'submitted'" class="p-4.5 bg-amber-50/70 dark:bg-amber-950/50 border border-amber-200/80 dark:border-amber-800 rounded-xl text-amber-900 dark:text-amber-200 text-xs text-left">
                    <span class="font-bold block mb-1">Status: Menunggu Koreksi Guru</span>
                    <p class="leading-relaxed">
                        Naskah ujian Anda memuat soal esai / uraian terbuka yang sedang dalam antrean pemeriksaan oleh Guru Mata Pelajaran. Nilai akhir resmi akan terbit setelah koreksi selesai.
                    </p>
                </div>

                <div v-else-if="attempt.status === 'graded'" class="space-y-4">
                    <div v-if="showScore" class="p-6 bg-slate-50/60 dark:bg-slate-800/60 border border-slate-200/90 dark:border-slate-700/80 rounded-2xl space-y-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block">
                            Nilai Akhir Anda
                        </span>
                        <p class="text-5xl font-black font-mono tracking-tight text-[#0F52BA] dark:text-blue-400 tabular-nums">
                            {{ Number(attempt.final_score).toFixed(0) }}
                        </p>

                        <!-- KKM Badge -->
                        <div>
                            <span
                                :class="[
                                    attempt.is_passed_kkm 
                                        ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' 
                                        : 'bg-rose-50 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-800',
                                    'inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider'
                                ]"
                            >
                                {{ attempt.is_passed_kkm ? 'Tuntas KKM' : 'Belum Tuntas (Perlu Remedial)' }}
                            </span>
                        </div>

                        <!-- Sub-scores -->
                        <div class="grid grid-cols-2 gap-3 pt-4 border-t border-slate-200/90 dark:border-slate-700 text-xs font-mono">
                            <div class="p-2.5 bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700">
                                <span class="text-slate-400 dark:text-slate-500 block text-[10px]">Skor Pilihan Ganda</span>
                                <strong class="text-slate-800 dark:text-slate-200 text-sm">{{ attempt.objective_score }}%</strong>
                            </div>
                            <div class="p-2.5 bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700">
                                <span class="text-slate-400 dark:text-slate-500 block text-[10px]">Skor Esai / Uraian</span>
                                <strong class="text-slate-800 dark:text-slate-200 text-sm">{{ attempt.essay_score }}%</strong>
                            </div>
                        </div>
                    </div>

                    <div v-else class="p-4.5 bg-slate-50/70 dark:bg-slate-800/70 border border-slate-200/90 dark:border-slate-700 rounded-xl text-xs text-slate-600 dark:text-slate-300">
                        Nilai ujian disimpan di basis data sekolah dan akan diumumkan secara serentak oleh panitia ujian.
                    </div>
                </div>

                <!-- Return Button -->
                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-center">
                    <Link
                        href="/student/dashboard"
                        class="px-5 py-2.5 bg-[#0F52BA] hover:bg-blue-700 active:scale-[0.98] text-white font-semibold text-xs rounded-xl shadow-xs inline-flex items-center gap-2 transition-all cursor-pointer"
                    >
                        <ArrowLeft class="w-3.5 h-3.5" />
                        <span>Kembali ke Portal Ujian</span>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
