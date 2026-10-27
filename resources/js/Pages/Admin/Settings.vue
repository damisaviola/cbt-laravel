<script setup>
import { ref, computed } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import BentoStatCard from '@/Components/BentoStatCard.vue';
import { 
    School, 
    Calendar, 
    ShieldCheck, 
    Server, 
    Palette, 
    CheckCircle2, 
    RotateCcw, 
    AlertCircle, 
    GraduationCap, 
    Cpu, 
    Lock, 
    Eye,
    Save
} from 'lucide-vue-next';

const props = defineProps({
    school: Object,
    serverInfo: Object,
});

const form = useForm({
    school_name: props.school?.school_name || '',
    npsn: props.school?.npsn || '',
    headmaster_name: props.school?.headmaster_name || '',
    headmaster_nip: props.school?.headmaster_nip || '',
    active_academic_year: props.school?.active_academic_year || '2026/2027',
    active_semester: props.school?.active_semester || 'Ganjil',
    primary_color: props.school?.primary_color || '#185338',
    seb_required: Boolean(props.school?.seb_required),
    seb_key_hash: props.school?.seb_key_hash || '',
});

const colorPresets = [
    { name: 'Forest Green (Modern)', hex: '#185338' },
    { name: 'Emerald Green', hex: '#059669' },
    { name: 'Royal Blue (EduPro)', hex: '#0F52BA' },
    { name: 'Indigo Blue', hex: '#2563EB' },
    { name: 'Deep Purple', hex: '#7C3AED' },
    { name: 'Rose Red', hex: '#E11D48' },
];

const resetForm = () => {
    form.school_name = props.school?.school_name || '';
    form.npsn = props.school?.npsn || '';
    form.headmaster_name = props.school?.headmaster_name || '';
    form.headmaster_nip = props.school?.headmaster_nip || '';
    form.active_academic_year = props.school?.active_academic_year || '2026/2027';
    form.active_semester = props.school?.active_semester || 'Ganjil';
    form.primary_color = props.school?.primary_color || '#185338';
    form.seb_required = Boolean(props.school?.seb_required);
    form.seb_key_hash = props.school?.seb_key_hash || '';
};

