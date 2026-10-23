<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import BentoStatCard from '@/Components/BentoStatCard.vue';
import TrendChart from '@/Components/TrendChart.vue';
import CategoryProgressWidget from '@/Components/CategoryProgressWidget.vue';
import { useConfirm } from '@/Composables/useConfirm';
import { 
    ShieldCheck, 
    Lock, 
    Unlock, 
    RotateCcw, 
    FileText, 
    Copy, 
    Check, 
    Maximize2, 
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
    UserCheck,
    Users
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

        <div v-if="!selectedSession" class="bg-white dark:bg-slate-900 p-12 rounded-2xl border border-dashed border-slate-200 dark:border-slate-800 text-center text-xs text-slate-500 dark:text-slate-400 shadow-sm">
            <ShieldCheck class="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto mb-2" />
            <p class="font-bold text-slate-700 dark:text-slate-200 text-sm">Tidak ada sesi ujian yang sedang aktif saat ini</p>
            <p class="text-slate-400 mt-1">Silakan pilih jadwal yang aktif pada menu Sesi Ujian di panel administrator.</p>
        </div>

        <div v-else class="space-y-6">
            <!-- Page Header matching contoh.jpg (Title on Left, Export & Projector Actions on Right) -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                        Dashboard Overview
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        Control Room Ruangan {{ selectedSession.room_name }} &bull; {{ selectedSession.session_name }}
                    </p>
                </div>

                <div class="flex items-center gap-3 shrink-0">
                    <!-- Export / Berita Acara Pill Button matching contoh.jpg -->
                    <button
                        type="button"
                        @click="printBeritaAcara"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800 rounded-full text-xs font-semibold text-slate-700 dark:text-slate-200 shadow-2xs transition-colors cursor-pointer"
                    >
                        <FileText class="w-4 h-4 text-emerald-600" />
                        <span>Berita Acara</span>
                    </button>

                    <!-- Fullscreen Projector Pill Button matching contoh.jpg -->
                    <button
                        type="button"
                        @click="fullscreenProjector = true"
                        class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-full text-xs font-semibold shadow-sm shadow-blue-600/25 transition-all cursor-pointer"
                    >
                        <Maximize2 class="w-4 h-4" />
                        <span>Mode Proyektor</span>
                    </button>
                </div>
            </div>

            <!-- 4 Bento Stat Cards Grid matching contoh.jpg -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Card 1: Total Siswa (Pastel Blue) -->
                <BentoStatCard
                    title="Total Peserta"
                    :value="studentsMatrix?.length || 0"
                    trend="+12%"
                    trend-type="up"
                    icon-bg="bg-blue-50 dark:bg-blue-950/60"
                    icon-color="text-blue-600 dark:text-blue-400"
                >
                    <template #icon>
                        <Users class="w-5 h-5" />
                    </template>
                </BentoStatCard>

                <!-- Card 2: Mengerjakan (Pastel Amber) -->
                <BentoStatCard
                    title="Sedang Mengerjakan"
                    :value="ongoingCount"
                    trend="+5%"
                    trend-type="up"
                    icon-bg="bg-amber-50 dark:bg-amber-950/60"
                    icon-color="text-amber-600 dark:text-amber-400"
                >
                    <template #icon>
                        <Monitor class="w-5 h-5" />
                    </template>
                </BentoStatCard>

                <!-- Card 3: Selesai (Pastel Emerald) -->
                <BentoStatCard
                    title="Ujian Selesai"
                    :value="completedCount"
                    trend="+8%"
                    trend-type="up"
                    icon-bg="bg-emerald-50 dark:bg-emerald-950/60"
                    icon-color="text-emerald-600 dark:text-emerald-400"
                >
                    <template #icon>
                        <CheckCircle2 class="w-5 h-5" />
                    </template>
                </BentoStatCard>

                <!-- Card 4: Terkunci (Pastel Rose) -->
                <BentoStatCard
                    title="Layar Terkunci"
                    :value="lockedCount"
                    :trend="lockedCount > 0 ? 'Perlu Bantuan' : 'Tertib'"
                    :trend-type="lockedCount > 0 ? 'down' : 'up'"
                    icon-bg="bg-rose-50 dark:bg-rose-950/60"
                    icon-color="text-rose-500 dark:text-rose-400"
                >
                    <template #icon>
                        <Lock class="w-5 h-5" />
                    </template>
                </BentoStatCard>
            </div>

            <!-- Two-Column Section matching contoh.jpg (Token & Notes on Left, Progress Breakdown on Right) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                <!-- Left: Big Projector Token Card & Session Selector (approx 65% width) -->
                <div class="lg:col-span-2 space-y-4">
                    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-6">
                        <!-- Token Display -->
                        <div class="text-center sm:text-left">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                Token Ruang Aktif
                            </span>
                            <div class="flex items-center gap-3 mt-1.5">
                                <p class="text-4xl sm:text-5xl font-black font-mono tracking-widest text-blue-600 dark:text-blue-400 select-all">
                                    {{ selectedSession.access_token }}
                                </p>
                                <button
                                    type="button"
                                    @click="copyToken"
                                    class="p-2 rounded-xl bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 text-slate-600 dark:text-slate-300 transition-colors cursor-pointer"
                                    title="Salin Token"
                                >
                                    <Check v-if="copiedToken" class="w-5 h-5 text-emerald-600" />
                                    <Copy v-else class="w-5 h-5" />
                                </button>
                            </div>
                            <p class="text-xs text-slate-400 mt-2">
                                Tampilkan token ini pada peserta ujian di ruang {{ selectedSession.room_name }}.
                            </p>
                        </div>

                        <!-- Session Switcher & Live Refresh -->
                        <div class="w-full sm:w-auto flex flex-col gap-3 min-w-[240px]">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">Pilih Ruangan Lain:</label>
                                <select
                                    v-model="currentSessionId"
                                    @change="switchSession"
                                    class="w-full text-xs font-medium px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:border-blue-600 focus:outline-none"
                                >
                                    <option v-for="s in sessions" :key="s.id" :value="s.id">
                                        {{ s.session_name }} &bull; {{ s.room_name }}
                                    </option>
                                </select>
                            </div>

                            <button
                                @click="refreshData"
                                :disabled="isRefreshing"
                                class="w-full py-2 bg-slate-50 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-750 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl flex items-center justify-center gap-1.5 transition-colors cursor-pointer"
                            >
                                <RefreshCw :class="{'animate-spin text-blue-600': isRefreshing}" class="w-3.5 h-3.5" />
                                <span>{{ isRefreshing ? 'Menyegarkan...' : 'Segarkan Data Siswa' }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Alert Banner for Locked Students if any -->
                    <div v-if="lockedCount > 0" class="p-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 rounded-2xl flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-rose-500 text-white flex items-center justify-center shrink-0">
                                <Lock class="w-4.5 h-4.5" />
                            </div>
                            <div>
                                <p class="text-xs font-bold text-rose-900 dark:text-rose-200">
                                    Perhatian: Ada {{ lockedCount }} Siswa Terkunci
                                </p>
                                <p class="text-[11px] text-rose-600 dark:text-rose-400">
                                    Siswa terdeteksi keluar dari layar ujian.
                                </p>
                            </div>
                        </div>

                        <button
                            @click="unlockAll"
                            class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-full shadow-2xs transition-all cursor-pointer flex items-center gap-1.5"
                        >
                            <Unlock class="w-3.5 h-3.5" />
                            <span>Buka Kunci Semua</span>
                        </button>
                    </div>
                </div>

                <!-- Right: Categories Breakdown (approx 35% width) -->
                <div class="lg:col-span-1">
                    <CategoryProgressWidget 
                        title="Top Performing Categories"
                        subtitle="Ketuntasan peserta ujian per kategori soal"
                    />
                </div>
            </div>

            <!-- Student Matrix Workspace (Rounded-2xl Container matching EduPro Table) -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
                <!-- Toolbar: Search, Filters & View Toggle -->
                <div class="p-4 bg-slate-50/70 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="relative w-full sm:w-64">
                            <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Cari NISN atau nama siswa..."
                                class="w-full pl-9 pr-3 py-2 text-xs bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 rounded-xl focus:border-blue-600 focus:outline-none transition-all"
                            />
                        </div>

                        <!-- Filter Status Pills -->
                        <div class="flex items-center gap-1 text-xs">
                            <button
                                @click="statusFilter = 'all'"
                                :class="['px-3 py-1.5 rounded-full text-xs font-semibold transition-all cursor-pointer', statusFilter === 'all' ? 'bg-blue-600 text-white shadow-2xs' : 'text-slate-500 hover:text-slate-900']"
                            >
                                Semua ({{ studentsMatrix.length }})
                            </button>
                            <button
                                @click="statusFilter = 'ongoing'"
                                :class="['px-3 py-1.5 rounded-full text-xs font-semibold transition-all cursor-pointer', statusFilter === 'ongoing' ? 'bg-blue-600 text-white shadow-2xs' : 'text-slate-500 hover:text-slate-900']"
                            >
                                Ujian ({{ ongoingCount }})
                            </button>
                            <button
                                @click="statusFilter = 'locked'"
                                :class="['px-3 py-1.5 rounded-full text-xs font-semibold transition-all cursor-pointer', statusFilter === 'locked' ? 'bg-rose-500 text-white shadow-2xs' : 'text-slate-500 hover:text-rose-600']"
                            >
                                Terkunci ({{ lockedCount }})
                            </button>
                            <button
                                @click="statusFilter = 'completed'"
                                :class="['px-3 py-1.5 rounded-full text-xs font-semibold transition-all cursor-pointer', statusFilter === 'completed' ? 'bg-emerald-600 text-white shadow-2xs' : 'text-slate-500 hover:text-slate-900']"
                            >
                                Selesai ({{ completedCount }})
                            </button>
                        </div>
                    </div>

                    <!-- View Switcher -->
                    <div class="flex items-center gap-1 bg-white dark:bg-slate-800 p-1 rounded-xl border border-slate-200/70 dark:border-slate-700">
                        <button
                            @click="viewMode = 'table'"
                            :class="[
                                'px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition-colors cursor-pointer',
                                viewMode === 'table' ? 'bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400' : 'text-slate-500 hover:text-slate-900'
                            ]"
                        >
                            <List class="w-3.5 h-3.5" />
                            <span>Tabel</span>
                        </button>
                        <button
                            @click="viewMode = 'grid'"
                            :class="[
                                'px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition-colors cursor-pointer',
                                viewMode === 'grid' ? 'bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400' : 'text-slate-500 hover:text-slate-900'
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
                                <span v-if="row.is_locked" class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-500 text-white">LOCKED</span>
                            </div>
                        </template>

                        <template #cell-class_name="{ row }">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">{{ row.class_name }}</span>
                        </template>

                        <template #cell-status="{ row }">
                            <span v-if="row.is_locked" class="px-3 py-0.5 rounded-full text-xs font-bold bg-rose-500 text-white flex items-center justify-center gap-1 w-max mx-auto shadow-2xs">
                                <Lock class="w-3 h-3" />
                                <span>TERKUNCI</span>
                            </span>
                            <span v-else-if="row.status === 'ongoing'" class="px-3 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400 inline-flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                                <span>Mengerjakan</span>
                            </span>
                            <span v-else-if="row.status === 'submitted' || row.status === 'graded'" class="px-3 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400 inline-flex items-center gap-1">
                                <Check class="w-3.5 h-3.5" />
                                <span>Selesai</span>
                            </span>
                            <span v-else class="px-3 py-0.5 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-400">
                                Belum Mulai
                            </span>
                        </template>

                        <template #cell-answered_count="{ row }">
                            <span v-if="row.attempt_id" class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-mono font-bold tabular-nums text-xs">
                                {{ row.answered_count }} / {{ row.total_questions }}
                            </span>
                            <span v-else class="text-slate-400">-</span>
                        </template>

                        <template #cell-cheat_strikes="{ row }">
                            <span :class="row.cheat_strikes > 0 ? 'px-2 py-0.5 rounded-md bg-rose-50 text-rose-600 font-mono font-bold text-xs' : 'text-slate-400 font-mono text-xs'">
                                {{ row.cheat_strikes }}x
                            </span>
                        </template>

                        <template #actions="{ row }">
                            <div class="inline-flex items-center gap-1.5 justify-end">
                                <button
                                    @click="openStudentDetail(row)"
                                    class="px-3 py-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 text-slate-700 dark:text-slate-300 font-semibold text-xs rounded-full inline-flex items-center gap-1 transition-colors cursor-pointer"
                                    title="Lihat Detail Siswa"
                                >
                                    <Eye class="w-3.5 h-3.5 text-blue-600" />
                                    <span>Lihat</span>
                                </button>

                                <button
                                    v-if="row.is_locked"
                                    @click="unlockStudent(row.attempt_id)"
                                    class="px-3 py-1 bg-rose-500 hover:bg-rose-600 text-white font-semibold text-xs rounded-full inline-flex items-center gap-1 shadow-2xs transition-colors cursor-pointer animate-pulse"
                                    title="Buka Kunci Layar Siswa"
                                >
                                    <Unlock class="w-3.5 h-3.5" />
                                    <span>Buka Kunci</span>
                                </button>

                                <button
                                    @click="resetLogin(row.student_id)"
                                    class="px-3 py-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 text-slate-700 dark:text-slate-300 font-semibold text-xs rounded-full inline-flex items-center gap-1 transition-colors cursor-pointer"
                                    title="Reset sesi login jika komputer siswa mati"
                                >
                                    <RotateCcw class="w-3.5 h-3.5 text-slate-400" />
                                    <span>Reset</span>
                                </button>
                            </div>
                        </template>
                    </DataTable>
                </div>

                <!-- View 2: Visual Seat Grid -->
                <div v-else class="p-6">
                    <div v-if="filteredStudents.length === 0" class="text-center py-12 text-xs text-slate-400">
                        Tidak ada data siswa yang sesuai filter.
                    </div>
                    <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                        <div
                            v-for="(std, idx) in filteredStudents"
                            :key="std.student_id"
                            class="p-4 bg-slate-50/70 dark:bg-slate-800/40 hover:bg-slate-50 dark:hover:bg-slate-800 border border-slate-100 dark:border-slate-800 rounded-2xl flex flex-col justify-between shadow-2xs transition-all"
                        >
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-[10px] font-mono font-bold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-700 px-2 py-0.5 rounded-md">
                                        Meja #{{ String(idx + 1).padStart(2, '0') }}
                                    </span>

                                    <span v-if="std.is_locked" class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-rose-500 text-white flex items-center gap-0.5">
                                        <Lock class="w-2.5 h-2.5" />
                                        <span>TERKUNCI</span>
                                    </span>
                                    <span v-else-if="std.status === 'ongoing'" class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-blue-50 text-blue-600 flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                                        <span>Ujian</span>
                                    </span>
                                    <span v-else-if="std.status === 'submitted' || std.status === 'graded'" class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-50 text-emerald-600 flex items-center gap-0.5">
                                        <Check class="w-2.5 h-2.5" />
                                        <span>Selesai</span>
                                    </span>
                                    <span v-else class="px-2 py-0.5 rounded-full text-[9px] font-semibold bg-slate-100 text-slate-400">
                                        Belum
                                    </span>
                                </div>

                                <div class="flex items-center gap-3 mb-3">
                                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs shrink-0">
                                        <Monitor class="w-4 h-4" />
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ std.name }}</h4>
                                        <p class="text-[10px] font-mono text-slate-400 truncate">{{ std.username }} &bull; {{ std.class_name }}</p>
                                    </div>
                                </div>

                                <div v-if="std.attempt_id" class="pt-2 border-t border-slate-200/50 dark:border-slate-700/60 text-[10px] font-mono flex items-center justify-between text-slate-400 mb-3">
                                    <span>Soal: <strong>{{ std.answered_count }}/{{ std.total_questions }}</strong></span>
                                    <span :class="std.cheat_strikes > 0 ? 'text-rose-500 font-bold' : 'text-slate-400'">
                                        {{ std.cheat_strikes > 0 ? std.cheat_strikes + 'x tegur' : 'Tertib' }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-200/50 dark:border-slate-700/60">
                                <button
                                    @click="openStudentDetail(std)"
                                    class="flex-1 py-1.5 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 hover:bg-slate-50 text-slate-700 dark:text-slate-200 font-semibold text-[11px] rounded-full transition-colors flex items-center justify-center gap-1 cursor-pointer"
                                >
                                    <Eye class="w-3 h-3 text-blue-600" />
                                    <span>Detail</span>
                                </button>
                                <button
                                    v-if="std.is_locked"
                                    @click="unlockStudent(std.attempt_id)"
                                    class="flex-1 py-1.5 bg-rose-500 hover:bg-rose-600 text-white font-semibold text-[11px] rounded-full transition-colors flex items-center justify-center gap-1 cursor-pointer animate-pulse"
                                >
                                    <Unlock class="w-3 h-3" />
                                    <span>Buka</span>
                                </button>
                                <button
                                    v-else
                                    @click="resetLogin(std.student_id)"
                                    class="flex-1 py-1.5 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 hover:bg-slate-50 text-slate-700 dark:text-slate-200 font-semibold text-[11px] rounded-full transition-colors cursor-pointer"
                                >
                                    Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fullscreen Projector Dialog -->
        <div v-if="fullscreenProjector" class="fixed inset-0 z-50 bg-[#0F172A] text-white flex flex-col justify-between p-8 md:p-12 select-none">
            <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                <div>
                    <h2 class="text-lg font-bold text-white uppercase tracking-wider">
                        {{ school?.school_name || 'EduPro CBT' }}
                    </h2>
                    <p class="text-xs text-slate-400">
                        {{ selectedSession?.exam_schedule?.title }} &bull; {{ selectedSession?.room_name }} ({{ selectedSession?.session_name }})
                    </p>
                </div>

                <button
                    @click="fullscreenProjector = false"
                    class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold rounded-full border border-slate-700 cursor-pointer"
                >
                    Tutup Layar Penuh
                </button>
            </div>

            <div class="text-center my-auto py-8">
                <p class="text-base font-bold uppercase tracking-widest text-slate-400 mb-4">
                    TOKEN MASUK RUANG UJIAN
                </p>
                <div class="inline-block px-12 py-8 rounded-3xl bg-slate-900 border-2 border-blue-600 shadow-2xl">
                    <p class="text-6xl md:text-8xl font-black font-mono tracking-widest text-blue-400 select-all tabular-nums">
                        {{ selectedSession?.access_token }}
                    </p>
                </div>
                <p class="text-xs text-slate-400 mt-5">
                    Ketikkan token di atas pada layar ujian siswa. Dilarang membuka peramban lain.
                </p>
            </div>

            <div class="flex items-center justify-between border-t border-slate-800 pt-4 text-xs text-slate-500 font-mono">
                <span>Durasi: {{ selectedSession?.exam_schedule?.duration_minutes }} Menit</span>
                <span class="text-emerald-400 font-bold">Status: Sesi Sedang Berjalan</span>
                <span>TA {{ school?.active_academic_year }} ({{ school?.active_semester }})</span>
            </div>
        </div>

        <!-- Student Detail Modal -->
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
                    class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 z-50 overflow-y-auto"
                >
                    <div
                        @click.stop
                        class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 max-w-lg w-full p-6 shadow-xl space-y-4 my-auto"
                    >
                        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm shrink-0">
                                    <Lock v-if="selectedStudent.is_locked" class="w-5 h-5 text-rose-500" />
                                    <UserCheck v-else class="w-5 h-5" />
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ selectedStudent.name }}</h3>
                                    <p class="text-xs text-slate-400 font-mono">NISN: {{ selectedStudent.username }} &bull; {{ selectedStudent.class_name }}</p>
                                </div>
                            </div>
                            <button
                                @click="closeStudentDetail"
                                class="p-1 text-slate-400 hover:text-slate-600 rounded-lg cursor-pointer"
                            >
                                <X class="w-4 h-4" />
                            </button>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                            <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl">
                                <span class="text-[10px] text-slate-400 block uppercase font-bold">Status Ujian</span>
                                <div class="mt-1">
                                    <span v-if="selectedStudent.is_locked" class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500 text-white">
                                        TERKUNCI
                                    </span>
                                    <span v-else-if="selectedStudent.status === 'ongoing'" class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-600">
                                        Mengerjakan
                                    </span>
                                    <span v-else-if="selectedStudent.status === 'submitted' || selectedStudent.status === 'graded'" class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-600">
                                        Selesai
                                    </span>
                                    <span v-else class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-200 text-slate-600">
                                        Belum Mulai
                                    </span>
                                </div>
                            </div>

                            <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl">
                                <span class="text-[10px] text-slate-400 block uppercase font-bold">Progres Jawaban</span>
                                <p class="text-sm font-bold font-mono text-slate-900 dark:text-white mt-0.5">
                                    {{ selectedStudent.answered_count || 0 }} / {{ selectedStudent.total_questions || 0 }}
                                </p>
                            </div>

                            <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl col-span-2 sm:col-span-1">
                                <span class="text-[10px] text-slate-400 block uppercase font-bold">Teguran Curang</span>
                                <p :class="selectedStudent.cheat_strikes > 0 ? 'text-rose-500 font-bold' : 'text-slate-700 dark:text-slate-300'" class="text-sm font-mono mt-0.5">
                                    {{ selectedStudent.cheat_strikes || 0 }} / {{ selectedStudent.max_cheat_strikes || 3 }}x
                                </p>
                            </div>
                        </div>

                        <div v-if="selectedStudent.is_locked" class="p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-xs space-y-1">
                            <p class="font-bold text-rose-700">Layar Terkunci Akibat Pelanggaran Sistem</p>
                            <p class="text-[11px] text-rose-600 leading-relaxed">
                                Peserta telah keluar dari layar ujian atau berpindah tab. Tekan Buka Kunci agar siswa dapat kembali melanjutkan pengerjaan soal.
                            </p>
                        </div>

                        <div class="flex items-center gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                            <button
                                v-if="selectedStudent.is_locked"
                                type="button"
                                @click="unlockStudent(selectedStudent.attempt_id)"
                                class="flex-1 py-2 px-4 bg-rose-500 hover:bg-rose-600 text-white font-bold text-xs rounded-full shadow-sm transition-all flex items-center justify-center gap-2 cursor-pointer"
                            >
                                <Unlock class="w-4 h-4" />
                                <span>Buka Kunci Layar</span>
                            </button>
                            <button
                                type="button"
                                @click="resetLogin(selectedStudent.student_id)"
                                class="py-2 px-4 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-semibold text-xs rounded-full transition-all flex items-center gap-1.5 cursor-pointer"
                            >
                                <RotateCcw class="w-3.5 h-3.5 text-slate-400" />
                                <span>Reset Sesi</span>
                            </button>
                            <button
                                type="button"
                                @click="closeStudentDetail"
                                class="py-2 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-full transition-all cursor-pointer"
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
