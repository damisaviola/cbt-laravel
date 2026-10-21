<script setup>
import { ref } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import {
    User,
    Lock,
    Eye,
    EyeOff,
    Loader2,
    ShieldAlert,
    School,
    CheckCircle2,
    ShieldCheck,
    HelpCircle,
    ArrowRight,
    Sparkles,
} from 'lucide-vue-next';
import ThemeToggle from '@/Components/ThemeToggle.vue';

const props = defineProps({
    school: Object,
    demoAccounts: Array,
});

const showPassword = ref(false);
const showDemoPanel = ref(false);

const form = useForm({
    username: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};

const selectDemo = (acc, autoSubmit = false) => {
    form.username = acc.username;
    form.password = acc.password;
    if (autoSubmit) {
        submit();
    }
};
</script>

<template>
    <div class="min-h-screen w-full flex flex-col justify-center items-center bg-slate-100/70 dark:bg-slate-950 text-slate-900 dark:text-slate-100 p-4 sm:p-6 lg:p-8 font-sans antialiased transition-colors duration-150">
        <Head :title="`Masuk Sistem - ${school?.school_name || 'Portal Ujian Sekolah'}`" />

        <!-- Center Container -->
        <div class="w-full max-w-4xl">
            <!-- Top Bar: Portal Label & Theme Toggle -->
            <div class="flex items-center justify-between mb-4 px-2">
                <div class="flex items-center gap-2">
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-xs font-medium text-slate-600 dark:text-slate-400">
                        Server CBT Siap &bull; Sesi Aktif
                    </span>
                </div>
                <div class="flex items-center gap-3">
                    <ThemeToggle />
                </div>
            </div>

            <!-- Elevated Split Card -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xl shadow-slate-200/50 dark:shadow-none overflow-hidden grid grid-cols-1 lg:grid-cols-12 transition-all">
                
                <!-- Left Panel: Academic Identity & Context (5 cols on lg) -->
                <div class="lg:col-span-5 bg-gradient-to-br from-slate-900 via-slate-850 to-blue-950 text-white p-6 sm:p-8 flex flex-col justify-between relative overflow-hidden">
                    <!-- Subtle background decoration -->
                    <div class="absolute -right-12 -top-12 w-48 h-48 rounded-full bg-blue-500/10 blur-2xl pointer-events-none"></div>
                    <div class="absolute -left-12 -bottom-12 w-48 h-48 rounded-full bg-indigo-500/10 blur-2xl pointer-events-none"></div>

                    <!-- Top School Identity -->
                    <div class="relative z-10">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-500/20 mb-5">
                            <School class="w-6 h-6" />
                        </div>

                        <span class="text-[11px] font-semibold tracking-wider uppercase text-blue-300 block mb-1">
                            Portal Ujian Berbasis Komputer
                        </span>

                        <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-white leading-snug">
                            {{ school?.school_name || 'SmartCBT School Edition' }}
                        </h1>

                        <div class="mt-4 flex flex-wrap items-center gap-2 text-xs">
                            <span v-if="school?.npsn" class="px-2.5 py-1 rounded-lg bg-white/10 text-slate-200 border border-white/10 font-mono">
                                NPSN: {{ school.npsn }}
                            </span>
                            <span class="px-2.5 py-1 rounded-lg bg-white/10 text-slate-200 border border-white/10">
                                TA {{ school?.active_academic_year || '2026/2027' }} ({{ school?.active_semester || 'Ganjil' }})
                            </span>
                        </div>
                    </div>

                    <!-- Middle: Exam Integrity Note -->
                    <div class="relative z-10 my-8 space-y-3.5 text-xs text-slate-300">
                        <div class="p-3.5 rounded-2xl bg-white/5 border border-white/10 space-y-2">
                            <div class="flex items-center gap-2 text-blue-300 font-semibold text-xs">
                                <ShieldCheck class="w-4 h-4 text-blue-400 shrink-0" />
                                <span>Petunjuk Masuk Peserta</span>
                            </div>
                            <p class="text-slate-300/90 leading-relaxed text-[11px]">
                                Masukkan <strong>NISN</strong> dan kata sandi yang tertera pada Kartu Ujian Anda. Pastikan nama ruang dan sesi telah sesuai jadwal.
                            </p>
                        </div>

                        <div class="flex items-center gap-2 text-[11px] text-slate-400">
                            <CheckCircle2 class="w-3.5 h-3.5 text-emerald-400 shrink-0" />
                            <span>Sistem dilengkapi perekam integritas & anti-kecurangan</span>
                        </div>
                    </div>

                    <!-- Bottom School Info -->
                    <div class="relative z-10 pt-4 border-t border-white/10 text-[11px] text-slate-400 flex items-center justify-between">
                        <span>Kepala Sekolah:</span>
                        <span class="text-slate-200 font-medium truncate max-w-[180px]">
                            {{ school?.headmaster_name || 'Pimpinan Sekolah' }}
                        </span>
                    </div>
                </div>

                <!-- Right Panel: Clean Authentication Form (7 cols on lg) -->
                <div class="lg:col-span-7 p-6 sm:p-8 lg:p-10 flex flex-col justify-between">
                    <div>
                        <!-- Header -->
                        <div class="mb-6">
                            <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                                Selamat Datang
                            </h2>
                            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                                Silakan masuk menggunakan akun resmi sekolah Anda.
                            </p>
                        </div>

                        <!-- SEB Warning Banner (if enabled) -->
                        <div
                            v-if="school?.seb_required"
                            class="mb-5 p-3.5 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/70 flex items-start gap-2.5 text-xs text-amber-900 dark:text-amber-200"
                        >
                            <ShieldAlert class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" />
                            <div>
                                <strong class="font-semibold">Mode SEB Wajib:</strong> Ujian ini memerlukan Safe Exam Browser. Pastikan Anda membuka melalui file konfigurasi SEB.
                            </div>
                        </div>

                        <!-- Form -->
                        <form @submit.prevent="submit" class="space-y-4">
                            <!-- Username / NISN -->
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label for="username" class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                                        NISN / Username / NIP
                                    </label>
                                    <span class="text-[11px] text-slate-400 dark:text-slate-500">
                                        Siswa gunakan NISN
                                    </span>
                                </div>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                                        <User class="w-4 h-4" />
                                    </div>
                                    <input
                                        id="username"
                                        v-model="form.username"
                                        type="text"
                                        autocomplete="username"
                                        placeholder="Contoh: 2026001 atau guru"
                                        required
                                        autofocus
                                        class="w-full pl-10 pr-3.5 py-2.5 text-sm rounded-xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:bg-white dark:focus:bg-slate-800 focus:border-blue-600 dark:focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all outline-none"
                                    />
                                </div>
                                <p v-if="form.errors.username" class="text-xs text-rose-600 dark:text-rose-400 font-medium mt-1.5 flex items-center gap-1">
                                    <span>&bull;</span>
                                    <span>{{ form.errors.username }}</span>
                                </p>
                            </div>

                            <!-- Password -->
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label for="password" class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                                        Kata Sandi
                                    </label>
                                </div>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                                        <Lock class="w-4 h-4" />
                                    </div>
                                    <input
                                        id="password"
                                        v-model="form.password"
                                        :type="showPassword ? 'text' : 'password'"
                                        autocomplete="current-password"
                                        placeholder="Masukkan kata sandi..."
                                        required
                                        class="w-full pl-10 pr-10 py-2.5 text-sm rounded-xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:bg-white dark:focus:bg-slate-800 focus:border-blue-600 dark:focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all outline-none"
                                    />
                                    <button
                                        type="button"
                                        @click="showPassword = !showPassword"
                                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors cursor-pointer"
                                        :title="showPassword ? 'Sembunyikan sandi' : 'Tampilkan sandi'"
                                        aria-label="Toggle password visibility"
                                    >
                                        <EyeOff v-if="showPassword" class="w-4 h-4" />
                                        <Eye v-else class="w-4 h-4" />
                                    </button>
                                </div>
                                <p v-if="form.errors.password" class="text-xs text-rose-600 dark:text-rose-400 font-medium mt-1.5 flex items-center gap-1">
                                    <span>&bull;</span>
                                    <span>{{ form.errors.password }}</span>
                                </p>
                            </div>

                            <!-- Options: Remember Me & Proctor Support -->
                            <div class="flex items-center justify-between text-xs pt-1">
                                <label class="flex items-center gap-2 cursor-pointer select-none text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200">
                                    <input
                                        v-model="form.remember"
                                        type="checkbox"
                                        class="w-4 h-4 rounded border-slate-300 dark:border-slate-600 text-blue-600 focus:ring-blue-500/20 dark:bg-slate-800 cursor-pointer"
                                    />
                                    <span>Ingat perangkat ini</span>
                                </label>
                                <span class="text-slate-500 dark:text-slate-400 text-[11px]">
                                    Lupa sandi? <span class="text-blue-600 dark:text-blue-400 font-semibold">Hubungi Proktor Ruang</span>
                                </span>
                            </div>

                            <!-- Submit Button -->
                            <div class="pt-2">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-semibold text-sm rounded-xl shadow-md shadow-blue-600/20 hover:shadow-lg hover:shadow-blue-600/25 transition-all duration-150 flex items-center justify-center gap-2 cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed"
                                >
                                    <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
                                    <span v-if="form.processing">Memverifikasi Akun...</span>
                                    <template v-else>
                                        <span>Masuk ke Sistem</span>
                                        <ArrowRight class="w-4 h-4" />
                                    </template>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Clean, Unobtrusive Quick-Fill Selector (No Bloat / No AI Slop) -->
                    <div v-if="demoAccounts && demoAccounts.length" class="mt-6 pt-5 border-t border-slate-100 dark:border-slate-800">
                        <div class="flex items-center justify-between mb-2.5">
                            <span class="text-[11px] font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                                Uji Coba Cepat (Khusus Mode Demo)
                            </span>
                            <button
                                type="button"
                                @click="showDemoPanel = !showDemoPanel"
                                class="text-[11px] font-medium text-blue-600 dark:text-blue-400 hover:underline cursor-pointer"
                            >
                                {{ showDemoPanel ? 'Sembunyikan' : 'Tampilkan Pilihan' }}
                            </button>
                        </div>

                        <!-- Segmented Pill Selector for Demo Accounts -->
                        <div v-show="showDemoPanel" class="grid grid-cols-2 sm:grid-cols-4 gap-2 transition-all">
                            <button
                                v-for="acc in demoAccounts"
                                :key="acc.role"
                                @click="selectDemo(acc, false)"
                                type="button"
                                class="px-2.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700/80 bg-slate-50 dark:bg-slate-800/60 hover:bg-white dark:hover:bg-slate-800 hover:border-blue-500 dark:hover:border-blue-400 text-left transition-all cursor-pointer group"
                            >
                                <div class="text-[11px] font-semibold text-slate-800 dark:text-slate-200 truncate group-hover:text-blue-600 dark:group-hover:text-blue-400">
                                    {{ acc.label.split(' ')[0] }}
                                </div>
                                <div class="text-[10px] text-slate-400 dark:text-slate-500 font-mono truncate">
                                    {{ acc.username }}
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer: Academic & Professional -->
            <footer class="mt-4 px-2 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-400 dark:text-slate-500">
                <p>&copy; 2026 {{ school?.school_name || 'SmartCBT' }}. Hak Cipta Dilindungi.</p>
                <p class="flex items-center gap-1.5">
                    <span>Sistem Evaluasi Terpadu Berbasis Web</span>
                </p>
            </footer>
        </div>
    </div>
</template>