const saveSettings = () => {
    form.post('/admin/settings', {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Pengaturan Sistem & Sekolah" />

        <template #breadcrumbs>
            <nav class="flex items-center gap-2 text-sm text-slate-400 dark:text-slate-500 font-medium select-none">
                <span class="hover:text-slate-600 dark:hover:text-slate-300 transition-colors">Home</span>
                <span class="text-slate-400">&gt;</span>
                <span class="text-slate-900 dark:text-white font-semibold">Settings</span>
            </nav>
        </template>

        <div class="space-y-6">
            <!-- Page Header matching contoh.jpg (Title on Left, Actions on Right) -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                        Settings & Preferences
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        Kelola identitas resmi sekolah, tahun akademik, branding antarmuka, dan keamanan ujian.
                    </p>
                </div>

                <div class="flex items-center gap-3 shrink-0">
                    <!-- Reset Button -->
                    <button
                        type="button"
                        @click="resetForm"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800 rounded-full text-xs font-semibold text-slate-700 dark:text-slate-200 shadow-2xs transition-colors cursor-pointer disabled:opacity-50"
                    >
                        <RotateCcw class="w-4 h-4 text-slate-500" />
                        <span>Reset Form</span>
                    </button>

                    <!-- Save Changes Primary Pill Button matching contoh.webp -->
                    <button
                        type="button"
                        @click="saveSettings"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#185338] hover:bg-[#12422c] text-white rounded-full text-xs font-bold shadow-xs transition-all cursor-pointer disabled:opacity-50"
                    >
                        <CheckCircle2 v-if="!form.processing" class="w-4 h-4" />
                        <span v-if="form.processing">Menyimpan...</span>
                        <span v-else>Simpan Pengaturan</span>
                    </button>
                </div>
            </div>

            <!-- 4 Bento Stat Cards Grid matching contoh.jpg -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Card 1: NPSN Sekolah (Pastel Blue) -->
                <BentoStatCard
                    title="NPSN Sekolah"
                    :value="form.npsn || 'Belum Diatur'"
                    trend="Terdaftar"
                    trend-type="up"
                    icon-bg="bg-blue-50 dark:bg-blue-950/60"
                    icon-color="text-blue-600 dark:text-blue-400"
                >
                    <template #icon>
                        <School class="w-5 h-5" />
                    </template>
                </BentoStatCard>

                <!-- Card 2: Kalender Akademik (Pastel Amber) -->
                <BentoStatCard
                    title="Semester Berjalan"
                    :value="form.active_semester"
                    :trend="form.active_academic_year"
                    trend-type="up"
                    icon-bg="bg-amber-50 dark:bg-amber-950/60"
                    icon-color="text-amber-600 dark:text-amber-400"
                >
                    <template #icon>
                        <Calendar class="w-5 h-5" />
                    </template>
                </BentoStatCard>

                <!-- Card 3: Keamanan SEB (Pastel Rose / Emerald) -->
                <BentoStatCard
                    title="Status Keamanan SEB"
                    :value="form.seb_required ? 'SEB Wajib' : 'Mode Bebas'"
                    :trend="form.seb_required ? 'Terkunci' : 'Standar'"
                    :trend-type="form.seb_required ? 'up' : 'neutral'"
                    :icon-bg="form.seb_required ? 'bg-rose-50 dark:bg-rose-950/60' : 'bg-slate-100 dark:bg-slate-800'"
                    :icon-color="form.seb_required ? 'text-rose-600 dark:text-rose-400' : 'text-slate-500'"
                >
                    <template #icon>
                        <ShieldCheck class="w-5 h-5" />
                    </template>
                </BentoStatCard>

                <!-- Card 4: Server Runtime (Pastel Sky) -->
                <BentoStatCard
                    title="Runtime Server CBT"
                    :value="`PHP ${serverInfo?.php_version || '8.5'}`"
                    trend="Optimal"
                    trend-type="up"
                    icon-bg="bg-sky-50 dark:bg-sky-950/60"
                    icon-color="text-sky-600 dark:text-sky-400"
                >
                    <template #icon>
                        <Server class="w-5 h-5" />
                    </template>
                </BentoStatCard>
            </div>

            <!-- Main Settings Form Grid (2 Columns: Form ~65%, Live Preview ~35%) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                
                <!-- Left: Master Settings Form (2 cols) -->
                <form @submit.prevent="saveSettings" class="lg:col-span-2 space-y-6">
                    
                    <!-- Section 1: Identitas Resmi Sekolah -->
                    <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-7 border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-5">
                        <div class="flex items-center gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
                            <div class="w-10 h-10 rounded-2xl bg-[#eaf4ef] dark:bg-emerald-950/60 text-[#185338] dark:text-emerald-400 flex items-center justify-center font-bold shrink-0">
                                <School class="w-5 h-5" />
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-slate-900 dark:text-white tracking-tight">
                                    Identitas Resmi Lembaga
                                </h2>
                                <p class="text-xs text-slate-400 dark:text-slate-500">
                                    Data ini dicetak otomatis pada KOP kartu ujian, lembar berita acara, dan daftar hadir.
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                    Nama Resmi Sekolah / Lembaga <span class="text-rose-500">*</span>
                                </label>
                                <input 
                                    v-model="form.school_name" 
                                    type="text" 
                                    placeholder="Contoh: SMA Negeri 1 Digital"
                                    class="w-full text-xs sm:text-sm px-4 py-2.5 border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800 text-slate-900 dark:text-white rounded-xl focus:border-[#185338] dark:focus:border-emerald-500 focus:bg-white dark:focus:bg-slate-850 focus:outline-none transition-all shadow-2xs" 
                                    required 
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                    NPSN (Nomor Pokok Sekolah Nasional) <span class="text-rose-500">*</span>
                                </label>
                                <input 
                                    v-model="form.npsn" 
                                    type="text" 
                                    placeholder="Contoh: 10203040"
                                    class="w-full text-xs sm:text-sm px-4 py-2.5 border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800 text-slate-900 dark:text-white rounded-xl font-mono focus:border-[#185338] dark:focus:border-emerald-500 focus:bg-white dark:focus:bg-slate-850 focus:outline-none transition-all shadow-2xs" 
                                    required 
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                    Nama Kepala Sekolah <span class="text-rose-500">*</span>
                                </label>
                                <input 
                                    v-model="form.headmaster_name" 
                                    type="text" 
                                    placeholder="Nama beserta gelar..."
                                    class="w-full text-xs sm:text-sm px-4 py-2.5 border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800 text-slate-900 dark:text-white rounded-xl focus:border-[#185338] dark:focus:border-emerald-500 focus:bg-white dark:focus:bg-slate-850 focus:outline-none transition-all shadow-2xs" 
                                    required 
                                />
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                    NIP Kepala Sekolah
                                </label>
                                <input 
                                    v-model="form.headmaster_nip" 
                                    type="text" 
                                    placeholder="19750512..."
                                    class="w-full text-xs sm:text-sm px-4 py-2.5 border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800 text-slate-900 dark:text-white rounded-xl font-mono focus:border-[#185338] dark:focus:border-emerald-500 focus:bg-white dark:focus:bg-slate-850 focus:outline-none transition-all shadow-2xs" 
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Kalender & Sesi Akademik -->
                    <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-7 border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-5">
                        <div class="flex items-center gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
                            <div class="w-10 h-10 rounded-2xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold shrink-0">
                                <Calendar class="w-5 h-5" />
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-slate-900 dark:text-white tracking-tight">
                                    Kalender & Sesi Akademik
                                </h2>
                                <p class="text-xs text-slate-400 dark:text-slate-500">
                                    Menentukan tahun ajaran dan semester aktif untuk pembuatan jadwal dan nilai.
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                    Tahun Ajaran Aktif <span class="text-rose-500">*</span>
                                </label>
                                <input 
                                    v-model="form.active_academic_year" 
                                    type="text" 
                                    placeholder="2026/2027" 
                                    class="w-full text-xs sm:text-sm px-4 py-2.5 border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800 text-slate-900 dark:text-white rounded-xl font-mono focus:border-[#185338] dark:focus:border-emerald-500 focus:bg-white dark:focus:bg-slate-850 focus:outline-none transition-all shadow-2xs" 
                                    required 
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                    Semester Aktif <span class="text-rose-500">*</span>
                                </label>
                                <select 
                                    v-model="form.active_semester" 
                                    class="w-full text-xs sm:text-sm px-4 py-2.5 border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800 text-slate-900 dark:text-white rounded-xl focus:border-[#185338] dark:focus:border-emerald-500 focus:bg-white dark:focus:bg-slate-850 focus:outline-none font-semibold transition-all shadow-2xs" 
                                    required
                                >
                                    <option value="Ganjil">Semester Ganjil</option>
                                    <option value="Genap">Semester Genap</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Branding & Warna Tema -->
                    <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-7 border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-5">
                        <div class="flex items-center gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
                            <div class="w-10 h-10 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-[#185338] dark:text-emerald-400 flex items-center justify-center font-bold shrink-0">
                                <Palette class="w-5 h-5" />
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-slate-900 dark:text-white tracking-tight">
                                    Branding & Warna Aksen Antarmuka
                                </h2>
                                <p class="text-xs text-slate-400 dark:text-slate-500">
                                    Pilih palet warna khas sekolah untuk aksen antarmuka, KOP kartu ujian, dan rapor.
                                </p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                    Pilihan Palet Warna Rekomendasi
                                </label>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                                    <button
                                        v-for="preset in colorPresets"
                                        :key="preset.hex"
                                        type="button"
                                        @click="form.primary_color = preset.hex"
                                        :class="[
                                            'px-3 py-2 rounded-xl border text-xs font-semibold flex items-center gap-2.5 transition-all cursor-pointer',
                                            form.primary_color.toUpperCase() === preset.hex.toUpperCase()
                                                ? 'border-[#185338] bg-[#eaf4ef]/60 dark:bg-emerald-950/40 text-[#185338] dark:text-emerald-200 ring-2 ring-emerald-500/20 shadow-2xs font-bold'
                                                : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:border-slate-300'
                                        ]"
                                    >
                                        <span class="w-4 h-4 rounded-full shadow-2xs shrink-0" :style="{ backgroundColor: preset.hex }"></span>
                                        <span class="truncate">{{ preset.name }}</span>
                                    </button>
                                </div>
                            </div>

                            <div class="pt-2 flex flex-col sm:flex-row sm:items-center gap-3">
                                <div class="flex items-center gap-2">
                                    <input 
                                        v-model="form.primary_color" 
                                        type="color" 
                                        class="w-10 h-10 p-0.5 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-xl cursor-pointer" 
                                    />
                                    <input 
                                        v-model="form.primary_color" 
                                        type="text" 
                                        placeholder="#185338"
                                        class="w-32 text-xs font-mono px-3 py-2 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-xl focus:border-[#185338] focus:outline-none" 
                                    />
                                </div>
                                <span class="text-xs text-slate-400 dark:text-slate-500">
                                    Nilai kode warna HEX custom
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Section 4: Keamanan Safe Exam Browser (SEB) -->
                    <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-7 border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-5">
                        <div class="flex items-center gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
                            <div class="w-10 h-10 rounded-2xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center font-bold shrink-0">
                                <ShieldCheck class="w-5 h-5" />
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-slate-900 dark:text-white tracking-tight">
                                    Integritas Ujian & Safe Exam Browser (SEB)
                                </h2>
                                <p class="text-xs text-slate-400 dark:text-slate-500">
                                    Cegah kecurangan dengan mewajibkan siswa mengakses melalui peramban terproteksi.
                                </p>
                            </div>
                        </div>

                        <!-- Toggle Switch SEB -->
                        <div class="p-4 bg-slate-50 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl flex items-start gap-4">
                            <div class="pt-0.5">
                                <input 
                                    id="seb-toggle"
                                    v-model="form.seb_required" 
                                    type="checkbox" 
                                    class="w-4.5 h-4.5 rounded border-slate-300 dark:border-slate-600 text-[#185338] focus:ring-0 cursor-pointer" 
                                />
                            </div>
                            <div class="flex-1">
                                <label for="seb-toggle" class="text-xs font-bold text-slate-900 dark:text-white cursor-pointer select-none">
                                    Wajibkan Safe Exam Browser (SEB) & Mode Kiosk Lockdown
                                </label>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                                    Bila diaktifkan, siswa tidak dapat membuka tab lain, menyalin teks, atau mengambil tangkapan layar. Siswa yang membuka via peramban standar otomatis ditolak.
                                </p>
                            </div>
                        </div>

                        <div v-if="form.seb_required" class="space-y-2 pt-1 animate-in fade-in duration-200">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                                Browser Exam Key (Hash SHA-256 SEB) <span class="text-slate-400 font-normal">(Opsional)</span>
                            </label>
                            <input 
                                v-model="form.seb_key_hash" 
                                type="text" 
                                placeholder="Masukkan 64-karakter SHA-256 Browser Exam Key..."
                                class="w-full text-xs font-mono px-4 py-2.5 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-xl focus:border-[#185338] focus:outline-none transition-all shadow-2xs" 
                            />
                            <p class="text-[11px] text-slate-400">
                                Jika diisi, sistem memvalidasi header X-SafeExamBrowser-RequestHash agar file konfigurasi SEB tidak dapat dimanipulasi siswa.
                            </p>
                        </div>
                    </div>

                    <!-- Bottom Submit Button -->
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button
                            type="button"
                            @click="resetForm"
                            class="px-5 py-2.5 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-semibold text-xs rounded-full hover:bg-slate-50 transition-colors cursor-pointer"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-7 py-2.5 bg-[#185338] hover:bg-[#12422c] text-white font-bold text-xs rounded-full shadow-xs transition-all flex items-center gap-2 cursor-pointer disabled:opacity-50"
                        >
                            <CheckCircle2 v-if="!form.processing" class="w-4 h-4" />
                            <span>{{ form.processing ? 'Menyimpan...' : 'Simpan Semua Pengaturan' }}</span>
                        </button>
                    </div>
                </form>

                <!-- Right: Live Preview Card & Server Diagnostics (1 col) -->
                <div class="space-y-6">
                    
                    <!-- Live Preview Card matching contoh.webp style -->
                    <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                            <div class="flex items-center gap-2 text-xs font-bold text-slate-900 dark:text-white">
                                <Eye class="w-4 h-4 text-[#185338]" />
                                <span>Pratinjau KOP & Kartu</span>
                            </div>
                            <span class="text-[10px] uppercase font-bold text-[#185338] bg-[#eaf4ef] dark:bg-emerald-950/60 dark:text-emerald-300 px-2.5 py-0.5 rounded-full font-mono">
                                Live
                            </span>
                        </div>

                        <!-- Mini Card Preview Container -->
                        <div class="p-4 rounded-xl border border-slate-200/80 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-850 space-y-3">
                            <div class="flex items-center gap-2.5 pb-2.5 border-b border-slate-200/70 dark:border-slate-700">
                                <div 
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-white font-bold text-xs shadow-2xs shrink-0"
                                    :style="{ backgroundColor: form.primary_color || '#185338' }"
                                >
                                    <GraduationCap class="w-4 h-4" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-bold text-slate-900 dark:text-white truncate uppercase tracking-tight">
                                        {{ form.school_name || 'NAMA SEKOLAH' }}
                                    </p>
                                    <p class="text-[10px] text-slate-400 font-mono truncate">
                                        NPSN: {{ form.npsn || '10203040' }} &bull; TA {{ form.active_academic_year }} ({{ form.active_semester }})
                                    </p>
                                </div>
                            </div>

                            <div class="text-[11px] text-slate-600 dark:text-slate-300 space-y-1">
                                <p class="text-slate-400 text-[10px] uppercase font-bold">Kepala Sekolah:</p>
                                <p class="font-bold text-slate-900 dark:text-white truncate">
                                    {{ form.headmaster_name || 'Dr. H. Ahmad Fauzi, M.Pd.' }}
                                </p>
                                <p v-if="form.headmaster_nip" class="text-[10px] font-mono text-slate-400 truncate">
                                    NIP: {{ form.headmaster_nip }}
                                </p>
                            </div>

                            <div class="pt-2 border-t border-slate-200/70 dark:border-slate-700 flex items-center justify-between text-[10px]">
                                <span class="text-slate-400 font-medium">Aksen Primer:</span>
                                <span class="font-mono font-bold uppercase" :style="{ color: form.primary_color }">
                                    {{ form.primary_color }}
                                </span>
                            </div>
                        </div>

                        <p class="text-[11px] text-slate-400 text-center">
                            Pratinjau otomatis menyesuaikan dengan data isian form secara langsung.
                        </p>
                    </div>

                    <!-- Server Diagnostics & Platform Info -->
                    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm space-y-4">
                        <div class="flex items-center gap-2 text-xs font-bold text-slate-900 dark:text-white pb-3 border-b border-slate-100 dark:border-slate-800">
                            <Cpu class="w-4 h-4 text-emerald-600" />
                            <span>Diagnostik Server CBT</span>
                        </div>

                        <div class="space-y-3 text-xs font-mono">
                            <div class="flex items-center justify-between py-1 border-b border-slate-100 dark:border-slate-800/80">
                                <span class="text-slate-400 font-sans">PHP Version</span>
                                <span class="font-bold text-slate-900 dark:text-white">{{ serverInfo?.php_version }}</span>
                            </div>
                            <div class="flex items-center justify-between py-1 border-b border-slate-100 dark:border-slate-800/80">
                                <span class="text-slate-400 font-sans">Framework</span>
                                <span class="font-bold text-slate-900 dark:text-white">Laravel {{ serverInfo?.laravel_version }}</span>
                            </div>
                            <div class="flex items-center justify-between py-1 border-b border-slate-100 dark:border-slate-800/80">
                                <span class="text-slate-400 font-sans">Sistem Operasi</span>
                                <span class="font-bold text-slate-900 dark:text-white">{{ serverInfo?.os }}</span>
                            </div>
                            <div class="flex items-center justify-between py-1">
                                <span class="text-slate-400 font-sans">Status Koneksi</span>
                                <span class="text-emerald-600 dark:text-emerald-400 font-bold flex items-center gap-1">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span>Aktif & Terhubung</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
