<script setup>
import { ref } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import Modal from '@/Components/Modal.vue';
import { Users, Plus, Pencil, Trash2, Key } from 'lucide-vue-next';

const props = defineProps({
    staff: Array,
});

const columns = [
    { key: 'name', label: 'Nama Lengkap & Gelar', sortable: true },
    { key: 'username', label: 'Username / NIP', sortable: true },
    { key: 'role', label: 'Peran Akses', sortable: true },
    { key: 'is_active', label: 'Status Akun', align: 'center', sortable: true },
    { key: 'actions', label: 'Aksi', align: 'right', sortable: false },
];

const showModal = ref(false);
const editingStaff = ref(null);

const form = useForm({
    name: '',
    username: '',
    email: '',
    password: '',
    role: 'teacher',
    is_active: true,
});

const openCreate = () => {
    editingStaff.value = null;
    form.reset();
    form.role = 'teacher';
    form.is_active = true;
    showModal.value = true;
};

const openEdit = (user) => {
    editingStaff.value = user;
    form.name = user.name;
    form.username = user.username;
    form.email = user.email || '';
    form.password = '';
    form.role = user.role;
    form.is_active = Boolean(user.is_active);
    showModal.value = true;
};

const submit = () => {
    if (editingStaff.value) {
        form.put(`/admin/staff/${editingStaff.value.id}`, {
            onSuccess: () => (showModal.value = false),
        });
    } else {
        form.post('/admin/staff', {
            onSuccess: () => (showModal.value = false),
        });
    }
};

import { useConfirm } from '@/Composables/useConfirm';

const { confirm } = useConfirm();

const deleteStaff = async (user) => {
    const ok = await confirm({
        title: 'Hapus Akun Pengguna',
        message: `Apakah Anda yakin ingin menghapus akun "${user.name}" (${user.username})? Tindakan ini akan mencabut seluruh hak akses akun.`,
        confirmText: 'Ya, Hapus Akun',
        cancelText: 'Batal',
        type: 'danger',
    });
    if (ok) {
        form.delete(`/admin/staff/${user.id}`);
    }
};
</script>

