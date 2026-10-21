<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { useForm, router, Head } from '@inertiajs/vue3';
import { 
    saveAnswerLocally, 
    getLocalAnswers, 
    getUnsyncedAnswers, 
    markAnswersAsSynced 
} from '@/Stores/examLocalDb';
import { renderMathInText } from '@/Utils/katexRender';
import axios from 'axios';
import { 
    Clock, 
    CheckCircle2, 
    Wifi, 
    WifiOff, 
    AlertTriangle, 
    Lock, 
    Unlock,
    ChevronLeft, 
    ChevronRight, 
    HelpCircle, 
    Maximize2, 
    Minimize2, 
    Check, 
    BookOpen,
    Send,
    LayoutGrid,
    FileText,
    CheckSquare,
    AlertCircle,
    X,
    Sparkles
} from 'lucide-vue-next';
import ThemeToggle from '@/Components/ThemeToggle.vue';

const props = defineProps({
    attempt: Object,
    schedule: Object,
    subject: Object,
    questions: Array,
    savedAnswers: Object,
    secondsRemaining: Number,
    canSubmitNow: Boolean,
    secondsUntilMinSubmit: Number,
    minSubmitMinutes: Number,
    maxCheatStrikes: Number,
    school: Object,
});

// Navigation state
const currentIndex = ref(0);
const currentQuestion = computed(() => props.questions[currentIndex.value] || props.questions[0]);

// Mobile Split Tab (for questions with reading passage)
const mobileActivePane = ref('question'); // 'passage' or 'question'

// Font sizing: 0: normal (16px), 1: medium (18px), 2: large (20px)
const fontSizes = ['text-sm sm:text-base', 'text-base sm:text-lg', 'text-lg sm:text-xl'];
const fontScaleIndex = ref(0);
const toggleFontSize = () => {
    fontScaleIndex.value = (fontScaleIndex.value + 1) % fontSizes.length;
};

// Fullscreen state
const isFullscreen = ref(false);
const toggleFullscreen = () => {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(() => {});
        isFullscreen.value = true;
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen().catch(() => {});
            isFullscreen.value = false;
        }
    }
};

// Answers State: dictionary question_id => { selectedOptions, essayText, isDoubt, isSynced }
const answersMap = ref({});
const isOnline = ref(navigator.onLine);
const isSaving = ref(false);
const syncCounter = ref(0);

// Timer State
const timeLeft = ref(Math.max(0, Math.floor(Number(props.secondsRemaining) || 0)));
const minSubmitCountdown = ref(Math.max(0, Math.floor(Number(props.secondsUntilMinSubmit) || 0)));
let timerInterval = null;
let syncInterval = null;

// Anti-Cheat Lock State
const strikes = ref(props.attempt.cheat_strikes || 0);
const isScreenLocked = ref(Boolean(props.attempt.is_locked));
const showWarningStrike = ref(false);
const strikeReason = ref('');
const showSubmitModal = ref(false);
const showMobileDrawer = ref(false);

// Summary statistics
const answeredCount = computed(() => {
    let count = 0;
    for (const q of props.questions) {
        const a = answersMap.value[q.id];
        if (!a) continue;
        if (q.type === 'single' || q.type === 'mcma') {
            if (a.selectedOptions && a.selectedOptions.length > 0) count++;
        } else if (q.type === 'true_false' || q.type === 'matching') {
            if (a.selectedOptions && Object.keys(a.selectedOptions).length > 0) count++;
        } else if (q.type === 'short_answer' || q.type === 'essay') {
            if (a.essayText && a.essayText.trim().length > 0) count++;
        }
    }
    return count;
});

const doubtCount = computed(() => {
    return Object.values(answersMap.value).filter(a => a?.isDoubt).length;
});

const unansweredCount = computed(() => {
    return Math.max(0, props.questions.length - answeredCount.value);
});

// Format seconds into HH:MM:SS
const formatTimer = (totalSeconds) => {
    const total = Math.max(0, Math.floor(Number(totalSeconds) || 0));
    if (total <= 0) return '00:00:00';
    const h = Math.floor(total / 3600);
    const m = Math.floor((total % 3600) / 60);
    const s = Math.floor(total % 60);
    const pad = (n) => String(n).padStart(2, '0');
    return `${pad(h)}:${pad(m)}:${pad(s)}`;
};

// Format mm:ss for minimum submit countdown
const formatMinSubmit = (secs) => {
    const total = Math.max(0, Math.floor(Number(secs) || 0));
    if (total <= 0) return '00:00';
    const m = Math.floor(total / 60);
    const s = Math.floor(total % 60);
    return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
};

// Check question status for grid box styling
const getQuestionStatus = (qid) => {
    const a = answersMap.value[qid];
    if (!a) return 'unanswered';
    if (a.isDoubt) return 'doubt';
    const q = props.questions.find(item => item.id === qid);
    if (!q) return 'unanswered';

    if (q.type === 'single' || q.type === 'mcma') {
        return (a.selectedOptions && a.selectedOptions.length > 0) ? 'answered' : 'unanswered';
    } else if (q.type === 'true_false' || q.type === 'matching') {
        return (a.selectedOptions && Object.keys(a.selectedOptions).length > 0) ? 'answered' : 'unanswered';
    } else {
        return (a.essayText && a.essayText.trim().length > 0) ? 'answered' : 'unanswered';
    }
};

const questionTypeName = (type) => {
    switch (type) {
        case 'single': return 'Pilihan Ganda';
        case 'mcma': return 'PG Kompleks (MCMA)';
        case 'true_false': return 'Pernyataan Benar / Salah';
        case 'matching': return 'Menjodohkan';
        case 'short_answer': return 'Isian Singkat';
        case 'essay': return 'Uraian / Esai';
        default: return 'Soal Ujian';
    }
};

// ==================== ANSWER HANDLING ====================

