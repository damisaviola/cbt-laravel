<script setup>
import { ref, computed } from 'vue';
import { Link, router, Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { 
    Award, 
    FileSpreadsheet, 
    BarChart3, 
    RotateCcw, 
    Search 
} from 'lucide-vue-next';
import { useConfirm } from '@/Composables/useConfirm';

const props = defineProps({
    schedules: Array,
});

const searchQuery = ref('');

const filteredSchedules = computed(() => {
    return props.schedules.filter(s => {
        if (!searchQuery.value) return true;
        const q = searchQuery.value.toLowerCase();
        return s.title.toLowerCase().includes(q) ||
            s.subject?.name?.toLowerCase().includes(q) ||
            s.subject?.code?.toLowerCase().includes(q);
    });
});

const { confirm } = useConfirm();

const triggerRemedial = async (sch) => {
    const ok = await confirm({
        title: 'Buat Sesi Ujian Remedial',
        message: `Buat sesi remedial 1-klik untuk seluruh siswa dengan nilai di bawah standar KKM pada ujian "${sch.title}"? Jadwal remedial baru akan otomatis dibuat.`,
        confirmText: 'Ya, Buat Remedial',
        cancelText: 'Batal',
        type: 'info',
    });
    if (ok) {
        router.post(`/teacher/grading/${sch.id}/remedial`);
    }
};

const exportLeger = (sch) => {
    window.open(`/teacher/grading/${sch.id}/export-leger`, '_blank');
};
</script>

<template>
    <AppLayout>
        <Head title="Evaluasi & Meja Koreksi Nilai" />

        <template #header>
            <div class="flex items-center justify-between w-full min-w-0">
                <div class="min-w-0">
                    <h1 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white tracking-tight truncate">Koreksi & Leger Nilai Ujian</h1>
                    <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-0.5 truncate sm:whitespace-normal">Koreksi esai dan rekap nilai ujian siswa berdasarkan jadwal aktif.</p>
                </div>
            </div>
        </template>

        <div class="space-y-4">
            <!-- Search Bar (Rounded-3xl) -->
            <div class="bg-white dark:bg-slate-900 p-4 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs flex items-center justify-between gap-3">
                <div class="relative w-full sm:w-96">
                    <Search class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Cari jadwal ujian atau nama mata pelajaran..."
                        class="w-full pl-10 pr-3.5 py-2 text-xs bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700 rounded-full text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:border-[#185338] dark:focus:border-emerald-500 focus:ring-2 focus:ring-[#185338]/10 focus:outline-none transition-all shadow-2xs"
                    />
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="filteredSchedules.length === 0" class="bg-white dark:bg-slate-900 p-12 rounded-3xl border border-dashed border-slate-200/80 dark:border-slate-800 text-center text-xs text-slate-500 dark:text-slate-400 shadow-xs">
                Belum ada jadwal ujian yang diselenggarakan.
            </div>

            <!-- Evaluation Cards (Rounded-3xl) -->
            <div v-else class="space-y-4">
                <div
                    v-for="sch in filteredSchedules"
                    :key="sch.id"
                    class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-xs hover:shadow-sm transition-all duration-150"
                >
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-800">
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="px-3 py-0.5 rounded-full text-[10px] font-mono font-bold bg-[#eaf4ef] dark:bg-emerald-950/60 text-[#185338] dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800">
                                    {{ sch.subject?.code }}
                                </span>
                                <h2 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wide">
                                    {{ sch.title }}
                                </h2>
                                <span v-if="sch.is_remedial" class="px-3 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                    Sesi Remedial
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5">
                                Mapel: <strong class="text-slate-800 dark:text-slate-200">{{ sch.subject?.name }}</strong> &bull; Standar KKM: <span class="font-bold text-slate-800 dark:text-slate-200">{{ sch.subject?.kkm_score }}</span>
                            </p>
                        </div>

                        <!-- Functional Metrics -->
                        <div class="flex items-center gap-4 sm:gap-6 text-xs font-mono shrink-0">
                            <div class="px-3.5 py-1.5 rounded-2xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700 text-center">
                                <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500 block">Terkumpul</span>
                                <span class="text-sm font-bold text-slate-800 dark:text-slate-200 tabular-nums">
                                    {{ sch.submitted_count }} / {{ sch.total_students }}
                                </span>
                            </div>
                            <div class="px-3.5 py-1.5 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/60 text-center">
                                <span class="text-[10px] uppercase font-bold text-amber-600 dark:text-amber-400 block">Perlu Koreksi</span>
                                <span class="text-sm font-bold text-amber-600 dark:text-amber-400 tabular-nums">
                                    {{ sch.needs_grading_count }}
                                </span>
                            </div>
                            <div class="px-3.5 py-1.5 rounded-2xl bg-[#eaf4ef] dark:bg-emerald-950/40 border border-emerald-200/80 dark:border-emerald-800/60 text-center">
                                <span class="text-[10px] uppercase font-bold text-[#185338] dark:text-emerald-400 block">Tuntas KKM</span>
                                <span class="text-sm font-bold text-[#185338] dark:text-emerald-300 tabular-nums">
                                    {{ sch.passed_kkm_count }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Toolbar -->
                    <div class="flex flex-wrap items-center justify-between gap-3 pt-3.5">
                        <div class="flex flex-wrap items-center gap-2">
                            <Link
                                :href="`/teacher/grading/${sch.id}/workbench`"
                                class="px-5 py-2.5 bg-[#185338] hover:bg-[#12422c] text-white font-bold text-xs rounded-full shadow-xs flex items-center gap-1.5 transition-all cursor-pointer"
                            >
                                <Award class="w-3.5 h-3.5" />
                                <span>Meja Koreksi Esai</span>
                            </Link>

                            <Link
                                :href="`/teacher/grading/${sch.id}/analysis`"
                                class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-semibold text-xs rounded-full shadow-2xs flex items-center gap-1.5 transition-all cursor-pointer"
                            >
                                <BarChart3 class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" />
                                <span>Analisis Butir Soal</span>
                            </Link>

                            <button
                                @click="triggerRemedial(sch)"
                                class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700 hover:bg-amber-50 dark:hover:bg-amber-950/40 text-amber-800 dark:text-amber-300 font-semibold text-xs rounded-full shadow-2xs flex items-center gap-1.5 transition-all cursor-pointer"
                                title="Buat Sesi Ujian Khusus Siswa < KKM"
                            >
                                <RotateCcw class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400" />
                                <span>Buat Sesi Remedial</span>
                            </button>
                        </div>

                        <button
                            @click="exportLeger(sch)"
                            class="px-5 py-2.5 bg-[#185338] hover:bg-[#12422c] text-white font-bold text-xs rounded-full shadow-xs hover:shadow flex items-center gap-1.5 transition-all cursor-pointer"
                        >
                            <FileSpreadsheet class="w-3.5 h-3.5" />
                            <span>Ekspor Leger Nilai (.xlsx)</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
