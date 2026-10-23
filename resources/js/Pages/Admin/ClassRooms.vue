<script setup>
import { ref } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import Modal from '@/Components/Modal.vue';
import { Layers, Plus, Pencil, Trash2 } from 'lucide-vue-next';

const props = defineProps({
    classes: Array,
    teachers: Array,
});

const columns = [
    { key: 'grade_level', label: 'Tingkat', sortable: true },
    { key: 'major_code', label: 'Jurusan / Peminatan', sortable: true },
    { key: 'name', label: 'Nama Rombel', sortable: true },
    { key: 'homeroom_teacher.name', label: 'Wali Kelas', sortable: true },
    { key: 'students.length', label: 'Jumlah Siswa', align: 'center', sortable: true },
    { key: 'actions', label: 'Aksi', align: 'right', sortable: false },
];

const showModal = ref(false);
const editingClass = ref(null);

const form = useForm({
    grade_level: 10,
    major_code: 'MIPA',
    name: '',
    homeroom_teacher_id: '',
});

const openCreate = () => {
    editingClass.value = null;
    form.reset();
    form.grade_level = 10;
    form.major_code = 'MIPA';
    showModal.value = true;
};

const openEdit = (cls) => {
    editingClass.value = cls;
    form.grade_level = cls.grade_level;
    form.major_code = cls.major_code;
    form.name = cls.name;
    form.homeroom_teacher_id = cls.homeroom_teacher_id || '';
    showModal.value = true;
};

const submit = () => {
    if (editingClass.value) {
        form.put(`/admin/classes/${editingClass.value.id}`, {
            onSuccess: () => (showModal.value = false),
        });
    } else {
        form.post('/admin/classes', {
            onSuccess: () => (showModal.value = false),
        });
    }
};

import { useConfirm } from '@/Composables/useConfirm';

const { confirm } = useConfirm();

const deleteClass = async (cls) => {
    const ok = await confirm({
        title: 'Hapus Rombongan Belajar',
        message: `Apakah Anda yakin ingin menghapus rombel "${cls.name}"? Pastikan tidak ada siswa yang masih terdaftar di rombel ini.`,
        confirmText: 'Ya, Hapus Rombel',
        cancelText: 'Batal',
        type: 'danger',
    });
    if (ok) {
        form.delete(`/admin/classes/${cls.id}`);
    }
};
</script>