<template>
    <AppLayout>
        <Head title="Manajemen Guru & Pengawas" />

        <template #header>
            <div class="min-w-0">
                <h1 class="text-base sm:text-lg font-bold text-slate-900 dark:text-slate-100 tracking-tight">Tenaga Pendidik & Pengawas Ruang</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">Kelola akun akses Guru Pembuat Soal, Pengawas Ujian, dan Administrator.</p>
            </div>
        </template>

        <!-- Staff Table Card -->
        <DataTable
            :items="staff"
            :columns="columns"
            title="Daftar Akun Tenaga Pendidik & Pengawas"
            subtitle="Kelola akun akses Guru Pembuat Soal, Pengawas Ujian, dan Administrator."
            search-placeholder="Cari nama, NIP, atau peran..."
            :per-page="10"
        >
            <template #header-actions>
                <button
                    @click="openCreate"
                    class="px-5 py-2.5 bg-[#185338] hover:bg-[#12422c] active:scale-[0.98] text-white font-bold text-xs rounded-full shadow-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer shrink-0"
                >
                    <Plus class="w-3.5 h-3.5" />
                    <span>Tambah Akun</span>
                </button>
            </template>

            <template #header-left>
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-2xl bg-[#eaf4ef] dark:bg-emerald-950/60 text-[#185338] dark:text-emerald-400 flex items-center justify-center shrink-0">
                        <Users class="w-4.5 h-4.5" />
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Daftar Akun Tenaga Pendidik & Pengawas</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Kelola akun akses Guru Pembuat Soal, Pengawas Ujian, dan Administrator.</p>
                    </div>
                </div>
            </template>

            <template #cell-name="{ row }">
                <span class="font-semibold text-slate-900 dark:text-white">{{ row.name }}</span>
            </template>

            <template #cell-username="{ row }">
                <span class="font-mono text-slate-600 dark:text-slate-300">{{ row.username }}</span>
            </template>

            <template #cell-role="{ row }">
                <span
                    :class="[
                        row.role === 'admin' 
                            ? 'bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800' 
                            : row.role === 'teacher' 
                            ? 'bg-[#eaf4ef] dark:bg-emerald-950/60 text-[#185338] dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800' 
                            : 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800',
                        'px-3 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider inline-flex items-center gap-1'
                    ]"
                >
                    {{ row.role === 'admin' ? 'Administrator' : row.role === 'teacher' ? 'Guru Mapel' : 'Pengawas' }}
                </span>
            </template>

            <template #cell-is_active="{ row }">
                <span :class="[row.is_active ? 'text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800' : 'text-rose-700 dark:text-rose-300 bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800', 'px-3 py-0.5 rounded-full text-xs font-bold inline-flex items-center gap-1']">
                    <span :class="[row.is_active ? 'bg-emerald-500' : 'bg-rose-500', 'w-1.5 h-1.5 rounded-full']"></span>
                    {{ row.is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </template>

            <template #actions="{ row }">
                <div class="inline-flex items-center gap-1">
                    <button
                        @click="openEdit(row)"
                        class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 rounded-lg transition-colors cursor-pointer"
                        title="Edit Akun"
                    >
                        <Pencil class="w-4 h-4" />
                    </button>
                    <button
                        @click="deleteStaff(row)"
                        class="p-1.5 hover:bg-rose-50 dark:hover:bg-rose-950/50 text-rose-600 dark:text-rose-400 rounded-lg transition-colors cursor-pointer"
                        title="Hapus Akun"
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
            :title="editingStaff ? 'Edit Akun Staf' : 'Tambah Akun Guru / Pengawas'"
            subtitle="Atur identitas akun pendidik, hak akses pengawas atau guru mapel"
            max-width="md"
        >
            <template #icon>
                <div class="w-10 h-10 rounded-2xl bg-[#eaf4ef] dark:bg-emerald-950/60 text-[#185338] dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <Users class="w-5 h-5" />
                </div>
            </template>

            <form id="staffForm" @submit.prevent="submit" class="space-y-4 pt-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nama Lengkap & Gelar</label>
                    <input
                        v-model="form.name"
                        type="text"
                        placeholder="Contoh: Budi Hartono, S.Pd."
                        class="w-full text-xs px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50/70 dark:bg-slate-800/60 text-slate-900 dark:text-white focus:bg-white dark:focus:bg-slate-800 focus:border-[#185338] dark:focus:border-emerald-500 focus:ring-4 focus:ring-[#185338]/10 focus:outline-none transition-all"
                        required
                    />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Username / NIP</label>
                    <input
                        v-model="form.username"
                        type="text"
                        placeholder="Contoh: guru atau 19800101..."
                        class="w-full text-xs px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl font-mono bg-slate-50/70 dark:bg-slate-800/60 text-slate-900 dark:text-white focus:bg-white dark:focus:bg-slate-800 focus:border-[#185338] dark:focus:border-emerald-500 focus:ring-4 focus:ring-[#185338]/10 focus:outline-none transition-all"
                        required
                    />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Peran Hak Akses</label>
                    <select
                        v-model="form.role"
                        class="w-full text-xs px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50/70 dark:bg-slate-800/60 text-slate-900 dark:text-white focus:bg-white dark:focus:bg-slate-800 focus:border-[#185338] dark:focus:border-emerald-500 focus:ring-4 focus:ring-[#185338]/10 focus:outline-none font-medium transition-all"
                        required
                    >
                        <option value="teacher">Guru Mata Pelajaran</option>
                        <option value="proctor">Pengawas Ruang Ujian</option>
                        <option value="admin">Administrator Sekolah</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Kata Sandi {{ editingStaff ? '(Kosongkan jika tidak diubah)' : '' }}
                    </label>
                    <input
                        v-model="form.password"
                        type="password"
                        placeholder="Minimal 4 karakter"
                        class="w-full text-xs px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50/70 dark:bg-slate-800/60 text-slate-900 dark:text-white focus:bg-white dark:focus:bg-slate-800 focus:border-[#185338] dark:focus:border-emerald-500 focus:ring-4 focus:ring-[#185338]/10 focus:outline-none transition-all"
                        :required="!editingStaff"
                    />
                </div>

                <div v-if="editingStaff" class="pt-1">
                    <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                        <input v-model="form.is_active" type="checkbox" class="rounded border-slate-300 dark:border-slate-600 text-[#185338] focus:ring-0" />
                        <span>Akun Aktif (Bisa Login)</span>
                    </label>
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
                        form="staffForm"
                        :disabled="form.processing"
                        class="px-6 py-2.5 bg-[#185338] hover:bg-[#12422c] text-white text-xs font-semibold rounded-full shadow-xs transition-all cursor-pointer"
                    >
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Akun' }}
                    </button>
                </div>
            </template>
        </Modal>
    </AppLayout>
</template>
