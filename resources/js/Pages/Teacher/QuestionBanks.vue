<script setup>
import { ref, computed } from 'vue';
import { useForm, Link, Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
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
                        class="px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-semibold text-xs rounded-full shadow-2xs flex items-center gap-1.5 transition-all cursor-pointer"
                    >
                        <Download class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" />
                        <span>Format Excel</span>
                    </button>

                    <button
                        @click="openCreate"
                        class="px-5 py-2.5 bg-[#185338] hover:bg-[#12422c] text-white font-bold text-xs rounded-full shadow-xs flex items-center gap-1.5 transition-all cursor-pointer"
                    >
                        <Plus class="w-3.5 h-3.5" />
                        <span>Buat Soal Ujian</span>
                    </button>
                </div>
            </div>
        </template>

        <div class="space-y-4">
            <!-- Filter & Search Bar (Rounded-3xl) -->
            <div class="bg-white dark:bg-slate-900 p-4 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="relative w-full sm:w-80">
                    <Search class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Cari judul paket atau mapel..."
                        class="w-full pl-10 pr-3.5 py-2 text-xs bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700 rounded-full text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:border-[#185338] dark:focus:border-emerald-500 focus:ring-2 focus:ring-[#185338]/10 focus:outline-none transition-all shadow-2xs"
                    />
                </div>

                <div class="w-full sm:w-auto">
                    <select
                        v-model="selectedSubject"
                        class="w-full sm:w-auto text-xs px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700 rounded-full text-slate-900 dark:text-white focus:border-[#185338] dark:focus:border-emerald-500 focus:outline-none font-semibold shadow-2xs cursor-pointer"
                    >
                        <option value="">Semua Mata Pelajaran</option>
                        <option v-for="s in subjects" :key="s.id" :value="s.id">
                            {{ s.name }} ({{ s.code }})
                        </option>
                    </select>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="filteredBanks.length === 0" class="bg-white dark:bg-slate-900 p-12 rounded-3xl border border-dashed border-slate-200/80 dark:border-slate-800 text-center text-xs text-slate-500 dark:text-slate-400 shadow-xs">
                Belum ada soal ujian yang sesuai kriteria pencarian.
            </div>

            <!-- Banks Grid (Rounded-3xl Cards) -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div
                    v-for="bank in filteredBanks"
                    :key="bank.id"
                    class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 p-5 sm:p-6 shadow-xs hover:border-[#185338] dark:hover:border-emerald-500 hover:shadow-sm transition-all duration-150 flex flex-col justify-between"
                >
                    <div>
                        <div class="flex items-center justify-between mb-2.5">
                            <span class="px-3 py-0.5 rounded-full text-[10px] font-mono font-bold bg-[#eaf4ef] dark:bg-emerald-950/60 text-[#185338] dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800">
                                {{ bank.subject?.code }}
                            </span>
                            <span class="text-xs font-mono font-bold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 px-3 py-0.5 rounded-full">
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
                        <div class="p-3 bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl text-xs mb-3 flex items-center justify-between font-mono text-slate-700 dark:text-slate-300">
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
                            class="px-4 py-2 bg-[#185338] hover:bg-[#12422c] text-white font-bold text-xs rounded-full flex items-center gap-1.5 shadow-xs transition-all cursor-pointer"
                        >
                            <span>Kelola Soal</span>
                            <ArrowRight class="w-3.5 h-3.5" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Modal using EduPro Modal Component -->
        <Modal
            :show="showModal"
            @close="showModal = false"
            title="Buat Soal Ujian Baru"
            subtitle="Tentukan mata pelajaran, judul bank soal, dan persentase bobot soal"
            max-width="md"
        >
            <template #icon>
                <div class="w-10 h-10 rounded-2xl bg-[#eaf4ef] dark:bg-emerald-950/60 text-[#185338] dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <BookOpen class="w-5 h-5" />
                </div>
            </template>

            <form id="createBankModalForm" @submit.prevent="submit" class="space-y-4 pt-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Mata Pelajaran</label>
                    <select v-model="form.subject_id" class="w-full text-xs px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50/70 dark:bg-slate-800/60 text-slate-900 dark:text-white focus:bg-white dark:focus:bg-slate-800 focus:border-[#185338] dark:focus:border-emerald-500 focus:ring-4 focus:ring-[#185338]/10 focus:outline-none font-medium transition-all" required>
                        <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }} ({{ s.code }})</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Judul Soal Ujian</label>
                    <input
                        v-model="form.title"
                        type="text"
                        placeholder="Contoh: Soal Ujian SAS Ganjil Matematika Kelas X"
                        class="w-full text-xs px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50/70 dark:bg-slate-800/60 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:bg-white dark:focus:bg-slate-800 focus:border-[#185338] dark:focus:border-emerald-500 focus:ring-4 focus:ring-[#185338]/10 focus:outline-none transition-all"
                        required
                    />
                </div>

                <div class="grid grid-cols-2 gap-3 p-4 bg-slate-50/70 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800 rounded-2xl">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Bobot PG (%)</label>
                        <input v-model.number="form.pg_weight_percent" type="number" min="0" max="100" class="w-full text-xs px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl font-mono bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-[#185338] focus:outline-none" required />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Bobot Esai (%)</label>
                        <input v-model.number="form.essay_weight_percent" type="number" min="0" max="100" class="w-full text-xs px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl font-mono bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-[#185338] focus:outline-none" required />
                    </div>
                    <p class="col-span-2 text-[11px] text-slate-400 font-medium">
                        Total bobot PG + Esai harus tepat 100%. Saat ini: <strong :class="form.pg_weight_percent + form.essay_weight_percent === 100 ? 'text-[#185338] dark:text-emerald-400' : 'text-rose-500'">{{ form.pg_weight_percent + form.essay_weight_percent }}%</strong>
                    </p>
                </div>
            </form>

            <template #footer>
                <div class="flex items-center justify-end gap-3">
                    <button type="button" @click="showModal = false" class="px-5 py-2.5 border border-slate-200 dark:border-slate-700 font-semibold rounded-full text-xs text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-750 transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button
                        type="submit"
                        form="createBankModalForm"
                        :disabled="form.processing || (form.pg_weight_percent + form.essay_weight_percent !== 100)"
                        class="px-6 py-2.5 bg-[#185338] hover:bg-[#12422c] text-white font-bold text-xs rounded-full shadow-xs disabled:opacity-50 transition-all cursor-pointer"
                    >
                        {{ form.processing ? 'Menyimpan...' : 'Buat Soal Ujian' }}
                    </button>
                </div>
            </template>
        </Modal>
    </AppLayout>
</template>
