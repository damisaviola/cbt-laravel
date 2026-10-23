<script setup>
import { ref } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import Modal from '@/Components/Modal.vue';
import { BookOpen, Plus, Pencil, Trash2 } from 'lucide-vue-next';

const props = defineProps({
    subjects: Array,
});

const columns = [
    { key: 'code', label: 'Kode Mapel', sortable: true },
    { key: 'name', label: 'Nama Mata Pelajaran', sortable: true },
    { key: 'grade_level', label: 'Tingkat Kelas', sortable: true },
    { key: 'kkm_score', label: 'Standar KKM', align: 'center', sortable: true },
    { key: 'question_banks_count', label: 'Soal Ujian Terkait', align: 'center', sortable: true },
    { key: 'actions', label: 'Aksi', align: 'right', sortable: false },
];

const showModal = ref(false);
const editingSubject = ref(null);

const form = useForm({
    code: '',
    name: '',
    grade_level: 10,
    kkm_score: 75.00,
});

const openCreate = () => {
    editingSubject.value = null;
    form.reset();
    form.grade_level = 10;
    form.kkm_score = 75.00;
    showModal.value = true;
};

const openEdit = (sub) => {
    editingSubject.value = sub;
    form.code = sub.code;
    form.name = sub.name;
    form.grade_level = sub.grade_level || 10;
    form.kkm_score = sub.kkm_score;
    showModal.value = true;
};

const submit = () => {
    if (editingSubject.value) {
        form.put(`/admin/subjects/${editingSubject.value.id}`, {
            onSuccess: () => (showModal.value = false),
        });
    } else {
        form.post('/admin/subjects', {
            onSuccess: () => (showModal.value = false),
        });
    }
};

import { useConfirm } from '@/Composables/useConfirm';

const { confirm } = useConfirm();

const deleteSubject = async (sub) => {
    const ok = await confirm({
        title: 'Hapus Mata Pelajaran',
        message: `Apakah Anda yakin ingin menghapus mata pelajaran "${sub.name}" (${sub.code})? Data soal ujian terkait mungkin terpengaruh.`,
        confirmText: 'Ya, Hapus Mapel',
        cancelText: 'Batal',
        type: 'danger',
    });
    if (ok) {
        form.delete(`/admin/subjects/${sub.id}`);
    }
};
</script>

