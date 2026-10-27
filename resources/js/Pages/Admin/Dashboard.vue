<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import BentoStatCard from '@/Components/BentoStatCard.vue';
import TrendChart from '@/Components/TrendChart.vue';
import CategoryProgressWidget from '@/Components/CategoryProgressWidget.vue';
import { 
    Users, 
    BookOpen, 
    GraduationCap, 
    Calendar, 
    Settings, 
    ShieldCheck, 
    Activity, 
    Layers, 
    Copy, 
    Check, 
    ArrowRight, 
    Download, 
    Plus, 
    Award, 
    School,
    Play,
    Pause,
    Clock,
    ArrowUpRight,
    Video
} from 'lucide-vue-next';

const props = defineProps({
    school: Object,
    stats: Object,
    recentSessions: Array,
    chartData: Object,
    categoryData: Array,
});

const copiedTokenId = ref(null);
const currentTime = ref('01:24:08');
const isTimerRunning = ref(true);
let timerInterval = null;

const copyToken = (sessionId, token) => {
    navigator.clipboard.writeText(token);
    copiedTokenId.value = sessionId;
    setTimeout(() => {
        copiedTokenId.value = null;
    }, 2000);
};

const printOrExport = () => {
    window.print();
};

const updateClock = () => {
    if (!isTimerRunning.value) return;
    const now = new Date();
    currentTime.value = now.toLocaleTimeString('en-GB', { hour12: false });
};

const toggleTimer = () => {
    isTimerRunning.value = !isTimerRunning.value;
};

const resetTimer = () => {
    const now = new Date();
    currentTime.value = now.toLocaleTimeString('en-GB', { hour12: false });
};

onMounted(() => {
    updateClock();
    timerInterval = setInterval(updateClock, 1000);
});

onUnmounted(() => {
    if (timerInterval) clearInterval(timerInterval);
});

// Primary upcoming reminder session
const upcomingSession = computed(() => {
    if (props.recentSessions && props.recentSessions.length > 0) {
        return props.recentSessions[0];
    }
    return null;
});
</script>

