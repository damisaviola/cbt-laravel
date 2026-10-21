<script setup>
import { ref, computed } from 'vue';
import { useForm, Head, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { 
    Clock, 
    Play, 
    Lock, 
    CheckCircle2, 
    Calendar,
    ArrowRight,
    KeyRound,
    AlertCircle,
    Sparkles,
    Trophy,
    ShieldCheck,
    BookOpen,
    Layers,
    Info,
    X,
    Wifi
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

        <template #header>
            <div class="flex items-center justify-between w-full min-w-0">
                <div class="min-w-0">
                    <h1 class="text-sm sm:text-base font-bold text-slate-900 dark:text-slate-100 tracking-tight flex items-center gap-2 truncate">
                        <span>Portal Ujian Siswa</span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-600 animate-pulse"></span>
                            Live CBT
                        </span>
                    </h1>
                    <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-0.5 truncate sm:whitespace-normal">
                        Pilih jadwal ujian aktif dan masukkan token ruang dari pengawas.
                    </p>
                </div>
            </div>
        </template>

        <div class="space-y-6 w-full">
            <!-- Student Hero Identity & Quick Metrics -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <!-- Identity Card (2 Cols on Desktop) -->
                <div class="lg:col-span-2 bg-gradient-to-br from-white via-blue-50/20 to-indigo-50/30 dark:from-slate-900 dark:via-slate-900 dark:to-blue-950/20 p-5 sm:p-6 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-5 relative overflow-hidden">
                    <div class="flex items-start gap-4 min-w-0">
                        <!-- Student Avatar -->
                        <div class="w-13 h-13 rounded-2xl bg-gradient-to-tr from-[#0F52BA] to-blue-600 text-white font-bold text-lg flex items-center justify-center shrink-0 shadow-md shadow-blue-600/20">
                            {{ auth?.name ? auth.name.charAt(0).toUpperCase() : 'S' }}
                        </div>

                        <div class="space-y-1.5 min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Peserta Ujian Aktif
                                </span>
                                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium truncate">
                                    {{ school?.school_name || 'SmartCBT School' }}
                                </span>
                            </div>

                            <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-slate-100 tracking-tight truncate">
                                Halo, {{ auth?.name }}! 👋
                            </h2>

                            <div class="flex flex-wrap items-center gap-2 pt-1 text-xs">
                                <span class="px-2.5 py-1 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-mono text-[11px] shadow-2xs">
                                    NISN: <strong class="text-slate-900 dark:text-slate-100">{{ auth?.username }}</strong>
                                </span>
                                <span v-if="auth?.class_room" class="px-2.5 py-1 rounded-xl bg-purple-50 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-800/60 text-purple-700 dark:text-purple-300 text-[11px] font-medium">
                                    Kelas: <strong>{{ auth?.class_room?.name }}</strong>
                                </span>
                                <span class="px-2.5 py-1 rounded-xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800/60 text-blue-700 dark:text-blue-300 text-[11px] font-medium">
                                    Ruang: <strong>{{ auth?.exam_room_name || 'Ruang Standar' }}</strong>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Exam Status Counter (1 Col on Desktop) -->
                <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex flex-col justify-between gap-3">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                        Ringkasan Ujian Hari Ini
                    </span>

                    <div class="grid grid-cols-3 gap-2 text-center">
                        <div class="p-2.5 rounded-xl bg-blue-50/70 dark:bg-blue-950/40 border border-blue-100 dark:border-blue-900/60">
                            <span class="text-lg sm:text-xl font-black font-mono text-blue-700 dark:text-blue-400 block tabular-nums">
                                {{ stats.total }}
                            </span>
                            <span class="text-[10px] font-semibold text-slate-600 dark:text-slate-300 block mt-0.5">Total Sesi</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-amber-50/70 dark:bg-amber-950/40 border border-amber-100 dark:border-amber-900/60">
                            <span class="text-lg sm:text-xl font-black font-mono text-amber-700 dark:text-amber-400 block tabular-nums">
                                {{ stats.pending }}
                            </span>
                            <span class="text-[10px] font-semibold text-slate-600 dark:text-slate-300 block mt-0.5">Tersedia</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-emerald-50/70 dark:bg-emerald-950/40 border border-emerald-100 dark:border-emerald-900/60">
                            <span class="text-lg sm:text-xl font-black font-mono text-emerald-700 dark:text-emerald-400 block tabular-nums">
                                {{ stats.completed }}
                            </span>
                            <span class="text-[10px] font-semibold text-slate-600 dark:text-slate-300 block mt-0.5">Selesai</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <span class="flex items-center gap-1.5">
                            <Wifi class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" />
                            <span>Koneksi CBT Terhubung</span>
                        </span>
                        <span class="font-mono text-emerald-700 dark:text-emerald-300 font-bold bg-emerald-50 dark:bg-emerald-950/50 px-2 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-800 text-[10px]">
                            Stabil
                        </span>
                    </div>
                </div>
            </div>

            <!-- Petunjuk Singkat CBT (Clean, Modern Information Card) -->
            <div class="p-4 sm:p-4.5 bg-gradient-to-r from-blue-50/60 via-slate-50 to-indigo-50/40 dark:from-slate-900 dark:via-slate-900/90 dark:to-indigo-950/20 border border-blue-100 dark:border-slate-800 rounded-2xl shadow-2xs">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-[#0F52BA] text-white flex items-center justify-center shrink-0 shadow-xs">
                            <ShieldCheck class="w-4.5 h-4.5" />
                        </div>
                        <div>
                            <h3 class="text-xs font-bold text-slate-900 dark:text-slate-100">
                                Tata Tertib & Ketentuan Ujian CBT
                            </h3>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                Patuhi prosedur demi kelancaran dan integritas pengerjaan ujian Anda.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-[11px] text-slate-600 dark:text-slate-300 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-200 dark:border-slate-800">
                        <div class="flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500 shrink-0"></span>
                            <span>Koneksi internet stabil</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                            <span>Minta token ke pengawas</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 shrink-0"></span>
                            <span>Dilarang buka tab lain</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Exams List Section -->
            <div class="space-y-4">
                <!-- Section Header with Filter Tabs -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">
                            Daftar Jadwal Ujian
                        </h2>
                        <span class="px-2 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-full text-[11px] font-bold font-mono">
                            {{ activeExams.length }} Sesi
                        </span>
                    </div>

                    <!-- Filter Tabs -->
                    <div class="flex items-center p-1 bg-slate-100 dark:bg-slate-800/80 rounded-xl text-xs font-semibold gap-1">
                        <button
                            type="button"
                            @click="activeTab = 'all'"
                            :class="[
                                'px-3 py-1.5 rounded-lg transition-all cursor-pointer',
                                activeTab === 'all'
                                    ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 shadow-2xs'
                                    : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100'
                            ]"
                        >
                            Semua ({{ stats.total }})
                        </button>
                        <button
                            type="button"
                            @click="activeTab = 'pending'"
                            :class="[
                                'px-3 py-1.5 rounded-lg transition-all cursor-pointer',
                                activeTab === 'pending'
                                    ? 'bg-white dark:bg-slate-700 text-blue-700 dark:text-blue-300 shadow-2xs'
                                    : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100'
                            ]"
                        >
                            Tersedia ({{ stats.pending }})
                        </button>
                        <button
                            type="button"
                            @click="activeTab = 'completed'"
                            :class="[
                                'px-3 py-1.5 rounded-lg transition-all cursor-pointer',
                                activeTab === 'completed'
                                    ? 'bg-white dark:bg-slate-700 text-emerald-700 dark:text-emerald-300 shadow-2xs'
                                    : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100'
                            ]"
                        >
                            Selesai ({{ stats.completed }})
                        </button>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="filteredExams.length === 0" class="bg-white dark:bg-slate-900 p-12 rounded-2xl border border-dashed border-slate-200 dark:border-slate-800 text-center text-xs text-slate-500 dark:text-slate-400 shadow-xs">
                    <Calendar class="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto mb-2" />
                    <p class="font-bold text-slate-700 dark:text-slate-200 text-sm">Tidak ada jadwal ujian pada kategori ini</p>
                    <p class="text-slate-400 dark:text-slate-500 mt-1 max-w-sm mx-auto">
                        Silakan hubungi proktor atau pengawas ruang jika sesi ujian Anda belum dijadwalkan.
                    </p>
                </div>

                <!-- Exams List (Modern Rounded-2xl Cards) -->
                <div v-else class="space-y-3.5">
                    <div
                        v-for="exam in filteredExams"
                        :key="exam.session_id"
                        :class="[
                            'bg-white dark:bg-slate-900 rounded-2xl p-5 sm:p-6 transition-all duration-200 flex flex-col md:flex-row md:items-center justify-between gap-5 border shadow-xs hover:shadow-md',
                            exam.is_locked 
                                ? 'border-l-4 border-l-rose-500 border-rose-200 dark:border-rose-900/60 bg-rose-50/10 dark:bg-rose-950/10' 
                                : exam.attempt_status === 'ongoing' 
                                ? 'border-l-4 border-l-blue-600 border-blue-200 dark:border-blue-900/60 bg-blue-50/10 dark:bg-blue-950/10' 
                                : (exam.attempt_status === 'submitted' || exam.attempt_status === 'graded')
                                ? 'border-l-4 border-l-emerald-500 border-emerald-200 dark:border-emerald-900/60 bg-emerald-50/10 dark:bg-emerald-950/10'
                                : 'border-l-4 border-l-indigo-600 border-slate-200 dark:border-slate-800 hover:border-blue-300 dark:hover:border-blue-700'
                        ]"
                    >
                        <div class="space-y-2.5 min-w-0 flex-1">
                            <!-- Category and Status Row -->
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-mono font-bold bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 uppercase">
                                    {{ exam.subject_name }}
                                </span>
                                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                                    {{ exam.session_name }} &bull; {{ exam.room_name }}
                                </span>

                                <span v-if="exam.is_locked" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-600 text-white flex items-center gap-1 shadow-xs shadow-rose-600/20">
                                    <Lock class="w-3 h-3" />
                                    <span>Layar Terkunci</span>
                                </span>
                                <span v-else-if="exam.attempt_status === 'ongoing'" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 dark:bg-blue-950/60 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-800 flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-600 animate-pulse"></span>
                                    <span>Sedang Dikerjakan</span>
                                </span>
                                <span v-else-if="exam.attempt_status === 'submitted' || exam.attempt_status === 'graded'" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 flex items-center gap-1">
                                    <CheckCircle2 class="w-3 h-3 text-emerald-600 dark:text-emerald-400" />
                                    <span>Sudah Selesai</span>
                                </span>
                                <span v-else class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                    Siap Dimulai
                                </span>
                            </div>

                            <!-- Exam Title -->
                            <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-slate-100 tracking-tight">
                                {{ exam.schedule_title }}
                            </h3>

                            <!-- Meta Pills (Duration, Min Submit, Score) -->
                            <div class="flex flex-wrap items-center gap-2.5 text-xs text-slate-500 dark:text-slate-400 font-mono">
                                <span class="flex items-center gap-1.5 bg-slate-50 dark:bg-slate-800/80 px-2.5 py-1 rounded-lg border border-slate-200 dark:border-slate-700">
                                    <Clock class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" />
                                    <span>Durasi: <strong class="text-slate-800 dark:text-slate-200">{{ exam.duration_minutes }} Menit</strong></span>
                                </span>
                                <span class="flex items-center gap-1.5 bg-slate-50 dark:bg-slate-800/80 px-2.5 py-1 rounded-lg border border-slate-200 dark:border-slate-700">
                                    <span>Minimal: <strong class="text-slate-800 dark:text-slate-200">{{ exam.min_submit_minutes }} Menit</strong></span>
                                </span>
                                <span v-if="exam.show_score && (exam.attempt_status === 'submitted' || exam.attempt_status === 'graded')" class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 font-bold">
                                    <Trophy class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" />
                                    <span>Skor: {{ exam.final_score ?? '-' }}</span>
                                </span>
                            </div>
                        </div>

                        <!-- Action Button Based on Status -->
                        <div class="shrink-0 flex items-center gap-2 pt-2 md:pt-0">
                            <!-- Locked -->
                            <div v-if="exam.is_locked">
                                <span class="px-4 py-2.5 bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border border-rose-300 dark:border-rose-800 text-xs font-bold rounded-xl flex items-center gap-1.5 shadow-xs">
                                    <Lock class="w-3.5 h-3.5" />
                                    <span>Hubungi Pengawas</span>
                                </span>
                            </div>

                            <!-- Finished -->
                            <div v-else-if="exam.attempt_status === 'submitted' || exam.attempt_status === 'graded'">
                                <a
                                    :href="`/student/exam/${exam.attempt_id}/result`"
                                    class="px-4.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-750 active:scale-[0.98] text-slate-700 dark:text-slate-200 hover:text-emerald-800 dark:hover:text-emerald-400 font-semibold text-xs rounded-xl shadow-2xs hover:shadow-xs inline-flex items-center gap-2 transition-all"
                                >
                                    <CheckCircle2 class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" />
                                    <span>Lihat Hasil Ujian</span>
                                </a>
                            </div>

                            <!-- Ongoing (Resume) -->
                            <div v-else-if="exam.attempt_status === 'ongoing'">
                                <a
                                    :href="`/student/exam/${exam.attempt_id}`"
                                    class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 active:scale-[0.98] text-white font-semibold text-xs rounded-xl shadow-xs hover:shadow-md inline-flex items-center gap-2 transition-all animate-pulse"
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
                                    class="px-5 py-2.5 bg-[#0F52BA] hover:bg-blue-700 active:scale-[0.98] text-white font-semibold text-xs rounded-xl shadow-xs hover:shadow-md inline-flex items-center gap-2 transition-all cursor-pointer"
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

        <!-- Token Input Modal (Lightweight & Smooth) -->
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
                    class="fixed inset-0 bg-slate-900/50 dark:bg-slate-950/70 flex items-center justify-center p-4 z-50 overflow-y-auto"
                >
                    <div
                        @click.stop
                        class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 max-w-sm w-full p-6 shadow-xl text-center space-y-4 my-auto transform-gpu"
                    >
                        <div class="flex justify-end">
                            <button 
                                type="button"
                                @click="closeTokenModal"
                                class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer transition-colors"
                                aria-label="Tutup"
                            >
                                <X class="w-4 h-4" />
                            </button>
                        </div>

                        <div class="w-13 h-13 rounded-2xl bg-gradient-to-br from-[#0F52BA] to-indigo-600 text-white flex items-center justify-center mx-auto shadow-md shadow-blue-500/25">
                            <KeyRound class="w-6 h-6" />
                        </div>

                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">
                                Konfirmasi Token Ruang Ujian
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
                                    placeholder="Contoh: SMART1"
                                    maxlength="10"
                                    required
                                    autofocus
                                    class="w-full text-center text-2xl font-black font-mono tracking-widest uppercase p-3 border-2 border-slate-200 dark:border-slate-700 rounded-xl focus:border-[#0F52BA] dark:focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:focus:ring-blue-900/30 focus:outline-none text-slate-900 dark:text-slate-100 cursor-text transition-colors bg-white dark:bg-slate-800 caret-[#0F52BA]"
                                />
                                <p v-if="tokenForm.errors.token" class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-1.5">
                                    {{ tokenForm.errors.token }}
                                </p>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1.5">
                                    Mintalah token 6-karakter kepada Pengawas Ruang.
                                </p>
                            </div>

                            <div class="flex items-center gap-2 pt-1">
                                <button 
                                    type="button" 
                                    @click="closeTokenModal" 
                                    class="flex-1 py-2.5 border border-slate-200 dark:border-slate-700 text-xs font-semibold rounded-xl text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 active:scale-[0.98] transition-all cursor-pointer"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    :disabled="tokenForm.processing || !tokenForm.token"
                                    class="flex-1 py-2.5 bg-[#0F52BA] hover:bg-blue-700 active:scale-[0.98] text-white text-xs font-semibold rounded-xl shadow-xs hover:shadow-md disabled:opacity-50 transition-all cursor-pointer"
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