<template>
    <AppLayout>
        <Head title="Manajemen Mata Pelajaran" />

        <template #header>
            <div class="min-w-0">
                <h1 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white tracking-tight">Mata Pelajaran & Standar KKM</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">Kelola kurikulum mata pelajaran dan Kriteria Ketuntasan Minimal.</p>
            </div>
        </template>

        <!-- Subjects Table Card -->
        <DataTable
            :items="subjects"
            :columns="columns"
            title="Daftar Mata Pelajaran Terdaftar"
            subtitle="Kelola kurikulum mata pelajaran dan Kriteria Ketuntasan Minimal."
            search-placeholder="Cari kode atau nama mapel..."
            :per-page="10"
        >
            <template #header-actions>
                <button
                    @click="openCreate"
                    class="px-3 py-1.5 bg-[#0F52BA] hover:bg-blue-700 active:scale-[0.98] text-white font-medium text-xs rounded-xl shadow-2xs flex items-center justify-center gap-1.5 transition-all cursor-pointer shrink-0"
                >
                    <Plus class="w-3.5 h-3.5" />
                    <span>Tambah Mapel</span>
                </button>
            </template>

            <template #header-left>
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-950/40 text-[#0F52BA] dark:text-blue-400 flex items-center justify-center shrink-0">
                        <BookOpen class="w-4 h-4" />
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Daftar Mata Pelajaran Terdaftar</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Kelola kurikulum mata pelajaran dan Kriteria Ketuntasan Minimal.</p>
                    </div>
                </div>
            </template>

            <template #cell-code="{ row }">
                <span class="font-mono font-bold text-slate-800 dark:text-slate-200">{{ row.code }}</span>
            </template>

            <template #cell-name="{ row }">
                <span class="font-semibold text-slate-900 dark:text-white">{{ row.name }}</span>
            </template>

            <template #cell-grade_level="{ row }">
                <span class="px-2.5 py-1 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-medium border border-transparent dark:border-slate-700/60">
                    {{ row.grade_level ? 'Kelas ' + row.grade_level : 'Semua Tingkat' }}
                </span>
            </template>

            <template #cell-kkm_score="{ row }">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-mono font-bold bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-800/60">
                    {{ Number(row.kkm_score).toFixed(0) }}
                </span>
            </template>

            <template #cell-question_banks_count="{ row }">
                <span class="font-mono tabular-nums text-slate-700 dark:text-slate-200 font-semibold">
                    {{ row.question_banks_count || 0 }}
                </span>
            </template>

            <template #actions="{ row }">
                <div class="inline-flex items-center gap-1">
                    <button
                        @click="openEdit(row)"
                        class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 rounded-lg transition-colors cursor-pointer"
                        title="Edit Mapel"
                    >
                        <Pencil class="w-4 h-4" />
                    </button>
                    <button
                        @click="deleteSubject(row)"
                        class="p-1.5 hover:bg-rose-50 dark:hover:bg-rose-950/30 text-rose-600 dark:text-rose-400 rounded-lg transition-colors cursor-pointer"
                        title="Hapus Mapel"
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
            :title="editingSubject ? 'Edit Mata Pelajaran' : 'Tambah Mata Pelajaran'"
            subtitle="Atur identitas mapel, kode kurikulum, tingkat kelas, dan standar KKM"
            max-width="md"
        >
            <template #icon>
                <div class="w-10 h-10 rounded-2xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                    <BookOpen class="w-5 h-5" />
                </div>
            </template>

            <form id="subjectForm" @submit.prevent="submit" class="space-y-4 pt-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Kode Mapel</label>
                    <input
                        v-model="form.code"
                        type="text"
                        placeholder="Contoh: MAT-10"
                        class="w-full text-xs px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl font-mono bg-slate-50/70 dark:bg-slate-800/60 text-slate-900 dark:text-white focus:bg-white dark:focus:bg-slate-800 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 focus:outline-none transition-all"
                        required
                    />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nama Mata Pelajaran</label>
                    <input
                        v-model="form.name"
                        type="text"
                        placeholder="Contoh: Matematika Wajib"
                        class="w-full text-xs px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50/70 dark:bg-slate-800/60 text-slate-900 dark:text-white focus:bg-white dark:focus:bg-slate-800 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 focus:outline-none transition-all"
                        required
                    />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tingkat Kelas</label>
                        <select
                            v-model="form.grade_level"
                            class="w-full text-xs px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50/70 dark:bg-slate-800/60 text-slate-900 dark:text-white focus:bg-white dark:focus:bg-slate-800 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 focus:outline-none transition-all"
                        >
                            <option :value="null">Semua Tingkat</option>
                            <option v-for="g in [7,8,9,10,11,12]" :key="g" :value="g">Kelas {{ g }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nilai KKM (0-100)</label>
                        <input
                            v-model="form.kkm_score"
                            type="number"
                            step="0.5"
                            min="0"
                            max="100"
                            class="w-full text-xs px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl font-mono bg-slate-50/70 dark:bg-slate-800/60 text-slate-900 dark:text-white focus:bg-white dark:focus:bg-slate-800 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 focus:outline-none transition-all"
                            required
                        />
                    </div>
                </div>
            </form>

            <template #footer>
                <div class="flex items-center justify-end gap-3">
                    <button
                        type="button"
                        @click="showModal = false"
                        class="px-5 py-2.5 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-300 rounded-full bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-750 transition-colors cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        form="subjectForm"
                        :disabled="form.processing"
                        class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-full shadow-sm shadow-blue-600/25 transition-all cursor-pointer"
                    >
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Mapel' }}
                    </button>
                </div>
            </template>
        </Modal>
    </AppLayout>
</template>
