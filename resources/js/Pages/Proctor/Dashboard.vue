<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useForm, router, Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import { 
    ShieldCheck, 
    KeyRound, 
    Lock, 
    Unlock, 
    RotateCcw, 
    FileText, 
    Copy, 
    Check, 
    Maximize2, 
    Minimize2, 
    Search, 
    LayoutGrid, 
    List, 
    Monitor,
    Eye,
    AlertTriangle,
    RefreshCw,
    X,
    Laptop,
    CheckCircle2,
    Clock,
    UserCheck
} from 'lucide-vue-next';

const tableColumns = [
    { key: 'username', label: 'NISN', sortable: true },
    { key: 'name', label: 'Nama Siswa', sortable: true },
    { key: 'class_name', label: 'Kelas', sortable: true },
    { key: 'status', label: 'Status Ujian', align: 'center', sortable: true },
    { key: 'answered_count', label: 'Progres Soal', align: 'center', sortable: true },
    { key: 'cheat_strikes', label: 'Pelanggaran', align: 'center', sortable: true },
    { key: 'actions', label: 'Aksi Pengawas', align: 'right', sortable: false },
];

const props = defineProps({
    sessions: Array,
    selectedSession: Object,
    studentsMatrix: Array,
    school: Object,
});

const currentSessionId = ref(props.selectedSession?.id || '');
const notes = ref(props.selectedSession?.proctor_notes || '');
const copiedToken = ref(false);
const fullscreenProjector = ref(false);
const searchQuery = ref('');
const statusFilter = ref('all'); // all, ongoing, locked, completed, not_started
const viewMode = ref('table'); // table, grid

// Detail Modal state
const selectedStudent = ref(null);
const showDetailModal = ref(false);

const openStudentDetail = (std) => {
    selectedStudent.value = std;
    showDetailModal.value = true;
};

const closeStudentDetail = () => {
    showDetailModal.value = false;
    selectedStudent.value = null;
};

// Auto refresh polling
const autoRefresh = ref(true);
const isRefreshing = ref(false);
let refreshInterval = null;

const refreshData = () => {
    isRefreshing.value = true;
    router.reload({
        only: ['studentsMatrix'],
        preserveScroll: true,
        preserveState: true,
        onFinish: () => {
            isRefreshing.value = false;
            // Update selectedStudent if modal is open
            if (selectedStudent.value && props.studentsMatrix) {
                const updated = props.studentsMatrix.find(s => s.student_id === selectedStudent.value.student_id);
                if (updated) selectedStudent.value = updated;
            }
        }
    });
};

onMounted(() => {
    refreshInterval = setInterval(() => {
        if (autoRefresh.value && !showDetailModal.value) {
            refreshData();
        }
    }, 8000);
});

onUnmounted(() => {
    if (refreshInterval) clearInterval(refreshInterval);
});

const switchSession = () => {
    router.get('/proctor/dashboard', { session_id: currentSessionId.value }, { preserveState: true });
};

const copyToken = () => {
    if (!props.selectedSession?.access_token) return;
    navigator.clipboard.writeText(props.selectedSession.access_token);
    copiedToken.value = true;
    setTimeout(() => {
        copiedToken.value = false;
    }, 2000);
};

import { useConfirm } from '@/Composables/useConfirm';

const { confirm } = useConfirm();

const unlockStudent = async (attemptId) => {
    const ok = await confirm({
        title: 'Buka Kunci Layar Siswa',
        message: 'Apakah Anda yakin ingin membuka kunci layar ujian untuk siswa ini? Siswa akan dapat langsung melanjutkan pengerjaan tes.',
        confirmText: 'Ya, Buka Kunci',
        cancelText: 'Batal',
        type: 'warning',
    });
    if (ok) {
        router.post(`/proctor/attempt/${attemptId}/unlock`, {}, { 
            preserveScroll: true,
            onSuccess: () => {
                if (selectedStudent.value && selectedStudent.value.attempt_id === attemptId) {
                    selectedStudent.value.is_locked = false;
                    selectedStudent.value.cheat_strikes = 0;
                }
            }
        });
    }
};

const unlockAll = async () => {
    if (!props.selectedSession) return;
    const ok = await confirm({
        title: 'Buka Kunci Semua Siswa Terkunci',
        message: `Apakah Anda yakin ingin membuka kunci semua (${lockedCount.value}) siswa yang saat ini terkunci di ruangan ini?`,
        confirmText: 'Ya, Buka Kunci Semua',
        cancelText: 'Batal',
        type: 'warning',
    });
    if (ok) {
        router.post(`/proctor/session/${props.selectedSession.id}/unlock-all`, {}, { preserveScroll: true });
    }
};

const resetLogin = async (studentId) => {
    const ok = await confirm({
        title: 'Reset Sesi Login Siswa',
        message: 'Apakah Anda yakin ingin me-reset sesi login siswa ini? Siswa akan diizinkan login kembali jika perangkat komputer sempat mati atau crash.',
        confirmText: 'Ya, Reset Login',
        cancelText: 'Batal',
        type: 'warning',
    });
    if (ok) {
        router.post(`/proctor/student/${studentId}/reset-login`, {}, { preserveScroll: true });
    }
};

const saveNotes = () => {
    router.post(`/proctor/session/${props.selectedSession.id}/notes`, { proctor_notes: notes.value }, { preserveScroll: true });
};

