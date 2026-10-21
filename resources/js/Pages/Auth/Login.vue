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
} from 'lucide-vue-next';
import ThemeToggle from '@/Components/ThemeToggle.vue';

const props = defineProps({
    school: Object,
    demoAccounts: Array,
});

const showPassword = ref(false);

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

const fillDemo = (acc) => {
    form.username = acc.username;
    form.password = acc.password;
    submit();
};
</script>

<template>
    <div class="min-h-screen w-full flex flex-col justify-between items-center bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 px-4 py-6 sm:py-8 font-sans transition-colors duration-150">
        <Head :title="`Masuk Sistem - ${school?.school_name || 'SmartCBT School Edition'}`" />

        <!-- Top Bar with Theme Toggle -->
        <header class="w-full max-w-md flex items-center justify-between">
            <span class="text-xs font-semibold tracking-wider uppercase text-slate-500 dark:text-slate-400">
                Portal Ujian Sekolah
            </span>
            <ThemeToggle />
        </header>

        <!-- Main Auth Card -->
        <main class="w-full max-w-md my-auto py-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden">
                <!-- Card Header -->
                <div class="p-6 sm:p-8 pb-0 text-center">
                    <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                        {{ school?.school_name || 'SmartCBT School Edition' }}
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                        Sistem Ujian Berbasis Komputer & Evaluasi Akademik
                    </p>
                    <div class="inline-flex items-center gap-1.5 mt-3 px-2.5 py-1 rounded-full text-[11px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700/80">
                        <span>TA {{ school?.active_academic_year || '2026/2027' }}</span>
                        <span>&bull;</span>
                        <span>Semester {{ school?.active_semester || 'Ganjil' }}</span>
                    </div>
                </div>

                <!-- SEB Warning Banner (if enabled) -->
                <div
                    v-if="school?.seb_required"
                    class="mx-6 sm:mx-8 mt-5 p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/70 flex items-start gap-2.5 text-xs text-amber-900 dark:text-amber-200"
                >
                    <ShieldAlert class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" />
                    <div>
                        <strong class="font-semibold">Mode SEB Aktif:</strong> Ujian ini mewajibkan Safe Exam Browser.
                    </div>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="p-6 sm:p-8 space-y-4">
                    <!-- Username / NISN -->
                    <div>
                        <label for="username" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            NISN / Username / NIP
                        </label>
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
                                class="w-full pl-10 pr-3.5 py-2.5 text-sm rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:bg-white dark:focus:bg-slate-800 focus:border-blue-600 dark:focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all outline-none"
                            />
                        </div>
                        <p v-if="form.errors.username" class="text-xs text-rose-600 dark:text-rose-400 font-medium mt-1.5">
                            {{ form.errors.username }}
                        </p>
                    </div>

                    <!-- Password with Show/Hide Toggle -->
                    <div>
                        <label for="password" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Kata Sandi
                        </label>
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
                                class="w-full pl-10 pr-10 py-2.5 text-sm rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:bg-white dark:focus:bg-slate-800 focus:border-blue-600 dark:focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all outline-none"
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

                    <!-- Remember & Proctor Help -->
                    <div class="flex items-center justify-between text-xs pt-1">
                        <label class="flex items-center gap-2 cursor-pointer select-none text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200">
                            <input
                                v-model="form.remember"
                                type="checkbox"
                                class="w-4 h-4 rounded border-slate-300 dark:border-slate-600 text-blue-600 focus:ring-blue-500/20 dark:bg-slate-800 cursor-pointer"
                            />
                            <span>Ingat sesi masuk</span>
                        </label>
                        <span class="text-slate-500 dark:text-slate-400">
                            Kendala akun? <span class="text-blue-600 dark:text-blue-400 font-medium">Hubungi Proktor</span>
                        </span>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-semibold text-sm rounded-xl shadow-xs transition-all duration-150 flex items-center justify-center gap-2 cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed"
                        >
                            <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
                            <span>{{ form.processing ? 'Memverifikasi...' : 'Masuk ke Sistem' }}</span>
                        </button>
                    </div>
                </form>

                <!-- Quick Demo Login (Minimalist, without excessive icons) -->
                <div class="px-6 sm:px-8 py-4.5 bg-slate-50/70 dark:bg-slate-900/60 border-t border-slate-100 dark:border-slate-800">
                    <div class="flex items-center justify-between mb-2.5">
                        <span class="text-[11px] font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                            Akses Cepat Pengujian (1-Klik)
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <button
                            v-for="acc in demoAccounts"
                            :key="acc.role"
                            @click="fillDemo(acc)"
                            type="button"
                            class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-700/80 bg-white dark:bg-slate-800/80 hover:border-blue-500 dark:hover:border-blue-400 text-left transition-colors cursor-pointer"
                        >
                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200">
                                {{ acc.label }}
                            </div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400 font-mono mt-0.5">
                                {{ acc.username }}
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </main>

        <!-- Page Footer -->
        <footer class="w-full max-w-md text-center py-2 text-xs text-slate-400 dark:text-slate-500">
            SmartCBT School Edition &bull; High-Craft Architecture
        </footer>
    </div>
</template>
