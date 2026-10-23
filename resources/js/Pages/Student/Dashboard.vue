<script setup>
import { ref, computed } from 'vue';
import { useForm, Head, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import BentoStatCard from '@/Components/BentoStatCard.vue';
import TrendChart from '@/Components/TrendChart.vue';
import CategoryProgressWidget from '@/Components/CategoryProgressWidget.vue';
import { 
    Clock, 
    Play, 
    Lock, 
    CheckCircle2, 
    Calendar,
    ArrowRight,
    KeyRound,
    Trophy,
    ShieldCheck,
    BookOpen,
    X,
    Wifi,
    Download
} from 'lucide-vue-next';

const props = defineProps({
    activeExams: {
        type: Array,
        default: () => [],
    },
    school: {
        type: Object,
        default: () => ({}),
    },
    chartData: {
        type: Object,
        default: () => null,
    },
    categoryData: {
        type: Array,
        default: () => null,
    },
});

const page = usePage();
const auth = computed(() => page.props.auth?.user);

const showTokenModal = ref(false);
const selectedExam = ref(null);
const activeTab = ref('all'); // 'all', 'pending', 'completed'

const tokenForm = useForm({
    token: '',
});

const openTokenModal = (exam) => {
    selectedExam.value = exam;
    tokenForm.reset();
    showTokenModal.value = true;
};

const closeTokenModal = () => {
    showTokenModal.value = false;
    selectedExam.value = null;
    tokenForm.reset();
};

const startExam = () => {
    if (!selectedExam.value) return;
    tokenForm.token = tokenForm.token.toUpperCase().trim();
    tokenForm.post(`/student/exam/${selectedExam.value.session_id}/start`, {
        onSuccess: () => {
            showTokenModal.value = false;
        },
    });
};

const stats = computed(() => {
    const exams = props.activeExams || [];
    const ongoing = exams.filter(e => e.attempt_status === 'ongoing').length;
    const ready = exams.filter(e => e.attempt_status === 'ready').length;
    const completed = exams.filter(e => e.attempt_status === 'submitted' || e.attempt_status === 'graded').length;
    return {
        total: exams.length,
        pending: ongoing + ready,
        ongoing,
        ready,
        completed,
    };
});

const filteredExams = computed(() => {
    const exams = props.activeExams || [];
    if (activeTab.value === 'pending') {
        return exams.filter(e => e.attempt_status === 'ready' || e.attempt_status === 'ongoing');
    }
    if (activeTab.value === 'completed') {
        return exams.filter(e => e.attempt_status === 'submitted' || e.attempt_status === 'graded');
    }
    return exams;
});
</script>

<template>
    <AppLayout>
        <Head title="Portal Ujian Siswa" />

        <div class="space-y-6 w-full">
            <!-- Page Header matching contoh.jpg (Title on Left, Actions on Right) -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                        Dashboard Overview
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        Halo, {{ auth?.name }}! Selamat datang di portal ujian resmi {{ school?.school_name || 'EduPro' }}.
                    </p>
                </div>

                <div class="flex items-center gap-3 shrink-0">
                    <!-- Wifi Status Pill -->
                    <div class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-full text-xs font-semibold text-slate-700 dark:text-slate-200 shadow-2xs">
                        <Wifi class="w-3.5 h-3.5 text-emerald-500" />
                        <span>Koneksi CBT Stabil</span>
                    </div>

                    <!-- Room Badge Pill -->
                    <div class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-blue-600 text-white rounded-full text-xs font-semibold shadow-sm shadow-blue-600/25">
                        <Clock class="w-4 h-4" />
                        <span>{{ auth?.exam_room_name || 'Ruang Standar' }}</span>
                    </div>
                </div>
            </div>

            <!-- 4 Bento Stat Cards Grid matching contoh.jpg -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Card 1: Total Sesi (Pastel Blue) -->
                <BentoStatCard
                    title="Total Sesi Ujian"
                    :value="stats.total"
                    trend="+12%"
                    trend-type="up"
                    icon-bg="bg-blue-50 dark:bg-blue-950/60"
                    icon-color="text-blue-600 dark:text-blue-400"
                >
                    <template #icon>
                        <Calendar class="w-5 h-5" />
                    </template>
                </BentoStatCard>

                <!-- Card 2: Ujian Tersedia (Pastel Amber) -->
                <BentoStatCard
                    title="Ujian Tersedia"
                    :value="stats.pending"
                    trend="+5%"
                    trend-type="up"
                    icon-bg="bg-amber-50 dark:bg-amber-950/60"
                    icon-color="text-amber-600 dark:text-amber-400"
                >
                    <template #icon>
                        <BookOpen class="w-5 h-5" />
                    </template>
                </BentoStatCard>

                <!-- Card 3: Ujian Selesai (Pastel Emerald) -->
                <BentoStatCard
                    title="Ujian Selesai"
                    :value="stats.completed"
                    trend="+8%"
                    trend-type="up"
                    icon-bg="bg-emerald-50 dark:bg-emerald-950/60"
                    icon-color="text-emerald-600 dark:text-emerald-400"
                >
                    <template #icon>
                        <CheckCircle2 class="w-5 h-5" />
                    </template>
                </BentoStatCard>

                <!-- Card 4: Sedang Berjalan (Pastel Sky) -->
                <BentoStatCard
                    title="Sedang Berjalan"
                    :value="stats.ongoing"
                    trend="+2%"
                    trend-type="up"
                    icon-bg="bg-sky-50 dark:bg-sky-950/60"
                    icon-color="text-sky-600 dark:text-sky-400"
                >
                    <template #icon>
                        <Play class="w-5 h-5" />
                    </template>
                </BentoStatCard>
            </div>

            <!-- Two-Column Section matching contoh.jpg (Trends Chart on Left, Categories on Right) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                <!-- Left: Exam Attendance & Score Trends (approx 65% width) -->
                <div class="lg:col-span-2">
                    <TrendChart 
                        title="Enrollment & Performance Trends"
                        subtitle="Grafik konsistensi pengerjaan dan skor rata-rata ujian"
                        :filter-options="['This Year', 'Last 6 Months', 'This Month']"
                        :data-sets="chartData"
                    />
                </div>

                <!-- Right: Subject Categories (approx 35% width) -->
                <div class="lg:col-span-1">
                    <CategoryProgressWidget 
                        title="Top Performing Categories"
                        subtitle="Capaian nilai menurut rumpun mata pelajaran"
                        :categories="categoryData"
                    />
                </div>
            </div>

            <!-- Active Exams List Section (EduPro Style) -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm space-y-5">
                <!-- Header with Filter Tabs -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white tracking-tight">
                            Daftar Jadwal Ujian Aktif
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Pilih jadwal ujian dan masukkan token ruang dari pengawas
                        </p>
                    </div>

                    <!-- Filter Tabs Pills -->
                    <div class="flex items-center p-1 bg-slate-50 dark:bg-slate-800 rounded-full text-xs font-semibold gap-1">
                        <button
                            type="button"
                            @click="activeTab = 'all'"
                            :class="[
                                'px-4 py-1.5 rounded-full transition-all cursor-pointer',
                                activeTab === 'all'
                                    ? 'bg-blue-600 text-white shadow-2xs'
                                    : 'text-slate-500 hover:text-slate-900 dark:hover:text-white'
                            ]"
                        >
                            Semua ({{ stats.total }})
                        </button>
                        <button
                            type="button"
                            @click="activeTab = 'pending'"
                            :class="[
                                'px-4 py-1.5 rounded-full transition-all cursor-pointer',
                                activeTab === 'pending'
                                    ? 'bg-blue-600 text-white shadow-2xs'
                                    : 'text-slate-500 hover:text-slate-900 dark:hover:text-white'
                            ]"
                        >
                            Tersedia ({{ stats.pending }})
                        </button>
                        <button
                            type="button"
                            @click="activeTab = 'completed'"
                            :class="[
                                'px-4 py-1.5 rounded-full transition-all cursor-pointer',
                                activeTab === 'completed'
                                    ? 'bg-blue-600 text-white shadow-2xs'
                                    : 'text-slate-500 hover:text-slate-900 dark:hover:text-white'
                            ]"
                        >
                            Selesai ({{ stats.completed }})
                        </button>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="filteredExams.length === 0" class="text-center py-12 text-xs text-slate-400 dark:text-slate-500 bg-slate-50 dark:bg-slate-800/40 rounded-2xl border border-dashed border-slate-200 dark:border-slate-800">
                    <Calendar class="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto mb-2" />
                    <p class="font-bold text-slate-700 dark:text-slate-200 text-sm">Tidak ada jadwal ujian pada kategori ini</p>
                    <p class="text-slate-400 mt-1">Silakan hubungi proktor atau pengawas jika sesi belum terjadwal.</p>
                </div>

                <!-- Exams List (Clean Rounded Cards) -->
                <div v-else class="space-y-3.5">
                    <div
                        v-for="exam in filteredExams"
                        :key="exam.session_id"
                        class="p-5 bg-slate-50/70 dark:bg-slate-800/40 hover:bg-slate-50 dark:hover:bg-slate-800 border border-slate-100 dark:border-slate-800 rounded-2xl flex flex-col md:flex-row md:items-center justify-between gap-4 transition-all"
                    >
                        <div class="space-y-2 min-w-0 flex-1">
                            <!-- Category and Status Row -->
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-3 py-1 rounded-full text-[10px] font-mono font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 uppercase">
                                    {{ exam.subject_name }}
                                </span>
                                <span class="text-xs text-slate-400 font-medium">
                                    {{ exam.session_name }} &bull; {{ exam.room_name }}
                                </span>

                                <span v-if="exam.is_locked" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-500 text-white flex items-center gap-1">
                                    <Lock class="w-3 h-3" />
                                    <span>Layar Terkunci</span>
                                </span>
                                <span v-else-if="exam.attempt_status === 'ongoing'" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    <span>Sedang Dikerjakan</span>
                                </span>
                                <span v-else-if="exam.attempt_status === 'submitted' || exam.attempt_status === 'graded'" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 flex items-center gap-1">
                                    <CheckCircle2 class="w-3 h-3 text-emerald-500" />
                                    <span>Sudah Selesai</span>
                                </span>
                                <span v-else class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300">
                                    Siap Dimulai
                                </span>
                            </div>

                            <!-- Exam Title -->
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white tracking-tight">
                                {{ exam.schedule_title }}
                            </h3>

                            <!-- Meta Details -->
                            <div class="flex flex-wrap items-center gap-3 text-xs text-slate-400">
                                <span class="flex items-center gap-1.5">
                                    <Clock class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" />
                                    <span>Durasi: <strong class="text-slate-700 dark:text-slate-200">{{ exam.duration_minutes }} Menit</strong></span>
                                </span>
                                <span>&bull;</span>
                                <span>Minimal: <strong class="text-slate-700 dark:text-slate-200">{{ exam.min_submit_minutes }} Menit</strong></span>
                                <span v-if="exam.show_score && (exam.attempt_status === 'submitted' || exam.attempt_status === 'graded')" class="flex items-center gap-1 text-emerald-600 font-bold ml-1">
                                    <Trophy class="w-3.5 h-3.5 text-emerald-500" />
                                    <span>Skor: {{ exam.final_score ?? '-' }}</span>
                                </span>
                            </div>
                        </div>

                        <!-- Action Button Based on Status -->
                        <div class="shrink-0 flex items-center gap-2">
                            <!-- Locked -->
                            <div v-if="exam.is_locked">
                                <span class="px-5 py-2.5 bg-rose-50 text-rose-600 text-xs font-bold rounded-full flex items-center gap-1.5">
                                    <Lock class="w-3.5 h-3.5" />
                                    <span>Hubungi Pengawas</span>
                                </span>
                            </div>

                            <!-- Finished -->
                            <div v-else-if="exam.attempt_status === 'submitted' || exam.attempt_status === 'graded'">
                                <a
                                    :href="`/student/exam/${exam.attempt_id}/result`"
                                    class="px-5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 text-slate-700 dark:text-slate-200 font-semibold text-xs rounded-full shadow-2xs inline-flex items-center gap-2 transition-all cursor-pointer"
                                >
                                    <CheckCircle2 class="w-3.5 h-3.5 text-emerald-500" />
                                    <span>Lihat Hasil</span>
                                </a>
                            </div>

                            <!-- Ongoing (Resume) -->
                            <div v-else-if="exam.attempt_status === 'ongoing'">
                                <a
                                    :href="`/student/exam/${exam.attempt_id}`"
                                    class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-full shadow-sm inline-flex items-center gap-2 transition-all cursor-pointer"
                                >
                                    <Play class="w-3.5 h-3.5 fill-current" />
                                    <span>Lanjutkan Tes</span>
                                </a>
                            </div>

                            <!-- Ready to Start -->
                            <div v-else>
                                <button
                                    type="button"
                                    @click="openTokenModal(exam)"
                                    class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-full shadow-sm inline-flex items-center gap-2 transition-all cursor-pointer"
                                >
                                    <span>Mulai Ujian</span>
                                    <ArrowRight class="w-3.5 h-3.5" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Token Input Modal -->
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
                    v-if="showTokenModal"
                    @click="closeTokenModal"
                    class="fixed inset-0 bg-slate-900/40 dark:bg-slate-950/70 backdrop-blur-xs flex items-center justify-center p-4 z-50 overflow-y-auto"
                >
                    <div
                        @click.stop
                        class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 max-w-sm w-full p-6 sm:p-7 shadow-2xl text-center space-y-4 my-auto relative"
                    >
                        <div class="flex justify-end">
                            <button 
                                type="button"
                                @click="closeTokenModal" 
                                class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                                aria-label="Tutup"
                            >
                                <X class="w-4 h-4" />
                            </button>
                        </div>

                        <div class="w-14 h-14 rounded-3xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center mx-auto shadow-sm shadow-blue-500/10">
                            <KeyRound class="w-7 h-7" />
                        </div>

                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white tracking-tight">
                                Konfirmasi Token Ujian
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-medium">
                                {{ selectedExam?.schedule_title }} &bull; {{ selectedExam?.room_name }}
                            </p>
                        </div>

                        <form @submit.prevent="startExam" class="space-y-4">
                            <div>
                                <input
                                    v-model="tokenForm.token"
                                    type="text"
                                    placeholder="CONTOH: SMART1"
                                    maxlength="10"
                                    required
                                    autofocus
                                    class="w-full text-center text-2xl font-black font-mono tracking-widest uppercase p-3 border border-slate-200 dark:border-slate-700 rounded-2xl focus:bg-white dark:focus:bg-slate-800 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 focus:outline-none text-slate-900 dark:text-white bg-slate-50/80 dark:bg-slate-800/60 cursor-text transition-all"
                                />
                                <p v-if="tokenForm.errors.token" class="text-xs text-rose-500 font-semibold mt-1.5">
                                    {{ tokenForm.errors.token }}
                                </p>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1.5">
                                    Mintalah token ruangan kepada Pengawas Ruang Anda.
                                </p>
                            </div>

                            <div class="flex items-center gap-3 pt-2">
                                <button 
                                    type="button" 
                                    @click="closeTokenModal" 
                                    class="flex-1 py-2.5 border border-slate-200 dark:border-slate-700 text-xs font-semibold rounded-full text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-750 transition-all cursor-pointer"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    :disabled="tokenForm.processing || !tokenForm.token"
                                    class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-full shadow-sm shadow-blue-600/25 disabled:opacity-50 transition-all cursor-pointer"
                                >
                                    {{ tokenForm.processing ? 'Memverifikasi...' : 'Masuk Ujian' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>