const printBeritaAcara = () => {
    if (!props.selectedSession) return;
    window.open(`/proctor/session/${props.selectedSession.id}/print-berita-acara`, '_blank');
};

const ongoingCount = computed(() => props.studentsMatrix?.filter(s => s.status === 'ongoing' && !s.is_locked).length || 0);
const lockedCount = computed(() => props.studentsMatrix?.filter(s => s.is_locked).length || 0);
const completedCount = computed(() => props.studentsMatrix?.filter(s => s.status === 'submitted' || s.status === 'graded').length || 0);
const notStartedCount = computed(() => props.studentsMatrix?.filter(s => !s.status || s.status === 'ready' || s.status === 'not_started').length || 0);

const filteredStudents = computed(() => {
    if (!props.studentsMatrix) return [];
    return props.studentsMatrix.filter(s => {
        const matchesSearch = !searchQuery.value ||
            s.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            s.username.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            s.class_name?.toLowerCase().includes(searchQuery.value.toLowerCase());

        let matchesStatus = true;
        if (statusFilter.value === 'ongoing') {
            matchesStatus = s.status === 'ongoing' && !s.is_locked;
        } else if (statusFilter.value === 'locked') {
            matchesStatus = Boolean(s.is_locked);
        } else if (statusFilter.value === 'completed') {
            matchesStatus = s.status === 'submitted' || s.status === 'graded';
        } else if (statusFilter.value === 'not_started') {
            matchesStatus = !s.status || s.status === 'ready' || s.status === 'not_started';
        }

        return matchesSearch && matchesStatus;
    });
});
</script>