// Initialize local answers
const initAnswers = async () => {
    // 1. First hydrate from server saved answers
    for (const [qid, ans] of Object.entries(props.savedAnswers || {})) {
        answersMap.value[qid] = {
            selectedOptions: ans.selected_options_json || [],
            essayText: ans.essay_text || '',
            isDoubt: Boolean(ans.is_doubt),
            isSynced: true,
        };
    }

    // 2. Overlay with IndexedDB (offline-first persistent storage)
    try {
        const localData = await getLocalAnswers(props.attempt.id);
        for (const [qid, ans] of Object.entries(localData)) {
            answersMap.value[qid] = {
                selectedOptions: ans.selectedOptions || [],
                essayText: ans.essayText || '',
                isDoubt: Boolean(ans.isDoubt),
                isSynced: ans.isSynced,
            };
        }
    } catch (e) {
        console.warn('IndexedDB read warning:', e);
    }
};

// Persist answer immediately (< 10 ms)
const saveCurrentAnswer = async () => {
    const qid = currentQuestion.value.id;
    const ans = answersMap.value[qid] || {
        selectedOptions: [],
        essayText: '',
        isDoubt: false,
    };

    ans.isSynced = false;
    answersMap.value[qid] = ans;
    isSaving.value = true;

    try {
        await saveAnswerLocally(props.attempt.id, qid, ans);
    } catch (e) {
        console.warn('IndexedDB save failed:', e);
    } finally {
        isSaving.value = false;
    }

    // Trigger batch sync if reached 5 answered questions
    syncCounter.value++;
    if (syncCounter.value >= 5) {
        triggerBatchSync();
    }
};

// Options selection helpers
const selectSingleOption = (label) => {
    const qid = currentQuestion.value.id;
    if (!answersMap.value[qid]) answersMap.value[qid] = { selectedOptions: [], essayText: '', isDoubt: false };
    answersMap.value[qid].selectedOptions = [label];
    saveCurrentAnswer();
};

const toggleMcmaOption = (label) => {
    const qid = currentQuestion.value.id;
    if (!answersMap.value[qid]) answersMap.value[qid] = { selectedOptions: [], essayText: '', isDoubt: false };
    const list = answersMap.value[qid].selectedOptions || [];
    const idx = list.indexOf(label);
    if (idx > -1) {
        list.splice(idx, 1);
    } else {
        list.push(label);
    }
    answersMap.value[qid].selectedOptions = list;
    saveCurrentAnswer();
};

const setTrueFalse = (rowKey, valStr) => {
    const qid = currentQuestion.value.id;
    if (!answersMap.value[qid]) answersMap.value[qid] = { selectedOptions: {}, essayText: '', isDoubt: false };
    if (Array.isArray(answersMap.value[qid].selectedOptions)) {
        answersMap.value[qid].selectedOptions = {};
    }
    answersMap.value[qid].selectedOptions[rowKey] = valStr;
    saveCurrentAnswer();
};

const setMatchingPair = (rowKey, targetText) => {
    const qid = currentQuestion.value.id;
    if (!answersMap.value[qid]) answersMap.value[qid] = { selectedOptions: {}, essayText: '', isDoubt: false };
    if (Array.isArray(answersMap.value[qid].selectedOptions)) {
        answersMap.value[qid].selectedOptions = {};
    }
    answersMap.value[qid].selectedOptions[rowKey] = targetText;
    saveCurrentAnswer();
};

const toggleDoubt = () => {
    const qid = currentQuestion.value.id;
    if (!answersMap.value[qid]) answersMap.value[qid] = { selectedOptions: [], essayText: '', isDoubt: false };
    answersMap.value[qid].isDoubt = !answersMap.value[qid].isDoubt;
    saveCurrentAnswer();
};

const jumpToQuestion = (idx) => {
    currentIndex.value = idx;
    showMobileDrawer.value = false;
    mobileActivePane.value = 'question';
};

// ==================== BATCH SYNC TO LARAVEL ====================
const triggerBatchSync = async () => {
    if (!navigator.onLine) return;

    try {
        const unsynced = await getUnsyncedAnswers(props.attempt.id);
        if (unsynced.length === 0) return;

        const payload = unsynced.map(u => ({
            question_id: u.questionId,
            selected_options: u.selectedOptions,
            essay_text: u.essayText,
            is_doubt: u.isDoubt,
        }));

        const res = await axios.post(`/api/exam/${props.attempt.id}/sync`, { answers: payload });
        if (res.data.success) {
            await markAnswersAsSynced(props.attempt.id, res.data.synced_question_ids);
            if (res.data.is_locked) {
                isScreenLocked.value = true;
            }
            if (typeof res.data.seconds_remaining === 'number') {
                timeLeft.value = Math.max(0, Math.floor(res.data.seconds_remaining));
            }
            syncCounter.value = 0;
        }
    } catch (e) {
        console.warn('Sync error:', e);
    }
};

// ==================== ANTI-CHEAT & TAB SWITCH DETECTION ====================
const handleVisibilityChange = async () => {
    if (document.hidden) {
        await reportCheatStrike('Pindah Tab / Membuka Aplikasi Lain');
    }
};

const handleWindowBlur = async () => {
    if (!document.fullscreenElement && isFullscreen.value) {
        await reportCheatStrike('Keluar dari Mode Layar Penuh');
    }
};

const reportCheatStrike = async (reason) => {
    strikes.value++;
    strikeReason.value = reason;
    showWarningStrike.value = true;

    try {
        const res = await axios.post(`/api/exam/${props.attempt.id}/strike`);
        if (res.data.is_locked) {
            isScreenLocked.value = true;
        }
    } catch (e) {
        console.warn('Failed to report strike:', e);
    }
};

// Poll heartbeat if locked to see when proctor unlocks
let lockCheckInterval = null;
const isUnlocking = ref(false);

const startLockPolling = () => {
    if (lockCheckInterval) clearInterval(lockCheckInterval);
    lockCheckInterval = setInterval(async () => {
        try {
            const res = await axios.get(`/api/exam/${props.attempt.id}/status`);
            if (!res.data.is_locked) {
                isScreenLocked.value = false;
                strikes.value = 0;
                clearInterval(lockCheckInterval);
            }
        } catch (e) {}
    }, 2500);
};

watch(isScreenLocked, (newVal) => {
    if (newVal) {
        startLockPolling();
    } else {
        if (lockCheckInterval) clearInterval(lockCheckInterval);
    }
}, { immediate: true });

