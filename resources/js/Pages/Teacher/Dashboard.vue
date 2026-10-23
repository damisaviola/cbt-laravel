<script setup>
import { ref } from 'vue';
import { useForm, Link, Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import BentoStatCard from '@/Components/BentoStatCard.vue';
import TrendChart from '@/Components/TrendChart.vue';
import CategoryProgressWidget from '@/Components/CategoryProgressWidget.vue';
import { 
    BookOpen, 
    Plus, 
    Layers, 
    ArrowRight, 
    Award, 
    FileSpreadsheet, 
    CheckCircle2, 
    Calendar,
    BarChart3
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

        <div class="space-y-6">
            <!-- Page Header matching contoh.jpg (Title on Left, Secondary & Primary Action on Right) -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                        Dashboard Overview
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        Welcome back, manage your course questions, grading workbench, and results.
                    </p>
                </div>

                <div class="flex items-center gap-3 shrink-0">
                    <!-- Export / Template Button matching contoh.jpg -->
                    <button
                        type="button"
                        @click="downloadExcelTemplate"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800 rounded-full text-xs font-semibold text-slate-700 dark:text-slate-200 shadow-2xs transition-colors cursor-pointer"
                    >
                        <FileSpreadsheet class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                        <span>Format Excel</span>
                    </button>

                    <!-- New Course / Action Pill Button matching contoh.jpg -->
                    <button
                        type="button"
                        @click="openCreate"
                        class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-full text-xs font-semibold shadow-sm shadow-blue-600/25 transition-all cursor-pointer"
                    >
                        <Plus class="w-4 h-4" />
                        <span>+ New Course</span>
                    </button>
                </div>
            </div>

            <!-- 4 Bento Stat Cards Grid matching contoh.jpg -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Card 1: Question Banks (Pastel Blue) -->
                <BentoStatCard
                    title="Total Question Banks"
                    :value="stats.total_banks"
                    trend="+12%"
                    trend-type="up"
                    icon-bg="bg-blue-50 dark:bg-blue-950/60"
                    icon-color="text-blue-600 dark:text-blue-400"
                >
                    <template #icon>
                        <BookOpen class="w-5 h-5" />
                    </template>
                </BentoStatCard>

                <!-- Card 2: Total Questions (Pastel Amber) -->
                <BentoStatCard
                    title="Active Questions"
                    :value="stats.total_questions"
                    trend="+5%"
                    trend-type="up"
                    icon-bg="bg-amber-50 dark:bg-amber-950/60"
                    icon-color="text-amber-600 dark:text-amber-400"
                >
                    <template #icon>
                        <Layers class="w-5 h-5" />
                    </template>
                </BentoStatCard>

                <!-- Card 3: Needs Grading (Pastel Emerald) -->
                <BentoStatCard
                    title="Needs Grading"
                    :value="stats.needs_grading_total"
                    trend="-2%"
                    trend-type="down"
                    icon-bg="bg-emerald-50 dark:bg-emerald-950/60"
                    icon-color="text-emerald-600 dark:text-emerald-400"
                >
                    <template #icon>
                        <Award class="w-5 h-5" />
                    </template>
                </BentoStatCard>

                <!-- Card 4: Passed KKM (Pastel Sky) -->
                <BentoStatCard
                    title="Passed Students"
                    :value="stats.passed_kkm_total"
                    trend="+8%"
                    trend-type="up"
                    icon-bg="bg-sky-50 dark:bg-sky-950/60"
                    icon-color="text-sky-600 dark:text-sky-400"
                >
                    <template #icon>
                        <CheckCircle2 class="w-5 h-5" />
                    </template>
                </BentoStatCard>
            </div>

            <!-- Two-Column Section matching contoh.jpg (Trends Chart on Left, Categories on Right) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                <!-- Left: Evaluation Trends Chart (approx 65% width) -->
                <div class="lg:col-span-2">
                    <TrendChart 
                        title="Enrollment & Submission Trends"
                        subtitle="Grafik penyelesaian ujian dan akumulasi jawaban siswa"
                        filter-options="['This Year', 'Last 6 Months', 'This Month']"
                    />
                </div>

                <!-- Right: Top Performing Categories (approx 35% width) -->
                <div class="lg:col-span-1">
                    <CategoryProgressWidget 
                        title="Top Performing Categories"
                        subtitle="Distribusi ketuntasan KKM mata pelajaran binaan"
                    />
                </div>
            </div>

            <!-- Two-Column Workspace Layout (Recent Schedules & Question Banks) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left: Recent Exam Schedules Evaluation (2 cols) -->
                <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-6 shadow-sm">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 mb-5">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold">
                                <Calendar class="w-4 h-4" />
                            </div>
                            <div>
                                <h2 class="text-sm font-bold text-slate-900 dark:text-white">
                                    Jadwal Ujian & Status Penilaian
                                </h2>
                                <p class="text-xs text-slate-400">Koreksi esai cepat, analisis daya beda, dan unduh leger nilai</p>
                            </div>
                        </div>
                        <Link href="/teacher/grading" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">
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
                            class="p-4 bg-slate-50/70 dark:bg-slate-800/40 hover:bg-slate-50 dark:hover:bg-slate-800 border border-slate-100 dark:border-slate-800 rounded-2xl space-y-3 transition-all"
                        >
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-200/50 dark:border-slate-700/60">
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                            {{ sch.subject?.code }}
                                        </span>
                                        <h3 class="text-xs font-bold text-slate-900 dark:text-white">
                                            {{ sch.title }}
                                        </h3>
                                        <span v-if="sch.is_remedial" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            Remedial
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-slate-400 mt-1">
                                        Mapel: <strong class="text-slate-700 dark:text-slate-300">{{ sch.subject?.name }}</strong> &bull; KKM: <span class="px-1.5 py-0.5 bg-white dark:bg-slate-700 rounded text-slate-800 dark:text-slate-200 font-bold">{{ sch.subject?.kkm_score }}</span>
                                    </p>
                                </div>

                                <!-- Functional metrics -->
                                <div class="flex items-center gap-2.5 text-xs font-mono shrink-0">
                                    <div class="px-3 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200/60 dark:border-slate-700 text-center min-w-[70px]">
                                        <span class="text-[9px] uppercase font-bold text-slate-400 block">Terkumpul</span>
                                        <span class="font-bold text-slate-800 dark:text-slate-200 tabular-nums">
                                            {{ sch.submitted_count }}/{{ sch.total_students }}
                                        </span>
                                    </div>
                                    <div class="px-3 py-1.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200/70 dark:border-amber-800/70 text-center min-w-[70px]">
                                        <span class="text-[9px] uppercase font-bold text-amber-600 dark:text-amber-400 block">Koreksi</span>
                                        <span :class="['font-bold tabular-nums', sch.needs_grading_count > 0 ? 'text-amber-800 dark:text-amber-300' : 'text-slate-400']">
                                            {{ sch.needs_grading_count }}
                                        </span>
                                    </div>
                                    <div class="px-3 py-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200/70 dark:border-emerald-800/70 text-center min-w-[70px]">
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
                                        class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-semibold text-xs rounded-full shadow-2xs flex items-center gap-1.5 transition-all cursor-pointer"
                                    >
                                        <Award class="w-3.5 h-3.5" />
                                        <span>Meja Koreksi Esai</span>
                                    </Link>

                                    <Link
                                        :href="`/teacher/grading/${sch.id}/analysis`"
                                        class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 text-slate-700 dark:text-slate-200 font-semibold text-xs rounded-full flex items-center gap-1.5 transition-all shadow-2xs cursor-pointer"
                                    >
                                        <BarChart3 class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" />
                                        <span>Analisis Butir</span>
                                    </Link>
                                </div>

                                <a
                                    :href="`/teacher/grading/${sch.id}/export-leger`"
                                    target="_blank"
                                    class="px-4 py-2 bg-emerald-50 dark:bg-emerald-950/40 hover:bg-emerald-100 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/80 font-semibold text-xs rounded-full flex items-center gap-1.5 transition-all shadow-2xs cursor-pointer"
                                >
                                    <FileSpreadsheet class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" />
                                    <span>Leger e-Rapor (.xlsx)</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Recent Question Banks (1 col) -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-6 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 mb-4">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold">
                                    <BookOpen class="w-4 h-4" />
                                </div>
                                <div>
                                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">
                                        Soal Ujian Terkini
                                    </h2>
                                    <p class="text-[11px] text-slate-400">Naskah soal aktif</p>
                                </div>
                            </div>
                            <Link href="/teacher/banks" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                                Semua &rarr;
                            </Link>
                        </div>

                        <div v-if="recentBanks.length === 0" class="text-center py-8 text-xs text-slate-400 dark:text-slate-500 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-dashed border-slate-200 dark:border-slate-800">
                            Belum ada paket soal ujian.
                        </div>

                        <div v-else class="space-y-3 overflow-y-auto max-h-[440px] pr-0.5">
                            <div
                                v-for="bank in recentBanks"
                                :key="bank.id"
                                class="p-3.5 bg-slate-50/70 dark:bg-slate-800/40 hover:bg-slate-50 dark:hover:bg-slate-800 border border-slate-100 dark:border-slate-800 rounded-2xl transition-all shadow-2xs group"
                            >
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300">
                                        {{ bank.subject?.code }}
                                    </span>
                                    <span class="text-xs font-mono font-bold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-700 px-2 py-0.5 rounded-md">
                                        {{ bank.questions_count }} Butir
                                    </span>
                                </div>

                                <h3 class="text-xs font-bold text-slate-900 dark:text-white line-clamp-1 mb-0.5 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                    {{ bank.title }}
                                </h3>
                                <p class="text-[11px] text-slate-400 mb-2 truncate">
                                    {{ bank.subject?.name }}
                                </p>

                                <div class="flex items-center justify-between pt-2 border-t border-slate-200/50 dark:border-slate-700/60 text-xs">
                                    <span class="text-[10px] text-slate-400 font-mono">
                                        Bobot: <strong class="text-slate-700 dark:text-slate-300">{{ bank.pg_weight_percent }}% / {{ bank.essay_weight_percent }}%</strong>
                                    </span>
                                    <Link
                                        :href="`/teacher/banks/${bank.id}`"
                                        class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-[11px] rounded-full flex items-center gap-1 shadow-2xs transition-colors"
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
                            class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-full flex items-center justify-center gap-2 shadow-sm transition-all cursor-pointer"
                        >
                            <Plus class="w-4 h-4" />
                            <span>Buat Soal Ujian Baru</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Soal Ujian Modal -->
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
                    class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 z-50 overflow-y-auto"
                >
                    <div
                        @click.stop
                        class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 max-w-md w-full p-6 shadow-xl space-y-4 my-auto"
                    >
                        <div class="flex items-center justify-between pb-3.5 border-b border-slate-100 dark:border-slate-800">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                                    <Plus class="w-4 h-4" />
                                </div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                                    Buat Soal Ujian Baru
                                </h3>
                            </div>
                            <button
                                type="button"
                                @click="showCreateModal = false"
                                class="p-1 text-slate-400 hover:text-slate-600 rounded-lg cursor-pointer"
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
                                    class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:border-blue-600 focus:outline-none font-medium"
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
                                    class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white placeholder:text-slate-400 focus:border-blue-600 focus:outline-none transition-all"
                                    required
                                />
                            </div>

                            <div class="grid grid-cols-2 gap-3 p-3.5 bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700 rounded-2xl">
                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Bobot PG (%)</label>
                                    <input
                                        v-model.number="form.pg_weight_percent"
                                        type="number"
                                        min="0"
                                        max="100"
                                        class="w-full px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white font-mono focus:border-blue-600 focus:outline-none"
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
                                        class="w-full px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white font-mono focus:border-blue-600 focus:outline-none"
                                        required
                                    />
                                </div>
                                <p class="col-span-2 text-[11px] text-slate-400 font-medium">
                                    Total persentase bobot harus 100%. Saat ini: <strong :class="form.pg_weight_percent + form.essay_weight_percent === 100 ? 'text-emerald-600' : 'text-rose-500'">{{ form.pg_weight_percent + form.essay_weight_percent }}%</strong>
                                </p>
                            </div>

                            <div class="flex justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                                <button
                                    type="button"
                                    @click="showCreateModal = false"
                                    class="px-4 py-2 border border-slate-200 dark:border-slate-700 font-semibold rounded-full text-slate-700 dark:text-slate-300 hover:bg-slate-50 transition-colors cursor-pointer"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    :disabled="form.processing || (form.pg_weight_percent + form.essay_weight_percent !== 100)"
                                    class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-full shadow-sm disabled:opacity-50 transition-all cursor-pointer"
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
