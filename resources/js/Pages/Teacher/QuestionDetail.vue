<script setup>
import { ref, computed } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { renderMathInText } from '@/Utils/katexRender';
import { 
    BookOpen, 
    Plus, 
    Upload, 
    Download, 
    Trash2, 
    FileSpreadsheet, 
    FileText, 
    CheckCircle2, 
    HelpCircle,
    Eye
} from 'lucide-vue-next';
import { useConfirm } from '@/Composables/useConfirm';

const props = defineProps({
    bank: Object,
});

const showQuestionModal = ref(false);
const showExcelModal = ref(false);
const showWordModal = ref(false);

const questionForm = useForm({
    type: 'single',
    reading_passage: '',
    content: '',
    max_score: 10.00,
    options: [
        { option_label: 'A', content: '', matching_pair_text: '', is_correct: true },
        { option_label: 'B', content: '', matching_pair_text: '', is_correct: false },
        { option_label: 'C', content: '', matching_pair_text: '', is_correct: false },
        { option_label: 'D', content: '', matching_pair_text: '', is_correct: false },
        { option_label: 'E', content: '', matching_pair_text: '', is_correct: false },
    ],
    short_answer_key: '',
    essay_rubric: '',
});

const excelForm = useForm({
    file: null,
});

const wordForm = useForm({
    file: null,
});

const changeQuestionType = () => {
    const t = questionForm.type;
    if (t === 'single' || t === 'mcma') {
        questionForm.options = [
            { option_label: 'A', content: '', matching_pair_text: '', is_correct: true },
            { option_label: 'B', content: '', matching_pair_text: '', is_correct: false },
            { option_label: 'C', content: '', matching_pair_text: '', is_correct: false },
            { option_label: 'D', content: '', matching_pair_text: '', is_correct: false },
            { option_label: 'E', content: '', matching_pair_text: '', is_correct: false },
        ];
    } else if (t === 'true_false') {
        questionForm.options = [
            { option_label: '1', content: 'Pernyataan 1', matching_pair_text: '', is_correct: true },
            { option_label: '2', content: 'Pernyataan 2', matching_pair_text: '', is_correct: false },
        ];
    } else if (t === 'matching') {
        questionForm.options = [
            { option_label: '1', content: 'Premis 1', matching_pair_text: 'Jawaban Pasangan 1', is_correct: true },
            { option_label: '2', content: 'Premis 2', matching_pair_text: 'Jawaban Pasangan 2', is_correct: true },
        ];
    }
};

const addOptionRow = () => {
    const len = questionForm.options.length;
    if (questionForm.type === 'single' || questionForm.type === 'mcma') {
        questionForm.options.push({
            option_label: String.fromCharCode(65 + len),
            content: '',
            matching_pair_text: '',
            is_correct: false,
        });
    } else {
        questionForm.options.push({
            option_label: String(len + 1),
            content: `Pernyataan / Premis ${len + 1}`,
            matching_pair_text: '',
            is_correct: false,
        });
    }
};

const removeOptionRow = (index) => {
    if (questionForm.options.length > 2) {
        questionForm.options.splice(index, 1);
    }
};

const submitQuestion = () => {
    questionForm.post(`/teacher/banks/${props.bank.id}/questions`, {
        onSuccess: () => {
            showQuestionModal.value = false;
            questionForm.reset();
            questionForm.type = 'single';
            changeQuestionType();
        },
    });
};

const submitExcel = () => {
    excelForm.post(`/teacher/banks/${props.bank.id}/import-excel`, {
        onSuccess: () => (showExcelModal.value = false),
    });
};

const submitWord = () => {
    wordForm.post(`/teacher/banks/${props.bank.id}/import-word`, {
        onSuccess: () => (showWordModal.value = false),
    });
};

const { confirm } = useConfirm();

