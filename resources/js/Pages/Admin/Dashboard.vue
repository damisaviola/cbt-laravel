<script setup>
import { ref } from 'vue';
import { useForm, Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { 
    Users, 
    BookOpen, 
    GraduationCap, 
    Calendar, 
    Server, 
    Settings, 
    ShieldCheck, 
    CheckCircle2, 
    Activity,
    Layers,
    Copy,
    Check,
    ArrowRight
} from 'lucide-vue-next';

const props = defineProps({
    school: Object,
    stats: Object,
    recentSessions: Array,
});

const form = useForm({
    school_name: props.school?.school_name || '',
    npsn: props.school?.npsn || '',
    headmaster_name: props.school?.headmaster_name || '',
    headmaster_nip: props.school?.headmaster_nip || '',
    active_academic_year: props.school?.active_academic_year || '2026/2027',
    active_semester: props.school?.active_semester || 'Ganjil',
    primary_color: props.school?.primary_color || '#0F52BA',
    seb_required: Boolean(props.school?.seb_required),
    seb_key_hash: props.school?.seb_key_hash || '',
});

const copiedTokenId = ref(null);

const copyToken = (sessionId, token) => {
    navigator.clipboard.writeText(token);
    copiedTokenId.value = sessionId;
    setTimeout(() => {
        copiedTokenId.value = null;
    }, 2000);
};

const saveSettings = () => {
    form.post('/admin/settings', {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Dashboard Administrator" />

        <template #header>
            <div class="min-w-0">
                <h1 class="text-sm sm:text-base font-bold text-slate-900 dark:text-slate-100 tracking-tight truncate">Dashboard & Pengaturan Sekolah</h1>
                <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-0.5 truncate sm:whitespace-normal">Pusat kendali master data akademik, beban server, dan konfigurasi ujian.</p>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Functional Metrics Grid (Modern Rounded-2xl Cards with Color Accents) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Card 1: Total Siswa -->
                <div class="bg-gradient-to-br from-white via-white to-blue-50/50 dark:from-slate-900 dark:via-slate-900 dark:to-blue-950/20 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs hover:border-blue-300 dark:hover:border-blue-700 hover:shadow-md transition-all duration-200">
                    <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Siswa Aktif</span>
                        <div class="w-9 h-9 rounded-xl bg-blue-500 text-white flex items-center justify-center shadow-xs shadow-blue-500/25">
                            <GraduationCap class="w-4.5 h-4.5" />
                        </div>
                    </div>
                    <p class="text-2xl font-black text-slate-900 dark:text-slate-100 font-mono tabular-nums">
                        {{ stats.total_students }}
                    </p>
                    <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
                        <span class="font-medium text-slate-600 dark:text-slate-300">{{ stats.total_classes }} Rombel / Kelas</span>
                        <Link href="/admin/students" class="text-blue-700 dark:text-blue-400 font-bold hover:underline flex items-center gap-1">
                            <span>Lihat Siswa</span>
                            <span>&rarr;</span>
                        </Link>
                    </div>
                </div>

                <!-- Card 2: Tenaga Pendidik -->
                <div class="bg-gradient-to-br from-white via-white to-emerald-50/50 dark:from-slate-900 dark:via-slate-900 dark:to-emerald-950/20 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs hover:border-emerald-300 dark:hover:border-emerald-700 hover:shadow-md transition-all duration-200">
                    <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Tenaga Pendidik</span>
                        <div class="w-9 h-9 rounded-xl bg-emerald-500 text-white flex items-center justify-center shadow-xs shadow-emerald-500/25">
                            <Users class="w-4.5 h-4.5" />
                        </div>
                    </div>
                    <p class="text-2xl font-black text-slate-900 dark:text-slate-100 font-mono tabular-nums">
                        {{ stats.total_teachers }} <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Guru</span> / {{ stats.total_proctors }} <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Pengawas</span>
                    </p>
                    <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
                        <span class="font-medium text-slate-600 dark:text-slate-300">{{ stats.total_subjects }} Mata Pelajaran</span>
                        <Link href="/admin/staff" class="text-emerald-700 dark:text-emerald-400 font-bold hover:underline flex items-center gap-1">
                            <span>Kelola</span>
                            <span>&rarr;</span>
                        </Link>
                    </div>
                </div>

                <!-- Card 3: Soal & Jadwal Ujian -->
                <div class="bg-gradient-to-br from-white via-white to-purple-50/50 dark:from-slate-900 dark:via-slate-900 dark:to-purple-950/20 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs hover:border-purple-300 dark:hover:border-purple-700 hover:shadow-md transition-all duration-200">
                    <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Soal & Jadwal Ujian</span>
                        <div class="w-9 h-9 rounded-xl bg-purple-500 text-white flex items-center justify-center shadow-xs shadow-purple-500/25">
                            <BookOpen class="w-4.5 h-4.5" />
                        </div>
                    </div>
                    <p class="text-2xl font-black text-slate-900 dark:text-slate-100 font-mono tabular-nums">
                        {{ stats.total_banks }}
                    </p>
                    <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
                        <span class="font-medium text-slate-600 dark:text-slate-300">{{ stats.active_schedules }} Jadwal Terjadwal</span>
                        <Link href="/admin/schedules" class="text-purple-700 dark:text-purple-400 font-bold hover:underline flex items-center gap-1">
                            <span>Jadwal</span>
                            <span>&rarr;</span>
                        </Link>
                    </div>
                </div>

                <!-- Card 4: Server Health -->
                <div class="bg-gradient-to-br from-white via-white to-amber-50/50 dark:from-slate-900 dark:via-slate-900 dark:to-amber-950/20 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs hover:border-amber-300 dark:hover:border-amber-700 hover:shadow-md transition-all duration-200">
                    <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Beban Sesi Server</span>
                        <div class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center shadow-xs shadow-amber-500/25">
                            <Server class="w-4.5 h-4.5" />
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <p class="text-2xl font-black text-slate-900 dark:text-slate-100 font-mono tabular-nums">
                            {{ stats.ongoing_attempts }}
                        </p>
                        <span class="text-xs font-semibold text-amber-700 dark:text-amber-300 bg-amber-100/70 dark:bg-amber-950/50 px-2 py-0.5 rounded-md">siswa sedang tes</span>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
                        <span class="text-emerald-700 dark:text-emerald-400 font-bold flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span>PHP {{ stats.server_php_version }} (Optimal)</span>
                        </span>
                        <span class="font-mono text-slate-400 dark:text-slate-500">{{ stats.server_os }}</span>
                    </div>
                </div>
            </div>

            <!-- Quick Action Launchers (Colorful & Friendly Cards) -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <Link
                    href="/admin/students"
                    class="p-3.5 bg-blue-50/60 dark:bg-blue-950/30 hover:bg-blue-50 dark:hover:bg-blue-950/50 border border-blue-200/70 dark:border-blue-900/60 hover:border-blue-400 dark:hover:border-blue-700 rounded-2xl shadow-2xs hover:shadow-sm transition-all flex items-center gap-3 group active:scale-[0.98]"
                >
                    <div class="w-10 h-10 rounded-xl bg-blue-500 text-white flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform shadow-xs shadow-blue-500/30">
                        <GraduationCap class="w-5 h-5" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-blue-950 dark:text-blue-200 truncate">Data Siswa & Kartu</p>
                        <p class="text-[11px] text-blue-700 dark:text-blue-400 truncate">Cetak kartu & absen</p>
                    </div>
                </Link>

                <Link
                    href="/admin/classes"
                    class="p-3.5 bg-purple-50/60 dark:bg-purple-950/30 hover:bg-purple-50 dark:hover:bg-purple-950/50 border border-purple-200/70 dark:border-purple-900/60 hover:border-purple-400 dark:hover:border-purple-700 rounded-2xl shadow-2xs hover:shadow-sm transition-all flex items-center gap-3 group active:scale-[0.98]"
                >
                    <div class="w-10 h-10 rounded-xl bg-purple-500 text-white flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform shadow-xs shadow-purple-500/30">
                        <Layers class="w-5 h-5" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-purple-950 dark:text-purple-200 truncate">Rombel & Kelas</p>
                        <p class="text-[11px] text-purple-700 dark:text-purple-400 truncate">Master rombel kelas</p>
                    </div>
                </Link>

                <Link
                    href="/admin/schedules"
                    class="p-3.5 bg-amber-50/60 dark:bg-amber-950/30 hover:bg-amber-50 dark:hover:bg-amber-950/50 border border-amber-200/70 dark:border-amber-900/60 hover:border-amber-400 dark:hover:border-amber-700 rounded-2xl shadow-2xs hover:shadow-sm transition-all flex items-center gap-3 group active:scale-[0.98]"
                >
                    <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform shadow-xs shadow-amber-500/30">
                        <Calendar class="w-5 h-5" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-amber-950 dark:text-amber-200 truncate">Jadwal Ujian & Sesi</p>
                        <p class="text-[11px] text-amber-700 dark:text-amber-400 truncate">Rilis token & waktu</p>
                    </div>
                </Link>

                <Link
                    href="/proctor/dashboard"
                    class="p-3.5 bg-emerald-50/60 dark:bg-emerald-950/30 hover:bg-emerald-50 dark:hover:bg-emerald-950/50 border border-emerald-200/70 dark:border-emerald-900/60 hover:border-emerald-400 dark:hover:border-emerald-700 rounded-2xl shadow-2xs hover:shadow-sm transition-all flex items-center gap-3 group active:scale-[0.98]"
                >
                    <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform shadow-xs shadow-emerald-500/30">
                        <ShieldCheck class="w-5 h-5" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-emerald-950 dark:text-emerald-200 truncate">Ruang Pengawas</p>
                        <p class="text-[11px] text-emerald-700 dark:text-emerald-400 truncate">Buka kunci layar siswa</p>
                    </div>
                </Link>
            </div>

            <!-- Two-Column Workspace Layout (Both Cards Rounded-2xl) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Settings Form (2 cols) -->
                <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 p-6 shadow-xs">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800 mb-5">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-400 flex items-center justify-center">
                                <Settings class="w-4 h-4" />
                            </div>
                            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-slate-100">
                                Identitas Sekolah & Konfigurasi Sesi
                            </h2>
                        </div>
                        <span class="text-xs text-blue-700 dark:text-blue-300 font-mono font-bold bg-blue-50 dark:bg-blue-950/50 border border-blue-200/80 dark:border-blue-800 px-2.5 py-1 rounded-lg">NPSN: {{ school?.npsn }}</span>
                    </div>

                    <form @submit.prevent="saveSettings" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nama Resmi Sekolah</label>
                                <input 
                                    v-model="form.school_name" 
                                    type="text" 
                                    class="w-full text-xs px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-xl focus:border-[#0F52BA] dark:focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 focus:outline-none transition-all" 
                                    required 
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">NPSN</label>
                                <input 
                                    v-model="form.npsn" 
                                    type="text" 
                                    class="w-full text-xs px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-xl focus:border-[#0F52BA] dark:focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 focus:outline-none font-mono transition-all" 
                                    required 
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nama Kepala Sekolah</label>
                                <input 
                                    v-model="form.headmaster_name" 
                                    type="text" 
                                    class="w-full text-xs px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-xl focus:border-[#0F52BA] dark:focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 focus:outline-none transition-all" 
                                    required 
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">NIP Kepala Sekolah</label>
                                <input 
                                    v-model="form.headmaster_nip" 
                                    type="text" 
                                    class="w-full text-xs px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-xl focus:border-[#0F52BA] dark:focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 focus:outline-none font-mono transition-all" 
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tahun Ajaran Aktif</label>
                                <input 
                                    v-model="form.active_academic_year" 
                                    type="text" 
                                    placeholder="2026/2027" 
                                    class="w-full text-xs px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-xl focus:border-[#0F52BA] dark:focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 focus:outline-none font-mono transition-all" 
                                    required 
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Semester Aktif</label>
                                <select 
                                    v-model="form.active_semester" 
                                    class="w-full text-xs px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-xl focus:border-[#0F52BA] dark:focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 focus:outline-none font-semibold transition-all" 
                                    required
                                >
                                    <option value="Ganjil">Semester Ganjil</option>
                                    <option value="Genap">Semester Genap</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Warna Aksen Primer</label>
                                <div class="flex items-center gap-3">
                                    <input 
                                        v-model="form.primary_color" 
                                        type="color" 
                                        class="w-10 h-10 p-0.5 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-xl cursor-pointer" 
                                    />
                                    <input 
                                        v-model="form.primary_color" 
                                        type="text" 
                                        class="w-36 text-xs px-3.5 py-2.5 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-xl font-mono focus:border-[#0F52BA] dark:focus:border-blue-500 focus:outline-none" 
                                    />
                                    <span class="text-xs text-slate-500 dark:text-slate-400">Warna identitas kop kartu & antarmuka</span>
                                </div>
                            </div>
                        </div>

                        <!-- Safe Exam Browser (SEB) Section (Rounded-xl) -->
                        <div class="pt-4 border-t border-slate-200 dark:border-slate-800">
                            <div class="p-4.5 bg-slate-50/80 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700/80 rounded-xl space-y-3">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-950/50 text-[#0F52BA] dark:text-blue-400 flex items-center justify-center shrink-0 mt-0.5">
                                        <ShieldCheck class="w-4.5 h-4.5" />
                                    </div>
                                    <div class="flex-1">
                                        <label class="flex items-center gap-2 text-xs font-bold text-slate-900 dark:text-slate-100 cursor-pointer">
                                            <input 
                                                v-model="form.seb_required" 
                                                type="checkbox" 
                                                class="rounded border-slate-300 dark:border-slate-600 text-[#0F52BA] focus:ring-0 bg-white dark:bg-slate-800" 
                                            />
                                            <span>Wajibkan Safe Exam Browser (SEB) & Exam Browser Kiosk</span>
                                        </label>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                                            Bila diaktifkan, siswa tidak dapat mengerjakan tes lewat browser umum (Chrome/Edge biasa) dan wajib melalui aplikasi SEB atau APK Kiosk khusus.
                                        </p>
                                    </div>
                                </div>

                                <div v-if="form.seb_required" class="pt-3 border-t border-slate-200 dark:border-slate-700">
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Hash Kunci Konfigurasi SEB (Opsional)</label>
                                    <input 
                                        v-model="form.seb_key_hash" 
                                        type="text" 
                                        placeholder="Masukkan 64-karakter Browser Exam Key SHA256..." 
                                        class="w-full text-xs px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl font-mono focus:border-[#0F52BA] dark:focus:border-blue-500 focus:outline-none" 
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end pt-2">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-5 py-2.5 bg-gradient-to-r from-[#0F52BA] to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white text-xs font-semibold rounded-xl shadow-xs hover:shadow transition-all disabled:opacity-50 flex items-center gap-1.5 cursor-pointer"
                            >
                                <CheckCircle2 v-if="!form.processing" class="w-3.5 h-3.5" />
                                <span>{{ form.processing ? 'Menyimpan...' : 'Simpan Pengaturan Sekolah' }}</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Recent Sesi Ujian List (1 col, Rounded-2xl Card) -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 p-6 shadow-xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-3.5 border-b border-slate-100 dark:border-slate-800 mb-4">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">
                                    <Activity class="w-4 h-4" />
                                </div>
                                <div>
                                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-slate-100">
                                        Sesi Ujian Aktif
                                    </h2>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Live monitoring token & ruangan</p>
                                </div>
                            </div>
                            <Link href="/admin/schedules" class="text-[11px] font-semibold text-[#0F52BA] dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 bg-blue-50 dark:bg-blue-950/50 px-2.5 py-1 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors">
                                Semua &rarr;
                            </Link>
                        </div>

                        <div v-if="recentSessions.length === 0" class="text-center py-10 text-xs text-slate-400 dark:text-slate-500 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-dashed border-slate-200 dark:border-slate-800">
                            Belum ada sesi ujian yang dijadwalkan.
                        </div>

                        <div v-else class="space-y-3 overflow-y-auto max-h-[440px] pr-0.5">
                            <div
                                v-for="sess in recentSessions"
                                :key="sess.id"
                                class="p-3.5 bg-gradient-to-br from-white to-slate-50/80 dark:from-slate-800/90 dark:to-slate-800/40 border border-slate-200/90 dark:border-slate-800 hover:border-blue-300 dark:hover:border-blue-700 rounded-xl flex flex-col gap-2.5 transition-all shadow-2xs group"
                            >
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 mb-0.5">
                                            <span class="w-2 h-2 rounded-full shrink-0" :class="sess.status === 'active' ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400'"></span>
                                            <p class="text-xs font-bold text-slate-900 dark:text-slate-100 truncate group-hover:text-[#0F52BA] dark:group-hover:text-blue-400 transition-colors">
                                                {{ sess.exam_schedule?.subject?.name }}
                                            </p>
                                        </div>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-1.5 pl-4">
                                            <span class="font-medium text-slate-700 dark:text-slate-300">{{ sess.session_name }}</span>
                                            <span class="text-slate-300 dark:text-slate-600">&bull;</span>
                                            <span class="text-slate-600 dark:text-slate-400">{{ sess.room_name }}</span>
                                        </p>
                                    </div>

                                    <span
                                        :class="[
                                            sess.status === 'active' 
                                                ? 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800 ring-1 ring-emerald-500/10' 
                                                : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700',
                                            'px-2 py-0.5 text-[10px] font-bold rounded-full border shrink-0 uppercase tracking-wide'
                                        ]"
                                    >
                                        {{ sess.status }}
                                    </span>
                                </div>

                                <!-- Token Chip with 1-click Copy -->
                                <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-slate-800 text-xs bg-slate-50/70 dark:bg-slate-800/80 -mx-3.5 -mb-3.5 px-3 py-2 rounded-b-xl">
                                    <div class="flex items-center gap-1.5 font-mono">
                                        <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500">Token:</span>
                                        <span class="bg-amber-100/90 dark:bg-amber-950/60 text-amber-900 dark:text-amber-200 border border-amber-300/80 dark:border-amber-800/80 px-2 py-0.5 rounded-md font-bold tracking-widest text-xs select-all">
                                            {{ sess.access_token }}
                                        </span>
                                    </div>

                                    <button
                                        @click="copyToken(sess.id, sess.access_token)"
                                        type="button"
                                        class="px-2.5 py-1 text-[11px] font-medium text-slate-600 dark:text-slate-300 hover:text-emerald-700 dark:hover:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/50 rounded-lg border border-slate-200 dark:border-slate-700 hover:border-emerald-200 dark:hover:border-emerald-800 transition-all flex items-center gap-1 bg-white dark:bg-slate-800 cursor-pointer"
                                    >
                                        <Check v-if="copiedTokenId === sess.id" class="w-3.5 h-3.5 text-emerald-600" />
                                        <Copy v-else class="w-3.5 h-3.5" />
                                        <span>{{ copiedTokenId === sess.id ? 'Tersalin!' : 'Salin Token' }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 dark:border-slate-800 mt-4">
                        <Link
                            href="/proctor/dashboard"
                            class="w-full py-2.5 bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 hover:from-slate-800 hover:to-indigo-900 text-white text-xs font-semibold rounded-xl flex items-center justify-center gap-2 shadow-xs hover:shadow transition-all"
                        >
                            <div class="w-5 h-5 rounded-full bg-emerald-500/20 flex items-center justify-center">
                                <ShieldCheck class="w-3.5 h-3.5 text-emerald-400" />
                            </div>
                            <span>Buka Ruang Pengawas (Live Monitor)</span>
                            <ArrowRight class="w-3.5 h-3.5 text-slate-400 ml-1" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
