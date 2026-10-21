<script setup>
import { ref, computed, nextTick } from 'vue';
import { useForm, Link, Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { renderMathInText } from '@/Utils/katexRender';
import { 
    Award, 
    ArrowLeft, 
    CheckCircle2, 
    User, 
    CornerDownLeft, 
    ChevronRight,
    Search
} from 'lucide-vue-next';

const props = defineProps({
    schedule: Object,
    essayQuestions: Array,
    attempts: Array,
});

const search = ref('');
const selectedIndex = ref(0);
const selectedQuestionId = ref(props.essayQuestions[0]?.id || null);

const currentQuestion = computed(() => {
    return props.essayQuestions.find(q => q.id === selectedQuestionId.value) || props.essayQuestions[0];
});

const filteredAttempts = computed(() => {
    if (!search.value) return props.attempts;
    const q = search.value.toLowerCase();
    return props.attempts.filter(a => 
        a.student.name.toLowerCase().includes(q) || 
        a.student.username.toLowerCase().includes(q)
    );
});

const currentAttempt = computed(() => {
    return filteredAttempts.value[selectedIndex.value] || null;
});

const currentAnswer = computed(() => {
    if (!currentAttempt.value || !currentQuestion.value) return null;
    return currentAttempt.value.answers.find(ans => ans.question_id === currentQuestion.value.id);
});

const scoreInput = ref(currentAnswer.value ? currentAnswer.value.score_obtained : 0);
const feedbackInput = ref(currentAnswer.value ? currentAnswer.value.teacher_feedback || '' : '');
const scoreInputRef = ref(null);

const selectStudent = (idx) => {
    selectedIndex.value = idx;
    nextTick(() => {
        scoreInput.value = currentAnswer.value ? currentAnswer.value.score_obtained : 0;
        feedbackInput.value = currentAnswer.value ? currentAnswer.value.teacher_feedback || '' : '';
        if (scoreInputRef.value) {
            scoreInputRef.value.focus();
            scoreInputRef.value.select();
        }
    });
};

const saveAndNext = () => {
    if (!currentAnswer.value) return;

    const form = useForm({
        score_obtained: Number(scoreInput.value),
        teacher_feedback: feedbackInput.value,
    });

    form.post(`/teacher/grading/answer/${currentAnswer.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            // Update local state
            currentAnswer.value.score_obtained = Number(scoreInput.value);
            currentAnswer.value.is_graded_by_teacher = true;

            // Move to next student
            if (selectedIndex.value < filteredAttempts.value.length - 1) {
                selectStudent(selectedIndex.value + 1);
            }
        },
    });
};
</script>

<template>
    <AppLayout>
        <Head :title="'Meja Koreksi - ' + schedule.title" />

        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between w-full gap-3">
                <div class="flex items-center gap-3">
                    <Link
                        href="/teacher/grading"
                        class="p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors cursor-pointer"
                        title="Kembali ke Daftar Penilaian"
                    >
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                    <div>
                        <h1 class="text-sm font-bold text-slate-900 dark:text-white tracking-tight">Meja Koreksi Esai (Speed-Grading Workbench)</h1>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ schedule.title }} &bull; Standar KKM: {{ schedule.subject?.kkm_score }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">Pilih Soal Esai:</span>
                    <select
                        v-model="selectedQuestionId"
                        class="text-xs px-3 py-1.5 border border-slate-200 dark:border-slate-700 rounded-xl font-semibold bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-[#0F52BA] dark:focus:border-blue-500 focus:outline-none transition-colors shadow-2xs"
                    >
                        <option v-for="(eq, i) in essayQuestions" :key="eq.id" :value="eq.id">
                            Nomor {{ eq.order_index }} (Max Poin: {{ eq.max_score }})
                        </option>
                    </select>
                </div>
            </div>
        </template>

        <div v-if="essayQuestions.length === 0" class="bg-white dark:bg-slate-900 p-8 rounded-2xl border border-slate-200/90 dark:border-slate-800 text-center text-xs text-slate-500 dark:text-slate-400 shadow-xs">
            Ujian ini tidak memiliki soal esai/uraian. Seluruh nilai objektif telah dikoreksi otomatis oleh sistem.
        </div>

        <div v-else class="grid grid-cols-12 gap-4 h-[calc(100vh-140px)]">
            <!-- Left Panel: Student Directory (Col 3) -->
            <div class="col-span-12 md:col-span-3 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 flex flex-col overflow-hidden shadow-xs">
                <div class="p-3 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40">
                    <div class="relative">
                        <Search class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500 absolute left-3 top-2.5" />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari siswa..."
                            class="w-full pl-8.5 pr-3 py-1.5 text-xs border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:border-[#0F52BA] dark:focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/10 transition-all"
                        />
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800">
                    <button
                        v-for="(att, idx) in filteredAttempts"
                        :key="att.id"
                        @click="selectStudent(idx)"
                        :class="[
                            idx === selectedIndex ? 'bg-blue-50/80 dark:bg-blue-950/40 border-l-4 border-[#0F52BA] dark:border-blue-500' : 'hover:bg-slate-50/60 dark:hover:bg-slate-800/40',
                            'w-full text-left p-3.5 flex items-center justify-between transition-colors cursor-pointer'
                        ]"
                    >
                        <div class="min-w-0 pr-2">
                            <p class="text-xs font-semibold text-slate-900 dark:text-white truncate">{{ att.student.name }}</p>
                            <p class="text-[10px] text-slate-500 dark:text-slate-400 font-mono mt-0.5">
                                {{ att.student.username }} &bull; {{ att.student.class_room?.name }}
                            </p>
                        </div>

                        <div class="text-right shrink-0">
                            <span
                                v-if="att.answers.find(a => a.question_id === currentQuestion.id)?.is_graded_by_teacher"
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60"
                            >
                                {{ att.answers.find(a => a.question_id === currentQuestion.id)?.score_obtained }} Poin
                            </span>
                            <span
                                v-else
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 dark:bg-amber-950/50 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60"
                            >
                                Belum
                            </span>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Center Panel: Question Rubric & Student Essay Answer (Col 6) -->
            <div class="col-span-12 md:col-span-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 flex flex-col overflow-hidden shadow-xs">
                <div class="p-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block mb-1">
                        Pertanyaan Soal Esai #{{ currentQuestion.order_index }} (Max Poin: {{ currentQuestion.max_score }})
                    </span>
                    <div class="text-xs text-slate-900 dark:text-slate-100 font-medium leading-relaxed" v-html="renderMathInText(currentQuestion.content)"></div>

                    <!-- Teacher Rubric Box -->
                    <div class="mt-3 p-3 bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-800/50 rounded-xl text-xs text-amber-900 dark:text-amber-200">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-amber-800 dark:text-amber-300 block">Kunci / Rubrik Pedoman Penilaian:</span>
                        <p class="text-[11px] leading-relaxed mt-0.5">{{ currentQuestion.options[0]?.content }}</p>
                    </div>
                </div>

                <div class="flex-1 p-5 overflow-y-auto bg-slate-50/30 dark:bg-slate-900/60">
                    <div class="flex items-center justify-between mb-2.5">
                        <span class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wide">
                            Jawaban Siswa: {{ currentAttempt?.student.name }}
                        </span>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 font-mono">
                            Kelas: {{ currentAttempt?.student.class_room?.name }}
                        </span>
                    </div>

                    <div class="p-4.5 bg-white dark:bg-slate-800/80 border border-slate-200/90 dark:border-slate-700/80 rounded-xl shadow-2xs min-h-[220px]">
                        <p v-if="!currentAnswer?.essay_text" class="text-xs text-slate-400 dark:text-slate-500 italic">
                            (Siswa tidak mengisi jawaban esai ini / dikosongkan)
                        </p>
                        <div v-else class="text-xs text-slate-900 dark:text-slate-100 leading-relaxed whitespace-pre-wrap font-sans">
                            {{ currentAnswer.essay_text }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Panel: Rapid Scoring Keypad (Col 3) -->
            <div class="col-span-12 md:col-span-3 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 p-5 flex flex-col justify-between shadow-xs">
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-white mb-4 pb-2.5 border-b border-slate-100 dark:border-slate-800">
                        Input Skor & Koreksi Cepat
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Berikan Nilai Poin (0 - {{ currentQuestion.max_score }})
                            </label>
                            <input
                                ref="scoreInputRef"
                                v-model="scoreInput"
                                @keyup.enter="saveAndNext"
                                type="number"
                                step="1"
                                min="0"
                                :max="currentQuestion.max_score"
                                class="w-full text-2xl font-bold font-mono text-center p-3 border-2 border-[#0F52BA] dark:border-blue-500 rounded-xl focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:focus:ring-blue-500/20 transition-all bg-blue-50/10 dark:bg-blue-950/20 text-slate-900 dark:text-white"
                                autofocus
                            />
                            <p class="text-[10px] text-slate-400 dark:text-slate-500 text-center mt-1.5">
                                Tekan <kbd class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-md font-mono font-bold text-[10px] text-slate-700 dark:text-slate-300">Enter</kbd> untuk simpan & lanjut
                            </p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Catatan Koreksi (Opsional)</label>
                            <textarea
                                v-model="feedbackInput"
                                rows="3"
                                placeholder="Komentar koreksi guru..."
                                class="w-full text-xs p-3 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:border-[#0F52BA] dark:focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/10 transition-all"
                            ></textarea>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                    <button
                        @click="saveAndNext"
                        type="button"
                        class="w-full py-2.5 bg-[#0F52BA] hover:bg-blue-700 active:scale-[0.98] text-white font-semibold text-xs rounded-xl shadow-xs flex items-center justify-center gap-2 transition-all cursor-pointer"
                    >
                        <span>Simpan & Siswa Berikutnya</span>
                        <CornerDownLeft class="w-3.5 h-3.5" />
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