const deleteQuestion = async (q) => {
    const ok = await confirm({
        title: 'Hapus Butir Soal',
        message: `Apakah Anda yakin ingin menghapus butir soal #${q.order_index} (${formattedType(q.type)})? Tindakan ini tidak dapat dibatalkan.`,
        confirmText: 'Ya, Hapus Soal',
        cancelText: 'Batal',
        type: 'danger',
    });
    if (ok) {
        useForm({}).delete(`/teacher/questions/${q.id}`);
    }
};

const formattedType = (type) => {
    const map = {
        single: 'Pilihan Ganda',
        mcma: 'PG Kompleks (MCMA)',
        true_false: 'Benar / Salah',
        matching: 'Menjodohkan',
        short_answer: 'Isian Singkat',
        essay: 'Esai / Uraian',
    };
    return map[type] || type;
};
</script>

<template>
    <AppLayout>
        <Head :title="bank.title" />

        <template #header>
            <div class="flex flex-col md:flex-row md:items-center justify-between w-full gap-3">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-mono font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                            {{ bank.subject?.code }}
                        </span>
                        <h1 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white tracking-tight truncate">{{ bank.title }}</h1>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Bobot: PG {{ bank.pg_weight_percent }}% &bull; Esai {{ bank.essay_weight_percent }}% &bull; Total: {{ bank.questions?.length || 0 }} Soal
                    </p>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <button
                        @click="showExcelModal = true"
                        class="px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 active:scale-[0.98] text-emerald-800 dark:text-emerald-300 font-semibold text-xs rounded-xl shadow-xs flex items-center gap-1.5 transition-all cursor-pointer"
                    >
                        <FileSpreadsheet class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" />
                        <span>Import Excel</span>
                    </button>

                    <button
                        @click="showWordModal = true"
                        class="px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 active:scale-[0.98] text-blue-800 dark:text-blue-300 font-semibold text-xs rounded-xl shadow-xs flex items-center gap-1.5 transition-all cursor-pointer"
                    >
                        <FileText class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" />
                        <span>Import Word</span>
                    </button>

                    <button
                        @click="showQuestionModal = true"
                        class="px-4 py-2 bg-[#0F52BA] hover:bg-blue-700 active:scale-[0.98] text-white font-semibold text-xs rounded-xl shadow-xs flex items-center gap-1.5 transition-all cursor-pointer"
                    >
                        <Plus class="w-3.5 h-3.5" />
                        <span>Tambah Soal</span>
                    </button>
                </div>
            </div>
        </template>

        <div class="space-y-4">
            <div v-if="!bank.questions || bank.questions.length === 0" class="bg-white dark:bg-slate-900 p-8 rounded-2xl border border-dashed border-slate-200/90 dark:border-slate-800 text-center text-xs text-slate-500 dark:text-slate-400 shadow-xs">
                Belum ada butir soal pada naskah ujian ini. Klik "Tambah Soal" atau gunakan "Import Excel/Word".
            </div>

            <!-- Questions List -->
            <div
                v-for="(q, idx) in bank.questions"
                :key="q.id"
                class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 p-5 shadow-xs space-y-4"
            >
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-slate-900 dark:bg-slate-800 text-white flex items-center justify-center font-bold text-xs font-mono">
                            {{ idx + 1 }}
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 uppercase tracking-wider border border-transparent dark:border-slate-700/60">
                            {{ formattedType(q.type) }}
                        </span>
                        <span class="text-xs font-mono text-slate-500 dark:text-slate-400">
                            Poin: {{ Number(q.max_score).toFixed(0) }}
                        </span>
                    </div>

                    <button
                        @click="deleteQuestion(q)"
                        class="p-1.5 hover:bg-rose-50 dark:hover:bg-rose-950/30 text-rose-600 dark:text-rose-400 rounded-lg transition-colors cursor-pointer"
                        title="Hapus Soal"
                    >
                        <Trash2 class="w-4 h-4" />
                    </button>
                </div>

                <!-- Reading passage if any -->
                <div v-if="q.reading_passage" class="p-4 bg-slate-50/70 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700/80 rounded-xl text-xs leading-relaxed max-w-prose text-slate-800 dark:text-slate-200">
                    <div class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500 mb-1">Wacana Teks (Split-Pane AKM):</div>
                    <div v-html="renderMathInText(q.reading_passage)"></div>
                </div>

                <!-- Content -->
                <div class="text-xs font-medium text-slate-900 dark:text-slate-100 leading-relaxed max-w-prose">
                    <div v-html="renderMathInText(q.content)"></div>
                </div>

                <!-- Options Display -->
                <div v-if="q.type === 'single' || q.type === 'mcma'" class="space-y-2">
                    <div
                        v-for="opt in q.options"
                        :key="opt.id"
                        :class="[
                            opt.is_correct ? 'bg-emerald-50/70 dark:bg-emerald-950/30 border-emerald-300 dark:border-emerald-800/60 text-emerald-950 dark:text-emerald-200 font-medium' : 'bg-slate-50/50 dark:bg-slate-800/50 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200',
                            'p-3 rounded-xl border text-xs flex items-center gap-3 transition-colors'
                        ]"
                    >
                        <span class="w-6 h-6 rounded-lg font-mono font-bold text-[11px] flex items-center justify-center border shrink-0" :class="opt.is_correct ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border-slate-300 dark:border-slate-600'">
                            {{ opt.option_label }}
                        </span>
                        <span class="flex-1" v-html="renderMathInText(opt.content)"></span>
                        <span v-if="opt.is_correct" class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-300 bg-emerald-100/70 dark:bg-emerald-900/50 px-2 py-0.5 rounded-full shrink-0">
                            Kunci Jawaban
                        </span>
                    </div>
                </div>

                <!-- True / False Table -->
                <div v-else-if="q.type === 'true_false'" class="rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden bg-white dark:bg-slate-900">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-600 dark:text-slate-200 font-bold border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="p-3">Pernyataan</th>
                                <th class="p-3 text-center w-24">Kunci</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-for="opt in q.options" :key="opt.id" class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
                                <td class="p-3 text-slate-800 dark:text-slate-200">{{ opt.content }}</td>
                                <td class="p-3 text-center font-bold font-mono" :class="opt.is_correct ? 'text-emerald-700 dark:text-emerald-300 bg-emerald-50/50 dark:bg-emerald-950/40' : 'text-rose-700 dark:text-rose-300 bg-rose-50/50 dark:bg-rose-950/40'">
                                    {{ opt.is_correct ? 'BENAR' : 'SALAH' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Matching Table -->
                <div v-else-if="q.type === 'matching'" class="rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden bg-white dark:bg-slate-900">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-600 dark:text-slate-200 font-bold border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="p-3">Premis / Konsep</th>
                                <th class="p-3">Pasangan Kunci</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-for="opt in q.options" :key="opt.id" class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
                                <td class="p-3 font-medium text-slate-800 dark:text-slate-200">{{ opt.content }}</td>
                                <td class="p-3 font-bold text-emerald-800 dark:text-emerald-300 bg-emerald-50/50 dark:bg-emerald-950/40">{{ opt.matching_pair_text }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Short Answer Box -->
                <div v-else-if="q.type === 'short_answer'" class="p-3.5 bg-slate-50/70 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/80 rounded-xl text-xs">
                    <span class="font-semibold text-slate-700 dark:text-slate-300">Kunci Isian Singkat:</span>
                    <strong class="font-mono text-emerald-700 dark:text-emerald-300 ml-2 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 px-2 py-0.5 rounded-lg">{{ q.options[0]?.content }}</strong>
                </div>

                <!-- Essay Rubric Box -->
                <div v-else-if="q.type === 'essay'" class="p-3.5 bg-amber-50/60 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-800/50 rounded-xl text-xs text-amber-900 dark:text-amber-200">
                    <span class="font-bold uppercase tracking-wider text-amber-800 dark:text-amber-300 text-[10px] block mb-1">Rubrik Penilaian Esai Guru:</span>
                    <p class="leading-relaxed">{{ q.options[0]?.content }}</p>
                </div>
            </div>
        </div>

        <!-- Add Question Modal -->
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
                    v-if="showQuestionModal"
                    @click="showQuestionModal = false"
                    class="fixed inset-0 bg-slate-900/50 dark:bg-slate-950/70 flex items-center justify-center p-4 z-50 overflow-y-auto"
                >
                    <div
                        @click.stop
                        class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 max-w-2xl w-full p-6 shadow-xl max-h-[90vh] overflow-y-auto my-auto transform-gpu"
                    >
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 mb-4">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">
                                Tambah Butir Soal Baru
                            </h3>
                            <button
                                type="button"
                                @click="showQuestionModal = false"
                                class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-lg cursor-pointer transition-colors"
                                aria-label="Tutup"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                <form @submit.prevent="submitQuestion" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tipe Soal</label>
                            <select v-model="questionForm.type" @change="changeQuestionType" class="w-full text-xs px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-[#0F52BA] dark:focus:border-blue-500 focus:outline-none" required>
                                <option value="single">Pilihan Ganda Biasa (1 Kunci)</option>
                                <option value="mcma">Pilihan Ganda Kompleks (MCMA)</option>
                                <option value="true_false">Benar / Salah (Pernyataan)</option>
                                <option value="matching">Menjodohkan (Matching)</option>
                                <option value="short_answer">Isian Singkat (Auto-Graded)</option>
                                <option value="essay">Esai / Uraian Panjang (Meja Koreksi)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Bobot Poin Soal</label>
                            <input v-model="questionForm.max_score" type="number" step="0.5" min="1" max="100" class="w-full text-xs px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl font-mono bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-[#0F52BA] dark:focus:border-blue-500 focus:outline-none" required />
                        </div>
                    </div>

                    <!-- Reading Passage (Optional) -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Wacana Teks Kiri / Stimulus AKM (Opsional)
                        </label>
                        <textarea
                            v-model="questionForm.reading_passage"
                            rows="3"
                            placeholder="Tuliskan cerita / narasi wacana panjang untuk split view di sini... (Mendukung rumus $formula$)"
                            class="w-full text-xs px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:border-[#0F52BA] dark:focus:border-blue-500 focus:outline-none"
                        ></textarea>
                    </div>

                    <!-- Question Content -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Pertanyaan / Butir Soal (KaTeX: gunakan $rumus$ atau $$blok$$)
                        </label>
                        <textarea
                            v-model="questionForm.content"
                            rows="4"
                            placeholder="Ketikkan teks pertanyaan di sini... Contoh: Diketahui f(x) = $x^2 + 5x$..."
                            class="w-full text-xs px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:border-[#0F52BA] dark:focus:border-blue-500 focus:outline-none"
                            required
                        ></textarea>
                    </div>

                    <!-- Dynamic Options based on type -->
                    <div v-if="questionForm.type === 'single' || questionForm.type === 'mcma'" class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Pilihan Jawaban</label>
                            <button type="button" @click="addOptionRow" class="text-[11px] text-[#0F52BA] dark:text-blue-400 font-semibold hover:underline cursor-pointer">
                                + Tambah Opsi
                            </button>
                        </div>

                        <div v-for="(opt, idx) in questionForm.options" :key="idx" class="flex items-center gap-2">
                            <button
                                type="button"
                                @click="questionForm.type === 'single' ? questionForm.options.forEach((o, i) => o.is_correct = (i === idx)) : opt.is_correct = !opt.is_correct"
                                :class="[
                                    opt.is_correct ? 'bg-emerald-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-300 dark:border-slate-700',
                                    'w-7 h-7 rounded-lg text-xs font-mono font-bold flex items-center justify-center shrink-0 cursor-pointer transition-colors'
                                ]"
                                :title="opt.is_correct ? 'Kunci Jawaban' : 'Jadikan Kunci'"
                            >
                                {{ opt.option_label }}
                            </button>
                            <input
                                v-model="opt.content"
                                type="text"
                                :placeholder="'Teks Opsi ' + opt.option_label"
                                class="flex-1 text-xs px-3.5 py-2 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:border-[#0F52BA] dark:focus:border-blue-500 focus:outline-none"
                                required
                            />
                            <button
                                v-if="questionForm.options.length > 2"
                                type="button"
                                @click="removeOptionRow(idx)"
                                class="p-1 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 cursor-pointer"
                            >
                                &times;
                            </button>
                        </div>
                    </div>

                    <div v-else-if="questionForm.type === 'true_false'" class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Tabel Pernyataan & Kunci</label>
                            <button type="button" @click="addOptionRow" class="text-[11px] text-[#0F52BA] dark:text-blue-400 font-semibold hover:underline cursor-pointer">
                                + Tambah Baris Pernyataan
                            </button>
                        </div>

                        <div v-for="(opt, idx) in questionForm.options" :key="idx" class="flex items-center gap-2">
                            <span class="w-6 text-xs font-mono font-bold text-slate-500 dark:text-slate-400">{{ idx + 1 }}.</span>
                            <input
                                v-model="opt.content"
                                type="text"
                                placeholder="Teks pernyataan..."
                                class="flex-1 text-xs px-3.5 py-2 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:border-[#0F52BA] dark:focus:border-blue-500 focus:outline-none"
                                required
                            />
                            <select v-model="opt.is_correct" class="text-xs px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl font-bold bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-[#0F52BA] dark:focus:border-blue-500 focus:outline-none">
                                <option :value="true">BENAR</option>
                                <option :value="false">SALAH</option>
                            </select>
                            <button
                                v-if="questionForm.options.length > 2"
                                type="button"
                                @click="removeOptionRow(idx)"
                                class="p-1 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 cursor-pointer"
                            >
                                &times;
                            </button>
                        </div>
                    </div>

                    <div v-else-if="questionForm.type === 'matching'" class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Pasangan Menjodohkan</label>
                            <button type="button" @click="addOptionRow" class="text-[11px] text-[#0F52BA] dark:text-blue-400 font-semibold hover:underline cursor-pointer">
                                + Tambah Pasangan
                            </button>
                        </div>

                        <div v-for="(opt, idx) in questionForm.options" :key="idx" class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <input
                                v-model="opt.content"
                                type="text"
                                :placeholder="'Premis ' + (idx + 1)"
                                class="text-xs px-3.5 py-2 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:border-[#0F52BA] dark:focus:border-blue-500 focus:outline-none"
                                required
                            />
                            <input
                                v-model="opt.matching_pair_text"
                                type="text"
                                :placeholder="'Target Pasangan ' + (idx + 1)"
                                class="text-xs px-3.5 py-2 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:border-[#0F52BA] dark:focus:border-blue-500 focus:outline-none"
                                required
                            />
                        </div>
                    </div>

                    <div v-else-if="questionForm.type === 'short_answer'">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Kunci Jawaban Singkat (Pencocokan Otomatis)
                        </label>
                        <input
                            v-model="questionForm.short_answer_key"
                            type="text"
                            placeholder="Contoh: 36 atau Fotosintesis"
                            class="w-full text-xs px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl font-mono bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:border-[#0F52BA] dark:focus:border-blue-500 focus:outline-none"
                            required
                        />
                    </div>

                    <div v-else-if="questionForm.type === 'essay'">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Pedoman / Rubrik Penilaian Guru
                        </label>
                        <textarea
                            v-model="questionForm.essay_rubric"
                            rows="3"
                            placeholder="Ketikkan poin-poin jawaban yang wajib ada agar siswa mendapat nilai penuh di meja koreksi..."
                            class="w-full text-xs px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:border-[#0F52BA] dark:focus:border-blue-500 focus:outline-none"
                            required
                        ></textarea>
                    </div>

                    <div class="flex justify-end gap-2.5 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="showQuestionModal = false" class="px-4 py-2 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-600 dark:text-slate-300 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 active:scale-[0.98] transition-all cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-[#0F52BA] hover:bg-blue-700 active:scale-[0.98] text-white text-xs font-semibold rounded-xl shadow-xs transition-all cursor-pointer">
                            Simpan Butir Soal
                        </button>
                    </div>
                </form>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Excel Import Modal -->
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
                    v-if="showExcelModal"
                    @click="showExcelModal = false"
                    class="fixed inset-0 bg-slate-900/50 dark:bg-slate-950/70 flex items-center justify-center p-4 z-50 overflow-y-auto"
                >
                    <div
                        @click.stop
                        class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 max-w-md w-full p-6 shadow-xl my-auto transform-gpu"
                    >
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 mb-4">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">
                                Import Soal dari File Excel (.xlsx)
                            </h3>
                            <button
                                type="button"
                                @click="showExcelModal = false"
                                class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-lg cursor-pointer transition-colors"
                                aria-label="Tutup"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <div class="mb-4 p-3.5 bg-slate-50/80 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl flex items-center justify-between">
                            <span class="text-xs font-medium text-slate-700 dark:text-slate-300">Format Template Soal:</span>
                            <a
                                href="/teacher/template/excel"
                                class="px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 text-xs font-semibold text-[#0F52BA] dark:text-blue-400 rounded-lg flex items-center gap-1.5 shadow-2xs transition-colors"
                            >
                                <Download class="w-3.5 h-3.5" />
                                <span>Template (.xlsx)</span>
                            </a>
                        </div>

                        <form @submit.prevent="submitExcel" class="space-y-4">
                            <input
                                type="file"
                                accept=".xlsx,.xls,.csv"
                                @change="(e) => excelForm.file = e.target.files[0]"
                                class="w-full text-xs border border-slate-200 dark:border-slate-700 rounded-xl p-2 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none"
                                required
                            />
                            <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                                <button type="button" @click="showExcelModal = false" class="px-4 py-2 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-600 dark:text-slate-300 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 active:scale-[0.98] transition-all cursor-pointer">
                                    Batal
                                </button>
                                <button type="submit" :disabled="excelForm.processing" class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 active:scale-[0.98] text-white text-xs font-semibold rounded-xl shadow-xs transition-all cursor-pointer">
                                    Import Soal
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Word Import Modal -->
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
                    v-if="showWordModal"
                    @click="showWordModal = false"
                    class="fixed inset-0 bg-slate-900/50 dark:bg-slate-950/70 flex items-center justify-center p-4 z-50 overflow-y-auto"
                >
                    <div
                        @click.stop
                        class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 max-w-md w-full p-6 shadow-xl my-auto transform-gpu"
                    >
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 mb-2">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">
                                Import Naskah Soal Word (.docx)
                            </h3>
                            <button
                                type="button"
                                @click="showWordModal = false"
                                class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-lg cursor-pointer transition-colors"
                                aria-label="Tutup"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                            Mendukung naskah bernomor standar (1. Soal, A. Opsi, B. Opsi ... KUNCI: A).
                        </p>

                        <form @submit.prevent="submitWord" class="space-y-4">
                            <input
                                type="file"
                                accept=".docx,.doc"
                                @change="(e) => wordForm.file = e.target.files[0]"
                                class="w-full text-xs border border-slate-200 dark:border-slate-700 rounded-xl p-2 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none"
                                required
                            />
                            <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                                <button type="button" @click="showWordModal = false" class="px-4 py-2 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-600 dark:text-slate-300 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 active:scale-[0.98] transition-all cursor-pointer">
                                    Batal
                                </button>
                                <button type="submit" :disabled="wordForm.processing" class="px-4 py-2 bg-blue-700 hover:bg-blue-800 active:scale-[0.98] text-white text-xs font-semibold rounded-xl shadow-xs transition-all cursor-pointer">
                                    Ekstrak Naskah Word
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>
