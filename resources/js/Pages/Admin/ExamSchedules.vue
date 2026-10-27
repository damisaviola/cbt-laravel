<script setup>
import { ref } from 'vue';
import { useForm, router, Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import { 
    Calendar, 
    Plus, 
    Clock, 
    KeyRound, 
    RefreshCw, 
    Pencil, 
    Trash2, 
    ShieldAlert, 
    Layers,
    Monitor,
    LayoutGrid,
    Table as TableIcon,
    Copy,
    Check,
    UserCheck,
    Sparkles,
    X
} from 'lucide-vue-next';
import { useConfirm } from '@/Composables/useConfirm';

const props = defineProps({
    schedules: Array,
    subjects: Array,
    questionBanks: Array,
    classes: Array,
    proctors: Array,
});

const showScheduleModal = ref(false);
const showSessionModal = ref(false);
const selectedSchedule = ref(null);
const editingSchedule = ref(null);
const sessionViewMode = ref('grid'); // 'grid' | 'table'
const copiedToken = ref(null);

const copyToken = (token) => {
    if (navigator?.clipboard) {
        navigator.clipboard.writeText(token);
        copiedToken.value = token;
        setTimeout(() => {
            if (copiedToken.value === token) copiedToken.value = null;
        }, 1500);
    }
};

const generateRandomToken = () => {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    let result = '';
    for (let i = 0; i < 6; i++) {
        result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return result;
};

const formatLocalDatetime = (date) => {
    const d = new Date(date);
    const pad = (n) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
};

const scheduleForm = useForm({
    title: '',
    subject_id: '',
    question_bank_id: '',
    target_class_ids_json: [],
    duration_minutes: 90,
    min_submit_minutes: 15,
    shuffle_questions: true,
    shuffle_options: true,
    max_cheat_strikes: 3,
    show_score_to_student: false,
    is_remedial: false,
    auto_create_session: true,
    session_name: 'Sesi 1 (Utama)',
    room_name: 'Lab Komputer 1',
    start_time: '',
    end_time: '',
    proctor_id: '',
    access_token: '',
});

const sessionForm = useForm({
    session_name: 'Sesi 1 (Pagi)',
    room_name: 'Lab Komputer 1',
    start_time: '',
    end_time: '',
    proctor_id: '',
    access_token: '',
});

const openCreateSchedule = () => {
    editingSchedule.value = null;
    scheduleForm.reset();
    scheduleForm.subject_id = props.subjects[0]?.id || '';
    scheduleForm.question_bank_id = props.questionBanks[0]?.id || '';
    scheduleForm.target_class_ids_json = props.classes.map(c => c.id);
    scheduleForm.duration_minutes = 90;
    scheduleForm.min_submit_minutes = 15;
    scheduleForm.max_cheat_strikes = 3;

    scheduleForm.auto_create_session = true;
    scheduleForm.session_name = 'Sesi 1 (Utama)';
    scheduleForm.room_name = 'Lab Komputer 1';
    
    const now = new Date();
    const end = new Date(now.getTime() + 6 * 3600 * 1000);
    scheduleForm.start_time = formatLocalDatetime(now);
    scheduleForm.end_time = formatLocalDatetime(end);
    scheduleForm.proctor_id = props.proctors[0]?.id || '';
    scheduleForm.access_token = generateRandomToken();

    showScheduleModal.value = true;
};

const submitSchedule = () => {
    if (editingSchedule.value) {
        scheduleForm.put(`/admin/schedules/${editingSchedule.value.id}`, {
            onSuccess: () => (showScheduleModal.value = false),
        });
    } else {
        scheduleForm.post('/admin/schedules', {
            onSuccess: () => (showScheduleModal.value = false),
        });
    }
};

const { confirm } = useConfirm();

const deleteSchedule = async (sch) => {
    const ok = await confirm({
        title: 'Hapus Jadwal Ujian',
        message: `Apakah Anda yakin ingin menghapus jadwal ujian "${sch.title}"? Seluruh data sesi ruang, token, dan jawaban siswa pada jadwal ini akan dihapus secara permanen.`,
        confirmText: 'Ya, Hapus Jadwal',
        cancelText: 'Batal',
        type: 'danger',
    });
    if (ok) {
        scheduleForm.delete(`/admin/schedules/${sch.id}`);
    }
};

const openAddSession = (sch) => {
    selectedSchedule.value = sch;
    sessionForm.reset();
    sessionForm.session_name = 'Sesi 1 (Pagi)';
    sessionForm.room_name = 'Lab Komputer 1';
    
    // Set default times (today)
    const now = new Date();
    const end = new Date(now.getTime() + 120 * 60000);
    sessionForm.start_time = now.toISOString().slice(0, 16);
    sessionForm.end_time = end.toISOString().slice(0, 16);
    sessionForm.proctor_id = props.proctors[0]?.id || '';
    showSessionModal.value = true;
};

const submitSession = () => {
    sessionForm.post(`/admin/schedules/${selectedSchedule.value.id}/sessions`, {
        onSuccess: () => (showSessionModal.value = false),
    });
};

const regenerateToken = (sess) => {
    router.post(`/admin/sessions/${sess.id}/regenerate-token`, {}, { preserveScroll: true });
};

const deleteSession = async (sess) => {
    const ok = await confirm({
        title: 'Hapus Sesi Ruang Ujian',
        message: `Apakah Anda yakin ingin menghapus sesi "${sess.session_name}" di ruangan "${sess.room_name}"?`,
        confirmText: 'Ya, Hapus Sesi',
        cancelText: 'Batal',
        type: 'danger',
    });
    if (ok) {
        router.delete(`/admin/sessions/${sess.id}`, { preserveScroll: true });
    }
};

const formatTimeRange = (start, end) => {
    if (!start) return '-';
    try {
        const s = new Date(start).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        const e = end ? new Date(end).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '-';
        return `${s} - ${e}`;
    } catch {
        return `${start} - ${end || ''}`;
    }
};
</script>

<template>
    <AppLayout>
        <Head title="Jadwal & Sesi Ujian" />

        <template #header>
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <h1 class="text-sm sm:text-base font-bold text-slate-900 dark:text-slate-100 tracking-tight truncate">
                        Jadwal Ujian & Sesi Ruang
                    </h1>
                    <span class="inline-flex items-center gap-1 px-3 py-0.5 rounded-full text-[10px] font-bold bg-[#eaf4ef] dark:bg-emerald-950/60 text-[#185338] dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800 shrink-0">
                        {{ schedules.length }} Jadwal
                    </span>
                </div>
                <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-0.5 truncate sm:whitespace-normal">
                    Kelola durasi tes, batas minimal kumpul, token proyektor, dan penugasan pengawas.
                </p>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Toolbar for View Mode & Create Action -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 p-4 sm:p-5 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                        Daftar Jadwal Ujian Aktif
                    </span>
                </div>

                <div class="flex items-center gap-2.5 self-end sm:self-auto">
                    <!-- View Mode Switcher -->
                    <div class="flex items-center p-1 bg-slate-100 dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-full">
                        <button
                            type="button"
                            @click="sessionViewMode = 'grid'"
                            :class="[
                                'px-3 py-1.5 rounded-full text-xs font-semibold flex items-center gap-1.5 transition-all cursor-pointer',
                                sessionViewMode === 'grid'
                                    ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs font-bold'
                                    : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'
                            ]"
                            title="Tampilan Kartu Sesi"
                        >
                            <LayoutGrid class="w-3.5 h-3.5" />
                            <span class="hidden sm:inline">Kartu</span>
                        </button>
                        <button
                            type="button"
                            @click="sessionViewMode = 'table'"
                            :class="[
                                'px-3 py-1.5 rounded-full text-xs font-semibold flex items-center gap-1.5 transition-all cursor-pointer',
                                sessionViewMode === 'table'
                                    ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs font-bold'
                                    : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'
                            ]"
                            title="Tampilan Tabel Sesi"
                        >
                            <TableIcon class="w-3.5 h-3.5" />
                            <span class="hidden sm:inline">Tabel</span>
                        </button>
                    </div>

                    <button
                        @click="openCreateSchedule"
                        class="px-5 py-2.5 bg-[#185338] hover:bg-[#12422c] text-white font-bold text-xs rounded-full shadow-xs flex items-center gap-1.5 transition-all cursor-pointer"
                    >
                        <Plus class="w-3.5 h-3.5" />
                        <span>Buat Jadwal Ujian</span>
                    </button>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="schedules.length === 0" class="bg-white dark:bg-slate-900 p-12 rounded-3xl border border-dashed border-slate-200/80 dark:border-slate-800 text-center text-xs text-slate-500 dark:text-slate-400 shadow-xs">
                Belum ada jadwal ujian. Klik tombol di atas untuk menjadwalkan ujian pertama.
            </div>

            <!-- Schedules Cards (Rounded-3xl) -->
            <div
                v-for="sch in schedules"
                :key="sch.id"
                class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden hover:shadow-sm transition-all duration-150"
            >
                <!-- Schedule Header Card -->
                <div class="p-5 bg-slate-50/70 dark:bg-slate-800/70 border-b border-slate-200 dark:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">
                                {{ sch.title }}
                            </h2>
                            <span v-if="sch.is_remedial" class="px-2.5 py-0.5 rounded-lg text-[10px] font-bold bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                Sesi Remedial
                            </span>
                        </div>
                        <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">
                            Mapel: <strong class="text-slate-800 dark:text-slate-200">{{ sch.subject?.name }}</strong> &bull; Bank Soal: <span class="text-slate-700 dark:text-slate-300">{{ sch.question_bank?.title }}</span>
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 sm:gap-4 text-xs text-slate-600 dark:text-slate-400 font-mono">
                        <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700">
                            <Clock class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" />
                            <span>Durasi: <strong class="text-slate-800 dark:text-slate-200">{{ sch.duration_minutes }}m</strong></span>
                        </span>
                        <span class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700">
                            Min Kumpul: <strong class="text-slate-800 dark:text-slate-200">{{ sch.min_submit_minutes }}m</strong>
                        </span>
                        <div class="flex items-center gap-2">
                            <button
                                @click="openAddSession(sch)"
                                class="px-4 py-2 bg-[#185338] hover:bg-[#12422c] text-white font-bold text-[11px] rounded-full shadow-xs flex items-center gap-1 transition-all cursor-pointer"
                            >
                                <Plus class="w-3.5 h-3.5" />
                                <span>Tambah Ruang / Sesi</span>
                            </button>
                            <button
                                @click="deleteSchedule(sch)"
                                class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/40 rounded-lg transition-colors cursor-pointer"
                                title="Hapus Jadwal"
                            >
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Sessions Content inside Schedule (Card Grid or Table View) -->
                <div class="p-5">
                    <div class="flex items-center justify-between mb-3.5">
                        <h3 class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-2">
                            <span>Daftar Ruang & Token Sesi Aktif</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-mono bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                {{ sch.sessions.length }} Sesi
                            </span>
                        </h3>
                    </div>

                    <div v-if="sch.sessions.length === 0" class="text-xs text-slate-400 dark:text-slate-500 italic py-4 text-center bg-slate-50/50 dark:bg-slate-800/40 rounded-xl border border-dashed border-slate-200 dark:border-slate-800">
                        Belum ada sesi ruang. Klik tombol "Tambah Ruang / Sesi" di atas untuk merilis token.
                    </div>

                    <!-- VIEW 1: Cards Grid View -->
                    <div v-else-if="sessionViewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5">
                        <div
                            v-for="sess in sch.sessions"
                            :key="sess.id"
                            class="p-4 bg-slate-50/80 dark:bg-slate-800/50 border border-slate-200/90 dark:border-slate-700/80 rounded-xl shadow-2xs hover:border-slate-300 dark:hover:border-slate-600 transition-all flex flex-col justify-between"
                        >
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-xs font-bold text-slate-900 dark:text-white">{{ sess.session_name }}</span>
                                    <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 uppercase font-mono">
                                        {{ sess.status }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-600 dark:text-slate-400 mb-3 flex items-center justify-between">
                                    <span>Ruang: <strong class="text-slate-800 dark:text-slate-200">{{ sess.room_name }}</strong></span>
                                    <span class="text-[11px] font-mono text-slate-400 dark:text-slate-500">{{ formatTimeRange(sess.start_time, sess.end_time) }}</span>
                                </p>

                                <!-- Token Display Card -->
                                <div class="p-3.5 bg-[#eaf4ef]/40 dark:bg-emerald-950/20 border border-dashed border-emerald-300 dark:border-emerald-800/60 rounded-2xl text-center mb-3 shadow-2xs relative group">
                                    <p class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500 tracking-wider">Token Ruang Ujian</p>
                                    <div class="flex items-center justify-center gap-2 mt-1">
                                        <p class="text-xl font-black font-mono tracking-widest text-[#185338] dark:text-emerald-400 tabular-nums select-all">
                                            {{ sess.access_token }}
                                        </p>
                                        <button
                                            type="button"
                                            @click="copyToken(sess.access_token)"
                                            class="p-1 rounded-md text-slate-400 hover:text-[#185338] dark:hover:text-emerald-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                                            :title="copiedToken === sess.access_token ? 'Disalin!' : 'Salin Token'"
                                        >
                                            <Check v-if="copiedToken === sess.access_token" class="w-3.5 h-3.5 text-emerald-500" />
                                            <Copy v-else class="w-3.5 h-3.5" />
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-2.5 border-t border-slate-200 dark:border-slate-700/80 text-xs text-slate-500 dark:text-slate-400">
                                <span class="truncate max-w-[170px]" :title="sess.proctor?.name || 'Tanpa Pengawas'">
                                    Pengawas: <span class="text-slate-700 dark:text-slate-300 font-medium">{{ sess.proctor?.name || '-' }}</span>
                                </span>
                                <div class="flex items-center gap-1 shrink-0">
                                    <button
                                        @click="regenerateToken(sess)"
                                        class="p-1.5 hover:bg-white dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-lg transition-colors cursor-pointer"
                                        title="Rilis Token Baru"
                                    >
                                        <RefreshCw class="w-3.5 h-3.5" />
                                    </button>
                                    <button
                                        @click="deleteSession(sess)"
                                        class="p-1.5 hover:bg-red-50 dark:hover:bg-red-950/40 text-slate-400 hover:text-red-600 dark:hover:text-red-400 rounded-lg transition-colors cursor-pointer"
                                        title="Hapus Sesi"
                                    >
                                        <Trash2 class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- VIEW 2: Table View (Sleek EduPro Style Table) -->
                    <div v-else class="overflow-x-auto rounded-2xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm">
                        <table class="w-full text-left text-xs text-slate-700 dark:text-slate-200">
                            <thead class="bg-slate-50/60 dark:bg-slate-800/40 border-b border-slate-100 dark:border-slate-800 text-slate-400 dark:text-slate-400 text-[11px] font-bold uppercase tracking-wider select-none">
                                <tr>
                                    <th class="px-5 py-3.5">Sesi & Ruangan</th>
                                    <th class="px-5 py-3.5">Pengawas Ruang</th>
                                    <th class="px-5 py-3.5">Waktu Sesi</th>
                                    <th class="px-5 py-3.5 text-center">Token Ujian</th>
                                    <th class="px-5 py-3.5 text-center">Status</th>
                                    <th class="px-5 py-3.5 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/70">
                                <tr
                                    v-for="sess in sch.sessions"
                                    :key="sess.id"
                                    class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors"
                                >
                                    <td class="px-5 py-3.5">
                                        <div class="font-bold text-slate-900 dark:text-white">{{ sess.session_name }}</div>
                                        <div class="text-[11px] text-slate-400 dark:text-slate-500">{{ sess.room_name }}</div>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center gap-1.5 text-slate-700 dark:text-slate-300">
                                            <UserCheck class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500 shrink-0" />
                                            <span class="font-medium">{{ sess.proctor?.name || '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5 font-mono text-[11px] text-slate-500 dark:text-slate-400">
                                        {{ formatTimeRange(sess.start_time, sess.end_time) }}
                                    </td>
                                    <td class="px-5 py-3.5 text-center">
                                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#eaf4ef] dark:bg-emerald-950/50 border border-emerald-200/70 dark:border-emerald-800/60">
                                            <span class="font-mono font-bold tracking-wider text-xs text-[#185338] dark:text-emerald-400 select-all">
                                                {{ sess.access_token }}
                                            </span>
                                            <button
                                                type="button"
                                                @click="copyToken(sess.access_token)"
                                                class="text-slate-400 hover:text-[#185338] dark:hover:text-emerald-400 p-0.5 rounded cursor-pointer transition-colors"
                                                :title="copiedToken === sess.access_token ? 'Disalin!' : 'Salin Token'"
                                            >
                                                <Check v-if="copiedToken === sess.access_token" class="w-3 h-3 text-emerald-500" />
                                                <Copy v-else class="w-3 h-3" />
                                            </button>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5 text-center">
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase font-mono bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                            {{ sess.status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="inline-flex items-center gap-1">
                                            <button
                                                @click="regenerateToken(sess)"
                                                class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-lg transition-colors cursor-pointer"
                                                title="Rilis Token Baru"
                                            >
                                                <RefreshCw class="w-3.5 h-3.5" />
                                            </button>
                                            <button
                                                @click="deleteSession(sess)"
                                                class="p-1.5 hover:bg-red-50 dark:hover:bg-red-950/40 text-slate-400 hover:text-red-600 dark:hover:text-red-400 rounded-lg transition-colors cursor-pointer"
                                                title="Hapus Sesi"
                                            >
                                                <Trash2 class="w-3.5 h-3.5" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- CREATE SCHEDULE MODAL (EduPro Design)                   -->
        <!-- ======================================================== -->
        <Modal
            :show="showScheduleModal"
            @close="showScheduleModal = false"
            :title="editingSchedule ? 'Edit Jadwal Ujian' : 'Buat Jadwal Ujian Baru'"
            subtitle="Atur mata pelajaran, bank soal, durasi waktu, dan kelas peserta ujian"
            max-width="lg"
        >
            <template #icon>
                <div class="w-10 h-10 rounded-2xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                    <Calendar class="w-5 h-5" />
                </div>
            </template>

            <form id="scheduleForm" @submit.prevent="submitSchedule" class="space-y-4 pt-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Judul / Nama Ujian</label>
                                <input
                                    v-model="scheduleForm.title"
                                    type="text"
                                    placeholder="Contoh: SAS Ganjil Matematika Kelas X"
                                    class="w-full text-xs px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:border-[#185338] dark:focus:border-emerald-500 focus:outline-none transition-all"
                                    required
                                />
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Mata Pelajaran</label>
                                    <select
                                        v-model="scheduleForm.subject_id"
                                        class="w-full text-xs px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:border-[#185338] dark:focus:border-emerald-500 focus:outline-none font-medium"
                                        required
                                    >
                                        <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }} ({{ s.code }})</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Naskah Soal Ujian</label>
                                    <select
                                        v-model="scheduleForm.question_bank_id"
                                        class="w-full text-xs px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:border-[#185338] dark:focus:border-emerald-500 focus:outline-none font-medium"
                                        required
                                    >
                                        <option v-for="b in questionBanks" :key="b.id" :value="b.id">{{ b.title }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Durasi (Menit)</label>
                                    <input
                                        v-model="scheduleForm.duration_minutes"
                                        type="number"
                                        min="5"
                                        max="360"
                                        class="w-full text-xs px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white font-mono focus:border-[#185338] dark:focus:border-emerald-500 focus:outline-none"
                                        required
                                    />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Min Kumpul (Mnt)</label>
                                    <input
                                        v-model="scheduleForm.min_submit_minutes"
                                        type="number"
                                        min="0"
                                        max="360"
                                        class="w-full text-xs px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white font-mono focus:border-[#185338] dark:focus:border-emerald-500 focus:outline-none"
                                        required
                                    />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Maks Curang</label>
                                    <input
                                        v-model="scheduleForm.max_cheat_strikes"
                                        type="number"
                                        min="1"
                                        max="10"
                                        class="w-full text-xs px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white font-mono focus:border-[#185338] dark:focus:border-emerald-500 focus:outline-none"
                                        required
                                    />
                                </div>
                            </div>

                            <!-- Target Classes -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Kelas Peserta</label>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 p-3 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl max-h-32 overflow-y-auto">
                                    <label v-for="c in classes" :key="c.id" class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                                        <input
                                            type="checkbox"
                                            :value="c.id"
                                            v-model="scheduleForm.target_class_ids_json"
                                            class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-[#185338] dark:text-emerald-500 focus:ring-0"
                                        />
                                        <span>{{ c.name }}</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Toggles -->
                            <div class="space-y-2.5 pt-3 border-t border-slate-100 dark:border-slate-800 text-xs">
                                <label class="flex items-center gap-2.5 cursor-pointer text-slate-700 dark:text-slate-300">
                                    <input v-model="scheduleForm.shuffle_questions" type="checkbox" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-[#185338] dark:text-emerald-500 focus:ring-0" />
                                    <span>Acak Urutan Soal (Shuffle Questions)</span>
                                </label>
                                <label class="flex items-center gap-2.5 cursor-pointer text-slate-700 dark:text-slate-300">
                                    <input v-model="scheduleForm.shuffle_options" type="checkbox" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-[#185338] dark:text-emerald-500 focus:ring-0" />
                                    <span>Acak Opsi Pilihan Ganda (Shuffle Options)</span>
                                </label>
                                <label class="flex items-center gap-2.5 cursor-pointer text-slate-700 dark:text-slate-300">
                                    <input v-model="scheduleForm.show_score_to_student" type="checkbox" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-[#185338] dark:text-emerald-500 focus:ring-0" />
                                    <span>Tampilkan Nilai Akhir ke Siswa setelah selesai</span>
                                </label>
                            </div>

                            <!-- Quick Session Setup (Simplified Mode) -->
                            <div v-if="!editingSchedule" class="p-4 bg-[#eaf4ef]/70 dark:bg-[#185338]/20 rounded-2xl border border-emerald-200/80 dark:border-emerald-800/60 space-y-3">
                                <div class="flex items-center justify-between">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input
                                            v-model="scheduleForm.auto_create_session"
                                            type="checkbox"
                                            class="rounded border-emerald-300 dark:border-emerald-700 text-[#185338] focus:ring-0"
                                        />
                                        <span class="text-xs font-bold text-[#185338] dark:text-emerald-300 flex items-center gap-1.5">
                                            <Sparkles class="w-3.5 h-3.5 text-[#185338] dark:text-emerald-400" />
                                            Otomatis Buat Sesi 1 & Rilis Token
                                        </span>
                                    </label>
                                    <span class="text-[10px] font-semibold px-2.5 py-0.5 rounded-full bg-[#185338] text-white">
                                        Sekali Klik
                                    </span>
                                </div>

                                <p class="text-[11px] text-[#185338]/80 dark:text-emerald-300/80 leading-relaxed">
                                    Sesi 1 dan token ujian langsung dibuat aktif otomatis. Siswa bisa langsung masuk ujian tanpa perlu tambah sesi manual.
                                </p>

                                <div v-if="scheduleForm.auto_create_session" class="space-y-3 pt-2.5 border-t border-emerald-200/60 dark:border-emerald-800/40">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                        <div>
                                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Sesi</label>
                                            <input
                                                v-model="scheduleForm.session_name"
                                                type="text"
                                                placeholder="Sesi 1 (Utama)"
                                                class="w-full text-xs px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:border-[#185338]"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Ruang Ujian</label>
                                            <input
                                                v-model="scheduleForm.room_name"
                                                type="text"
                                                placeholder="Lab Komputer 1 / Semua Ruangan"
                                                class="w-full text-xs px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:border-[#185338]"
                                            />
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                        <div>
                                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Mulai Ujian</label>
                                            <input
                                                v-model="scheduleForm.start_time"
                                                type="datetime-local"
                                                class="w-full text-xs px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:border-[#185338] font-mono"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Batas Selesai Ujian</label>
                                            <input
                                                v-model="scheduleForm.end_time"
                                                type="datetime-local"
                                                class="w-full text-xs px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:border-[#185338] font-mono"
                                            />
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                        <div>
                                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Pengawas Ruang (Opsional)</label>
                                            <select
                                                v-model="scheduleForm.proctor_id"
                                                class="w-full text-xs px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:border-[#185338]"
                                            >
                                                <option value="">Tanpa Penugasan Pengawas</option>
                                                <option v-for="p in proctors" :key="p.id" :value="p.id">{{ p.name }}</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Token Masuk Ujian</label>
                                            <div class="flex items-center gap-1.5">
                                                <input
                                                    v-model="scheduleForm.access_token"
                                                    type="text"
                                                    placeholder="Token 6 Karakter"
                                                    maxlength="10"
                                                    class="w-full text-xs px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white font-mono font-bold tracking-widest uppercase focus:outline-none focus:border-[#185338]"
                                                />
                                                <button
                                                    type="button"
                                                    @click="scheduleForm.access_token = generateRandomToken()"
                                                    class="p-2 border border-slate-200 dark:border-slate-700 rounded-full bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:text-[#185338] transition-colors cursor-pointer shrink-0"
                                                    title="Acak Token Baru"
                                                >
                                                    <RefreshCw class="w-3.5 h-3.5" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

            </form>

            <template #footer>
                <div class="flex items-center justify-end gap-3">
                    <button
                        type="button"
                        @click="showScheduleModal = false"
                        class="px-5 py-2.5 border border-slate-200 dark:border-slate-700 text-xs font-semibold rounded-full text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-750 transition-colors cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        form="scheduleForm"
                        class="px-6 py-2.5 bg-[#185338] hover:bg-[#12422c] text-white text-xs font-semibold rounded-full shadow-xs transition-all cursor-pointer"
                    >
                        Simpan Jadwal
                    </button>
                </div>
            </template>
        </Modal>

        <!-- ======================================================== -->
        <!-- ADD SESSION MODAL (EduPro Design)                        -->
        <!-- ======================================================== -->
        <Modal
            :show="showSessionModal"
            @close="showSessionModal = false"
            title="Tambah Ruang & Sesi"
            :subtitle="selectedSchedule?.title"
            max-width="md"
        >
            <template #icon>
                <div class="w-10 h-10 rounded-2xl bg-[#eaf4ef] dark:bg-emerald-950/60 text-[#185338] dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <Clock class="w-5 h-5" />
                </div>
            </template>

            <form id="sessionForm" @submit.prevent="submitSession" class="space-y-4 pt-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nama Sesi</label>
                        <input
                            v-model="sessionForm.session_name"
                            type="text"
                            placeholder="Sesi 1 (Pagi)"
                            class="w-full text-xs px-3.5 py-2.5 bg-slate-50/70 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:bg-white dark:focus:bg-slate-800 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 focus:outline-none transition-all"
                            required
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nama Ruang</label>
                        <input
                            v-model="sessionForm.room_name"
                            type="text"
                            placeholder="Lab Komputer 1"
                            class="w-full text-xs px-3.5 py-2.5 bg-slate-50/70 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:bg-white dark:focus:bg-slate-800 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 focus:outline-none transition-all"
                            required
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Waktu Mulai</label>
                        <input
                            v-model="sessionForm.start_time"
                            type="datetime-local"
                            class="w-full text-xs px-3.5 py-2.5 bg-slate-50/70 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white font-mono focus:bg-white dark:focus:bg-slate-800 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 focus:outline-none transition-all"
                            required
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Waktu Selesai</label>
                        <input
                            v-model="sessionForm.end_time"
                            type="datetime-local"
                            class="w-full text-xs px-3.5 py-2.5 bg-slate-50/70 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white font-mono focus:bg-white dark:focus:bg-slate-800 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 focus:outline-none transition-all"
                            required
                        />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Pengawas Ruang</label>
                    <select
                        v-model="sessionForm.proctor_id"
                        class="w-full text-xs px-3.5 py-2.5 bg-slate-50/70 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:bg-white dark:focus:bg-slate-800 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 focus:outline-none font-medium transition-all"
                    >
                        <option value="">-- Tanpa Pengawas Khusus --</option>
                        <option v-for="p in proctors" :key="p.id" :value="p.id">{{ p.name }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Token Ruang (Opsional, kosongkan untuk buat otomatis)
                    </label>
                    <input
                        v-model="sessionForm.access_token"
                        type="text"
                        placeholder="Misal: SMART1"
                        class="w-full text-xs px-3.5 py-2.5 bg-slate-50/70 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white font-mono uppercase focus:bg-white dark:focus:bg-slate-800 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 focus:outline-none transition-all"
                    />
                </div>
            </form>

            <template #footer>
                <div class="flex items-center justify-end gap-3">
                    <button
                        type="button"
                        @click="showSessionModal = false"
                        class="px-5 py-2.5 border border-slate-200 dark:border-slate-700 text-xs font-semibold rounded-full text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-750 transition-colors cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        form="sessionForm"
                        class="px-6 py-2.5 bg-[#185338] hover:bg-[#12422c] text-white text-xs font-semibold rounded-full shadow-xs transition-all cursor-pointer"
                    >
                        Rilis Sesi & Token
                    </button>
                </div>
            </template>
        </Modal>
    </AppLayout>
</template>
