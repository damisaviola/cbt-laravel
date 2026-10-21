<script setup>
import { ref } from 'vue';
import { useForm, Link, Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { 
    BookOpen, 
    Plus, 
    Layers, 
    ArrowRight, 
    Trash2, 
    Award, 
    FileSpreadsheet, 
    CheckCircle2, 
    Clock, 
    Download, 
    BarChart3,
    Calendar,
    Users
} from 'lucide-vue-next';

const props = defineProps({
    stats: Object,
    recentBanks: Array,
    recentSchedules: Array,
    subjects: Array,
});

const showCreateModal = ref(false);

const form = useForm({
    subject_id: props.subjects[0]?.id || '',
    title: '',
    pg_weight_percent: 70,
    essay_weight_percent: 30,
});

const openCreate = () => {
    form.reset();
    form.subject_id = props.subjects[0]?.id || '';
    form.pg_weight_percent = 70;
    form.essay_weight_percent = 30;
    showCreateModal.value = true;
};

const submitCreate = () => {
    form.post('/teacher/banks', {
        onSuccess: () => (showCreateModal.value = false),
    });
};

const downloadExcelTemplate = () => {
    window.location.href = '/teacher/template/excel';
};
</script>

<template>
    <AppLayout>
        <Head title="Dashboard Guru" />

        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 w-full">
                <div>
                    <h1 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                        <span>Dashboard Guru Mapel</span>
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                            Pendidik
                        </span>
                    </h1>
                    <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-0.5">Kelola naskah soal ujian, meja koreksi esai, dan rekap nilai siswa.</p>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        @click="downloadExcelTemplate"
                        class="px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-emerald-300 dark:hover:border-emerald-600 hover:bg-emerald-50/50 dark:hover:bg-emerald-950/40 text-slate-700 dark:text-slate-200 hover:text-emerald-800 dark:hover:text-emerald-300 font-semibold text-xs rounded-xl shadow-2xs flex items-center gap-1.5 transition-all cursor-pointer"
                    >
                        <FileSpreadsheet class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" />
                        <span>Format Excel</span>
                    </button>

                    <button
                        @click="openCreate"
                        class="px-4 py-2 bg-gradient-to-r from-[#0F52BA] to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold text-xs rounded-xl shadow-xs hover:shadow-md flex items-center gap-1.5 transition-all cursor-pointer"
                    >
                        <Plus class="w-3.5 h-3.5" />
                        <span>Buat Soal Ujian</span>
                    </button>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- 4 Clean Colorful Bento Metric Cards (Rounded-2xl) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- 1. Soal Ujian (Blue Accent) -->
                <div class="bg-gradient-to-br from-white to-blue-50/60 dark:from-slate-900 dark:to-blue-950/30 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs hover:shadow-sm hover:border-blue-300 dark:hover:border-blue-700 transition-all duration-150">
                    <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 mb-3">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400">Soal Ujian</span>
                        <div class="w-9 h-9 rounded-xl bg-blue-500 text-white flex items-center justify-center shadow-xs shadow-blue-500/25">
                            <BookOpen class="w-4 h-4" />
                        </div>
                    </div>
                    <p class="text-2xl font-black text-slate-900 dark:text-white font-mono tabular-nums">
                        {{ stats.total_banks }}
                    </p>
                    <div class="mt-3 pt-3 border-t border-slate-200/60 dark:border-slate-800 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
                        <span>{{ stats.total_questions }} Butir Tersedia</span>
                        <Link href="/teacher/banks" class="text-blue-700 dark:text-blue-400 font-bold hover:underline">
                            Buka &rarr;
                        </Link>
                    </div>
                </div>

                <!-- 2. Total Butir Soal (Indigo Accent) -->
                <div class="bg-gradient-to-br from-white to-indigo-50/60 dark:from-slate-900 dark:to-indigo-950/30 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs hover:shadow-sm hover:border-indigo-300 dark:hover:border-indigo-700 transition-all duration-150">
                    <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 mb-3">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400">Total Butir Soal</span>
                        <div class="w-9 h-9 rounded-xl bg-indigo-500 text-white flex items-center justify-center shadow-xs shadow-indigo-500/25">
                            <Layers class="w-4 h-4" />
                        </div>
                    </div>
                    <p class="text-2xl font-black text-slate-900 dark:text-white font-mono tabular-nums">
                        {{ stats.total_questions }}
                    </p>
                    <div class="mt-3 pt-3 border-t border-slate-200/60 dark:border-slate-800 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
                        <span>Multi-tipe AKM</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 dark:bg-indigo-950/60 text-indigo-800 dark:text-indigo-300 border border-transparent dark:border-indigo-800/80">Siap Ujian</span>
                    </div>
                </div>

                <!-- 3. Perlu Dikoreksi (Amber Accent) -->
                <div class="bg-gradient-to-br from-white to-amber-50/60 dark:from-slate-900 dark:to-amber-950/30 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs hover:shadow-sm hover:border-amber-300 dark:hover:border-amber-700 transition-all duration-150">
                    <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 mb-3">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400">Perlu Dikoreksi</span>
                        <div class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center shadow-xs shadow-amber-500/25">
                            <Award class="w-4 h-4" />
                        </div>
                    </div>
                    <p class="text-2xl font-black text-amber-700 dark:text-amber-400 font-mono tabular-nums">
                        {{ stats.needs_grading_total }}
                    </p>
                    <div class="mt-3 pt-3 border-t border-slate-200/60 dark:border-slate-800 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
                        <span>Jawaban Esai Siswa</span>
                        <Link href="/teacher/grading" class="text-amber-700 dark:text-amber-400 font-bold hover:underline">
                            Koreksi &rarr;
                        </Link>
                    </div>
                </div>

                <!-- 4. Siswa Tuntas KKM (Emerald Accent) -->
                <div class="bg-gradient-to-br from-white to-emerald-50/60 dark:from-slate-900 dark:to-emerald-950/30 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs hover:shadow-sm hover:border-emerald-300 dark:hover:border-emerald-700 transition-all duration-150">
                    <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 mb-3">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400">Siswa Tuntas KKM</span>
                        <div class="w-9 h-9 rounded-xl bg-emerald-500 text-white flex items-center justify-center shadow-xs shadow-emerald-500/25">
                            <CheckCircle2 class="w-4 h-4" />
                        </div>
                    </div>
                    <p class="text-2xl font-black text-emerald-700 dark:text-emerald-400 font-mono tabular-nums">
                        {{ stats.passed_kkm_total }}
                    </p>
                    <div class="mt-3 pt-3 border-t border-slate-200/60 dark:border-slate-800 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
                        <span>Standar Kelulusan</span>
                        <Link href="/teacher/grading" class="text-emerald-700 dark:text-emerald-400 font-bold hover:underline">
                            Rekap Leger &rarr;
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Two-Column Workspace (Both Cards Rounded-2xl) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left: Recent Exam Schedules Evaluation (2 cols) -->
                <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 p-6 shadow-xs">
                    <div class="flex items-center justify-between pb-3.5 border-b border-slate-100 dark:border-slate-800 mb-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold">
                                <Calendar class="w-4 h-4" />
                            </div>
                            <div>
                                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">
                                    Jadwal Ujian & Status Penilaian
                                </h2>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Koreksi esai cepat, analisis daya beda, dan unduh leger e-Rapor.</p>
                            </div>
                        </div>
                        <Link href="/teacher/grading" class="text-xs font-semibold text-[#0F52BA] dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 bg-blue-50 dark:bg-blue-950/60 px-2.5 py-1 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors">
                            Semua Jadwal &rarr;
                        </Link>
                    </div>

                    <div v-if="recentSchedules.length === 0" class="text-center py-12 text-xs text-slate-400 dark:text-slate-500 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-dashed border-slate-200 dark:border-slate-800">
                        Belum ada jadwal ujian yang diselenggarakan.
                    </div>

                    <div v-else class="space-y-3.5">
                        <div
                            v-for="sch in recentSchedules"
                            :key="sch.id"
                            class="p-4.5 bg-gradient-to-br from-white to-slate-50/70 dark:from-slate-900 dark:to-slate-800/80 border border-slate-200/90 dark:border-slate-800 rounded-2xl space-y-3 hover:border-blue-300 dark:hover:border-blue-700 hover:shadow-2xs transition-all"
                        >
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-200/80 dark:border-slate-800">
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-mono font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                            {{ sch.subject?.code }}
                                        </span>
                                        <h3 class="text-xs font-bold text-slate-900 dark:text-white">
                                            {{ sch.title }}
                                        </h3>
                                        <span v-if="sch.is_remedial" class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                            Remedial
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                                        Mapel: <strong class="text-slate-800 dark:text-slate-200">{{ sch.subject?.name }}</strong> &bull; KKM: <span class="px-1.5 py-0.2 bg-slate-100 dark:bg-slate-800 rounded text-slate-700 dark:text-slate-200 font-bold">{{ sch.subject?.kkm_score }}</span>
                                    </p>
                                </div>

                                <!-- Functional metrics -->
                                <div class="flex items-center gap-2.5 text-xs font-mono shrink-0">
                                    <div class="px-2.5 py-1 rounded-xl bg-slate-100/80 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700 text-center min-w-[70px]">
                                        <span class="text-[9px] uppercase font-bold text-slate-400 dark:text-slate-500 block">Terkumpul</span>
                                        <span class="font-bold text-slate-800 dark:text-slate-200 tabular-nums">
                                            {{ sch.submitted_count }}/{{ sch.total_students }}
                                        </span>
                                    </div>
                                    <div class="px-2.5 py-1 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/70 text-center min-w-[70px]">
                                        <span class="text-[9px] uppercase font-bold text-amber-600 dark:text-amber-400 block">Koreksi</span>
                                        <span :class="['font-bold tabular-nums', sch.needs_grading_count > 0 ? 'text-amber-800 dark:text-amber-300' : 'text-slate-400 dark:text-slate-500']">
                                            {{ sch.needs_grading_count }}
                                        </span>
                                    </div>
                                    <div class="px-2.5 py-1 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200/80 dark:border-emerald-800/70 text-center min-w-[70px]">
                                        <span class="text-[9px] uppercase font-bold text-emerald-600 dark:text-emerald-400 block">Tuntas</span>
                                        <span class="font-bold text-emerald-800 dark:text-emerald-300 tabular-nums">
                                            {{ sch.passed_kkm_count }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center justify-between gap-2 pt-0.5">
                                <div class="flex items-center gap-2">
                                    <Link
                                        :href="`/teacher/grading/${sch.id}/workbench`"
                                        class="px-3.5 py-1.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-semibold text-xs rounded-xl shadow-xs hover:shadow flex items-center gap-1.5 transition-all cursor-pointer"
                                    >
                                        <Award class="w-3.5 h-3.5" />
                                        <span>Meja Koreksi Esai</span>
                                    </Link>

                                    <Link
                                        :href="`/teacher/grading/${sch.id}/analysis`"
                                        class="px-3.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-semibold text-xs rounded-xl flex items-center gap-1.5 transition-all shadow-2xs cursor-pointer"
                                    >
                                        <BarChart3 class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" />
                                        <span>Analisis Butir</span>
                                    </Link>
                                </div>

                                <a
                                    :href="`/teacher/grading/${sch.id}/export-leger`"
                                    target="_blank"
                                    class="px-3.5 py-1.5 bg-emerald-50 dark:bg-emerald-950/40 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/80 font-semibold text-xs rounded-xl flex items-center gap-1.5 transition-all shadow-2xs cursor-pointer"
                                >
                                    <FileSpreadsheet class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" />
                                    <span>Leger e-Rapor (.xlsx)</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Recent Question Banks (1 col, Rounded-2xl Card) -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 p-6 shadow-xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-3.5 border-b border-slate-100 dark:border-slate-800 mb-4">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold">
                                    <BookOpen class="w-4 h-4" />
                                </div>
                                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">
                                    Soal Ujian Terkini
                                </h2>
                            </div>
                            <Link href="/teacher/banks" class="text-[11px] font-semibold text-[#0F52BA] dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 bg-blue-50 dark:bg-blue-950/60 px-2 py-1 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors">
                                Semua &rarr;
                            </Link>
                        </div>

                        <div v-if="recentBanks.length === 0" class="text-center py-8 text-xs text-slate-400 dark:text-slate-500 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-dashed border-slate-200 dark:border-slate-800">
                            Belum ada paket soal ujian.
                        </div>

                        <div v-else class="space-y-3 overflow-y-auto max-h-[440px] pr-0.5">
                            <div
                                v-for="bank in recentBanks"
                                :key="bank.id"
                                class="p-3.5 bg-gradient-to-br from-white to-indigo-50/20 dark:from-slate-900 dark:to-indigo-950/20 border border-slate-200/90 dark:border-slate-800 hover:border-indigo-300 dark:hover:border-indigo-700 rounded-xl transition-all shadow-2xs group"
                            >
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-mono font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                        {{ bank.subject?.code }}
                                    </span>
                                    <span class="text-xs font-mono font-bold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-md">
                                        {{ bank.questions_count }} Butir
                                    </span>
                                </div>

                                <h3 class="text-xs font-bold text-slate-900 dark:text-white line-clamp-1 mb-0.5 group-hover:text-[#0F52BA] dark:group-hover:text-blue-400 transition-colors">
                                    {{ bank.title }}
                                </h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-2 truncate">
                                    {{ bank.subject?.name }}
                                </p>

                                <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-slate-800 text-xs">
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">
                                        Bobot: <strong class="text-slate-700 dark:text-slate-300">{{ bank.pg_weight_percent }}% / {{ bank.essay_weight_percent }}%</strong>
                                    </span>
                                    <Link
                                        :href="`/teacher/banks/${bank.id}`"
                                        class="px-2.5 py-1 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-semibold text-[11px] rounded-lg flex items-center gap-1 shadow-xs transition-colors"
                                    >
                                        <span>Kelola</span>
                                        <ArrowRight class="w-3 h-3" />
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 dark:border-slate-800 mt-4">
                        <button
                            @click="openCreate"
                            class="w-full py-2.5 bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 hover:from-slate-800 hover:to-indigo-900 dark:from-slate-800 dark:via-indigo-950 dark:to-slate-800 dark:border dark:border-slate-700/60 text-white text-xs font-semibold rounded-xl flex items-center justify-center gap-2 shadow-xs hover:shadow transition-all cursor-pointer"
                        >
                            <Plus class="w-4 h-4 text-blue-400" />
                            <span>Buat Soal Ujian Baru</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Soal Ujian Modal (Lightweight & Smooth) -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-150 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-100 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="showCreateModal"
                    @click="showCreateModal = false"
                    class="fixed inset-0 bg-slate-900/50 dark:bg-slate-950/70 flex items-center justify-center p-4 z-50 overflow-y-auto"
                >
                    <div
                        @click.stop
                        class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 max-w-md w-full p-6 shadow-xl space-y-4 my-auto transform-gpu"
                    >
                        <div class="flex items-center justify-between pb-3.5 border-b border-slate-100 dark:border-slate-800">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-xl bg-blue-500/10 text-[#0F52BA] dark:text-blue-400 flex items-center justify-center font-bold">
                                    <Plus class="w-4 h-4" />
                                </div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                                    Buat Soal Ujian Baru
                                </h3>
                            </div>
                            <button
                                type="button"
                                @click="showCreateModal = false"
                                class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-lg cursor-pointer transition-colors"
                                aria-label="Tutup"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <form @submit.prevent="submitCreate" class="space-y-4 text-xs">
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Mata Pelajaran</label>
                                <select
                                    v-model="form.subject_id"
                                    class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:border-[#0F52BA] dark:focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/40 focus:outline-none font-medium"
                                    required
                                >
                                    <option v-for="s in subjects" :key="s.id" :value="s.id">
                                        {{ s.name }} ({{ s.code }})
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Judul / Nama Soal Ujian</label>
                                <input
                                    v-model="form.title"
                                    type="text"
                                    placeholder="Contoh: Ujian STS Matematika Kelas X Ganjil 2026"
                                    class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:border-[#0F52BA] dark:focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/40 focus:outline-none transition-all"
                                    required
                                />
                            </div>

                            <div class="grid grid-cols-2 gap-3 p-3.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl">
                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Bobot PG (%)</label>
                                    <input
                                        v-model.number="form.pg_weight_percent"
                                        type="number"
                                        min="0"
                                        max="100"
                                        class="w-full px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white font-mono focus:border-[#0F52BA] dark:focus:border-blue-500 focus:outline-none"
                                        required
                                    />
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Bobot Esai (%)</label>
                                    <input
                                        v-model.number="form.essay_weight_percent"
                                        type="number"
                                        min="0"
                                        max="100"
                                        class="w-full px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white font-mono focus:border-[#0F52BA] dark:focus:border-blue-500 focus:outline-none"
                                        required
                                    />
                                </div>
                                <p class="col-span-2 text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                                    Total persentase bobot harus 100%. Saat ini: <strong :class="form.pg_weight_percent + form.essay_weight_percent === 100 ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">{{ form.pg_weight_percent + form.essay_weight_percent }}%</strong>
                                </p>
                            </div>

                            <div class="flex justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                                <button
                                    type="button"
                                    @click="showCreateModal = false"
                                    class="px-4 py-2.5 border border-slate-200 dark:border-slate-700 font-semibold rounded-xl text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    :disabled="form.processing || (form.pg_weight_percent + form.essay_weight_percent !== 100)"
                                    class="px-5 py-2.5 bg-[#0F52BA] hover:bg-blue-700 text-white font-semibold rounded-xl shadow-xs hover:shadow disabled:opacity-50 transition-all cursor-pointer"
                                >
                                    {{ form.processing ? 'Menyimpan...' : 'Buat Soal Ujian' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>
