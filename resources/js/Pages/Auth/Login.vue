<script setup>
import { ref, computed } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import { Eye, EyeOff, Loader2, AlertCircle } from 'lucide-vue-next';
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

const schoolInitials = computed(() => {
    if (!props.school?.school_name) return 'CBT';
    return props.school.school_name
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map(w => w[0])
        .join('')
        .toUpperCase();
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
            <!-- Top Bar: Status & Theme Toggle -->
            <div class="flex items-center justify-between mb-4 px-2">
                <div class="flex items-center gap-2">
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span class="text-xs font-medium text-slate-600 dark:text-slate-400">
                        Server CBT Siap &bull; Sesi Aktif
                    </span>
                </div>
                <div class="flex items-center gap-3">
                    <ThemeToggle />
                </div>
            </div>

            <!-- Elevated Split Card -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xl shadow-slate-200/40 dark:shadow-none overflow-hidden grid grid-cols-1 lg:grid-cols-12 transition-all">
                
                <!-- Left Panel: Institutional Brand & Academic Info (5 cols on lg) -->
                <div class="lg:col-span-5 bg-[#0e3725] text-white p-7 sm:p-9 flex flex-col justify-between relative">
                    <!-- School Identity -->
                    <div>
                        <div class="mb-5">
                            <img
                                v-if="school?.logo_path"
                                :src="'/storage/' + school.logo_path"
                                :alt="school?.school_name || 'Logo Sekolah'"
                                class="w-12 h-12 rounded-xl object-contain bg-white/10 p-1"
                            />
                            <div
                                v-else
                                class="w-11 h-11 rounded-full bg-[#185338] text-white font-bold text-base flex items-center justify-center tracking-tight shadow-sm select-none border border-emerald-500/30"
                            >
                                {{ schoolInitials }}
                            </div>
                        </div>

                        <span class="text-[11px] font-semibold tracking-wider uppercase text-emerald-300 block mb-1">
                            Portal Ujian Berbasis Komputer
                        </span>

                        <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-white leading-snug">
                            {{ school?.school_name || 'SmartCBT School Edition' }}
                        </h1>

                        <div class="mt-3 flex flex-wrap items-center gap-x-2.5 gap-y-1 text-xs text-slate-300">
                            <span v-if="school?.npsn" class="font-mono text-slate-300">
                                NPSN {{ school.npsn }}
                            </span>
                            <span v-if="school?.npsn" class="text-slate-600">&bull;</span>
                            <span class="text-slate-300">
                                TA {{ school?.active_academic_year || '2026/2027' }} ({{ school?.active_semester || 'Ganjil' }})
                            </span>
                        </div>
                    </div>

                    <!-- Middle: Clean Institutional Guidelines (Minimal & Typography-Driven) -->
                    <div class="my-8 space-y-4">
                        <div class="border-l-2 border-emerald-400 pl-3.5 py-0.5">
                            <p class="text-xs font-semibold text-emerald-200 uppercase tracking-wider mb-0.5">
                                Peserta Didik
                            </p>
                            <p class="text-xs text-slate-300 leading-relaxed">
                                Gunakan NISN dan kata sandi pada Kartu Peserta. Pastikan ruang dan sesi ujian sesuai jadwal.
                            </p>
                        </div>

                        <div class="border-l-2 border-slate-700 pl-3.5 py-0.5">
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-0.5">
                                Guru & Pengawas
                            </p>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Masuk menggunakan NIP atau username terdaftar untuk ruang kendali proktor dan penilaian.
                            </p>
                        </div>
                    </div>

                    <!-- Bottom School Info -->
                    <div class="pt-4 border-t border-slate-800 text-xs text-slate-400 flex items-center justify-between">
                        <span>Kepala Sekolah:</span>
                        <span class="text-slate-200 font-medium truncate max-w-[190px]">
                            {{ school?.headmaster_name || 'Pimpinan Sekolah' }}
                        </span>
                    </div>
                </div>

                <!-- Right Panel: Clean, Focused Authentication Form (7 cols on lg) -->
                <div class="lg:col-span-7 p-7 sm:p-9 lg:p-11 flex flex-col justify-between">
                    <div>
                        <!-- Header -->
                        <div class="mb-6">
                            <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                                Masuk Akun
                            </h2>
                            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                                Silakan masukkan kredensial resmi Anda untuk melanjutkan.
                            </p>
                        </div>

                        <!-- SEB Warning Banner (Only when required) -->
                        <div
                            v-if="school?.seb_required"
                            class="mb-5 p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/70 flex items-start gap-2.5 text-xs text-amber-900 dark:text-amber-200"
                        >
                            <AlertCircle class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" />
                            <div>
                                <strong class="font-semibold">Mode SEB Wajib:</strong> Ujian ini memerlukan Safe Exam Browser. Pastikan Anda membuka melalui aplikasi SEB terkonfigurasi.
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
                                <input
                                    id="username"
                                    v-model="form.username"
                                    type="text"
                                    autocomplete="username"
                                    placeholder="Contoh: 2026001 atau guru"
                                    required
                                    autofocus
                                    class="w-full px-4 py-2.5 text-sm rounded-full bg-slate-50 dark:bg-slate-800/70 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:bg-white dark:focus:bg-slate-800 focus:border-[#185338] dark:focus:border-emerald-500 focus:ring-3 focus:ring-[#185338]/15 transition-all outline-none"
                                />
                                <p v-if="form.errors.username" class="text-xs text-rose-600 dark:text-rose-400 font-medium mt-1.5">
                                    {{ form.errors.username }}
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
                                    <input
                                        id="password"
                                        v-model="form.password"
                                        :type="showPassword ? 'text' : 'password'"
                                        autocomplete="current-password"
                                        placeholder="Masukkan kata sandi..."
                                        required
                                        class="w-full pl-4 pr-10 py-2.5 text-sm rounded-full bg-slate-50 dark:bg-slate-800/70 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:bg-white dark:focus:bg-slate-800 focus:border-[#185338] dark:focus:border-emerald-500 focus:ring-3 focus:ring-[#185338]/15 transition-all outline-none"
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
                                <p v-if="form.errors.password" class="text-xs text-rose-600 dark:text-rose-400 font-medium mt-1.5">
                                    {{ form.errors.password }}
                                </p>
                            </div>

                            <!-- Options: Remember Me & Proctor Support -->
                            <div class="flex items-center justify-between text-xs pt-0.5">
                                <label class="flex items-center gap-2 cursor-pointer select-none text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200">
                                    <input
                                        v-model="form.remember"
                                        type="checkbox"
                                        class="w-4 h-4 rounded border-slate-300 dark:border-slate-600 text-[#185338] focus:ring-[#185338]/20 dark:bg-slate-800 cursor-pointer"
                                    />
                                    <span>Ingat perangkat ini</span>
                                </label>
                                <span class="text-slate-500 dark:text-slate-400 text-[11px]">
                                    Lupa sandi? <span class="text-[#185338] dark:text-emerald-400 font-semibold">Hubungi Proktor</span>
                                </span>
                            </div>

                            <!-- Submit Button -->
                            <div class="pt-2">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="w-full py-2.5 px-4 bg-[#185338] hover:bg-[#12422c] active:bg-[#0c2e1f] text-white font-semibold text-sm rounded-full shadow-xs transition-colors duration-150 flex items-center justify-center gap-2 cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed"
                                >
                                    <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
                                    <span>{{ form.processing ? 'Memverifikasi...' : 'Masuk ke Sistem' }}</span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Clean Quick-Fill Selector (Demo Mode) -->
                    <div v-if="demoAccounts && demoAccounts.length" class="mt-6 pt-5 border-t border-slate-100 dark:border-slate-800">
                        <div class="flex items-center justify-between mb-2.5">
                            <span class="text-[11px] font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                                Uji Coba Cepat (Mode Demo)
                            </span>
                            <button
                                type="button"
                                @click="showDemoPanel = !showDemoPanel"
                                class="text-[11px] font-medium text-[#185338] dark:text-emerald-400 hover:underline cursor-pointer"
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
                                class="px-2.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700/80 bg-slate-50 dark:bg-slate-800/60 hover:bg-white dark:hover:bg-slate-800 hover:border-[#185338] dark:hover:border-emerald-500 text-left transition-all cursor-pointer group"
                            >
                                <div class="text-[11px] font-semibold text-slate-800 dark:text-slate-200 truncate group-hover:text-[#185338] dark:group-hover:text-emerald-400">
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

            <!-- Footer: Clean Academic Copyright -->
            <footer class="mt-4 px-2 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-400 dark:text-slate-500">
                <p>&copy; 2026 {{ school?.school_name || 'SmartCBT' }}. Hak Cipta Dilindungi.</p>
                <p>Sistem Evaluasi Terpadu Berbasis Web</p>
            </footer>
        </div>
    </div>
</template>