const adminDirectUnlock = async () => {
    isUnlocking.value = true;
    try {
        const res = await axios.post(`/api/exam/${props.attempt.id}/unlock`);
        if (res.data.success) {
            isScreenLocked.value = false;
            strikes.value = 0;
            if (lockCheckInterval) clearInterval(lockCheckInterval);
        }
    } catch (e) {
        console.error('Failed to unlock:', e);
    } finally {
        isUnlocking.value = false;
    }
};

// ==================== KEYBOARD SHORTCUTS ====================
const handleKeyDown = (e) => {
    if (isScreenLocked.value || showSubmitModal.value || showWarningStrike.value) return;
    if (e.target.tagName === 'TEXTAREA' || e.target.tagName === 'INPUT') return;

    const key = e.key.toUpperCase();

    if (e.key === 'ArrowLeft') {
        if (currentIndex.value > 0) jumpToQuestion(currentIndex.value - 1);
    } else if (e.key === 'ArrowRight') {
        if (currentIndex.value < props.questions.length - 1) jumpToQuestion(currentIndex.value + 1);
    } else if (key === 'R') {
        toggleDoubt();
    } else if (currentQuestion.value.type === 'single' && ['A', 'B', 'C', 'D', 'E'].includes(key)) {
        selectSingleOption(key);
    }
};

// Submit Exam
const isSubmitting = ref(false);
const confirmSubmit = () => {
    if (isSubmitting.value) return;
    isSubmitting.value = true;
    triggerBatchSync().finally(() => {
        router.post(`/student/exam/${props.attempt.id}/submit`, {}, {
            onFinish: () => {
                isSubmitting.value = false;
            }
        });
    });
};

// Lifecycle
onMounted(() => {
    initAnswers();

    // Timer countdown
    timerInterval = setInterval(() => {
        if (timeLeft.value > 0) {
            timeLeft.value--;
            if (timeLeft.value === 0) {
                confirmSubmit(); // Auto submit on 00:00:00
            }
        }
        if (minSubmitCountdown.value > 0) {
            minSubmitCountdown.value--;
        }
    }, 1000);

    // Periodic sync every 60s
    syncInterval = setInterval(() => {
        triggerBatchSync();
    }, 60000);

    // Anti-cheat listeners
    document.addEventListener('visibilitychange', handleVisibilityChange);
    window.addEventListener('blur', handleWindowBlur);
    window.addEventListener('keydown', handleKeyDown);

    window.addEventListener('online', () => (isOnline.value = true));
    window.addEventListener('offline', () => (isOnline.value = false));
});

onUnmounted(() => {
    if (timerInterval) clearInterval(timerInterval);
    if (syncInterval) clearInterval(syncInterval);
    if (lockCheckInterval) clearInterval(lockCheckInterval);
    document.removeEventListener('visibilitychange', handleVisibilityChange);
    window.removeEventListener('blur', handleWindowBlur);
    window.removeEventListener('keydown', handleKeyDown);
});
</script>

