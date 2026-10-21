<script setup>
import { ref, computed } from 'vue';
import { useForm, Link, Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { 
    BookOpen, 
    Plus, 
    Layers, 
    ArrowRight, 
    Trash2, 
    Search, 
    Download 
} from 'lucide-vue-next';
import { useConfirm } from '@/Composables/useConfirm';

const props = defineProps({
    banks: Array,
    subjects: Array,
});

const searchQuery = ref('');
const selectedSubject = ref('');
const showModal = ref(false);

const form = useForm({
    subject_id: props.subjects[0]?.id || '',
    title: '',
    pg_weight_percent: 70,
    essay_weight_percent: 30,
});

const openCreate = () => {
    form.reset();
    form.subject_id = props.subjects[0]?.id || '';
    form.pg_weight_percent = 70;
    form.essay_weight_percent = 30;
    showModal.value = true;
};

const submit = () => {
    form.post('/teacher/banks', {
        onSuccess: () => (showModal.value = false),
    });
};

const { confirm } = useConfirm();

const deleteBank = async (bank) => {
    const ok = await confirm({
        title: 'Hapus Soal Ujian',
        message: `Apakah Anda yakin ingin menghapus paket soal ujian "${bank.title}"? Seluruh butir soal, gambar, dan kunci jawaban di dalamnya akan dihapus secara permanen.`,
        confirmText: 'Ya, Hapus Soal Ujian',
        cancelText: 'Batal',
        type: 'danger',
    });
    if (ok) {
        form.delete(`/teacher/banks/${bank.id}`);
    }
};

const downloadTemplate = () => {
    window.location.href = '/teacher/template/excel';
};

const filteredBanks = computed(() => {
    return props.banks.filter(b => {
        const matchesSearch = !searchQuery.value || 
            b.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            b.subject?.name?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            b.subject?.code?.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesSubject = !selectedSubject.value || b.subject_id === Number(selectedSubject.value);
        return matchesSearch && matchesSubject;
    });
});
</script>

<template>
    <AppLayout>
        <Head title="Soal Ujian Guru" />

        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between w-full gap-3">
                <div>
                    <h1 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white tracking-tight">Soal Ujian Mata Pelajaran</h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">Kelola naskah soal ujian kurikulum Merdeka & ANBK (PG, Kompleks, Menjodohkan, Esai).</p>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <button
                        @click="downloadTemplate"
                        class="px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-semibold text-xs rounded-xl shadow-xs flex items-center gap-1.5 transition-all cursor-pointer"
                    >
                        <Download class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" />
                        <span>Format Excel</span>
                    </button>

                    <button
                        @click="openCreate"
                        class="px-3.5 py-2 bg-[#0F52BA] hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow-xs hover:shadow flex items-center gap-1.5 transition-all cursor-pointer"
                    >
                        <Plus class="w-3.5 h-3.5" />
                        <span>Buat Soal Ujian</span>
                    </button>
                </div>
            </div>
        </template>

        <div class="space-y-4">
            <!-- Filter & Search Bar (Rounded-xl) -->
            <div class="bg-white dark:bg-slate-900 p-3.5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="relative w-full sm:w-80">
                    <Search class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Cari judul paket atau mapel..."
                        class="w-full pl-9 pr-3 py-2 text-xs bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:border-[#0F52BA] dark:focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 focus:outline-none transition-all"
                    />
                </div>

                <div class="w-full sm:w-auto">
                    <select
                        v-model="selectedSubject"
                        class="w-full sm:w-auto text-xs px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:border-[#0F52BA] dark:focus:border-blue-500 focus:outline-none font-medium"
                    >
                        <option value="">Semua Mata Pelajaran</option>
                        <option v-for="s in subjects" :key="s.id" :value="s.id">
                            {{ s.name }} ({{ s.code }})
                        </option>
                    </select>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="filteredBanks.length === 0" class="bg-white dark:bg-slate-900 p-12 rounded-2xl border border-dashed border-slate-200/90 dark:border-slate-800 text-center text-xs text-slate-500 dark:text-slate-400 shadow-xs">
                Belum ada soal ujian yang sesuai kriteria pencarian.
            </div>

            <!-- Banks Grid (Rounded-2xl Cards) -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div
                    v-for="bank in filteredBanks"
                    :key="bank.id"
                    class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 p-5 shadow-xs hover:border-[#0F52BA] dark:hover:border-blue-500 hover:shadow-sm transition-all duration-150 flex flex-col justify-between"
                >
                    <div>
                        <div class="flex items-center justify-between mb-2.5">
                            <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-mono font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                {{ bank.subject?.code }}
                            </span>
                            <span class="text-xs font-mono font-bold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 px-2.5 py-0.5 rounded-lg">
                                {{ bank.questions_count }} Butir Soal
                            </span>
                        </div>

                        <h2 class="text-xs font-bold text-slate-900 dark:text-white line-clamp-2 mb-1">
                            {{ bank.title }}
                        </h2>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-3.5">
                            Mapel: {{ bank.subject?.name }}
                        </p>

                        <!-- Weights -->
                        <div class="p-3 bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80 rounded-xl text-xs mb-3 flex items-center justify-between font-mono text-slate-700 dark:text-slate-300">
                            <span>Bobot PG: <strong>{{ bank.pg_weight_percent }}%</strong></span>
                            <span class="text-slate-300 dark:text-slate-600">&bull;</span>
                            <span>Bobot Esai: <strong>{{ bank.essay_weight_percent }}%</strong></span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button
                            @click="deleteBank(bank)"
                            class="text-xs text-slate-400 hover:text-red-700 dark:hover:text-red-400 p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-950/40 transition-colors cursor-pointer"
                            title="Hapus Soal Ujian"
                        >
                            <Trash2 class="w-4 h-4" />
                        </button>

                        <Link
                            :href="`/teacher/banks/${bank.id}`"
                            class="px-3.5 py-1.5 bg-[#0F52BA] hover:bg-blue-700 text-white font-semibold text-xs rounded-xl flex items-center gap-1.5 shadow-xs hover:shadow transition-all cursor-pointer"
                        >
                            <span>Kelola Soal</span>
                            <ArrowRight class="w-3.5 h-3.5" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Modal (Lightweight & Smooth) -->
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
                    v-if="showModal"
                    @click="showModal = false"
                    class="fixed inset-0 bg-slate-900/50 dark:bg-slate-950/70 flex items-center justify-center p-4 z-50 overflow-y-auto"
                >
                    <div
                        @click.stop
                        class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 max-w-md w-full p-6 shadow-xl space-y-4 my-auto transform-gpu"
                    >
                        <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">
                                Buat Soal Ujian Baru
                            </h3>
                            <button
                                type="button"
                                @click="showModal = false"
                                class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-lg cursor-pointer transition-colors"
                                aria-label="Tutup"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <form @submit.prevent="submit" class="space-y-3.5 text-xs">
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Mata Pelajaran</label>
                                <select v-model="form.subject_id" class="w-full px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-[#0F52BA] dark:focus:border-blue-500 focus:outline-none font-medium" required>
                                    <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }} ({{ s.code }})</option>
                                </select>
                            </div>

                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Judul Soal Ujian</label>
                                <input
                                    v-model="form.title"
                                    type="text"
                                    placeholder="Contoh: Soal Ujian SAS Ganjil Matematika Kelas X"
                                    class="w-full px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:border-[#0F52BA] dark:focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 focus:outline-none transition-all"
                                    required
                                />
                            </div>

                            <div class="grid grid-cols-2 gap-3 p-3.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl">
                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Bobot PG (%)</label>
                                    <input v-model.number="form.pg_weight_percent" type="number" min="0" max="100" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-lg font-mono bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-[#0F52BA] dark:focus:border-blue-500 focus:outline-none" required />
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Bobot Esai (%)</label>
                                    <input v-model.number="form.essay_weight_percent" type="number" min="0" max="100" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-lg font-mono bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-[#0F52BA] dark:focus:border-blue-500 focus:outline-none" required />
                                </div>
                                <p class="col-span-2 text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                                    Total bobot PG + Esai harus tepat 100%. Saat ini: <strong :class="form.pg_weight_percent + form.essay_weight_percent === 100 ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">{{ form.pg_weight_percent + form.essay_weight_percent }}%</strong>
                                </p>
                            </div>

                            <div class="flex justify-end gap-2 pt-2 border-t border-slate-200 dark:border-slate-800">
                                <button type="button" @click="showModal = false" class="px-4 py-2 border border-slate-200 dark:border-slate-700 font-semibold rounded-xl text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors cursor-pointer">
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    :disabled="form.processing || (form.pg_weight_percent + form.essay_weight_percent !== 100)"
                                    class="px-5 py-2 bg-[#0F52BA] hover:bg-blue-700 text-white font-semibold rounded-xl shadow-xs hover:shadow disabled:opacity-50 transition-all cursor-pointer"
                                >
                                    {{ form.processing ? 'Menyimpan...' : 'Buat Soal Ujian' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>