<template>
    <AppLayout>
        <Head title="Ruang Pengawas - Control Room" />

        <template #header>
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <h1 class="text-sm sm:text-base font-bold text-slate-900 dark:text-slate-100 tracking-tight truncate">
                        Ruang Pengawas Ujian
                    </h1>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 shrink-0">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Live Control
                    </span>
                </div>
                <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-0.5 truncate sm:whitespace-normal">
                    Monitor status pengerjaan siswa di ruang, rilis token proyektor, dan buka kunci layar.
                </p>
            </div>
        </template>

        <div v-if="!selectedSession" class="bg-white dark:bg-slate-900 p-12 rounded-2xl border border-dashed border-slate-300 dark:border-slate-800 text-center text-xs text-slate-500 dark:text-slate-400 shadow-xs">
            <ShieldCheck class="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto mb-2" />
            <p class="font-semibold text-slate-700 dark:text-slate-300">Tidak ada sesi ujian yang sedang aktif saat ini</p>
            <p class="text-slate-400 dark:text-slate-500 mt-1">Silakan pilih jadwal yang aktif pada menu Sesi Ujian di panel administrator.</p>
        </div>

        <div v-else class="space-y-6">
            <!-- Room & Session Control Bar -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 p-4 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-3.5">
                <div class="flex flex-col sm:flex-row sm:items-center gap-3 min-w-0">
                    <div class="flex items-center gap-2 shrink-0">
                        <div class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800 text-[#0F52BA] dark:text-blue-400 flex items-center justify-center shrink-0">
                            <Monitor class="w-4 h-4" />
                        </div>
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                            Pilih Ruang & Sesi:
                        </span>
                    </div>

                    <div class="relative min-w-[280px] sm:min-w-[340px] max-w-md">
                        <select
                            v-model="currentSessionId"
                            @change="switchSession"
                            class="w-full text-xs font-semibold py-2 pl-3.5 pr-8 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-200 focus:border-[#0F52BA] dark:focus:border-blue-500 focus:outline-none shadow-2xs cursor-pointer transition-all"
                        >
                            <option v-for="s in sessions" :key="s.id" :value="s.id">
                                {{ s.session_name }} &bull; {{ s.room_name }} ({{ s.exam_schedule?.subject?.code || s.exam_schedule?.subject?.name }})
                            </option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-2 self-end sm:self-auto shrink-0">
                    <button
                        @click="refreshData"
                        :disabled="isRefreshing"
                        class="px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-blue-300 dark:hover:border-blue-700 hover:bg-blue-50/50 dark:hover:bg-blue-950/40 text-slate-700 dark:text-slate-300 hover:text-[#0F52BA] dark:hover:text-blue-400 font-semibold text-xs rounded-xl shadow-2xs flex items-center gap-1.5 transition-all cursor-pointer"
                        title="Segarkan data siswa"
                    >
                        <RefreshCw :class="{'animate-spin text-blue-600 dark:text-blue-400': isRefreshing}" class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" />
                        <span>{{ isRefreshing ? 'Menyegarkan...' : 'Segarkan' }}</span>
                    </button>

                    <button
                        @click="printBeritaAcara"
                        class="px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-emerald-300 dark:hover:border-emerald-700 hover:bg-emerald-50/50 dark:hover:bg-emerald-950/40 text-slate-700 dark:text-slate-300 hover:text-emerald-800 dark:hover:text-emerald-400 font-semibold text-xs rounded-xl shadow-2xs flex items-center gap-1.5 transition-all cursor-pointer"
                    >
                        <FileText class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" />
                        <span>Cetak Berita Acara</span>
                    </button>
                </div>
            </div>
            <!-- Top Control Bar: Big Token Card + Metadata + Notes (All Rounded-2xl) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Big Projector Token Card (Clean, High Contrast, Rounded-2xl) -->
                <div class="bg-gradient-to-br from-white via-blue-50/30 to-indigo-50/40 dark:from-slate-900 dark:via-slate-900 dark:to-blue-950/30 p-5 rounded-2xl border-2 border-[#0F52BA] dark:border-blue-500 flex flex-col justify-between items-center text-center shadow-xs">
                    <div class="w-full flex items-center justify-between text-slate-500 dark:text-slate-400">
                        <span class="text-[10px] font-bold uppercase tracking-widest text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-950/60 px-2 py-0.5 rounded-md border border-indigo-200 dark:border-indigo-800">
                            Token Proyektor Ruang
                        </span>
                        <div class="flex items-center gap-1.5">
                            <button
                                @click="copyToken"
                                class="px-2.5 py-1 text-[11px] text-slate-600 dark:text-slate-400 hover:text-[#0F52BA] dark:hover:text-blue-400 hover:bg-white dark:hover:bg-slate-800 rounded-lg transition-colors flex items-center gap-1 border border-transparent hover:border-slate-200 dark:hover:border-slate-700 cursor-pointer"
                                title="Salin Token"
                            >
                                <Check v-if="copiedToken" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" />
                                <Copy v-else class="w-3.5 h-3.5" />
                                <span>{{ copiedToken ? 'Tersalin' : 'Salin' }}</span>
                            </button>

                            <button
                                @click="fullscreenProjector = true"
                                class="p-1.5 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-white dark:hover:bg-slate-800 rounded-lg border border-transparent hover:border-slate-200 dark:hover:border-slate-700 transition-colors cursor-pointer"
                                title="Mode Proyektor Penuh"
                            >
                                <Maximize2 class="w-3.5 h-3.5" />
                            </button>
                        </div>
                    </div>

                    <p class="text-4xl md:text-5xl font-black font-mono tracking-widest text-[#0F52BA] dark:text-blue-400 tabular-nums my-3 select-all drop-shadow-xs">
                        {{ selectedSession.access_token }}
                    </p>

                    <p class="text-xs text-slate-600 dark:text-slate-400 font-medium">
                        {{ selectedSession.room_name }} &bull; {{ selectedSession.session_name }}
                    </p>
                </div>

                <!-- Session Metadata (Rounded-2xl) -->
                <div class="bg-gradient-to-br from-white to-slate-50/70 dark:from-slate-900 dark:to-slate-850 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex flex-col justify-between">
                    <div>
                        <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block mb-1">Jadwal Ujian</span>
                        <h2 class="text-xs font-bold text-slate-900 dark:text-slate-100 line-clamp-1">{{ selectedSession.exam_schedule?.title }}</h2>
                        <p class="text-xs text-slate-600 dark:text-slate-300 mt-1">
                            Mapel: <strong>{{ selectedSession.exam_schedule?.subject?.name }}</strong>
                        </p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-mono mt-0.5">
                            Durasi: <strong>{{ selectedSession.exam_schedule?.duration_minutes }}m</strong> (Min Kumpul: {{ selectedSession.exam_schedule?.min_submit_minutes }}m)
                        </p>
                    </div>
                    <div class="pt-3 border-t border-slate-200/70 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                        <span class="px-2 py-0.5 rounded-md bg-amber-50 dark:bg-amber-950/50 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800 font-medium text-[11px]">
                            Maks Pelanggaran: {{ selectedSession.exam_schedule?.max_cheat_strikes }}x
                        </span>
                        <span class="font-mono text-emerald-700 dark:text-emerald-300 font-bold uppercase bg-emerald-50 dark:bg-emerald-950/50 px-2 py-0.5 rounded-lg border border-emerald-200 dark:border-emerald-800 text-[10px] flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            AKTIF
                        </span>
                    </div>
                </div>

                <!-- Proctor Notes Box (Rounded-2xl) -->
                <div class="bg-gradient-to-br from-white to-slate-50/70 dark:from-slate-900 dark:to-slate-850 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex flex-col justify-between">
                    <div>
                        <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block mb-1.5">Catatan Ruang Ujian</span>
                        <textarea
                            v-model="notes"
                            rows="2"
                            placeholder="Catat kejadian di ruang (misal: siswa terlambat, kendala PC)..."
                            class="w-full text-xs p-2.5 border border-slate-200 dark:border-slate-700 rounded-xl focus:border-[#0F52BA] dark:focus:border-blue-500 focus:outline-none resize-none transition-all bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500"
                        ></textarea>
                    </div>
                    <div class="flex justify-end pt-2">
                        <button
                            @click="saveNotes"
                            class="px-3.5 py-1.5 bg-gradient-to-r from-slate-900 to-indigo-950 hover:from-slate-800 hover:to-indigo-900 text-white font-semibold text-xs rounded-xl shadow-xs transition-colors cursor-pointer"
                        >
                            Simpan Catatan
                        </button>
                    </div>
                </div>
            </div>

            <!-- Colorful Status Metrics Bar (Rounded-2xl) -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div 
                    @click="statusFilter = 'all'"
                    :class="[
                        'p-4 rounded-2xl border transition-all cursor-pointer shadow-xs',
                        statusFilter === 'all' 
                            ? 'bg-gradient-to-br from-slate-900 to-slate-800 text-white border-slate-900 shadow-sm ring-2 ring-slate-900/20' 
                            : 'bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 border-slate-200/90 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700'
                    ]"
                >
                    <span class="text-[10px] uppercase font-bold tracking-wider block opacity-80">Total Siswa</span>
                    <p class="text-2xl font-black font-mono tabular-nums mt-1">{{ studentsMatrix?.length || 0 }}</p>
                    <p class="text-[10px] opacity-70 mt-0.5">di ruang ujian</p>
                </div>

                <div 
                    @click="statusFilter = 'ongoing'"
                    :class="[
                        'p-4 rounded-2xl border transition-all cursor-pointer shadow-xs',
                        statusFilter === 'ongoing' 
                            ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white border-blue-600 shadow-sm ring-2 ring-blue-500/20' 
                            : 'bg-gradient-to-br from-white to-blue-50/40 dark:from-slate-900 dark:to-blue-950/20 text-blue-900 dark:text-blue-200 border-slate-200/90 dark:border-slate-800 hover:border-blue-300 dark:hover:border-blue-700'
                    ]"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] uppercase font-bold tracking-wider block opacity-80">Mengerjakan</span>
                        <span class="w-2 h-2 rounded-full" :class="statusFilter === 'ongoing' ? 'bg-cyan-300 animate-pulse' : 'bg-blue-500 animate-pulse'"></span>
                    </div>
                    <p class="text-2xl font-black font-mono tabular-nums mt-1">{{ ongoingCount }}</p>
                    <p class="text-[10px] opacity-70 mt-0.5">siswa sedang tes</p>
                </div>

                <div 
                    @click="statusFilter = 'locked'"
                    :class="[
                        'p-4 rounded-2xl border transition-all cursor-pointer shadow-xs',
                        statusFilter === 'locked' 
                            ? 'bg-gradient-to-r from-rose-600 to-red-600 text-white border-rose-600 shadow-sm ring-2 ring-rose-500/20' 
                            : 'bg-gradient-to-br from-white to-rose-50/40 dark:from-slate-900 dark:to-rose-950/20 text-rose-900 dark:text-rose-200 border-slate-200/90 dark:border-slate-800 hover:border-rose-300 dark:hover:border-rose-700'
                    ]"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] uppercase font-bold tracking-wider block opacity-80">Terkunci</span>
                        <Lock class="w-3.5 h-3.5" :class="statusFilter === 'locked' ? 'text-white' : 'text-rose-600'" />
                    </div>
                    <p class="text-2xl font-black font-mono tabular-nums mt-1">{{ lockedCount }}</p>
                    <p class="text-[10px] opacity-70 mt-0.5">butuh buka kunci</p>
                </div>

                <div 
                    @click="statusFilter = 'completed'"
                    :class="[
                        'p-4 rounded-2xl border transition-all cursor-pointer shadow-xs',
                        statusFilter === 'completed' 
                            ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white border-emerald-600 shadow-sm ring-2 ring-emerald-500/20' 
                            : 'bg-gradient-to-br from-white to-emerald-50/40 dark:from-slate-900 dark:to-emerald-950/20 text-emerald-900 dark:text-emerald-200 border-slate-200/90 dark:border-slate-800 hover:border-emerald-300 dark:hover:border-emerald-700'
                    ]"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] uppercase font-bold tracking-wider block opacity-80">Selesai</span>
                        <Check class="w-3.5 h-3.5" :class="statusFilter === 'completed' ? 'text-white' : 'text-emerald-600'" />
                    </div>
                    <p class="text-2xl font-black font-mono tabular-nums mt-1">{{ completedCount }}</p>
                    <p class="text-[10px] opacity-70 mt-0.5">jawaban terkumpul</p>
                </div>
            </div>

            <!-- Alert Banner for Locked Students -->
            <div v-if="lockedCount > 0" class="p-4 bg-gradient-to-r from-rose-600 via-rose-500 to-red-600 rounded-2xl text-white shadow-md flex flex-col sm:flex-row sm:items-center justify-between gap-3 animate-in fade-in duration-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center shrink-0 border border-white/30 animate-pulse">
                        <Lock class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <h3 class="text-xs sm:text-sm font-bold uppercase tracking-wider flex items-center gap-1.5">
                            <span>Perhatian: Ada {{ lockedCount }} Siswa Terkunci!</span>
                        </h3>
                        <p class="text-[11px] sm:text-xs text-rose-100 mt-0.5">
                            Sistem mendeteksi aktivitas mencurigakan. Klik siswa untuk melihat detail pelanggaran atau buka kunci seluruhnya.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button
                        @click="statusFilter = 'locked'"
                        class="px-3.5 py-2 bg-white/20 hover:bg-white/30 text-white font-bold text-xs rounded-xl border border-white/30 transition-all cursor-pointer"
                    >
                        Filter Siswa Terkunci ({{ lockedCount }})
                    </button>
                    <button
                        @click="unlockAll"
                        class="px-4 py-2 bg-white hover:bg-rose-50 text-rose-700 font-extrabold text-xs rounded-xl shadow-sm transition-all flex items-center gap-1.5 cursor-pointer"
                    >
                        <Unlock class="w-3.5 h-3.5" />
                        <span>Buka Kunci Semua</span>
                    </button>
                </div>
            </div>

            <!-- Student Matrix Workspace (Rounded-2xl Container) -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 overflow-hidden shadow-xs">
                <!-- Toolbar: Search, Filters & View Toggle -->
                <div class="p-3.5 bg-slate-50 dark:bg-slate-850 border-b border-slate-200 dark:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="relative w-full sm:w-64">
                            <Search class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Cari NISN atau nama siswa..."
                                class="w-full pl-9 pr-3 py-1.5 text-xs bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 rounded-xl focus:border-[#0F52BA] dark:focus:border-blue-500 focus:outline-none transition-all"
                            />
                        </div>

                        <!-- Filter Status Tabs with Colorful Indicators -->
                        <div class="flex items-center gap-1 text-xs">
                            <button
                                @click="statusFilter = 'all'"
                                :class="['px-3 py-1.5 rounded-xl text-xs transition-colors cursor-pointer', statusFilter === 'all' ? 'bg-white dark:bg-slate-750 font-bold text-slate-900 dark:text-slate-100 border border-slate-200 dark:border-slate-700 shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100']"
                            >
                                Semua ({{ studentsMatrix.length }})
                            </button>
                            <button
                                @click="statusFilter = 'ongoing'"
                                :class="['px-3 py-1.5 rounded-xl text-xs transition-colors flex items-center gap-1.5 cursor-pointer', statusFilter === 'ongoing' ? 'bg-blue-50 dark:bg-blue-950/60 font-bold text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:text-blue-700 dark:hover:text-blue-300']"
                            >
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                                <span>Ujian ({{ ongoingCount }})</span>
                            </button>
                            <button
                                @click="statusFilter = 'locked'"
                                :class="['px-3 py-1.5 rounded-xl text-xs transition-colors flex items-center gap-1.5 cursor-pointer', statusFilter === 'locked' ? 'bg-rose-50 dark:bg-rose-950/60 font-bold text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:text-rose-700 dark:hover:text-rose-300']"
                            >
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                <span>Terkunci ({{ lockedCount }})</span>
                            </button>
                            <button
                                @click="statusFilter = 'completed'"
                                :class="['px-3 py-1.5 rounded-xl text-xs transition-colors flex items-center gap-1.5 cursor-pointer', statusFilter === 'completed' ? 'bg-emerald-50 dark:bg-emerald-950/60 font-bold text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:text-emerald-700 dark:hover:text-emerald-300']"
                            >
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                <span>Selesai ({{ completedCount }})</span>
                            </button>
                        </div>
                    </div>

                    <!-- View Switcher -->
                    <div class="flex items-center gap-1">
                        <button
                            @click="viewMode = 'table'"
                            :class="[
                                'px-3 py-1.5 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-colors cursor-pointer',
                                viewMode === 'table' ? 'bg-white dark:bg-slate-750 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100'
                            ]"
                        >
                            <List class="w-3.5 h-3.5" />
                            <span>Tabel</span>
                        </button>
                        <button
                            @click="viewMode = 'grid'"
                            :class="[
                                'px-3 py-1.5 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-colors cursor-pointer',
                                viewMode === 'grid' ? 'bg-white dark:bg-slate-750 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100'
                            ]"
                        >
                            <LayoutGrid class="w-3.5 h-3.5" />
                            <span>Peta Meja</span>
                        </button>
                    </div>
                </div>

                <!-- View 1: Clean Table View -->
                <div v-if="viewMode === 'table'">
                    <DataTable
                        :items="filteredStudents"
                        :columns="tableColumns"
                        :show-header="false"
                        :per-page="25"
                        card-class="border-0 shadow-none rounded-none"
                    >
                        <template #cell-username="{ row }">
                            <span class="font-mono font-bold text-slate-800 dark:text-slate-200">{{ row.username }}</span>
                        </template>

                        <template #cell-name="{ row }">
                            <div class="flex items-center gap-1.5">
                                <span class="font-semibold text-slate-900 dark:text-slate-100">{{ row.name }}</span>
                                <span v-if="row.is_locked" class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-600 text-white">LOCKED</span>
                            </div>
                        </template>

                        <template #cell-class_name="{ row }">
                            <span class="text-slate-600 dark:text-slate-400 font-medium">{{ row.class_name }}</span>
                        </template>

                        <template #cell-status="{ row }">
                            <span v-if="row.is_locked" class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-600 text-white flex items-center justify-center gap-1 w-max mx-auto shadow-2xs">
                                <Lock class="w-3 h-3" />
                                <span>TERKUNCI</span>
                            </span>
                            <span v-else-if="row.status === 'ongoing'" class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 dark:bg-blue-950/60 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-800 inline-flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-600 animate-pulse"></span>
                                <span>Mengerjakan</span>
                            </span>
                            <span v-else-if="row.status === 'submitted' || row.status === 'graded'" class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 inline-flex items-center gap-1">
                                <Check class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" />
                                <span>Selesai</span>
                            </span>
                            <span v-else class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">
                                Belum Mulai
                            </span>
                        </template>

                        <template #cell-answered_count="{ row }">
                            <span v-if="row.attempt_id" class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 font-mono font-bold tabular-nums text-xs">
                                {{ row.answered_count }} / {{ row.total_questions }}
                            </span>
                            <span v-else class="text-slate-400 dark:text-slate-600">-</span>
                        </template>

                        <template #cell-cheat_strikes="{ row }">
                            <span :class="row.cheat_strikes > 0 ? 'px-2 py-0.5 rounded-md bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 font-mono font-bold tabular-nums text-xs' : 'text-slate-400 dark:text-slate-500 font-mono text-xs'">
                                {{ row.cheat_strikes }}x
                            </span>
                        </template>

                        <template #actions="{ row }">
                            <div class="inline-flex items-center gap-1.5 justify-end">
                                <button
                                    @click="openStudentDetail(row)"
                                    class="px-2.5 py-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-blue-50 dark:hover:bg-blue-950/40 hover:border-blue-200 dark:hover:border-blue-800 text-slate-700 dark:text-slate-300 hover:text-blue-700 dark:hover:text-blue-400 font-semibold text-xs rounded-lg inline-flex items-center gap-1 transition-colors cursor-pointer"
                                    title="Lihat Detail Siswa & Status Ujian"
                                >
                                    <Eye class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" />
                                    <span>Lihat</span>
                                </button>

                                <button
                                    v-if="row.is_locked"
                                    @click="unlockStudent(row.attempt_id)"
                                    class="px-3 py-1 bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-700 hover:to-red-700 text-white font-semibold text-xs rounded-lg inline-flex items-center gap-1 shadow-xs transition-colors cursor-pointer animate-pulse"
                                    title="Buka Kunci Layar Siswa"
                                >
                                    <Unlock class="w-3.5 h-3.5" />
                                    <span>Buka Kunci</span>
                                </button>

                                <button
                                    @click="resetLogin(row.student_id)"
                                    class="px-2.5 py-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-750 text-slate-700 dark:text-slate-300 font-semibold text-xs rounded-lg inline-flex items-center gap-1 transition-colors cursor-pointer"
                                    title="Reset sesi login jika komputer siswa mati"
                                >
                                    <RotateCcw class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" />
                                    <span>Reset</span>
                                </button>
                            </div>
                        </template>
                    </DataTable>
                </div>

                <!-- View 2: Visual Seat Grid (Peta Meja Siswa with Rounded-xl Cards) -->
                <div v-else class="p-4">
                    <div v-if="filteredStudents.length === 0" class="text-center py-12 text-xs text-slate-400 dark:text-slate-500">
                        Tidak ada data siswa yang sesuai filter.
                    </div>
                    <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3.5">
                        <div
                            v-for="(std, idx) in filteredStudents"
                            :key="std.student_id"
                            :class="[
                                std.is_locked 
                                    ? 'bg-rose-50/80 dark:bg-rose-950/30 border-rose-300 dark:border-rose-900/60 ring-1 ring-rose-400/20' 
                                    : std.status === 'ongoing' 
                                        ? 'bg-blue-50/20 dark:bg-blue-950/20 border-blue-200 dark:border-blue-900/50' 
                                        : (std.status === 'submitted' || std.status === 'graded')
                                        ? 'bg-emerald-50/20 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-900/50'
                                        : 'bg-white dark:bg-slate-850 border-slate-200/90 dark:border-slate-800',
                                'p-3.5 rounded-2xl border flex flex-col justify-between shadow-2xs hover:shadow-xs transition-all'
                            ]"
                        >
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-[10px] font-mono font-bold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-md">
                                        Meja #{{ String(idx + 1).padStart(2, '0') }}
                                    </span>

                                    <span v-if="std.is_locked" class="px-2 py-0.5 rounded-md text-[9px] font-bold bg-rose-600 text-white flex items-center gap-0.5">
                                        <Lock class="w-2.5 h-2.5" />
                                        <span>TERKUNCI</span>
                                    </span>
                                    <span v-else-if="std.status === 'ongoing'" class="px-2 py-0.5 rounded-md text-[9px] font-bold bg-blue-100 dark:bg-blue-950/60 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-800 flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-600 animate-pulse"></span>
                                        <span>Ujian</span>
                                    </span>
                                    <span v-else-if="std.status === 'submitted' || std.status === 'graded'" class="px-2 py-0.5 rounded-md text-[9px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 flex items-center gap-0.5">
                                        <Check class="w-2.5 h-2.5 text-emerald-600 dark:text-emerald-400" />
                                        <span>Selesai</span>
                                    </span>
                                    <span v-else class="px-2 py-0.5 rounded-md text-[9px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">
                                        Belum
                                    </span>
                                </div>

                                <div class="flex items-center gap-2.5 mb-2.5">
                                    <div 
                                        :class="[
                                            std.is_locked 
                                                ? 'bg-rose-500 text-white' 
                                                : std.status === 'ongoing' 
                                                    ? 'bg-blue-500 text-white' 
                                                    : (std.status === 'submitted' || std.status === 'graded')
                                                    ? 'bg-emerald-500 text-white'
                                                    : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300',
                                            'w-8 h-8 rounded-xl flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs'
                                        ]"
                                    >
                                        <Monitor class="w-4 h-4" />
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="text-xs font-bold text-slate-900 dark:text-slate-100 truncate">{{ std.name }}</h4>
                                        <p class="text-[10px] font-mono text-slate-500 dark:text-slate-400 truncate">{{ std.username }} &bull; {{ std.class_name }}</p>
                                    </div>
                                </div>

                                <div v-if="std.attempt_id" class="pt-2 border-t border-slate-200/80 dark:border-slate-800 text-[10px] font-mono flex items-center justify-between text-slate-600 dark:text-slate-400 mb-2">
                                    <span>Soal: <strong>{{ std.answered_count }}/{{ std.total_questions }}</strong></span>
                                    <span :class="std.cheat_strikes > 0 ? 'text-rose-600 dark:text-rose-400 font-bold' : 'text-slate-400 dark:text-slate-500'">
                                        {{ std.cheat_strikes > 0 ? std.cheat_strikes + 'x tegur' : 'Tertib' }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-1.5 pt-2 border-t border-slate-100 dark:border-slate-800">
                                <button
                                    @click="openStudentDetail(std)"
                                    class="flex-1 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-blue-50 dark:hover:bg-blue-950/40 hover:border-blue-200 dark:hover:border-blue-800 text-slate-700 dark:text-slate-300 hover:text-blue-700 dark:hover:text-blue-400 font-semibold text-[11px] rounded-lg transition-colors flex items-center justify-center gap-1 cursor-pointer"
                                    title="Lihat Detail Siswa"
                                >
                                    <Eye class="w-3 h-3 text-blue-600 dark:text-blue-400" />
                                    <span>Lihat</span>
                                </button>
                                <button
                                    v-if="std.is_locked"
                                    @click="unlockStudent(std.attempt_id)"
                                    class="flex-1 py-1.5 bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-700 hover:to-red-700 text-white font-semibold text-[11px] rounded-lg shadow-2xs transition-colors flex items-center justify-center gap-1 cursor-pointer animate-pulse"
                                >
                                    <Unlock class="w-3 h-3" />
                                    <span>Buka Kunci</span>
                                </button>
                                <button
                                    v-else
                                    @click="resetLogin(std.student_id)"
                                    class="flex-1 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-750 text-slate-700 dark:text-slate-300 font-semibold text-[11px] rounded-lg transition-colors cursor-pointer"
                                >
                                    Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fullscreen Projector Dialog (Clean, High Visibility) -->
        <div v-if="fullscreenProjector" class="fixed inset-0 z-50 bg-[#0F172A] text-white flex flex-col justify-between p-8 md:p-12 select-none">
            <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                <div>
                    <h2 class="text-lg font-bold text-white uppercase tracking-wider">
                        {{ school?.school_name || 'SmartCBT' }}
                    </h2>
                    <p class="text-xs text-slate-400">
                        {{ selectedSession?.exam_schedule?.title }} &bull; {{ selectedSession?.room_name }} ({{ selectedSession?.session_name }})
                    </p>
                </div>

                <button
                    @click="fullscreenProjector = false"
                    class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold rounded-md border border-slate-700"
                >
                    Tutup Layar Penuh
                </button>
            </div>

            <!-- Big Projector Token -->
            <div class="text-center my-auto py-8">
                <p class="text-base font-bold uppercase tracking-widest text-slate-400 mb-3">
                    TOKEN MASUK RUANG UJIAN
                </p>
                <div class="inline-block px-10 py-6 rounded-xl bg-slate-900 border-2 border-[#0F52BA]">
                    <p class="text-6xl md:text-8xl font-black font-mono tracking-widest text-[#60A5FA] select-all tabular-nums">
                        {{ selectedSession?.access_token }}
                    </p>
                </div>
                <p class="text-xs text-slate-400 mt-4">
                    Ketikkan token di atas pada layar ujian siswa. Dilarang membuka peramban lain.
                </p>
            </div>

            <div class="flex items-center justify-between border-t border-slate-800 pt-4 text-xs text-slate-500 font-mono">
                <span>Durasi: {{ selectedSession?.exam_schedule?.duration_minutes }} Menit</span>
                <span class="text-emerald-400 font-bold">Status: Sesi Sedang Berjalan</span>
                <span>TA {{ school?.active_academic_year }} ({{ school?.active_semester }})</span>
            </div>
        </div>
        <!-- ==================== STUDENT DETAIL & UNLOCK MODAL ==================== -->
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
                    v-if="showDetailModal && selectedStudent"
                    @click="closeStudentDetail"
                    class="fixed inset-0 bg-slate-900/50 dark:bg-slate-950/70 flex items-center justify-center p-4 z-50 overflow-y-auto"
                >
                    <div
                        @click.stop
                        class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 max-w-lg w-full p-6 shadow-xl space-y-4 my-auto transform-gpu"
                    >
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-3">
                        <div 
                            :class="[
                                selectedStudent.is_locked 
                                    ? 'bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300' 
                                    : selectedStudent.status === 'ongoing' 
                                        ? 'bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300' 
                                        : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300',
                                'w-10 h-10 rounded-xl flex items-center justify-center font-bold text-sm shrink-0'
                            ]"
                        >
                            <Lock v-if="selectedStudent.is_locked" class="w-5 h-5 text-rose-600 animate-pulse" />
                            <UserCheck v-else class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ selectedStudent.name }}</h3>
                            <p class="text-xs font-mono text-slate-500 dark:text-slate-400">NISN: {{ selectedStudent.username }} &bull; {{ selectedStudent.class_name }}</p>
                        </div>
                    </div>
                    <button
                        @click="closeStudentDetail"
                        class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                    >
                        <X class="w-4 h-4" />
                    </button>
                </div>

                <!-- Status Summary Cards -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 text-xs">
                    <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200/80 dark:border-slate-700/80">
                        <span class="text-[10px] text-slate-400 dark:text-slate-500 block uppercase font-bold">Status Ujian</span>
                        <div class="mt-1">
                            <span v-if="selectedStudent.is_locked" class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-600 text-white inline-flex items-center gap-1">
                                <Lock class="w-2.5 h-2.5" />
                                <span>TERKUNCI</span>
                            </span>
                            <span v-else-if="selectedStudent.status === 'ongoing'" class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 dark:bg-blue-950/60 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                Mengerjakan
                            </span>
                            <span v-else-if="selectedStudent.status === 'submitted' || selectedStudent.status === 'graded'" class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                Selesai
                            </span>
                            <span v-else class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                                Belum Mulai
                            </span>
                        </div>
                    </div>

                    <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200/80 dark:border-slate-700/80">
                        <span class="text-[10px] text-slate-400 dark:text-slate-500 block uppercase font-bold">Progres Jawaban</span>
                        <p class="text-sm font-black font-mono text-slate-900 dark:text-slate-100 mt-0.5">
                            {{ selectedStudent.answered_count || 0 }} / {{ selectedStudent.total_questions || 0 }}
                        </p>
                    </div>

                    <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200/80 dark:border-slate-700/80 col-span-2 sm:col-span-1">
                        <span class="text-[10px] text-slate-400 dark:text-slate-500 block uppercase font-bold">Teguran Cheat</span>
                        <p :class="selectedStudent.cheat_strikes > 0 ? 'text-rose-600 dark:text-rose-400 font-bold' : 'text-slate-700 dark:text-slate-300'" class="text-sm font-mono mt-0.5">
                            {{ selectedStudent.cheat_strikes || 0 }} / {{ selectedStudent.max_cheat_strikes || 3 }}x
                        </p>
                    </div>
                </div>

                <!-- Lock Status & Violation Details -->
                <div v-if="selectedStudent.is_locked" class="p-3.5 bg-rose-50 dark:bg-rose-950/40 border border-rose-200/80 dark:border-rose-900/60 rounded-xl space-y-2">
                    <div class="flex items-center gap-2 text-rose-800 dark:text-rose-200 text-xs font-bold">
                        <AlertTriangle class="w-4 h-4 text-rose-600 dark:text-rose-400 shrink-0" />
                        <span>Layar Terkunci Akibat Pelanggaran Anti-Curang</span>
                    </div>
                    <p class="text-[11px] text-rose-700 dark:text-rose-300 leading-relaxed">
                        Siswa telah melebihi batas toleransi keluar layar penuh atau berpindah tab peramban. Peserta tidak dapat memilih opsi atau navigasi soal sampai Pengawas menekan tombol <strong>Buka Kunci</strong>.
                    </p>
                </div>

                <!-- Device & Session Info -->
                <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200/80 dark:border-slate-700/80 text-xs space-y-1.5 font-mono">
                    <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                        <span class="text-[11px] text-slate-400 dark:text-slate-500">Waktu Mulai:</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ selectedStudent.started_at || '-' }}</span>
                    </div>
                    <div v-if="selectedStudent.finished_at" class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                        <span class="text-[11px] text-slate-400 dark:text-slate-500">Waktu Selesai:</span>
                        <span class="font-bold text-emerald-700 dark:text-emerald-400">{{ selectedStudent.finished_at }}</span>
                    </div>
                    <div v-if="selectedStudent.device_info" class="pt-1.5 border-t border-slate-200/70 dark:border-slate-700 text-[10px] text-slate-500 dark:text-slate-400 break-all leading-tight">
                        <Laptop class="w-3 h-3 inline mr-1 text-slate-400 dark:text-slate-500" />
                        <span>{{ selectedStudent.device_info }}</span>
                    </div>
                </div>

                <!-- Modal Actions -->
                <div class="flex items-center gap-2.5 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <button
                        v-if="selectedStudent.is_locked"
                        type="button"
                        @click="unlockStudent(selectedStudent.attempt_id)"
                        class="flex-1 py-2.5 px-4 bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-700 hover:to-red-700 active:scale-[0.98] text-white font-bold text-xs rounded-xl shadow-sm transition-all flex items-center justify-center gap-2 cursor-pointer"
                    >
                        <Unlock class="w-4 h-4" />
                        <span>Buka Kunci Layar Sekarang</span>
                    </button>
                    <button
                        type="button"
                        @click="resetLogin(selectedStudent.student_id)"
                        class="py-2.5 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-750 text-slate-700 dark:text-slate-300 font-semibold text-xs rounded-xl transition-all flex items-center gap-1.5 cursor-pointer"
                    >
                        <RotateCcw class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" />
                        <span>Reset Sesi</span>
                    </button>
                    <button
                        type="button"
                        @click="closeStudentDetail"
                        class="py-2.5 px-4 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold text-xs rounded-xl transition-all cursor-pointer"
                    >
                        Tutup
                    </button>
                </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>