<template>
    <div class="min-h-screen bg-[#F8FAFC] dark:bg-slate-950 text-slate-900 dark:text-slate-100 flex flex-col select-none font-sans antialiased transition-colors duration-150">
        <Head :title="schedule.title + ' - SmartCBT Engine'" />

        <!-- ==================== TOP BAR (Precision 56px Exam Header) ==================== -->
        <header class="h-14 bg-white dark:bg-slate-900 border-b border-slate-200/90 dark:border-slate-800 px-4 sm:px-6 flex items-center justify-between shrink-0 sticky top-0 z-40 shadow-2xs transition-colors duration-150">
            <!-- Left: Exam Identity & School -->
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-[#0F52BA] to-blue-700 text-white flex items-center justify-center font-black text-xs shadow-xs shrink-0">
                    CBT
                </div>
                <div class="min-w-0">
                    <h1 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white truncate uppercase tracking-tight">
                        {{ subject.name }}
                    </h1>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium truncate">
                        {{ schedule.title }}
                    </p>
                </div>
            </div>

            <!-- Center: Persistent Save Indicator (Desktop Only) -->
            <div class="hidden lg:flex items-center gap-2 px-3 py-1 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-full text-[11px] font-medium text-slate-600 dark:text-slate-300">
                <span :class="[isOnline ? 'bg-emerald-500' : 'bg-amber-500', 'w-2 h-2 rounded-full shrink-0 animate-pulse']"></span>
                <span v-if="isSaving">Menyimpan ke perangkat...</span>
                <span v-else-if="!isOnline">Mode Offline (Tersimpan Lokal)</span>
                <span v-else>Tersimpan Otomatis (IndexedDB)</span>
            </div>

            <!-- Right: Font Switcher, Theme Toggle, Fullscreen, Timer, & Mobile Nav Trigger -->
            <div class="flex items-center gap-2">
                <!-- Theme Switcher (Compact for Header) -->
                <ThemeToggle compact />

                <!-- Font Switcher -->
                <button
                    type="button"
                    @click="toggleFontSize"
                    class="px-2.5 py-1 text-xs font-bold text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 bg-white dark:bg-slate-800/80 rounded-xl border border-slate-200 dark:border-slate-700 transition-colors cursor-pointer"
                    title="Ubah Ukuran Huruf"
                >
                    A<span class="text-[10px]">{{ fontScaleIndex === 0 ? '' : fontScaleIndex === 1 ? '+' : '++' }}</span>
                </button>

                <!-- Fullscreen button -->
                <button
                    type="button"
                    @click="toggleFullscreen"
                    class="p-1.5 text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 bg-white dark:bg-slate-800/80 rounded-xl border border-slate-200 dark:border-slate-700 hidden sm:block transition-colors cursor-pointer"
                    title="Layar Penuh"
                >
                    <Maximize2 v-if="!isFullscreen" class="w-3.5 h-3.5" />
                    <Minimize2 v-else class="w-3.5 h-3.5" />
                </button>

                <!-- Countdown Timer -->
                <div
                    :class="[
                        timeLeft < 300 
                            ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border-rose-300 dark:border-rose-800 animate-pulse' 
                            : 'bg-slate-50 dark:bg-slate-800/90 text-slate-900 dark:text-white border-slate-200 dark:border-slate-700',
                        'px-3 py-1 rounded-xl border flex items-center gap-1.5 font-mono font-bold text-xs sm:text-sm tracking-wider tabular-nums shadow-2xs'
                    ]"
                >
                    <Clock class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400 shrink-0" />
                    <span>{{ formatTimer(timeLeft) }}</span>
                </div>

                <!-- Early Submit Button in Header (Always Accessible) -->
                <button
                    type="button"
                    @click="showSubmitModal = true"
                    class="px-2.5 sm:px-3 py-1 bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] text-white font-bold text-xs rounded-xl flex items-center gap-1.5 shadow-2xs transition-all cursor-pointer"
                    title="Selesai dan Kumpulkan Ujian Lebih Awal"
                >
                    <CheckCircle2 class="w-3.5 h-3.5" />
                    <span class="hidden sm:inline">Selesai Ujian</span>
                </button>

                <!-- Mobile Navigation Trigger (Drawer Open) -->
                <button
                    type="button"
                    @click="showMobileDrawer = true"
                    class="md:hidden px-2.5 py-1 bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900/60 rounded-xl text-xs font-bold flex items-center gap-1.5 cursor-pointer"
                    aria-label="Buka Daftar Soal"
                >
                    <LayoutGrid class="w-3.5 h-3.5" />
                    <span class="font-mono text-[11px]">{{ answeredCount }}/{{ questions.length }}</span>
                </button>
            </div>
        </header>

        <!-- ==================== MAIN EXAM WORKSPACE ==================== -->
        <div class="flex-1 flex overflow-hidden">
            <!-- Left & Center Workspace -->
            <div class="flex-1 flex flex-col overflow-y-auto bg-white dark:bg-slate-900 transition-colors duration-150">
                <!-- Question Top Action Header Bar -->
                <div class="px-4 sm:px-6 py-2.5 bg-slate-50/80 dark:bg-slate-900/90 border-b border-slate-200/90 dark:border-slate-800 flex flex-wrap items-center justify-between gap-2.5 shrink-0">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-1 rounded-xl bg-slate-900 dark:bg-slate-800 text-white font-mono font-bold text-xs shadow-2xs">
                            Soal {{ currentIndex + 1 }} / {{ questions.length }}
                        </span>
                        <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 uppercase tracking-wide">
                            {{ questionTypeName(currentQuestion.type) }}
                        </span>
                        <span class="text-[11px] font-mono text-slate-500 dark:text-slate-400 hidden sm:inline">
                            Poin: {{ Number(currentQuestion.max_score).toFixed(0) }}
                        </span>
                    </div>

                    <!-- Doubt / Ragu-ragu Checkbox Button -->
                    <button
                        type="button"
                        @click="toggleDoubt"
                        :class="[
                            answersMap[currentQuestion.id]?.isDoubt
                                ? 'bg-amber-500 text-white border-amber-500 shadow-xs'
                                : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700',
                            'px-3.5 py-1 rounded-xl border text-xs font-semibold flex items-center gap-1.5 transition-all cursor-pointer'
                        ]"
                    >
                        <span class="w-3.5 h-3.5 rounded-md border border-current flex items-center justify-center text-[9px]">
                            <Check v-if="answersMap[currentQuestion.id]?.isDoubt" class="w-3 h-3 stroke-3" />
                        </span>
                        <span>Ragu-ragu (R)</span>
                    </button>
                </div>

                <!-- Mobile Stimulus Tabs (If question has reading passage) -->
                <div v-if="currentQuestion.reading_passage" class="lg:hidden flex border-b border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-800/80 p-1 gap-1 shrink-0 text-xs font-semibold">
                    <button
                        type="button"
                        @click="mobileActivePane = 'passage'"
                        :class="[
                            'flex-1 py-1.5 rounded-lg flex items-center justify-center gap-1.5 transition-all cursor-pointer',
                            mobileActivePane === 'passage' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-2xs font-bold' : 'text-slate-500 dark:text-slate-400'
                        ]"
                    >
                        <FileText class="w-3.5 h-3.5" />
                        <span>Wacana Bacaan</span>
                    </button>
                    <button
                        type="button"
                        @click="mobileActivePane = 'question'"
                        :class="[
                            'flex-1 py-1.5 rounded-lg flex items-center justify-center gap-1.5 transition-all cursor-pointer',
                            mobileActivePane === 'question' ? 'bg-white dark:bg-slate-700 text-blue-700 dark:text-blue-300 shadow-2xs font-bold' : 'text-slate-500 dark:text-slate-400'
                        ]"
                    >
                        <CheckSquare class="w-3.5 h-3.5" />
                        <span>Soal & Pilihan</span>
                    </button>
                </div>

                <!-- Question Split-Pane Layout -->
                <div class="flex-1 flex flex-col lg:flex-row overflow-y-auto">
                    <!-- Left Pane: Reading Passage / Stimulus -->
                    <div
                        v-if="currentQuestion.reading_passage"
                        :class="[
                            mobileActivePane === 'passage' ? 'block' : 'hidden lg:block',
                            'lg:w-1/2 p-5 sm:p-6 lg:border-r border-slate-200 dark:border-slate-800 overflow-y-auto bg-slate-50/40 dark:bg-slate-950/40'
                        ]"
                    >
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-800 font-bold text-[10px] uppercase tracking-wider mb-3">
                            <BookOpen class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" />
                            <span>Wacana / Stimulus AKM</span>
                        </div>
                        <div
                            :class="[fontSizes[fontScaleIndex], 'leading-relaxed text-slate-900 dark:text-slate-200 max-w-prose space-y-3 select-text']"
                            v-html="renderMathInText(currentQuestion.reading_passage)"
                        ></div>
                    </div>

                    <!-- Right Pane: Question Text & Interactive Options -->
                    <div
                        :class="[
                            currentQuestion.reading_passage && mobileActivePane === 'passage' ? 'hidden lg:flex' : 'flex',
                            currentQuestion.reading_passage ? 'lg:w-1/2' : 'max-w-4xl mx-auto w-full',
                            'p-5 sm:p-6 overflow-y-auto flex-1 flex-col justify-between'
                        ]"
                    >
                        <div class="space-y-6">
                            <!-- Question Content -->
                            <div
                                :class="[fontSizes[fontScaleIndex], 'leading-relaxed text-slate-900 dark:text-slate-100 font-medium select-text']"
                                v-html="renderMathInText(currentQuestion.content)"
                            ></div>

                            <!-- 1. Single Choice: Tactile Keycaps [A], [B], [C], [D], [E] -->
                            <div v-if="currentQuestion.type === 'single'" class="space-y-2.5">
                                <button
                                    v-for="opt in currentQuestion.options"
                                    :key="opt.id"
                                    @click="selectSingleOption(opt.option_label)"
                                    type="button"
                                    :class="[
                                        answersMap[currentQuestion.id]?.selectedOptions?.includes(opt.option_label)
                                            ? 'bg-blue-50/70 dark:bg-blue-950/50 border-2 border-[#0F52BA] dark:border-blue-500 text-slate-900 dark:text-white shadow-xs'
                                            : 'bg-white dark:bg-slate-800/80 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/60 hover:border-slate-300 dark:hover:border-slate-600',
                                        'w-full text-left p-3.5 rounded-2xl border flex items-start gap-3.5 transition-all cursor-pointer active:scale-[0.99]'
                                    ]"
                                >
                                    <!-- Tactile Keycap -->
                                    <span
                                        :class="[
                                            answersMap[currentQuestion.id]?.selectedOptions?.includes(opt.option_label)
                                                ? 'bg-[#0F52BA] dark:bg-blue-600 text-white border-[#0F52BA] dark:border-blue-600 shadow-xs'
                                                : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 border-slate-300 dark:border-slate-600',
                                            'w-7 h-7 rounded-xl font-mono font-bold text-xs flex items-center justify-center shrink-0 border transition-colors'
                                        ]"
                                    >
                                        {{ opt.option_label }}
                                    </span>
                                    <span :class="[fontSizes[fontScaleIndex], 'flex-1 pt-0.5 leading-normal select-text']" v-html="renderMathInText(opt.content)"></span>
                                </button>
                            </div>

                            <!-- 2. MCMA: Complex Multiple Choice (Checkboxes) -->
                            <div v-else-if="currentQuestion.type === 'mcma'" class="space-y-2.5">
                                <p class="text-xs text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-950/50 p-2.5 rounded-xl border border-blue-200/60 dark:border-blue-800/60 font-medium flex items-center gap-1.5">
                                    <Sparkles class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400 shrink-0" />
                                    <span>Pilihan Ganda Kompleks: Anda dapat memilih lebih dari satu jawaban benar.</span>
                                </p>
                                <button
                                    v-for="opt in currentQuestion.options"
                                    :key="opt.id"
                                    @click="toggleMcmaOption(opt.option_label)"
                                    type="button"
                                    :class="[
                                        answersMap[currentQuestion.id]?.selectedOptions?.includes(opt.option_label)
                                            ? 'bg-blue-50/70 dark:bg-blue-950/50 border-2 border-[#0F52BA] dark:border-blue-500 text-slate-900 dark:text-white shadow-xs'
                                            : 'bg-white dark:bg-slate-800/80 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/60 hover:border-slate-300 dark:hover:border-slate-600',
                                        'w-full text-left p-3.5 rounded-2xl border flex items-start gap-3.5 transition-all cursor-pointer active:scale-[0.99]'
                                    ]"
                                >
                                    <span
                                        :class="[
                                            answersMap[currentQuestion.id]?.selectedOptions?.includes(opt.option_label)
                                                ? 'bg-[#0F52BA] dark:bg-blue-600 text-white border-[#0F52BA] dark:border-blue-600'
                                                : 'bg-white dark:bg-slate-700 text-transparent border-slate-300 dark:border-slate-600',
                                            'w-5 h-5 rounded-lg border flex items-center justify-center shrink-0 mt-0.5 transition-colors shadow-2xs'
                                        ]"
                                    >
                                        <Check class="w-3.5 h-3.5 stroke-3 text-white" />
                                    </span>
                                    <span :class="[fontSizes[fontScaleIndex], 'flex-1 leading-normal select-text']" v-html="renderMathInText(opt.content)"></span>
                                </button>
                            </div>

                            <!-- 3. True / False: Interactive Cards -->
                            <div v-else-if="currentQuestion.type === 'true_false'" class="space-y-3">
                                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium italic">
                                    Tentukan status <strong>BENAR</strong> atau <strong>SALAH</strong> untuk setiap baris pernyataan di bawah ini:
                                </p>
                                <div
                                    v-for="opt in currentQuestion.options"
                                    :key="opt.id"
                                    class="p-4 bg-slate-50/70 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs"
                                >
                                    <span :class="[fontSizes[fontScaleIndex], 'font-medium text-slate-800 dark:text-slate-200 sm:w-2/3 select-text']">
                                        {{ opt.content }}
                                    </span>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <button
                                            type="button"
                                            @click="setTrueFalse(opt.order_index, 'true')"
                                            :class="[
                                                answersMap[currentQuestion.id]?.selectedOptions?.[opt.order_index] === 'true'
                                                    ? 'bg-emerald-600 text-white font-bold border-emerald-600 shadow-xs'
                                                    : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-emerald-50 dark:hover:bg-emerald-950/30',
                                                'px-3.5 py-1.5 rounded-xl border text-xs font-semibold transition-all cursor-pointer'
                                            ]"
                                        >
                                            BENAR
                                        </button>
                                        <button
                                            type="button"
                                            @click="setTrueFalse(opt.order_index, 'false')"
                                            :class="[
                                                answersMap[currentQuestion.id]?.selectedOptions?.[opt.order_index] === 'false'
                                                    ? 'bg-rose-600 text-white font-bold border-rose-600 shadow-xs'
                                                    : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-rose-50 dark:hover:bg-rose-950/30',
                                                'px-3.5 py-1.5 rounded-xl border text-xs font-semibold transition-all cursor-pointer'
                                            ]"
                                        >
                                            SALAH
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- 4. Matching: Connect Premise to Target -->
                            <div v-else-if="currentQuestion.type === 'matching'" class="space-y-3 bg-white dark:bg-slate-800/40 p-4.5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs">
                                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium italic mb-2">
                                    Pasangkan konsep pada kolom kiri dengan jawaban target yang tepat pada menu pilihan kanan:
                                </p>
                                <div
                                    v-for="opt in currentQuestion.options"
                                    :key="opt.id"
                                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3.5 bg-slate-50/70 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700 rounded-xl"
                                >
                                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200 sm:w-1/2 select-text">
                                        {{ opt.content }}
                                    </span>
                                    <select
                                        :value="answersMap[currentQuestion.id]?.selectedOptions?.[opt.order_index] || ''"
                                        @change="(e) => setMatchingPair(opt.order_index, e.target.value)"
                                        class="text-xs p-2.5 border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-900 dark:text-white sm:w-1/2 font-medium focus:border-[#0F52BA] focus:outline-none transition-colors"
                                    >
                                        <option value="">-- Pilih Pasangan Jawaban --</option>
                                        <option v-for="pair in currentQuestion.options" :key="pair.id" :value="pair.matching_pair_text">
                                            {{ pair.matching_pair_text }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <!-- 5. Short Answer & Essay: Text Area -->
                            <div v-else-if="currentQuestion.type === 'short_answer' || currentQuestion.type === 'essay'" class="space-y-2">
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                                    {{ currentQuestion.type === 'short_answer' ? 'Ketikkan Jawaban Singkat Anda:' : 'Ketikkan Jawaban Uraian Anda:' }}
                                </label>
                                <textarea
                                    :rows="currentQuestion.type === 'short_answer' ? 2 : 6"
                                    :value="answersMap[currentQuestion.id]?.essayText || ''"
                                    @input="(e) => {
                                        if (!answersMap[currentQuestion.id]) answersMap[currentQuestion.id] = { selectedOptions: [], essayText: '', isDoubt: false };
                                        answersMap[currentQuestion.id].essayText = e.target.value;
                                        saveCurrentAnswer();
                                    }"
                                    :placeholder="currentQuestion.type === 'short_answer' ? 'Contoh: 42 atau Fotosintesis...' : 'Tuliskan penjelasan dan argumen jawaban Anda secara lengkap di sini...'"
                                    class="w-full text-xs p-3.5 border border-slate-200 dark:border-slate-700 rounded-2xl focus:border-[#0F52BA] dark:focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 focus:outline-none leading-relaxed bg-white dark:bg-slate-800/90 shadow-2xs font-sans text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 select-text cursor-text caret-[#0F52BA]"
                                ></textarea>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500 text-right">
                                    * Jawaban tersimpan otomatis saat Anda mengetik.
                                </p>
                            </div>
                        </div>

                        <!-- Bottom Navigation Buttons (Prev, Next, Submit) -->
                        <div class="flex items-center justify-between pt-6 border-t border-slate-200 dark:border-slate-800 mt-8 gap-2">
                            <button
                                type="button"
                                @click="currentIndex > 0 && jumpToQuestion(currentIndex - 1)"
                                :disabled="currentIndex === 0"
                                class="px-4 py-2 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold text-xs rounded-xl shadow-2xs flex items-center gap-1.5 transition-all disabled:opacity-40 cursor-pointer active:scale-[0.98]"
                            >
                                <ChevronLeft class="w-4 h-4" />
                                <span>Sebelumnya (←)</span>
                            </button>

                            <!-- Center Mobile Button to Open Sheet -->
                            <button
                                type="button"
                                @click="showMobileDrawer = true"
                                class="md:hidden px-3 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold text-xs rounded-xl flex items-center gap-1 cursor-pointer"
                            >
                                <LayoutGrid class="w-3.5 h-3.5" />
                                <span>Daftar Soal</span>
                            </button>

                            <button
                                v-if="currentIndex < questions.length - 1"
                                type="button"
                                @click="jumpToQuestion(currentIndex + 1)"
                                class="px-5 py-2 bg-[#0F52BA] hover:bg-blue-700 active:scale-[0.98] text-white font-semibold text-xs rounded-xl shadow-xs flex items-center gap-1.5 transition-all cursor-pointer"
                            >
                                <span>Selanjutnya (→)</span>
                                <ChevronRight class="w-4 h-4" />
                            </button>

                            <button
                                v-else
                                type="button"
                                @click="showSubmitModal = true"
                                class="px-5 py-2 font-bold text-xs rounded-xl flex items-center gap-1.5 transition-all bg-emerald-700 hover:bg-emerald-800 active:scale-[0.98] text-white cursor-pointer shadow-xs"
                            >
                                <Send class="w-3.5 h-3.5" />
                                <span>Selesai & Kumpulkan</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ==================== DESKTOP RIGHT PANEL: QUESTION GRID ==================== -->
            <aside class="w-72 bg-white dark:bg-slate-900 border-l border-slate-200/90 dark:border-slate-800 hidden md:flex flex-col shrink-0 select-none shadow-2xs transition-colors duration-150">
                <div class="p-4 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-white">
                        Navigasi Nomor Soal
                    </h3>
                    <div class="flex items-center justify-between text-[11px] font-mono mt-1 text-slate-500 dark:text-slate-400">
                        <span>Terjawab: <strong class="text-emerald-700 dark:text-emerald-400 font-bold">{{ answeredCount }}</strong> / {{ questions.length }}</span>
                        <span class="text-amber-600 dark:text-amber-400 font-bold" v-if="doubtCount > 0">{{ doubtCount }} Ragu</span>
                    </div>
                </div>

                <!-- Number Grid (5 columns) -->
                <div class="flex-1 p-4 overflow-y-auto custom-scrollbar">
                    <div class="grid grid-cols-5 gap-2">
                        <button
                            v-for="(q, idx) in questions"
                            :key="q.id"
                            @click="jumpToQuestion(idx)"
                            type="button"
                            :class="[
                                idx === currentIndex ? 'ring-2 ring-[#0F52BA] dark:ring-blue-500 ring-offset-2 dark:ring-offset-slate-900 font-black' : '',
                                getQuestionStatus(q.id) === 'answered'
                                    ? 'bg-emerald-600 text-white border-emerald-600 shadow-2xs'
                                    : getQuestionStatus(q.id) === 'doubt'
                                    ? 'bg-amber-500 text-white border-amber-500 shadow-2xs'
                                    : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700',
                                'h-10 rounded-xl border font-mono font-bold text-xs flex flex-col items-center justify-center transition-all cursor-pointer relative'
                            ]"
                        >
                            <span>{{ idx + 1 }}</span>
                            <span v-if="answersMap[q.id]?.selectedOptions?.[0] && q.type === 'single'" class="text-[9px] uppercase font-bold tracking-tighter opacity-90 leading-none">
                                {{ answersMap[q.id].selectedOptions[0] }}
                            </span>
                        </button>
                    </div>

                    <!-- Legend -->
                    <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800 space-y-2 text-[11px] text-slate-600 dark:text-slate-400">
                        <div class="flex items-center gap-2">
                            <span class="w-3.5 h-3.5 rounded-md bg-emerald-600 shrink-0"></span>
                            <span>Sudah Dijawab ({{ answeredCount }})</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3.5 h-3.5 rounded-md bg-amber-500 shrink-0"></span>
                            <span>Ragu-ragu ({{ doubtCount }})</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3.5 h-3.5 rounded-md bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 shrink-0"></span>
                            <span>Belum Dijawab ({{ unansweredCount }})</span>
                        </div>
                    </div>
                </div>

                <!-- Bottom Submit Button -->
                <div class="p-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-900/60">
                    <button
                        type="button"
                        @click="showSubmitModal = true"
                        class="w-full py-2.5 bg-emerald-700 hover:bg-emerald-800 active:scale-[0.98] text-white font-bold text-xs rounded-xl flex items-center justify-center gap-2 transition-all cursor-pointer shadow-xs"
                    >
                        <CheckCircle2 class="w-4 h-4" />
                        <span>Kumpulkan Ujian</span>
                    </button>
                    <p v-if="minSubmitCountdown > 0" class="text-[10px] text-slate-500 dark:text-slate-400 text-center mt-1.5 leading-tight font-mono">
                        Waktu standar tersisa: <strong class="text-slate-700 dark:text-slate-300">{{ formatMinSubmit(minSubmitCountdown) }}</strong>
                    </p>
                </div>
            </aside>
        </div>

        <!-- ==================== MOBILE QUESTION NAVIGATION DRAWER ==================== -->
        <div v-if="showMobileDrawer" class="fixed inset-0 z-50 md:hidden flex flex-col justify-end">
            <!-- Backdrop -->
            <div 
                @click="showMobileDrawer = false" 
                class="fixed inset-0 bg-slate-900/50 dark:bg-black/70 transition-opacity"
            />

            <!-- Drawer Sheet -->
            <div class="bg-white dark:bg-slate-900 rounded-t-3xl border-t border-slate-200 dark:border-slate-800 p-5 z-10 max-h-[80vh] flex flex-col space-y-4 shadow-2xl animate-in slide-in-from-bottom duration-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Daftar Nomor Soal</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-mono">
                            Terjawab: <strong class="text-emerald-700 dark:text-emerald-400">{{ answeredCount }}</strong> / {{ questions.length }}
                        </p>
                    </div>
                    <button 
                        type="button" 
                        @click="showMobileDrawer = false" 
                        class="p-1.5 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800"
                    >
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <!-- Grid -->
                <div class="flex-1 overflow-y-auto">
                    <div class="grid grid-cols-5 gap-2">
                        <button
                            v-for="(q, idx) in questions"
                            :key="q.id"
                            @click="jumpToQuestion(idx)"
                            type="button"
                            :class="[
                                idx === currentIndex ? 'ring-2 ring-[#0F52BA] dark:ring-blue-500 ring-offset-1 dark:ring-offset-slate-900 font-black' : '',
                                getQuestionStatus(q.id) === 'answered'
                                    ? 'bg-emerald-600 text-white border-emerald-600'
                                    : getQuestionStatus(q.id) === 'doubt'
                                    ? 'bg-amber-500 text-white border-amber-500'
                                    : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700',
                                'h-10 rounded-xl border font-mono font-bold text-xs flex flex-col items-center justify-center transition-all cursor-pointer'
                            ]"
                        >
                            <span>{{ idx + 1 }}</span>
                            <span v-if="answersMap[q.id]?.selectedOptions?.[0] && q.type === 'single'" class="text-[9px] uppercase font-bold leading-none">
                                {{ answersMap[q.id].selectedOptions[0] }}
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Drawer Submit -->
                <div class="pt-2">
                    <button
                        type="button"
                        @click="showMobileDrawer = false; showSubmitModal = true"
                        class="w-full py-2.5 bg-emerald-700 hover:bg-emerald-800 active:scale-[0.98] text-white font-bold text-xs rounded-xl flex items-center justify-center gap-2 cursor-pointer shadow-xs"
                    >
                        <CheckCircle2 class="w-4 h-4" />
                        <span>Kumpulkan Ujian</span>
                    </button>
                    <p v-if="minSubmitCountdown > 0" class="text-[10px] text-slate-500 dark:text-slate-400 text-center mt-1.5 leading-tight font-mono">
                        Waktu tersisa: {{ formatMinSubmit(minSubmitCountdown) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- ==================== ANTI-CHEAT WARNING STRIKE MODAL ==================== -->
        <div v-if="showWarningStrike && !isScreenLocked" class="fixed inset-0 bg-slate-900/50 dark:bg-black/70 flex items-center justify-center p-4 z-50">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border-2 border-amber-300 dark:border-amber-600 max-w-sm w-full p-6 shadow-xl text-center space-y-4 animate-in zoom-in-95 duration-150 transform-gpu">
                <div class="w-12 h-12 rounded-2xl bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 flex items-center justify-center mx-auto">
                    <AlertTriangle class="w-6 h-6" />
                </div>

                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">
                        Peringatan Pelanggaran #{{ strikes }}
                    </h3>
                    <p class="text-xs text-amber-800 dark:text-amber-300 font-semibold mt-1">
                        {{ strikeReason || 'Terdeteksi keluar dari layar ujian CBT' }}
                    </p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 leading-relaxed">
                        Anda tidak diperkenankan membuka tab baru, membagi layar, atau meninggalkan aplikasi. Jika mencapai <strong>{{ maxCheatStrikes }}x peringatan</strong>, layar ujian Anda akan terkunci secara permanen!
                    </p>
                </div>

                <button
                    type="button"
                    @click="showWarningStrike = false"
                    class="w-full py-2.5 bg-[#0F52BA] hover:bg-blue-700 active:scale-[0.98] text-white text-xs font-semibold rounded-xl shadow-xs transition-all cursor-pointer"
                >
                    Saya Mengerti & Lanjutkan Ujian
                </button>
            </div>
        </div>

        <!-- ==================== SUBMIT CONFIRMATION MODAL ==================== -->
        <div v-if="showSubmitModal" class="fixed inset-0 bg-slate-900/50 dark:bg-black/70 flex items-center justify-center p-4 z-50">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 max-w-sm w-full p-6 shadow-xl text-center space-y-4 animate-in zoom-in-95 duration-150 transform-gpu">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto border border-emerald-200/60 dark:border-emerald-800">
                    <CheckCircle2 class="w-6 h-6" />
                </div>

                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">
                        Konfirmasi Selesai Ujian
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Apakah Anda yakin ingin mengakhiri sesi ujian ini? Seluruh jawaban akan dikirimkan secara final ke server sekolah.
                    </p>
                </div>

                <!-- Summary Status Breakdown -->
                <div class="grid grid-cols-3 gap-2 p-3 bg-slate-50 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700 rounded-xl text-xs font-mono">
                    <div>
                        <span class="text-[10px] text-slate-400 dark:text-slate-500 block uppercase">Terjawab</span>
                        <strong class="text-emerald-700 dark:text-emerald-400 text-sm">{{ answeredCount }}</strong>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 dark:text-slate-500 block uppercase">Ragu-ragu</span>
                        <strong class="text-amber-600 dark:text-amber-400 text-sm">{{ doubtCount }}</strong>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 dark:text-slate-500 block uppercase">Kosong</span>
                        <strong :class="unansweredCount > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-700 dark:text-slate-300'" class="text-sm">{{ unansweredCount }}</strong>
                    </div>
                </div>

                <div v-if="unansweredCount > 0 || doubtCount > 0" class="p-2.5 bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-800 rounded-xl text-left text-[11px] text-amber-900 dark:text-amber-200 flex items-start gap-2">
                    <AlertCircle class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" />
                    <span>Masih ada soal yang belum dijawab atau ditandai ragu-ragu. Anda disarankan memeriksa kembali.</span>
                </div>

                <!-- Early submission notice -->
                <div v-if="minSubmitCountdown > 0" class="p-2.5 bg-blue-50/90 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800 rounded-xl text-left text-[11px] text-blue-900 dark:text-blue-200 flex items-start gap-2">
                    <Clock class="w-4 h-4 text-blue-600 dark:text-blue-400 shrink-0 mt-0.5" />
                    <div>
                        <strong class="block text-blue-950 dark:text-blue-100 font-bold">Mengumpulkan Lebih Awal</strong>
                        <span>Waktu standar pengerjaan masih tersisa <strong>{{ formatMinSubmit(minSubmitCountdown) }}</strong>. Seluruh lembar jawaban Anda akan langsung tersimpan dan dinilai secara resmi.</span>
                    </div>
                </div>

                <div class="flex gap-2.5 pt-1">
                    <button
                        @click="showSubmitModal = false"
                        type="button"
                        class="flex-1 py-2.5 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-600 dark:text-slate-300 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 active:scale-[0.98] transition-all cursor-pointer bg-white dark:bg-slate-800"
                    >
                        Periksa Lagi
                    </button>
                    <button
                        @click="confirmSubmit"
                        type="button"
                        :disabled="isSubmitting"
                        class="flex-1 py-2.5 bg-emerald-700 hover:bg-emerald-800 active:scale-[0.98] text-white text-xs font-bold rounded-xl shadow-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-50"
                    >
                        <Send class="w-3.5 h-3.5" />
                        <span>{{ isSubmitting ? 'Mengirimkan...' : 'Ya, Kumpulkan' }}</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- ==================== ANTI-CHEAT SCREEN LOCK OVERLAY ==================== -->
        <div v-if="isScreenLocked" class="fixed inset-0 bg-slate-950/95 flex items-center justify-center p-4 z-50 text-center">
            <div class="max-w-md w-full bg-slate-900 border border-rose-500/50 rounded-3xl p-8 shadow-xl text-white space-y-4 transform-gpu">
                <div class="w-16 h-16 rounded-2xl bg-rose-500/20 text-rose-500 flex items-center justify-center mx-auto border border-rose-500/40 animate-pulse">
                    <Lock class="w-8 h-8" />
                </div>

                <div>
                    <h2 class="text-base sm:text-lg font-bold uppercase tracking-wider text-rose-400">
                        Layar Ujian Terkunci Otomatis
                    </h2>
                    <p class="text-xs text-slate-300 leading-relaxed mt-2">
                        Sistem mendeteksi aktivitas mencurigakan (membuka tab baru, keluar dari fullscreen, atau meninggalkan aplikasi) sebanyak <strong>{{ strikes }} kali</strong> (Batas maksimal: {{ maxCheatStrikes }}x).
                    </p>
                </div>

                <div class="p-4 bg-slate-800/80 border border-slate-700 rounded-2xl text-xs text-slate-300 text-left space-y-1">
                    <p class="font-bold text-white">Instruksi Peserta Ujian:</p>
                    <p class="leading-relaxed">
                        Silakan angkat tangan di ruang ujian dan mintalah <strong>Pengawas Ruang (Proctor)</strong> untuk menekan tombol <em>Buka Kunci (Unlock)</em> dari Meja Pengawas.
                    </p>
                </div>

                <!-- Admin / Teacher Direct Unlock button for test convenience -->
                <div v-if="$page.props.auth?.user?.role === 'admin' || $page.props.auth?.user?.role === 'teacher'" class="pt-2">
                    <button
                        type="button"
                        @click="adminDirectUnlock"
                        :disabled="isUnlocking"
                        class="w-full py-2.5 px-4 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 active:scale-[0.98] text-white font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer"
                    >
                        <Unlock class="w-4 h-4" />
                        <span>{{ isUnlocking ? 'Membuka Kunci...' : 'Buka Kunci Sekarang (Mode Admin/Guru)' }}</span>
                    </button>
                    <p class="text-[10px] text-slate-400 mt-1.5">
                        Tombol ini hanya tampil untuk akun Admin & Guru saat pengujian CBT.
                    </p>
                </div>

                <div v-else class="flex items-center justify-center gap-2 text-xs text-slate-400 font-mono pt-2">
                    <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                    <span>Menunggu otorisasi Pengawas Ruang...</span>
                </div>
            </div>
        </div>
    </div>
</template>