<template>
    <AppLayout>
        <Head title="Dashboard" />

        <div class="space-y-6 w-full">
            <!-- Page Header matching contoh.webp (Title on Left, + Add Project & Import Data on Right) -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                        Dashboard
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                        Plan, prioritize, and accomplish your tasks with ease.
                    </p>
                </div>

                <div class="flex items-center gap-2.5 sm:gap-3 shrink-0">
                    <!-- Primary Pill Action Button (+ Add Project style in contoh.webp) -->
                    <Link
                        href="/admin/schedules"
                        class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-[#185338] hover:bg-[#12422c] text-white rounded-full text-xs font-semibold shadow-xs transition-all cursor-pointer"
                    >
                        <Plus class="w-4 h-4 text-white" />
                        <span>Add Project</span>
                    </Link>

                    <!-- Secondary Pill Action Button (Import Data style in contoh.webp) -->
                    <button
                        type="button"
                        @click="printOrExport"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 border border-slate-200/80 dark:border-slate-800 rounded-full text-xs font-semibold text-slate-700 dark:text-slate-200 shadow-2xs transition-colors cursor-pointer"
                    >
                        <span>Import Data</span>
                    </button>
                </div>
            </div>

            <!-- 4 Bento Stat Cards Grid matching contoh.webp (Card 1 Hero Forest Green, Cards 2-4 White) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Card 1: Total Projects / Siswa (Hero Forest Green) -->
                <BentoStatCard
                    is-hero
                    title="Total Projects"
                    :value="stats.total_students"
                    trend="5"
                    trend-label="Increased from last month"
                />

                <!-- Card 2: Ended Projects / Bank Soal (Clean White Card) -->
                <BentoStatCard
                    title="Ended Projects"
                    :value="stats.total_banks"
                    trend="6"
                    trend-label="Increased from last month"
                />

                <!-- Card 3: Running Projects / Ujian Aktif (Clean White Card) -->
                <BentoStatCard
                    title="Running Projects"
                    :value="stats.active_schedules"
                    trend="2"
                    trend-label="Increased from last month"
                />

                <!-- Card 4: Pending Project / Tenaga Pendidik (Clean White Card) -->
                <BentoStatCard
                    title="Pending Project"
                    :value="stats.total_teachers + stats.total_proctors"
                    trend="2"
                    trend-label="On Discuss"
                />
            </div>

            <!-- Middle Bento Row: Project Analytics (Left 2 cols) & Reminders (Right 1 col) matching contoh.webp -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                <!-- Left: Project Analytics Line/Bar Chart (2 cols) -->
                <div class="lg:col-span-2">
                    <TrendChart 
                        title="Project Analytics"
                        subtitle="Aktivitas ujian dan tren peserta terdaftar mingguan"
                        :filter-options="['This Year', 'Last 6 Months', 'This Month']"
                        :data-sets="chartData"
                    />
                </div>

                <!-- Right: Reminders Card matching contoh.webp -->
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-7 border border-slate-200/80 dark:border-slate-800 shadow-2xs flex flex-col justify-between min-h-[290px]">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-base font-bold text-slate-900 dark:text-white tracking-tight">
                                Reminders
                            </h3>
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        </div>

                        <div v-if="upcomingSession" class="space-y-1 mt-2">
                            <h4 class="text-base font-bold text-slate-900 dark:text-white leading-snug">
                                {{ upcomingSession.exam_schedule?.subject?.name || 'Meeting with Arc Company' }}
                            </h4>
                            <p class="text-xs text-slate-400 dark:text-slate-500">
                                Sesi: {{ upcomingSession.session_name }} &bull; {{ upcomingSession.room_name }}
                            </p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 pt-1">
                                Time : 08.00 am - 11.00 am
                            </p>
                        </div>
                        <div v-else class="space-y-1 mt-2">
                            <h4 class="text-base font-bold text-slate-900 dark:text-white leading-snug">
                                Meeting with Arc Company
                            </h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Time : 02.00 pm - 04.00 pm
                            </p>
                        </div>
                    </div>

                    <!-- Full Width Forest Green Pill Button matching 'Start Meeting' in contoh.webp -->
                    <div class="pt-6">
                        <Link
                            href="/proctor/dashboard"
                            class="w-full py-3 bg-[#185338] hover:bg-[#12422c] text-white text-xs font-semibold rounded-full flex items-center justify-center gap-2 shadow-xs transition-all cursor-pointer"
                        >
                            <Video class="w-4 h-4 fill-white text-white" />
                            <span>Start Meeting</span>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Lower Bento Row: Team Collaboration (Left 2 cols) & Project Progress + Time Tracker (Right 1 col) matching contoh.webp -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                <!-- Left: Team Collaboration / Live Sessions List (2 cols) matching contoh.webp -->
                <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-7 border border-slate-200/80 dark:border-slate-800 shadow-2xs flex flex-col justify-between">
                    <div>
                        <!-- Header with + Add Member Pill matching contoh.webp -->
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 mb-5">
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white tracking-tight">
                                    Team Collaboration
                                </h3>
                                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
                                    Aktivitas live pengawas ruang dan peserta ujian
                                </p>
                            </div>
                            <Link 
                                href="/admin/students"
                                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-750 border border-slate-200/80 dark:border-slate-700 shadow-2xs transition-all cursor-pointer"
                            >
                                <Plus class="w-3.5 h-3.5" />
                                <span>Add Member</span>
                            </Link>
                        </div>

                        <!-- Empty State -->
                        <div v-if="!recentSessions || recentSessions.length === 0" class="text-center py-10 text-xs text-slate-400 dark:text-slate-500 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-dashed border-slate-200 dark:border-slate-800">
                            Belum ada sesi ujian yang dijadwalkan saat ini.
                        </div>

                        <!-- User Rows matching Alexandra Deff, Edwin Adenike etc. in contoh.webp -->
                        <div v-else class="space-y-4">
                            <div
                                v-for="(sess, sIdx) in recentSessions.slice(0, 4)"
                                :key="sess.id"
                                class="flex items-center justify-between gap-3 p-2 rounded-2xl hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group"
                            >
                                <div class="flex items-center gap-3.5 min-w-0">
                                    <!-- Circular Avatar with pastel background -->
                                    <div 
                                        :class="[
                                            sIdx % 4 === 0 ? 'bg-amber-100 text-amber-800' :
                                            sIdx % 4 === 1 ? 'bg-emerald-100 text-emerald-800' :
                                            sIdx % 4 === 2 ? 'bg-blue-100 text-blue-800' : 'bg-rose-100 text-rose-800',
                                            'w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs'
                                        ]"
                                    >
                                        {{ sess.proctor?.name ? sess.proctor.name[0] : (sIdx === 0 ? 'A' : sIdx === 1 ? 'E' : sIdx === 2 ? 'I' : 'D') }}
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white truncate">
                                            {{ sess.proctor?.name || (sIdx === 0 ? 'Alexandra Deff' : sIdx === 1 ? 'Edwin Adenike' : sIdx === 2 ? 'Isaac Oluwatemilorun' : 'David Oshodi') }}
                                        </h4>
                                        <p class="text-[11px] text-slate-400 dark:text-slate-500 truncate">
                                            Working on <span class="font-medium text-slate-700 dark:text-slate-300">{{ sess.exam_schedule?.subject?.name || 'Github Project Repository' }}</span>
                                        </p>
                                    </div>
                                </div>

                                <!-- Status Badge matching Completed, In Progress, Pending in contoh.webp -->
                                <div class="flex items-center gap-2 shrink-0">
                                    <span
                                        :class="[
                                            sess.status === 'active'
                                                ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800'
                                                : sIdx % 2 === 0
                                                    ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-200/60 dark:border-amber-800'
                                                    : 'bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200/60 dark:border-rose-800',
                                            'px-2.5 py-0.5 rounded-md text-[10px] font-semibold'
                                        ]"
                                    >
                                        {{ sess.status === 'active' ? 'In Progress' : (sIdx % 2 === 0 ? 'Completed' : 'Pending') }}
                                    </span>

                                    <!-- Quick Copy Token button -->
                                    <button
                                        v-if="sess.access_token"
                                        @click="copyToken(sess.id, sess.access_token)"
                                        type="button"
                                        title="Salin Token Ujian"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-emerald-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                                    >
                                        <Check v-if="copiedTokenId === sess.id" class="w-3.5 h-3.5 text-emerald-600" />
                                        <Copy v-else class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-5 border-t border-slate-100 dark:border-slate-800 mt-5">
                        <Link
                            href="/proctor/dashboard"
                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#185338] dark:text-emerald-400 hover:underline"
                        >
                            <span>Buka Ruang Pengawas Lengkap</span>
                            <ArrowRight class="w-3.5 h-3.5" />
                        </Link>
                    </div>
                </div>

                <!-- Right Column: Project Progress (Gauge) & Time Tracker matching contoh.webp -->
                <div class="space-y-6">
                    <!-- Project Progress Semi-Circle Gauge Card matching contoh.webp -->
                    <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-800 shadow-2xs">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white tracking-tight mb-4">
                            Project Progress
                        </h3>

                        <!-- Semi-Circular Donut Gauge SVG -->
                        <div class="relative flex flex-col items-center justify-center my-2">
                            <svg class="w-48 h-28" viewBox="0 0 200 110">
                                <!-- Background Track -->
                                <path
                                    d="M 20 100 A 80 80 0 0 1 180 100"
                                    fill="none"
                                    stroke="#e2e8f0"
                                    stroke-width="20"
                                    stroke-linecap="round"
                                    class="dark:stroke-slate-800"
                                />
                                <!-- Completed Arc (Forest Green) -->
                                <path
                                    d="M 20 100 A 80 80 0 0 1 125 24"
                                    fill="none"
                                    stroke="#185338"
                                    stroke-width="20"
                                    stroke-linecap="round"
                                />
                                <!-- Active Arc (Light Emerald) -->
                                <path
                                    d="M 125 24 A 80 80 0 0 1 150 42"
                                    fill="none"
                                    stroke="#34d399"
                                    stroke-width="20"
                                    stroke-linecap="round"
                                />
                            </svg>

                            <!-- Center Value -->
                            <div class="absolute bottom-0 text-center">
                                <span class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight block">
                                    41%
                                </span>
                                <span class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">
                                    Project Ended
                                </span>
                            </div>
                        </div>

                        <!-- Legend below matching contoh.webp -->
                        <div class="flex items-center justify-center gap-3 pt-4 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-600 dark:text-slate-400 font-medium">
                            <span class="inline-flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-[#185338]"></span>
                                <span>Completed</span>
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                <span>In Progress</span>
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-slate-300 dark:bg-slate-700"></span>
                                <span>Pending</span>
                            </span>
                        </div>
                    </div>

                    <!-- Time Tracker Card matching dark forest green organic wave card in contoh.webp -->
                    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#0a271a] via-[#0f3826] to-[#185338] p-6 text-white shadow-sm border border-emerald-950/40">
                        <!-- Wave lines overlay matching contoh.webp -->
                        <div class="absolute -right-8 -top-8 w-44 h-44 rounded-full border border-white/10 pointer-events-none"></div>
                        <div class="absolute -right-14 -top-14 w-56 h-56 rounded-full border border-white/5 pointer-events-none"></div>
                        <div class="absolute -right-20 -top-20 w-68 h-68 rounded-full border border-white/5 pointer-events-none"></div>

                        <div class="relative z-10 space-y-4">
                            <span class="text-xs font-semibold text-emerald-100/80 tracking-wide uppercase">
                                Time Tracker
                            </span>

                            <!-- Big Digital Clock matching 01:24:08 in contoh.webp -->
                            <div class="text-4xl sm:text-5xl font-mono font-extrabold tracking-wider text-white">
                                {{ currentTime }}
                            </div>

                            <!-- Circular Control Buttons matching Pause & Stop in contoh.webp -->
                            <div class="flex items-center gap-3 pt-2">
                                <!-- Pause / Play Circular Button (White circle with green icon) -->
                                <button
                                    type="button"
                                    @click="toggleTimer"
                                    :title="isTimerRunning ? 'Jeda waktu' : 'Lanjutkan waktu'"
                                    class="w-11 h-11 rounded-full bg-white text-[#185338] hover:bg-slate-100 flex items-center justify-center shadow-md transition-transform hover:scale-105 cursor-pointer"
                                >
                                    <Pause v-if="isTimerRunning" class="w-5 h-5 fill-current" />
                                    <Play v-else class="w-5 h-5 fill-current ml-0.5" />
                                </button>

                                <!-- Stop / Reset Circular Button (Red circle with white square) -->
                                <button
                                    type="button"
                                    @click="resetTimer"
                                    title="Reset ke waktu server"
                                    class="w-11 h-11 rounded-full bg-rose-500 hover:bg-rose-600 text-white flex items-center justify-center shadow-md transition-transform hover:scale-105 cursor-pointer"
                                >
                                    <div class="w-4 h-4 bg-white rounded-xs"></div>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
