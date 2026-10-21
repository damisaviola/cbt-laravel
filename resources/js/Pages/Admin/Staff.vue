<script setup>
import { ref } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from '@/Components/DataTable.vue';
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
                    class="px-3 py-1.5 bg-[#0F52BA] hover:bg-blue-700 active:scale-[0.98] text-white font-medium text-xs rounded-xl shadow-2xs flex items-center justify-center gap-1.5 transition-all cursor-pointer shrink-0"
                >
                    <Plus class="w-3.5 h-3.5" />
                    <span>Tambah Akun</span>
                </button>
            </template>

            <template #header-left>
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-[#0F52BA] dark:text-blue-400 flex items-center justify-center shrink-0">
                        <Users class="w-4 h-4" />
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
                            ? 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800' 
                            : 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800',
                        'px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider inline-flex items-center gap-1'
                    ]"
                >
                    {{ row.role === 'admin' ? 'Administrator' : row.role === 'teacher' ? 'Guru Mapel' : 'Pengawas' }}
                </span>
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

        <!-- Modern Rounded Modal (Lightweight & Smooth) -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-150 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-100 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto">
                    <!-- Lightweight Backdrop (No blur) -->
                    <div class="fixed inset-0 bg-slate-900/50 dark:bg-slate-950/70" @click="showModal = false" />

                    <!-- Dialog Container -->
                    <div class="relative bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 max-w-md w-full p-6 shadow-xl z-10 transform-gpu" @click.stop>
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 mb-5">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-slate-100">
                                {{ editingStaff ? 'Edit Akun Staf' : 'Tambah Akun Guru / Pengawas' }}
                            </h3>
                            <button @click="showModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-lg leading-none cursor-pointer">
                                &times;
                            </button>
                        </div>

                        <form @submit.prevent="submit" class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Lengkap & Gelar</label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    placeholder="Contoh: Budi Hartono, S.Pd."
                                    class="w-full text-xs px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50/50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:border-[#0F52BA] dark:focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 transition-all"
                                    required
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Username / NIP</label>
                                <input
                                    v-model="form.username"
                                    type="text"
                                    placeholder="Contoh: guru atau 19800101..."
                                    class="w-full text-xs px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl font-mono bg-slate-50/50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:border-[#0F52BA] dark:focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 transition-all"
                                    required
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Peran Hak Akses</label>
                                <select
                                    v-model="form.role"
                                    class="w-full text-xs px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50/50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:border-[#0F52BA] dark:focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 transition-all"
                                    required
                                >
                                    <option value="teacher">Guru Mata Pelajaran</option>
                                    <option value="proctor">Pengawas Ruang Ujian</option>
                                    <option value="admin">Administrator Sekolah</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Kata Sandi {{ editingStaff ? '(Kosongkan jika tidak diubah)' : '' }}
                                </label>
                                <input
                                    v-model="form.password"
                                    type="password"
                                    placeholder="Minimal 4 karakter"
                                    class="w-full text-xs px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50/50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:border-[#0F52BA] dark:focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 transition-all"
                                    :required="!editingStaff"
                                />
                            </div>

                            <div v-if="editingStaff" class="pt-1">
                                <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                                    <input v-model="form.is_active" type="checkbox" class="rounded text-[#0F52BA] focus:ring-0" />
                                    <span>Akun Aktif (Bisa Login)</span>
                                </label>
                            </div>

                            <div class="flex justify-end gap-2.5 pt-4 border-t border-slate-100 dark:border-slate-800">
                                <button
                                    type="button"
                                    @click="showModal = false"
                                    class="px-4 py-2 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-600 dark:text-slate-300 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 active:scale-[0.98] transition-all cursor-pointer"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    class="px-4 py-2 bg-[#0F52BA] hover:bg-blue-700 active:scale-[0.98] text-white text-xs font-semibold rounded-xl shadow-xs transition-all cursor-pointer"
                                >
                                    Simpan Akun
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>
