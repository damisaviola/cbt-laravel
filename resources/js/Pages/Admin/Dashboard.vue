<script setup>
import { ref } from 'vue';
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
    Server, 
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
    School
} from 'lucide-vue-next';

const props = defineProps({
    school: Object,
    stats: Object,
    recentSessions: Array,
});

const copiedTokenId = ref(null);

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
</script>

<template>
    <AppLayout>
        <Head title="Dashboard Overview" />

        <div class="space-y-6">
            <!-- Page Header matching contoh.jpg (Title on Left, Export & Primary Action on Right) -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                        Dashboard Overview
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        Welcome back, here's what's happening with your school and exam system.
                    </p>
                </div>

                <div class="flex items-center gap-3 shrink-0">
                    <!-- Export Pill Button matching contoh.jpg -->
                    <button
                        type="button"
                        @click="printOrExport"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800 rounded-full text-xs font-semibold text-slate-700 dark:text-slate-200 shadow-2xs transition-colors cursor-pointer"
                    >
                        <Download class="w-4 h-4 text-slate-500" />
                        <span>Export</span>
                    </button>

                    <!-- New Course / Action Pill Button matching contoh.jpg (Vibrant Blue) -->
                    <Link
                        href="/admin/schedules"
                        class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-full text-xs font-semibold shadow-sm shadow-blue-600/25 transition-all cursor-pointer"
                    >
                        <Plus class="w-4 h-4" />
                        <span>New Course</span>
                    </Link>
                </div>
            </div>

            <!-- 4 Bento Stat Cards Grid matching contoh.jpg -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Card 1: Total Students (Pastel Blue) -->
                <BentoStatCard
                    title="Total Students"
                    :value="stats.total_students"
                    trend="+12%"
                    trend-type="up"
                    icon-bg="bg-blue-50 dark:bg-blue-950/60"
                    icon-color="text-blue-600 dark:text-blue-400"
                >
                    <template #icon>
                        <Users class="w-5 h-5" />
                    </template>
                </BentoStatCard>

                <!-- Card 2: Active Courses (Pastel Amber) -->
                <BentoStatCard
                    title="Active Courses"
                    :value="stats.active_schedules"
                    trend="+5%"
                    trend-type="up"
                    icon-bg="bg-amber-50 dark:bg-amber-950/60"
                    icon-color="text-amber-600 dark:text-amber-400"
                >
                    <template #icon>
                        <BookOpen class="w-5 h-5" />
                    </template>
                </BentoStatCard>

                <!-- Card 3: Total Bank / Revenue (Pastel Emerald) -->
                <BentoStatCard
                    title="Question Banks"
                    :value="stats.total_banks"
                    trend="-2%"
                    trend-type="down"
                    icon-bg="bg-emerald-50 dark:bg-emerald-950/60"
                    icon-color="text-emerald-600 dark:text-emerald-400"
                >
                    <template #icon>
                        <Award class="w-5 h-5" />
                    </template>
                </BentoStatCard>

                <!-- Card 4: Instructors / Pendidik (Pastel Sky) -->
                <BentoStatCard
                    title="Instructors"
                    :value="stats.total_teachers + stats.total_proctors"
                    trend="+8%"
                    trend-type="up"
                    icon-bg="bg-sky-50 dark:bg-sky-950/60"
                    icon-color="text-sky-600 dark:text-sky-400"
                >
                    <template #icon>
                        <GraduationCap class="w-5 h-5" />
                    </template>
                </BentoStatCard>
            </div>

            <!-- Two-Column Section matching contoh.jpg (Trends Chart on Left, Categories on Right) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                <!-- Left: Enrollment Trends Chart (approx 65% width) -->
                <div class="lg:col-span-2">
                    <TrendChart 
                        title="Enrollment Trends"
                        subtitle="Aktivitas pertumbuhan siswa dan peserta ujian terdaftar"
                        filter-options="['This Year', 'Last 6 Months', 'This Month']"
                    />
                </div>

                <!-- Right: Top Performing Categories (approx 35% width) -->
                <div class="lg:col-span-1">
                    <CategoryProgressWidget 
                        title="Top Performing Categories"
                        subtitle="Distribusi performa kelulusan berdasarkan bidang"
                    />
                </div>
            </div>

            <!-- Quick Action Cards matching EduPro clean style -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <Link
                    href="/admin/students"
                    class="p-4 bg-white dark:bg-slate-900 hover:bg-blue-50/40 dark:hover:bg-blue-950/20 border border-slate-100 dark:border-slate-800 hover:border-blue-200 dark:hover:border-blue-900 rounded-2xl shadow-sm transition-all flex items-center gap-3.5 group"
                >
                    <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                        <GraduationCap class="w-5 h-5" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-slate-900 dark:text-white truncate">Data Siswa</p>
                        <p class="text-[11px] text-slate-400 truncate">Cetak kartu & absen</p>
                    </div>
                </Link>

                <Link
                    href="/admin/classes"
                    class="p-4 bg-white dark:bg-slate-900 hover:bg-purple-50/40 dark:hover:bg-purple-950/20 border border-slate-100 dark:border-slate-800 hover:border-purple-200 dark:hover:border-purple-900 rounded-2xl shadow-sm transition-all flex items-center gap-3.5 group"
                >
                    <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                        <Layers class="w-5 h-5" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-slate-900 dark:text-white truncate">Rombel & Kelas</p>
                        <p class="text-[11px] text-slate-400 truncate">Master kelas & ruang</p>
                    </div>
                </Link>

                <Link
                    href="/admin/schedules"
                    class="p-4 bg-white dark:bg-slate-900 hover:bg-amber-50/40 dark:hover:bg-amber-950/20 border border-slate-100 dark:border-slate-800 hover:border-amber-200 dark:hover:border-amber-900 rounded-2xl shadow-sm transition-all flex items-center gap-3.5 group"
                >
                    <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                        <Calendar class="w-5 h-5" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-slate-900 dark:text-white truncate">Jadwal Ujian</p>
                        <p class="text-[11px] text-slate-400 truncate">Sesi & rilis token</p>
                    </div>
                </Link>

                <Link
                    href="/admin/settings"
                    class="p-4 bg-white dark:bg-slate-900 hover:bg-emerald-50/40 dark:hover:bg-emerald-950/20 border border-slate-100 dark:border-slate-800 hover:border-emerald-200 dark:hover:border-emerald-900 rounded-2xl shadow-sm transition-all flex items-center gap-3.5 group"
                >
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                        <Settings class="w-5 h-5" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-slate-900 dark:text-white truncate">Pengaturan</p>
                        <p class="text-[11px] text-slate-400 truncate">Profil & sistem sekolah</p>
                    </div>
                </Link>
            </div>

            <!-- Two-Column Workspace Layout (Active Sessions & School Preferences Summary) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                <!-- Left: Recent Sesi Ujian List (2 cols) -->
                <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-6 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 mb-5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold">
                                    <Activity class="w-4 h-4" />
                                </div>
                                <div>
                                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">
                                        Sesi Ujian Aktif
                                    </h2>
                                    <p class="text-xs text-slate-400">Live monitoring token & ruang ujian</p>
                                </div>
                            </div>
                            <Link href="/admin/schedules" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                                Semua Jadwal &rarr;
                            </Link>
                        </div>

                        <div v-if="recentSessions.length === 0" class="text-center py-10 text-xs text-slate-400 dark:text-slate-500 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-dashed border-slate-200 dark:border-slate-800">
                            Belum ada sesi ujian yang dijadwalkan saat ini.
                        </div>

                        <div v-else class="space-y-3 overflow-y-auto max-h-[440px] pr-0.5">
                            <div
                                v-for="sess in recentSessions"
                                :key="sess.id"
                                class="p-4 bg-slate-50/70 dark:bg-slate-800/50 hover:bg-slate-50 dark:hover:bg-slate-800 border border-slate-100 dark:border-slate-800 rounded-2xl flex flex-col gap-3 transition-all shadow-2xs group"
                            >
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 mb-0.5">
                                            <span class="w-2 h-2 rounded-full shrink-0" :class="sess.status === 'active' ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400'"></span>
                                            <p class="text-xs font-bold text-slate-900 dark:text-slate-100 truncate group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                                {{ sess.exam_schedule?.subject?.name }}
                                            </p>
                                        </div>
                                        <p class="text-[11px] text-slate-400 flex items-center gap-1.5 pl-4">
                                            <span class="font-medium text-slate-600 dark:text-slate-300">{{ sess.session_name }}</span>
                                            <span>&bull;</span>
                                            <span>{{ sess.room_name }}</span>
                                            <span v-if="sess.proctor">&bull; Pengawas: {{ sess.proctor.name }}</span>
                                        </p>
                                    </div>

                                    <span
                                        :class="[
                                            sess.status === 'active' 
                                                ? 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400' 
                                                : 'bg-slate-100 dark:bg-slate-800 text-slate-500',
                                            'px-2.5 py-0.5 text-[10px] font-semibold rounded-full shrink-0 uppercase tracking-wide'
                                        ]"
                                    >
                                        {{ sess.status }}
                                    </span>
                                </div>

                                <!-- Token Chip with 1-click Copy -->
                                <div class="flex items-center justify-between pt-2.5 border-t border-slate-200/50 dark:border-slate-700/60 text-xs">
                                    <div class="flex items-center gap-2 font-mono">
                                        <span class="text-[10px] uppercase font-bold text-slate-400">Token Masuk:</span>
                                        <span class="bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 px-2.5 py-0.5 rounded-lg font-bold tracking-widest text-xs select-all">
                                            {{ sess.access_token }}
                                        </span>
                                    </div>

                                    <button
                                        @click="copyToken(sess.id, sess.access_token)"
                                        type="button"
                                        class="px-3 py-1 text-[11px] font-medium text-slate-600 dark:text-slate-300 hover:text-emerald-600 dark:hover:text-emerald-400 hover:bg-white dark:hover:bg-slate-700 rounded-full transition-all flex items-center gap-1 cursor-pointer"
                                    >
                                        <Check v-if="copiedTokenId === sess.id" class="w-3.5 h-3.5 text-emerald-600" />
                                        <Copy v-else class="w-3.5 h-3.5" />
                                        <span>{{ copiedTokenId === sess.id ? 'Tersalin' : 'Salin Token' }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 dark:border-slate-800 mt-4">
                        <Link
                            href="/proctor/dashboard"
                            class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-full flex items-center justify-center gap-2 shadow-sm transition-all"
                        >
                            <ShieldCheck class="w-4 h-4" />
                            <span>Buka Ruang Pengawas (Live Monitoring)</span>
                            <ArrowRight class="w-3.5 h-3.5" />
                        </Link>
                    </div>
                </div>

                <!-- Right: School Profile & Settings Shortcut Card (1 col) -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-6 shadow-sm space-y-5">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold">
                                <School class="w-4 h-4" />
                            </div>
                            <div>
                                <h2 class="text-sm font-bold text-slate-900 dark:text-white">
                                    Profil & Sistem Sekolah
                                </h2>
                                <p class="text-xs text-slate-400">Identitas lembaga terdaftar</p>
                            </div>
                        </div>
                        <span class="text-xs text-purple-700 dark:text-purple-300 font-mono font-bold bg-purple-50 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800 px-2.5 py-0.5 rounded-full">
                            Aktif
                        </span>
                    </div>

                    <div class="space-y-3.5 text-xs">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Nama Sekolah</span>
                            <p class="font-bold text-slate-900 dark:text-white text-sm mt-0.5">{{ school?.school_name }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-3 pt-2 border-t border-slate-100 dark:border-slate-800">
                            <div>
                                <span class="text-[10px] uppercase font-bold text-slate-400 block">NPSN</span>
                                <p class="font-mono font-bold text-slate-800 dark:text-slate-200 mt-0.5">{{ school?.npsn }}</p>
                            </div>
                            <div>
                                <span class="text-[10px] uppercase font-bold text-slate-400 block">Tahun Ajaran</span>
                                <p class="font-semibold text-slate-800 dark:text-slate-200 mt-0.5">{{ school?.active_academic_year }} ({{ school?.active_semester }})</p>
                            </div>
                        </div>
                        <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Kepala Sekolah</span>
                            <p class="font-semibold text-slate-800 dark:text-slate-200 mt-0.5">{{ school?.headmaster_name }}</p>
                            <p v-if="school?.headmaster_nip" class="text-[11px] font-mono text-slate-400">NIP: {{ school.headmaster_nip }}</p>
                        </div>
                        <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                            <span class="text-slate-400">Keamanan SEB:</span>
                            <span :class="school?.seb_required ? 'text-rose-600 font-bold' : 'text-slate-500 font-medium'">
                                {{ school?.seb_required ? 'Wajib Kiosk' : 'Standar' }}
                            </span>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                        <Link
                            href="/admin/settings"
                            class="w-full py-2.5 bg-slate-50 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-750 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-full border border-slate-200/80 dark:border-slate-700 flex items-center justify-center gap-2 transition-colors"
                        >
                            <Settings class="w-4 h-4 text-slate-500" />
                            <span>Buka Halaman Pengaturan Lengkap</span>
                            <ArrowRight class="w-3.5 h-3.5" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
