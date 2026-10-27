<script setup>
import { ref, computed } from 'vue';
import { useForm, router, Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import { 
    GraduationCap, 
    Plus, 
    Upload, 
    Download, 
    Printer, 
    Search, 
    Filter, 
    Pencil, 
    Trash2,
    FileSpreadsheet,
    ClipboardPaste,
    Sparkles,
    UserPlus,
    X,
    Check,
    AlertCircle,
    Info,
    FileText,
    Layers
} from 'lucide-vue-next';

const props = defineProps({
    students: Object,
    classes: Array,
    filters: Object,
});

const totalStudents = computed(() => props.students?.total ?? props.students?.data?.length ?? (Array.isArray(props.students) ? props.students.length : 0));

const columns = [
    { key: 'username', label: 'NISN / No', sortable: true },
    { key: 'name', label: 'Nama Siswa', sortable: true },
    { key: 'class_room.name', label: 'Rombel / Kelas', sortable: true },
    { key: 'exam_room_name', label: 'Ruang Ujian', sortable: true },
    { key: 'is_active', label: 'Status', align: 'center', sortable: true },
    { key: 'actions', label: 'Aksi', align: 'right', sortable: false },
];

const search = ref(props.filters.search || '');
const selectedClass = ref(props.filters.class_id || '');

const showModal = ref(false);
const editingStudent = ref(null);
const activeTab = ref('excel'); // 'excel', 'paste', 'generate', 'manual'

// Mode 1 & 4: Single Form
const form = useForm({
    name: '',
    username: '',
    password: '',
    class_room_id: '',
    exam_room_name: 'Lab Komputer 1',
    is_active: true,
});

// Mode 1: Excel Import
const importForm = useForm({
    file: null,
});

// Mode 2: Quick Batch Paste
const pasteText = ref('');
const isSavingBatch = ref(false);
const maxPreviewLimit = ref(20);
const batchDefaultClass = ref(props.classes[0]?.id || '');
const batchRows = ref([
    { username: '', name: '', class_room_id: props.classes[0]?.id || '', exam_room_name: 'Lab Komputer 1', password: 'siswa123' },
]);

const displayedBatchRows = computed(() => {
    return batchRows.value.slice(0, maxPreviewLimit.value);
});

const detectedLinesCount = computed(() => {
    if (!pasteText.value) return 0;
    return pasteText.value.split('\n').filter(l => l.trim().length > 0).length;
});

const applyDefaultClassToAll = () => {
    if (!batchDefaultClass.value) return;
    for (const row of batchRows.value) {
        row.class_room_id = batchDefaultClass.value;
    }
};

// Mode 3: Auto-Generator
const generateForm = useForm({
    class_room_id: props.classes[0]?.id || '',
    count: 32,
    nisn_prefix: '202610',
    nisn_start: 1,
    name_prefix: 'Siswa',
    exam_room_name: 'Lab Komputer 1',
    password: 'siswa123',
});

const generatePreview = computed(() => {
    const prefix = generateForm.nisn_prefix || '';
    const start = Number(generateForm.nisn_start) || 1;
    const namePrefix = generateForm.name_prefix || 'Siswa';
    const count = Math.min(3, Number(generateForm.count) || 1);
    const list = [];
    for (let i = 0; i < count; i++) {
        const num = start + i;
        list.push({
            username: prefix + String(num).padStart(3, '0'),
            name: `${namePrefix} ${String(i + 1).padStart(2, '0')}`,
            password: generateForm.password || 'siswa123',
        });
    }
    return list;
});

const validBatchCount = computed(() => {
    return batchRows.value.filter(r => r.username?.trim() && r.name?.trim()).length;
});

const filterData = () => {
    router.get('/admin/students', {
        search: search.value,
        class_id: selectedClass.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const openCreate = (tab = 'excel') => {
    editingStudent.value = null;
    activeTab.value = tab;
    form.reset();
    form.exam_room_name = 'Lab Komputer 1';
    form.class_room_id = props.classes[0]?.id || '';
    form.is_active = true;
    pasteText.value = '';
    maxPreviewLimit.value = 20;
    if (batchRows.value.length === 0) {
        batchRows.value = [
            { username: '', name: '', class_room_id: props.classes[0]?.id || '', exam_room_name: 'Lab Komputer 1', password: 'siswa123' },
        ];
    }
    showModal.value = true;
};

const openEdit = (std) => {
    editingStudent.value = std;
    activeTab.value = 'manual';
    form.name = std.name;
    form.username = std.username;
    form.password = '';
    form.class_room_id = std.class_room_id;
    form.exam_room_name = std.exam_room_name || 'Lab Komputer 1';
    form.is_active = Boolean(std.is_active);
    showModal.value = true;
};

const addBatchRow = () => {
    batchRows.value.push({
        username: '',
        name: '',
        class_room_id: props.classes[0]?.id || '',
        exam_room_name: 'Lab Komputer 1',
        password: 'siswa123',
    });
};

const removeBatchRow = (index) => {
    batchRows.value.splice(index, 1);
};

const parsePastedText = () => {
    if (!pasteText.value.trim()) return;
    const lines = pasteText.value.trim().split(/\r?\n/);
    const parsed = [];
    for (const line of lines) {
        const parts = line.split(/\t|,|;/).map(p => p.trim());
        if (parts.length >= 2 && parts[0] && parts[1]) {
            let classId = props.classes[0]?.id || '';
            let className = '';
            if (parts[2]) {
                className = parts[2];
                const matched = props.classes.find(c => c.name.toLowerCase() === parts[2].toLowerCase());
                if (matched) classId = matched.id;
            }
            parsed.push({
                username: parts[0],
                name: parts[1],
                class_room_id: classId,
                class_name: className,
                exam_room_name: parts[3] || 'Lab Komputer 1',
                password: parts[4] || 'siswa123',
            });
        }
    }
    if (parsed.length > 0) {
        batchRows.value = parsed;
        pasteText.value = '';
    }
};

const submit = () => {
    if (editingStudent.value) {
        form.put(`/admin/students/${editingStudent.value.id}`, {
            onSuccess: () => (showModal.value = false),
        });
    } else {
        form.post('/admin/students', {
            onSuccess: () => (showModal.value = false),
        });
    }
};

const submitImport = () => {
    importForm.post('/admin/students/import', {
        preserveScroll: true,
        onSuccess: () => (showModal.value = false),
    });
};

const submitBatch = () => {
    const validRows = batchRows.value.filter(r => r.username.trim() && r.name.trim());
    if (validRows.length === 0) {
        alert('Mohon isi minimal 1 baris siswa dengan NISN dan Nama.');
        return;
    }
    isSavingBatch.value = true;
    router.post('/admin/students/batch', { students: validRows }, {
        preserveScroll: true,
        onSuccess: () => {
            showModal.value = false;
            batchRows.value = [];
        },
        onFinish: () => {
            isSavingBatch.value = false;
        }
    });
};

const submitGenerate = () => {
    generateForm.post('/admin/students/generate', {
        preserveScroll: true,
        onSuccess: () => {
            showModal.value = false;
        }
    });
};

import { useConfirm } from '@/Composables/useConfirm';

const { confirm } = useConfirm();
const selectedStudentIds = ref([]);
const isDeletingBulk = ref(false);

const deleteStudent = async (std) => {
    const ok = await confirm({
        title: 'Hapus Data Siswa',
        message: `Apakah Anda yakin ingin menghapus data siswa "${std.name}" (${std.username})? Riwayat ujian siswa juga akan dihapus.`,
        confirmText: 'Ya, Hapus Siswa',
        cancelText: 'Batal',
        type: 'danger',
    });
    if (ok) {
        form.delete(`/admin/students/${std.id}`);
    }
};

const bulkDeleteStudents = async (ids, clearSelection) => {
    if (!ids || ids.length === 0) return;
    const ok = await confirm({
        title: 'Hapus Siswa Terpilih',
        message: `Apakah Anda yakin ingin menghapus ${ids.length} siswa yang dipilih sekaligus? Riwayat ujian siswa tersebut juga akan dihapus permanen.`,
        confirmText: `Ya, Hapus ${ids.length} Siswa`,
        cancelText: 'Batal',
        type: 'danger',
    });

    if (ok) {
        isDeletingBulk.value = true;
        router.post('/admin/students/bulk-destroy', { ids }, {
            preserveScroll: true,
            onSuccess: () => {
                if (clearSelection) clearSelection();
                selectedStudentIds.value = [];
            },
            onFinish: () => {
                isDeletingBulk.value = false;
            }
        });
    }
};

const openPrintCards = () => {
    const url = selectedClass.value 
        ? `/admin/students/print-cards?class_id=${selectedClass.value}` 
        : '/admin/students/print-cards';
    window.open(url, '_blank');
};

const openPrintAttendance = () => {
    window.open('/admin/students/print-attendance?room=Lab Komputer 1', '_blank');
};
</script>

<template>
    <AppLayout>
        <Head title="Manajemen Data Siswa" />

        <template #header>
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <h1 class="text-sm sm:text-base font-bold text-slate-900 dark:text-slate-100 tracking-tight truncate">
                        Data Siswa & Kartu Peserta Ujian
                    </h1>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#eaf4ef] dark:bg-emerald-950/60 text-[#185338] dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800 shrink-0">
                        {{ totalStudents }} Siswa
                    </span>
                </div>
                <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-0.5 truncate sm:whitespace-normal">
                    Kelola NISN siswa, import template massal, cetak kartu & daftar hadir ruang.
                </p>
            </div>
        </template>

        <div class="space-y-4">
            <!-- Student Management & Action Bar matching contoh.webp -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4">
                <!-- Filter Section -->
                <div class="flex flex-col sm:flex-row sm:items-center gap-3 min-w-0">
                    <div class="flex items-center gap-2 shrink-0">
                        <div class="w-8 h-8 rounded-full bg-[#eaf4ef] dark:bg-emerald-950/60 border border-emerald-200/60 dark:border-emerald-800 text-[#185338] dark:text-emerald-400 flex items-center justify-center shrink-0">
                            <Filter class="w-4 h-4" />
                        </div>
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                            Filter Kelas:
                        </span>
                    </div>

                    <div class="relative min-w-[200px] sm:min-w-[240px]">
                        <select
                            v-model="selectedClass"
                            @change="filterData"
                            class="w-full text-xs font-semibold py-2 pl-4 pr-8 bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700 rounded-full text-slate-800 dark:text-slate-200 focus:border-[#185338] dark:focus:border-emerald-500 focus:outline-none shadow-2xs cursor-pointer transition-all"
                        >
                            <option value="">Semua Kelas ({{ classes.length }} Rombel)</option>
                            <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>
                </div>

                <!-- Action Buttons matching contoh.webp pill buttons -->
                <div class="flex flex-wrap items-center gap-2.5 self-start md:self-auto shrink-0">
                    <button
                        @click="openPrintCards"
                        class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-750 text-slate-700 dark:text-slate-200 font-semibold text-xs rounded-full shadow-2xs flex items-center gap-1.5 transition-all cursor-pointer"
                        title="Cetak Kartu Ujian (PDF/Print)"
                    >
                        <Printer class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" />
                        <span>Cetak Kartu</span>
                    </button>

                    <button
                        @click="openPrintAttendance"
                        class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-750 text-slate-700 dark:text-slate-200 font-semibold text-xs rounded-full shadow-2xs flex items-center gap-1.5 transition-all cursor-pointer"
                        title="Cetak Daftar Hadir Ruang"
                    >
                        <Printer class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" />
                        <span>Daftar Hadir</span>
                    </button>

                    <button
                        @click="openCreate('excel')"
                        class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-750 text-slate-700 dark:text-slate-200 font-semibold text-xs rounded-full shadow-2xs flex items-center gap-1.5 transition-all cursor-pointer"
                        title="Import massal file Excel (.xlsx)"
                    >
                        <FileSpreadsheet class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" />
                        <span>Import Excel</span>
                    </button>

                    <button
                        @click="openCreate('paste')"
                        class="px-5 py-2.5 bg-[#185338] hover:bg-[#12422c] text-white font-semibold text-xs rounded-full shadow-xs flex items-center gap-1.5 transition-all cursor-pointer"
                    >
                        <Plus class="w-3.5 h-3.5" />
                        <span>Tambah Siswa</span>
                    </button>
                </div>
            </div>

            <DataTable
                :items="students"
                :columns="columns"
                selectable
                v-model:selected="selectedStudentIds"
                title="Daftar Siswa Terdaftar"
                subtitle="Kelola data akun siswa peserta ujian, rombel, dan ruang ujian."
                search-placeholder="Cari nama, NISN, atau ruang..."
                :per-page="15"
            >
                <template #bulk-actions="{ selected, clearSelection }">
                    <button
                        type="button"
                        @click="bulkDeleteStudents(selected, clearSelection)"
                        :disabled="isDeletingBulk"
                        class="px-3.5 py-1.5 bg-rose-600 hover:bg-rose-700 disabled:opacity-50 text-white font-semibold text-xs rounded-xl shadow-2xs flex items-center gap-1.5 transition-colors cursor-pointer"
                    >
                        <Trash2 class="w-3.5 h-3.5" />
                        <span>{{ isDeletingBulk ? 'Menghapus...' : `Hapus ${selected.length} Siswa Terpilih` }}</span>
                    </button>
                </template>
                <template #header-left>
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-full bg-[#eaf4ef] dark:bg-emerald-950/60 text-[#185338] dark:text-emerald-400 flex items-center justify-center shrink-0">
                            <GraduationCap class="w-4 h-4" />
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Daftar Siswa Terdaftar</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Kelola data akun siswa peserta ujian, rombel, dan ruang ujian.</p>
                        </div>
                    </div>
                </template>

                <template #cell-username="{ row }">
                    <span class="font-mono font-bold text-slate-800 dark:text-slate-100">{{ row.username }}</span>
                </template>

                <template #cell-name="{ row }">
                    <span class="font-semibold text-slate-900 dark:text-white">{{ row.name }}</span>
                </template>

                <template #cell-class_room.name="{ row }">
                    <span class="px-2.5 py-0.5 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/60 dark:border-slate-700">
                        {{ row.class_room ? row.class_room.name : '-' }}
                    </span>
                </template>

                <template #cell-exam_room_name="{ row }">
                    <span class="text-slate-600 dark:text-slate-300 font-medium">{{ row.exam_room_name || '-' }}</span>
                </template>

                <template #cell-is_active="{ row }">
                    <span :class="[row.is_active ? 'text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800' : 'text-rose-700 dark:text-rose-300 bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800', 'px-2.5 py-0.5 rounded-full text-xs font-bold inline-flex items-center gap-1']">
                        <span :class="[row.is_active ? 'bg-emerald-500' : 'bg-rose-500', 'w-1.5 h-1.5 rounded-full']"></span>
                        {{ row.is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </template>

                <template #actions="{ row }">
                    <div class="inline-flex items-center gap-1">
                        <button
                            @click="openEdit(row)"
                            class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 rounded-lg transition-colors cursor-pointer"
                            title="Edit Data Siswa"
                        >
                            <Pencil class="w-4 h-4" />
                        </button>
                        <button
                            @click="deleteStudent(row)"
                            class="p-1.5 hover:bg-rose-50 dark:hover:bg-rose-950/50 text-rose-600 dark:text-rose-400 rounded-lg transition-colors cursor-pointer"
                            title="Hapus Data Siswa"
                        >
                            <Trash2 class="w-4 h-4" />
                        </button>
                    </div>
                </template>
            </DataTable>
        </div>

        <!-- Unified Student Management Modal (Automatic, Excel & Manual) -->
        <div v-if="showModal" class="fixed inset-0 bg-slate-900/40 dark:bg-slate-950/70 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 z-50 overflow-y-auto">
            <div :class="[activeTab === 'paste' ? 'max-w-4xl' : 'max-w-2xl', 'bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 w-full shadow-2xl flex flex-col max-h-[90vh] overflow-hidden my-auto']">
                
                <!-- Modal Header -->
                <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between gap-3 shrink-0">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-bold text-slate-900 dark:text-white tracking-tight">
                                {{ editingStudent ? 'Edit Data Siswa' : 'Tambah Data Siswa Peserta Ujian' }}
                            </h3>
                            <span v-if="!editingStudent" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#eaf4ef] dark:bg-emerald-950/60 text-[#185338] dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800">
                                Otomatis & Manual
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            {{ editingStudent ? `Perbarui profil dan kata sandi akun ${editingStudent.name}.` : 'Pilih metode yang paling praktis: Salin dari Excel, Upload Template, Auto-Generate, atau Manual.' }}
                        </p>
                    </div>
                    <button
                        type="button"
                        @click="showModal = false"
                        class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer shrink-0"
                    >
                        <X class="w-4 h-4" />
                    </button>
                </div>

                <!-- Navigation Tabs (Only when creating new students) -->
                <div v-if="!editingStudent" class="px-6 pt-3 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-850/40 shrink-0">
                    <div class="flex items-center gap-2 overflow-x-auto pb-2.5 scrollbar-none">
                        <button
                            type="button"
                            @click="activeTab = 'paste'"
                            :class="[
                                activeTab === 'paste'
                                    ? 'bg-white dark:bg-slate-800 text-[#185338] dark:text-emerald-400 shadow-xs border-[#185338]/30 dark:border-emerald-500/30 font-bold'
                                    : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-white/50 dark:hover:bg-slate-800/50 border-transparent',
                                'px-4 py-2 rounded-full text-xs border flex items-center gap-2 transition-all cursor-pointer shrink-0'
                            ]"
                        >
                            <ClipboardPaste class="w-4 h-4 text-[#185338] dark:text-emerald-400" />
                            <span>Salin dari Excel (Tabel Grid)</span>
                            <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-[#eaf4ef] dark:bg-emerald-950/80 text-[#185338] dark:text-emerald-400">
                                Praktis
                            </span>
                        </button>

                        <button
                            type="button"
                            @click="activeTab = 'excel'"
                            :class="[
                                activeTab === 'excel'
                                    ? 'bg-white dark:bg-slate-800 text-[#185338] dark:text-emerald-400 shadow-xs border-[#185338]/30 dark:border-emerald-500/30 font-bold'
                                    : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-white/50 dark:hover:bg-slate-800/50 border-transparent',
                                'px-4 py-2 rounded-full text-xs border flex items-center gap-2 transition-all cursor-pointer shrink-0'
                            ]"
                        >
                            <FileSpreadsheet class="w-4 h-4 text-[#185338] dark:text-emerald-400" />
                            <span>Upload File Excel (.xlsx)</span>
                        </button>

                        <button
                            type="button"
                            @click="activeTab = 'generate'"
                            :class="[
                                activeTab === 'generate'
                                    ? 'bg-white dark:bg-slate-800 text-amber-700 dark:text-amber-400 shadow-xs border-slate-200 dark:border-slate-700 font-bold'
                                    : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-white/50 dark:hover:bg-slate-800/50 border-transparent',
                                'px-4 py-2 rounded-full text-xs border flex items-center gap-2 transition-all cursor-pointer shrink-0'
                            ]"
                        >
                            <Sparkles class="w-4 h-4 text-amber-500" />
                            <span>Generate Akun Rombel</span>
                            <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-100 dark:bg-amber-950/80 text-amber-700 dark:text-amber-400">
                                Instan
                            </span>
                        </button>

                        <button
                            type="button"
                            @click="activeTab = 'manual'"
                            :class="[
                                activeTab === 'manual'
                                    ? 'bg-white dark:bg-slate-800 text-[#185338] dark:text-emerald-400 shadow-xs border-[#185338]/30 dark:border-emerald-500/30 font-bold'
                                    : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-white/50 dark:hover:bg-slate-800/50 border-transparent',
                                'px-4 py-2 rounded-full text-xs border flex items-center gap-2 transition-all cursor-pointer shrink-0'
                            ]"
                        >
                            <UserPlus class="w-4 h-4 text-slate-500" />
                            <span>Input Manual Form</span>
                        </button>
                    </div>
                </div>

                <!-- Modal Body (Scrollable) -->
                <div class="p-5 overflow-y-auto space-y-4">
                    
                    <!-- TAB 1: Salin dari Excel (Quick Paste & Dynamic Table Grid) -->
                    <div v-if="!editingStudent && activeTab === 'paste'" class="space-y-4">
                        <div class="bg-[#eaf4ef]/60 dark:bg-emerald-950/30 border border-emerald-200/80 dark:border-emerald-900/60 rounded-2xl p-3.5 flex items-start gap-3">
                            <Info class="w-4 h-4 text-[#185338] dark:text-emerald-400 shrink-0 mt-0.5" />
                            <div class="text-xs text-slate-700 dark:text-emerald-200 leading-relaxed">
                                <span class="font-bold text-[#185338] dark:text-emerald-300">Cara Cepat dari Excel:</span> Buka file data siswa di Microsoft Excel atau Google Sheets Anda, seleksi (blok) kolom <span class="font-semibold underline">NISN & Nama</span> (opsional: Kelas, Ruang, Password), lalu tekan <kbd class="px-1.5 py-0.5 bg-white dark:bg-slate-800 border border-emerald-300 dark:border-emerald-800 rounded text-[10px] font-mono font-bold shadow-2xs">Ctrl + C</kbd>. Tempelkan pada kotak di bawah lalu klik <span class="font-semibold text-[#185338] dark:text-emerald-300">Konversi ke Tabel</span>.
                            </div>
                        </div>

                        <!-- Quick Paste Area -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                    <ClipboardPaste class="w-3.5 h-3.5 text-[#185338] dark:text-emerald-400" />
                                    <span>Area Tempel (Paste) dari Excel / Spreadsheet</span>
                                </label>
                                <span class="text-[11px] text-slate-400 dark:text-slate-500">
                                    Format: Baris dipisahkan Tab atau Koma
                                </span>
                            </div>

                            <textarea
                                v-model="pasteText"
                                rows="3"
                                placeholder="Klik di sini lalu tekan Ctrl + V...&#10;Contoh data dari Excel:&#10;2026101&#x9;Ahmad Pratama&#x9;X-MIPA-1&#x9;Lab 1&#10;2026102&#x9;Dewi Anggraini&#x9;X-MIPA-1&#x9;Lab 1"
                                class="w-full text-xs font-mono p-3 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:border-[#185338] dark:focus:border-emerald-500 focus:outline-none focus:bg-white dark:focus:bg-slate-800 transition-colors resize-y"
                            ></textarea>

                            <div class="flex items-center justify-between pt-1">
                                <div class="text-[11px] text-slate-500 dark:text-slate-400">
                                    <span v-if="detectedLinesCount > 0">{{ detectedLinesCount }} baris terdeteksi di clipboard</span>
                                    <span v-else>Atau Anda bisa langsung ketik baris di tabel bawah</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button
                                        v-if="pasteText.trim()"
                                        type="button"
                                        @click="pasteText = ''"
                                        class="px-2.5 py-1 text-xs text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition-colors cursor-pointer"
                                    >
                                        Bersihkan
                                    </button>
                                    <button
                                        type="button"
                                        @click="parsePastedText"
                                        :disabled="!pasteText.trim()"
                                        class="px-4 py-2 bg-[#185338] hover:bg-[#12422c] disabled:opacity-40 text-white text-xs font-semibold rounded-full shadow-2xs flex items-center gap-1.5 transition-colors cursor-pointer"
                                    >
                                        <Layers class="w-3.5 h-3.5" />
                                        <span>Konversi ke Tabel Siswa</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Editable Dynamic Table Grid -->
                        <div class="space-y-2 pt-2 border-t border-slate-200 dark:border-slate-800">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">
                                        Tabel Siswa ({{ batchRows.length }} Baris)
                                    </h4>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                        {{ validBatchCount }} Valid
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[11px] text-slate-500">Rombel Massal:</span>
                                        <select
                                            v-model="batchDefaultClass"
                                            @change="applyDefaultClassToAll"
                                            class="text-xs py-1 px-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-200 focus:outline-none"
                                        >
                                            <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
                                        </select>
                                        <button
                                            type="button"
                                            @click="applyDefaultClassToAll"
                                            class="px-2 py-1 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-[11px] font-semibold rounded-lg transition-colors cursor-pointer"
                                            title="Terapkan rombel terpilih ke semua baris siswa"
                                        >
                                            Terapkan
                                        </button>
                                    </div>
                                    <button
                                        type="button"
                                        @click="addBatchRow"
                                        class="px-2.5 py-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-[#185338] dark:hover:border-emerald-700 text-xs font-semibold text-[#185338] dark:text-emerald-400 rounded-lg flex items-center gap-1 shadow-2xs transition-colors cursor-pointer"
                                    >
                                        <Plus class="w-3.5 h-3.5" />
                                        <span>Baris</span>
                                    </button>
                                </div>
                            </div>

                            <div class="border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden max-h-60 overflow-y-auto">
                                <table class="w-full text-left border-collapse text-xs">
                                    <thead class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold sticky top-0 z-10 border-b border-slate-200 dark:border-slate-700">
                                        <tr>
                                            <th class="py-2 px-2.5 w-10 text-center">#</th>
                                            <th class="py-2 px-2.5 min-w-[120px]">NISN / No. Induk *</th>
                                            <th class="py-2 px-2.5 min-w-[160px]">Nama Lengkap Siswa *</th>
                                            <th class="py-2 px-2.5 min-w-[130px]">Kelas / Rombel</th>
                                            <th class="py-2 px-2.5 min-w-[110px]">Ruang Ujian</th>
                                            <th class="py-2 px-2.5 min-w-[100px]">Password</th>
                                            <th class="py-2 px-2 w-9 text-center"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800 bg-white dark:bg-slate-900">
                                        <tr v-for="(row, idx) in displayedBatchRows" :key="idx" class="hover:bg-slate-50/70 dark:hover:bg-slate-800/50">
                                            <td class="py-1 px-2 text-center text-[11px] font-mono text-slate-400">
                                                {{ idx + 1 }}
                                            </td>
                                            <td class="py-1 px-1.5">
                                                <input
                                                    v-model="row.username"
                                                    type="text"
                                                    placeholder="2026..."
                                                    class="w-full text-xs font-mono px-2 py-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-md focus:border-[#185338] dark:focus:border-emerald-500 focus:outline-none"
                                                />
                                            </td>
                                            <td class="py-1 px-1.5">
                                                <input
                                                    v-model="row.name"
                                                    type="text"
                                                    placeholder="Nama Siswa..."
                                                    class="w-full text-xs px-2 py-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-md focus:border-[#185338] dark:focus:border-emerald-500 focus:outline-none"
                                                />
                                            </td>
                                            <td class="py-1 px-1.5">
                                                <select
                                                    v-model="row.class_room_id"
                                                    class="w-full text-xs px-2 py-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-md focus:border-[#185338] dark:focus:border-emerald-500 focus:outline-none"
                                                >
                                                    <option value="">-- Kelas --</option>
                                                    <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
                                                </select>
                                            </td>
                                            <td class="py-1 px-1.5">
                                                <input
                                                    v-model="row.exam_room_name"
                                                    type="text"
                                                    placeholder="Lab 1"
                                                    class="w-full text-xs px-2 py-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-md focus:border-[#185338] dark:focus:border-emerald-500 focus:outline-none"
                                                />
                                            </td>
                                            <td class="py-1 px-1.5">
                                                <input
                                                    v-model="row.password"
                                                    type="text"
                                                    placeholder="siswa123"
                                                    class="w-full text-xs font-mono px-2 py-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-md focus:border-[#185338] dark:focus:border-emerald-500 focus:outline-none"
                                                />
                                            </td>
                                            <td class="py-1 px-1.5 text-center">
                                                <button
                                                    type="button"
                                                    @click="removeBatchRow(idx)"
                                                    class="p-1 hover:bg-rose-50 dark:hover:bg-rose-950/60 text-slate-400 hover:text-rose-600 rounded cursor-pointer"
                                                    title="Hapus baris ini"
                                                >
                                                    <Trash2 class="w-3.5 h-3.5" />
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                
                                <div v-if="batchRows.length > maxPreviewLimit" class="p-2 text-center bg-slate-50 dark:bg-slate-800/60 border-t border-slate-200 dark:border-slate-700 text-xs text-slate-600 dark:text-slate-400">
                                    Menampilkan {{ maxPreviewLimit }} baris pratinjau pertama dari total {{ batchRows.length }} baris. Seluruh {{ validBatchCount }} siswa akan tetap otomatis tersimpan.
                                    <button
                                        type="button"
                                        @click="maxPreviewLimit = batchRows.length"
                                        class="ml-2 font-bold text-[#185338] dark:text-emerald-400 underline cursor-pointer"
                                    >
                                        Buka Semua ({{ batchRows.length }})
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 1 Footer -->
                        <div class="flex items-center justify-between pt-3 border-t border-slate-200 dark:border-slate-800">
                            <span class="text-xs text-slate-500 dark:text-slate-400">
                                Menambahkan <span class="font-bold text-slate-900 dark:text-slate-100">{{ validBatchCount }}</span> siswa ke database
                            </span>
                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    @click="showModal = false"
                                    class="px-4 py-2 border border-slate-200 dark:border-slate-700 text-xs font-semibold rounded-full text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                                >
                                    Batal
                                </button>
                                <button
                                    type="button"
                                    @click="submitBatch"
                                    :disabled="validBatchCount === 0 || isSavingBatch"
                                    class="px-5 py-2.5 bg-[#185338] hover:bg-[#12422c] disabled:opacity-40 text-white text-xs font-semibold rounded-full shadow-xs hover:shadow flex items-center gap-1.5 transition-colors cursor-pointer"
                                >
                                    <Check class="w-3.5 h-3.5" />
                                    <span>{{ isSavingBatch ? 'Menyimpan Siswa...' : `Simpan ${validBatchCount} Siswa Sekaligus` }}</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: Upload File Excel (.xlsx / .xls) -->
                    <div v-else-if="!editingStudent && activeTab === 'excel'" class="space-y-4">
                        <!-- Template Download Box -->
                        <div class="p-4 bg-gradient-to-r from-emerald-500/10 via-emerald-500/5 to-transparent border border-emerald-500/20 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-start gap-3">
                                <div class="w-9 h-9 rounded-xl bg-emerald-100 dark:bg-emerald-950/80 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                    <FileSpreadsheet class="w-5 h-5" />
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-slate-900 dark:text-slate-100">
                                        Format Template Resmi Import Siswa
                                    </h4>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                        Unduh template file Excel untuk memastikan susunan kolom NISN, Nama, dan Kelas sesuai.
                                    </p>
                                </div>
                            </div>
                            <a
                                href="/admin/students/template"
                                class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow-2xs flex items-center justify-center gap-1.5 transition-all shrink-0"
                            >
                                <Download class="w-3.5 h-3.5" />
                                <span>Unduh Template (.xlsx)</span>
                            </a>
                        </div>

                        <!-- Upload Form -->
                        <form @submit.prevent="submitImport" class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                    Pilih File Dokumen Excel (.xlsx, .xls, .csv)
                                </label>
                                <div class="border-2 border-dashed border-slate-300 dark:border-slate-700 hover:border-emerald-500 dark:hover:border-emerald-500 rounded-2xl p-6 text-center bg-slate-50/50 dark:bg-slate-800/30 transition-all cursor-pointer relative">
                                    <input
                                        type="file"
                                        accept=".xlsx,.xls,.csv"
                                        @change="(e) => importForm.file = e.target.files[0]"
                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                        required
                                    />
                                    <div class="flex flex-col items-center justify-center gap-2 pointer-events-none">
                                        <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                            <Upload class="w-5 h-5" />
                                        </div>
                                        <div>
                                            <span v-if="importForm.file" class="text-xs font-bold text-emerald-700 dark:text-emerald-400">
                                                File terpilih: {{ importForm.file.name }}
                                            </span>
                                            <span v-else class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                                Klik di sini atau seret file Excel ke dalam kotak
                                            </span>
                                            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">
                                                Mendukung format Microsoft Excel (.xlsx, .xls) dan CSV
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="p-3 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl space-y-1.5 text-[11px] text-slate-600 dark:text-slate-400">
                                <div class="font-semibold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                                    <Check class="w-3.5 h-3.5 text-emerald-500" />
                                    <span>Fitur Cerdas Import:</span>
                                </div>
                                <ul class="list-disc pl-5 space-y-0.5">
                                    <li>Jika nama kelas belum ada, sistem akan membuatkan rombel kelas tersebut secara otomatis.</li>
                                    <li>Siswa dengan NISN yang sudah terdaftar akan diperbarui datanya (tidak akan duplikat).</li>
                                    <li>Password default siswa otomatis diatur ke <code class="font-mono text-slate-800 dark:text-slate-200">siswa123</code> bila kolom dikosongkan.</li>
                                </ul>
                            </div>

                            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-200 dark:border-slate-800">
                                <button
                                    type="button"
                                    @click="showModal = false"
                                    class="px-4 py-2 border border-slate-200 dark:border-slate-700 text-xs font-semibold rounded-xl text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    :disabled="!importForm.file || importForm.processing"
                                    class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white text-xs font-semibold rounded-xl shadow-xs hover:shadow flex items-center gap-1.5 transition-all cursor-pointer"
                                >
                                    <Upload class="w-3.5 h-3.5" />
                                    <span>{{ importForm.processing ? 'Mengimpor Data...' : 'Mulai Import Excel' }}</span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- TAB 3: Generate Akun Otomatis per Rombel -->
                    <div v-else-if="!editingStudent && activeTab === 'generate'" class="space-y-4">
                        <div class="bg-amber-50/70 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-900/60 rounded-xl p-3.5 flex items-start gap-3">
                            <Sparkles class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" />
                            <div class="text-xs text-amber-900 dark:text-amber-200 leading-relaxed">
                                <span class="font-bold">Generator Cepat & User-Friendly:</span> Cocok untuk membuat akun siswa 1 rombel/kelas secara instan tanpa perlu repot mengetik nama satu-per-satu atau membuat file Excel manual.
                            </div>
                        </div>

                        <form @submit.prevent="submitGenerate" class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                        Pilih Rombel / Kelas Target *
                                    </label>
                                    <select
                                        v-model="generateForm.class_room_id"
                                        class="w-full text-xs px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-xl focus:border-[#185338] dark:focus:border-emerald-500 focus:outline-none"
                                        required
                                    >
                                        <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                        Jumlah Siswa yang Dibuat *
                                    </label>
                                    <input
                                        v-model.number="generateForm.count"
                                        type="number"
                                        min="1"
                                        max="100"
                                        class="w-full text-xs px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-xl focus:border-[#185338] dark:focus:border-emerald-500 focus:outline-none font-bold"
                                        required
                                    />
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                        Awalan Format NISN
                                    </label>
                                    <input
                                        v-model="generateForm.nisn_prefix"
                                        type="text"
                                        placeholder="Contoh: 202610"
                                        class="w-full text-xs font-mono px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-xl focus:border-[#185338] dark:focus:border-emerald-500 focus:outline-none"
                                        required
                                    />
                                    <span class="text-[10px] text-slate-400 mt-1 block">Contoh hasil: 202610001, 202610002...</span>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                        Nomor Urut Mulai
                                    </label>
                                    <input
                                        v-model.number="generateForm.nisn_start"
                                        type="number"
                                        min="1"
                                        class="w-full text-xs font-mono px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-xl focus:border-[#185338] dark:focus:border-emerald-500 focus:outline-none"
                                        required
                                    />
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                        Awalan Nama Siswa
                                    </label>
                                    <input
                                        v-model="generateForm.name_prefix"
                                        type="text"
                                        placeholder="Contoh: Siswa atau Peserta"
                                        class="w-full text-xs px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-xl focus:border-[#185338] dark:focus:border-emerald-500 focus:outline-none"
                                    />
                                    <span class="text-[10px] text-slate-400 mt-1 block">Contoh: Siswa 01, Siswa 02...</span>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                        Ruang Ujian Default
                                    </label>
                                    <input
                                        v-model="generateForm.exam_room_name"
                                        type="text"
                                        placeholder="Lab Komputer 1"
                                        class="w-full text-xs px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-xl focus:border-[#185338] dark:focus:border-emerald-500 focus:outline-none"
                                    />
                                </div>
                            </div>

                            <!-- Live Preview Box -->
                            <div class="bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl p-3.5 space-y-2">
                                <div class="text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider flex items-center justify-between">
                                    <span>Pratinjau Akun yang Akan Terbentuk (3 Pertama):</span>
                                    <span class="text-[#185338] dark:text-emerald-400 font-normal">Password: {{ generateForm.password || 'siswa123' }}</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                    <div v-for="(p, i) in generatePreview" :key="i" class="p-2.5 bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-700/80 rounded-lg text-xs">
                                        <div class="font-bold text-slate-900 dark:text-slate-100">{{ p.name }}</div>
                                        <div class="font-mono text-[11px] text-[#185338] dark:text-emerald-400 font-semibold">{{ p.username }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-200 dark:border-slate-800">
                                <button
                                    type="button"
                                    @click="showModal = false"
                                    class="px-4 py-2 border border-slate-200 dark:border-slate-700 text-xs font-semibold rounded-xl text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    :disabled="generateForm.processing"
                                    class="px-5 py-2 bg-amber-600 hover:bg-amber-700 disabled:opacity-50 text-white text-xs font-semibold rounded-xl shadow-xs hover:shadow flex items-center gap-1.5 transition-all cursor-pointer"
                                >
                                    <Sparkles class="w-3.5 h-3.5" />
                                    <span>{{ generateForm.processing ? 'Memproses...' : `Buat ${generateForm.count} Akun Otomatis` }}</span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- TAB 4: Input Manual (1 Siswa) OR Edit Mode -->
                    <div v-else class="space-y-4">
                        <form @submit.prevent="submit" class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                    Nama Lengkap Siswa *
                                </label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    placeholder="Contoh: Ahmad Pratama"
                                    class="w-full text-xs px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-xl focus:border-[#185338] dark:focus:border-emerald-500 focus:outline-none"
                                    required
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                    NISN / Nomor Induk Siswa *
                                </label>
                                <input
                                    v-model="form.username"
                                    type="text"
                                    placeholder="Contoh: 2026001"
                                    class="w-full text-xs px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-xl font-mono focus:border-[#185338] dark:focus:border-emerald-500 focus:outline-none"
                                    required
                                />
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                        Rombel / Kelas *
                                    </label>
                                    <select
                                        v-model="form.class_room_id"
                                        class="w-full text-xs px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-xl focus:border-[#185338] dark:focus:border-emerald-500 focus:outline-none"
                                        required
                                    >
                                        <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                        Ruang Ujian
                                    </label>
                                    <input
                                        v-model="form.exam_room_name"
                                        type="text"
                                        placeholder="Lab Komputer 1"
                                        class="w-full text-xs px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-xl focus:border-[#185338] dark:focus:border-emerald-500 focus:outline-none"
                                    />
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                    Kata Sandi Login {{ editingStudent ? '(Kosongkan bila tidak ingin diganti)' : '*' }}
                                </label>
                                <input
                                    v-model="form.password"
                                    type="text"
                                    placeholder="Default: siswa123"
                                    class="w-full text-xs px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-xl font-mono focus:border-[#185338] dark:focus:border-emerald-500 focus:outline-none"
                                    :required="!editingStudent"
                                />
                            </div>

                            <div v-if="editingStudent" class="pt-1">
                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        v-model="form.is_active"
                                        class="rounded text-[#185338] focus:ring-[#185338] dark:bg-slate-800 dark:border-slate-700"
                                    />
                                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                        Akun Siswa Aktif (Bisa Mengikuti Ujian)
                                    </span>
                                </label>
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                                <button
                                    type="button"
                                    @click="showModal = false"
                                    class="px-5 py-2.5 border border-slate-200 dark:border-slate-700 text-xs font-semibold rounded-full text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-750 transition-colors cursor-pointer"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="px-6 py-2.5 bg-[#185338] hover:bg-[#12422c] disabled:opacity-50 text-white text-xs font-semibold rounded-full shadow-sm shadow-emerald-950/20 flex items-center gap-1.5 transition-all cursor-pointer"
                                >
                                    <Check class="w-3.5 h-3.5" />
                                    <span>{{ form.processing ? 'Menyimpan...' : (editingStudent ? 'Simpan Perubahan' : 'Simpan Siswa') }}</span>
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
</template>