<template>
    <AppLayout>
        <Head title="Manajemen Rombel / Kelas" />

        <template #header>
            <div class="min-w-0">
                <h1 class="text-base sm:text-lg font-bold text-slate-900 dark:text-slate-100 tracking-tight">Rombongan Belajar (Rombel / Kelas)</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">Kelola tingkat kelas, peminatan/jurusan, dan penugasan wali kelas.</p>
            </div>
        </template>

        <!-- Classes Table Card -->
        <DataTable
            :items="classes"
            :columns="columns"
            title="Daftar Rombongan Belajar (Rombel / Kelas)"
            subtitle="Kelola tingkat kelas, peminatan/jurusan, dan penugasan wali kelas."
            search-placeholder="Cari kelas, jurusan, wali..."
            :per-page="10"
        >
            <template #header-actions>
                <button
                    @click="openCreate"
                    class="px-3 py-1.5 bg-[#0F52BA] hover:bg-blue-700 active:scale-[0.98] text-white font-medium text-xs rounded-xl shadow-2xs flex items-center justify-center gap-1.5 transition-all cursor-pointer shrink-0"
                >
                    <Plus class="w-3.5 h-3.5" />
                    <span>Tambah Rombel</span>
                </button>
            </template>

            <template #header-left>
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-[#0F52BA] dark:text-blue-400 flex items-center justify-center shrink-0">
                        <Layers class="w-4 h-4" />
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Daftar Rombongan Belajar (Rombel / Kelas)</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Kelola tingkat kelas, peminatan/jurusan, dan penugasan wali kelas.</p>
                    </div>
                </div>
            </template>

            <template #cell-grade_level="{ row }">
                <span class="font-semibold font-mono text-slate-800 dark:text-slate-100">Kelas {{ row.grade_level }}</span>
            </template>

            <template #cell-major_code="{ row }">
                <span class="px-2.5 py-0.5 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/60 dark:border-slate-700">
                    {{ row.major_code }}
                </span>
            </template>

            <template #cell-name="{ row }">
                <span class="font-bold text-slate-900 dark:text-white">{{ row.name }}</span>
            </template>

            <template #cell-homeroom_teacher.name="{ row }">
                <span class="text-slate-700 dark:text-slate-300">{{ row.homeroom_teacher ? row.homeroom_teacher.name : '-' }}</span>
            </template>

            <template #cell-students.length="{ row }">
                <span class="font-mono tabular-nums font-semibold text-slate-700 dark:text-slate-300">
                    {{ row.students?.length || 0 }}
                </span>
            </template>

            <template #actions="{ row }">
                <div class="inline-flex items-center gap-1">
                    <button
                        @click="openEdit(row)"
                        class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 rounded-lg transition-colors cursor-pointer"
                        title="Edit Rombel"
                    >
                        <Pencil class="w-4 h-4" />
                    </button>
                    <button
                        @click="deleteClass(row)"
                        class="p-1.5 hover:bg-rose-50 dark:hover:bg-rose-950/50 text-rose-600 dark:text-rose-400 rounded-lg transition-colors cursor-pointer"
                        title="Hapus Rombel"
                    >
                        <Trash2 class="w-4 h-4" />
                    </button>
                </div>
            </template>
        </DataTable>

        <!-- Modal using EduPro Modal Component -->
        <Modal
            :show="showModal"
            @close="showModal = false"
            :title="editingClass ? 'Edit Rombongan Belajar' : 'Tambah Rombongan Belajar'"
            subtitle="Atur data rombel kelas, jurusan peminatan, dan wali kelas"
            max-width="md"
        >
            <template #icon>
                <div class="w-10 h-10 rounded-2xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                    <Layers class="w-5 h-5" />
                </div>
            </template>

            <form id="classRoomForm" @submit.prevent="submit" class="space-y-4 pt-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tingkat Kelas</label>
                    <select v-model="form.grade_level" class="w-full text-xs px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50/70 dark:bg-slate-800/60 text-slate-900 dark:text-white focus:bg-white dark:focus:bg-slate-800 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 focus:outline-none font-medium transition-all" required>
                        <option v-for="g in [7,8,9,10,11,12]" :key="g" :value="g">Kelas {{ g }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Jurusan / Peminatan</label>
                    <input v-model="form.major_code" type="text" placeholder="Contoh: MIPA, IPS, TKJ, Umum" class="w-full text-xs px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/60 text-slate-900 dark:text-white rounded-xl focus:bg-white dark:focus:bg-slate-800 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 focus:outline-none transition-all" required />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nama Rombel / Kelas</label>
                    <input v-model="form.name" type="text" placeholder="Contoh: X-MIPA-1" class="w-full text-xs px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/60 text-slate-900 dark:text-white rounded-xl focus:bg-white dark:focus:bg-slate-800 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 focus:outline-none transition-all" required />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Wali Kelas (Opsional)</label>
                    <select v-model="form.homeroom_teacher_id" class="w-full text-xs px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50/70 dark:bg-slate-800/60 text-slate-900 dark:text-white focus:bg-white dark:focus:bg-slate-800 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 focus:outline-none transition-all">
                        <option value="">-- Pilih Guru Wali Kelas --</option>
                        <option v-for="t in teachers" :key="t.id" :value="t.id">{{ t.name }}</option>
                    </select>
                </div>
            </form>

            <template #footer>
                <div class="flex items-center justify-end gap-3">
                    <button type="button" @click="showModal = false" class="px-5 py-2.5 border border-slate-200 dark:border-slate-700 text-xs font-semibold rounded-full text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-750 transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" form="classRoomForm" :disabled="form.processing" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-full shadow-sm shadow-blue-600/25 transition-all cursor-pointer">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Rombel' }}
                    </button>
                </div>
            </template>
        </Modal>
    </AppLayout>
</template>
