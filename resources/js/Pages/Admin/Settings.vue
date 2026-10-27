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
    GraduationCap, 
    Cpu, 
    Lock, 
    Eye,
    QrCode,
    Sparkles,
    Check,
    FileCheck,
    Shield,
    UserCheck,
    Clock,
    Hash,
    Layers,
    Sliders,
    HelpCircle,
    Copy,
    ChevronRight
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

// Active section filter tab
const activeTab = ref('all');

const tabs = [
    { id: 'all', label: 'Semua Pengaturan', icon: Sliders },
    { id: 'identity', label: 'Identitas Sekolah', icon: School },
    { id: 'academic', label: 'Kalender Akademik', icon: Calendar },
    { id: 'branding', label: 'Branding & Tampilan', icon: Palette },
    { id: 'security', label: 'Keamanan SEB', icon: ShieldCheck },
];

const colorPresets = [
    { name: 'Forest Green (Modern)', hex: '#185338', tone: 'Signature' },
    { name: 'Emerald Green', hex: '#059669', tone: 'Fresh' },
    { name: 'Royal Navy Blue', hex: '#0F52BA', tone: 'EduPro' },
    { name: 'Deep Indigo', hex: '#4F46E5', tone: 'Modern' },
    { name: 'Imperial Violet', hex: '#7C3AED', tone: 'Creative' },
    { name: 'Crimson Rose', hex: '#E11D48', tone: 'Bold' },
];

const academicYearOptions = [
    '2024/2025',
    '2025/2026',
    '2026/2027',
    '2027/2028',
];

const copiedHash = ref(false);
const copyHash = () => {
    if (!form.seb_key_hash) return;
    navigator.clipboard.writeText(form.seb_key_hash);
    copiedHash.value = true;
    setTimeout(() => {
        copiedHash.value = false;
    }, 2000);
};

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

        <div class="space-y-6 w-full">
            <!-- Page Header matching contoh.webp (Title on Left, Actions on Right) -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <!-- Breadcrumbs trail -->
                    <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 dark:text-slate-500 mb-1.5">
                        <span class="hover:text-slate-600 dark:hover:text-slate-300 transition-colors">Admin</span>
                        <span>/</span>
                        <span class="text-[#185338] dark:text-emerald-400 font-bold">Settings</span>
                    </div>
                    <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                        Pengaturan Sistem & Lembaga
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                        Kelola identitas resmi sekolah, kalender akademik, palet visual antarmuka, dan keamanan ujian.
                    </p>
                </div>

                <!-- Action Buttons: Reset & Simpan Pengaturan -->
                <div class="flex items-center gap-3 shrink-0">
                    <button
                        type="button"
                        @click="resetForm"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800 rounded-full text-xs font-semibold text-slate-700 dark:text-slate-300 shadow-2xs transition-all cursor-pointer disabled:opacity-50"
                    >
                        <RotateCcw class="w-4 h-4 text-slate-500" />
                        <span>Reset Form</span>
                    </button>

                    <button
                        type="button"
                        @click="saveSettings"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#185338] hover:bg-[#12422c] text-white rounded-full text-xs font-bold shadow-xs hover:shadow-emerald-950/20 transition-all cursor-pointer disabled:opacity-50"
                    >
                        <CheckCircle2 v-if="!form.processing" class="w-4 h-4" />
                        <span v-if="form.processing">Menyimpan...</span>
                        <span v-else>Simpan Pengaturan</span>
                    </button>
                </div>
            </div>

            <!-- 4 Bento Stat Cards Grid matching contoh.webp (Card 1 Hero Forest Green, Cards 2-4 Clean White) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Card 1: Hero Forest Green (NPSN Lembaga) -->
                <BentoStatCard
                    is-hero
                    title="NPSN Sekolah"
                    :value="form.npsn || '10203040'"
                    trend="Terdaftar"
                    trend-type="up"
                    trend-label="Kemendikbudristek"
                />

                <!-- Card 2: Kalender Akademik (Clean White Bento Card) -->
                <BentoStatCard
                    title="Semester Berjalan"
                    :value="form.active_semester"
                    :trend="form.active_academic_year"
                    trend-type="up"
                    trend-label="Tahun Ajaran Aktif"
                />

                <!-- Card 3: Keamanan SEB (Clean White Bento Card) -->
                <BentoStatCard
                    title="Integritas Ujian"
                    :value="form.seb_required ? 'SEB Wajib' : 'Mode Bebas'"
                    :trend="form.seb_required ? 'Kiosk ON' : 'Standar'"
                    :trend-type="form.seb_required ? 'up' : 'neutral'"
                    trend-label="Status Safe Exam Browser"
                />

                <!-- Card 4: Runtime Server CBT (Clean White Bento Card) -->
                <BentoStatCard
                    title="Runtime Server CBT"
                    :value="`PHP ${serverInfo?.php_version?.split('-')[0] || '8.5'}`"
                    trend="Laravel 12"
                    trend-type="up"
                    trend-label="Koneksi Sistem Optimal"
                />
            </div>

            <!-- Segmented Pill Filter / Section Navigation -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
                <button
                    v-for="tab in tabs"
                    :key="tab.id"
                    type="button"
                    @click="activeTab = tab.id"
                    :class="[
                        'px-4 py-2 rounded-full text-xs font-bold transition-all flex items-center gap-2 shrink-0 cursor-pointer shadow-2xs',
                        activeTab === tab.id
                            ? 'bg-[#185338] text-white shadow-xs'
                            : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 border border-slate-200/80 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800'
                    ]"
                >
                    <component :is="tab.icon" class="w-3.5 h-3.5" />
                    <span>{{ tab.label }}</span>
                </button>
            </div>

            <!-- Main Layout: 2 Columns (Form Left ~65%, Live Preview Right ~35%) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                
                <!-- Left Column: Master Form Sections -->
                <form @submit.prevent="saveSettings" class="lg:col-span-2 space-y-6">
                    
                    <!-- Section 1: Identitas Resmi Sekolah -->
                    <div 
                        v-show="activeTab === 'all' || activeTab === 'identity'"
                        class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-7 border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-5 transition-all"
                    >
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-2xl bg-[#eaf4ef] dark:bg-emerald-950/60 text-[#185338] dark:text-emerald-400 flex items-center justify-center font-bold shrink-0 shadow-2xs">
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
                            <span class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-[#185338] dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/40">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#185338] dark:bg-emerald-400"></span>
                                Legalitas Utama
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Nama Resmi Sekolah -->
                            <div class="sm:col-span-2 space-y-1.5">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                    Nama Resmi Sekolah / Lembaga <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <School class="w-4 h-4" />
                                    </div>
                                    <input 
                                        v-model="form.school_name" 
                                        type="text" 
                                        placeholder="Contoh: SMA Negeri 1 Digital"
                                        class="w-full text-xs sm:text-sm pl-10 pr-4 py-3 border border-slate-200 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/60 text-slate-900 dark:text-white rounded-2xl focus:bg-white dark:focus:bg-slate-800 focus:border-[#185338] dark:focus:border-emerald-500 focus:ring-4 focus:ring-[#185338]/10 dark:focus:ring-emerald-500/10 focus:outline-none transition-all shadow-2xs" 
                                        required 
                                    />
                                </div>
                            </div>

                            <!-- NPSN -->
                            <div class="space-y-1.5">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                    NPSN (Nomor Pokok Sekolah) <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <Hash class="w-4 h-4" />
                                    </div>
                                    <input 
                                        v-model="form.npsn" 
                                        type="text" 
                                        placeholder="Contoh: 10203040"
                                        class="w-full text-xs sm:text-sm pl-10 pr-4 py-3 border border-slate-200 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/60 text-slate-900 dark:text-white rounded-2xl font-mono focus:bg-white dark:focus:bg-slate-800 focus:border-[#185338] dark:focus:border-emerald-500 focus:ring-4 focus:ring-[#185338]/10 dark:focus:ring-emerald-500/10 focus:outline-none transition-all shadow-2xs" 
                                        required 
                                    />
                                </div>
                            </div>

                            <!-- Nama Kepala Sekolah -->
                            <div class="space-y-1.5">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                    Nama Kepala Sekolah <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <UserCheck class="w-4 h-4" />
                                    </div>
                                    <input 
                                        v-model="form.headmaster_name" 
                                        type="text" 
                                        placeholder="Nama lengkap beserta gelar..."
                                        class="w-full text-xs sm:text-sm pl-10 pr-4 py-3 border border-slate-200 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/60 text-slate-900 dark:text-white rounded-2xl focus:bg-white dark:focus:bg-slate-800 focus:border-[#185338] dark:focus:border-emerald-500 focus:ring-4 focus:ring-[#185338]/10 dark:focus:ring-emerald-500/10 focus:outline-none transition-all shadow-2xs" 
                                        required 
                                    />
                                </div>
                            </div>

                            <!-- NIP Kepala Sekolah -->
                            <div class="sm:col-span-2 space-y-1.5">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                    NIP Kepala Sekolah <span class="text-slate-400 font-normal">(Opsional)</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <FileCheck class="w-4 h-4" />
                                    </div>
                                    <input 
                                        v-model="form.headmaster_nip" 
                                        type="text" 
                                        placeholder="Contoh: 197505122000031002"
                                        class="w-full text-xs sm:text-sm pl-10 pr-4 py-3 border border-slate-200 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/60 text-slate-900 dark:text-white rounded-2xl font-mono focus:bg-white dark:focus:bg-slate-800 focus:border-[#185338] dark:focus:border-emerald-500 focus:ring-4 focus:ring-[#185338]/10 dark:focus:ring-emerald-500/10 focus:outline-none transition-all shadow-2xs" 
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Kalender & Sesi Akademik -->
                    <div 
                        v-show="activeTab === 'all' || activeTab === 'academic'"
                        class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-7 border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-5 transition-all"
                    >
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-2xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold shrink-0 shadow-2xs">
                                    <Calendar class="w-5 h-5" />
                                </div>
                                <div>
                                    <h2 class="text-base font-bold text-slate-900 dark:text-white tracking-tight">
                                        Kalender & Sesi Akademik
                                    </h2>
                                    <p class="text-xs text-slate-400 dark:text-slate-500">
                                        Menentukan tahun ajaran dan semester aktif untuk pembuatan jadwal dan rekapitulasi nilai.
                                    </p>
                                </div>
                            </div>
                            <span class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800/40">
                                <Clock class="w-3.5 h-3.5" />
                                Periode Aktif
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <!-- Tahun Ajaran Aktif -->
                            <div class="space-y-2">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                    Tahun Ajaran Aktif <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <Calendar class="w-4 h-4" />
                                    </div>
                                    <input 
                                        v-model="form.active_academic_year" 
                                        type="text" 
                                        placeholder="2026/2027" 
                                        class="w-full text-xs sm:text-sm pl-10 pr-4 py-3 border border-slate-200 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/60 text-slate-900 dark:text-white rounded-2xl font-mono focus:bg-white dark:focus:bg-slate-800 focus:border-[#185338] dark:focus:border-emerald-500 focus:ring-4 focus:ring-[#185338]/10 dark:focus:ring-emerald-500/10 focus:outline-none transition-all shadow-2xs" 
                                        required 
                                    />
                                </div>

                                <!-- Quick Select Chips for Academic Year -->
                                <div class="flex items-center gap-1.5 pt-1 flex-wrap">
                                    <span class="text-[11px] text-slate-400 mr-1">Pilihan Cepat:</span>
                                    <button
                                        v-for="year in academicYearOptions"
                                        :key="year"
                                        type="button"
                                        @click="form.active_academic_year = year"
                                        :class="[
                                            'px-2.5 py-1 rounded-lg text-[11px] font-mono font-semibold transition-all cursor-pointer',
                                            form.active_academic_year === year
                                                ? 'bg-[#185338] text-white shadow-2xs'
                                                : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'
                                        ]"
                                    >
                                        {{ year }}
                                    </button>
                                </div>
                            </div>

                            <!-- Semester Berjalan (Modern Segmented Pill Toggle) -->
                            <div class="space-y-2">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                    Semester Berjalan <span class="text-rose-500">*</span>
                                </label>
                                
                                <div class="grid grid-cols-2 p-1.5 bg-slate-100 dark:bg-slate-800/80 border border-slate-200/60 dark:border-slate-700/60 rounded-2xl gap-1.5">
                                    <button
                                        type="button"
                                        @click="form.active_semester = 'Ganjil'"
                                        :class="[
                                            'py-2.5 px-3 rounded-xl text-xs font-bold flex items-center justify-center gap-2 transition-all cursor-pointer',
                                            form.active_semester === 'Ganjil'
                                                ? 'bg-white dark:bg-slate-900 text-[#185338] dark:text-emerald-300 shadow-2xs ring-1 ring-slate-200/80 dark:ring-slate-700'
                                                : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
                                        ]"
                                    >
                                        <span class="w-2 h-2 rounded-full" :class="form.active_semester === 'Ganjil' ? 'bg-[#185338] dark:bg-emerald-400' : 'bg-slate-300 dark:bg-slate-600'"></span>
                                        <span>Semester Ganjil</span>
                                    </button>

                                    <button
                                        type="button"
                                        @click="form.active_semester = 'Genap'"
                                        :class="[
                                            'py-2.5 px-3 rounded-xl text-xs font-bold flex items-center justify-center gap-2 transition-all cursor-pointer',
                                            form.active_semester === 'Genap'
                                                ? 'bg-white dark:bg-slate-900 text-[#185338] dark:text-emerald-300 shadow-2xs ring-1 ring-slate-200/80 dark:ring-slate-700'
                                                : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
                                        ]"
                                    >
                                        <span class="w-2 h-2 rounded-full" :class="form.active_semester === 'Genap' ? 'bg-[#185338] dark:bg-emerald-400' : 'bg-slate-300 dark:bg-slate-600'"></span>
                                        <span>Semester Genap</span>
                                    </button>
                                </div>
                                <p class="text-[11px] text-slate-400">
                                    Memengaruhi penomoran token soal dan filtering rekap nilai siswa.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Branding & Warna Tema -->
                    <div 
                        v-show="activeTab === 'all' || activeTab === 'branding'"
                        class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-7 border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-5 transition-all"
                    >
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-[#185338] dark:text-emerald-400 flex items-center justify-center font-bold shrink-0 shadow-2xs">
                                    <Palette class="w-5 h-5" />
                                </div>
                                <div>
                                    <h2 class="text-base font-bold text-slate-900 dark:text-white tracking-tight">
                                        Branding & Warna Aksen Antarmuka
                                    </h2>
                                    <p class="text-xs text-slate-400 dark:text-slate-500">
                                        Pilih palet khas sekolah untuk tombol utama, KOP kartu ujian, dan rapor siswa.
                                    </p>
                                </div>
                            </div>
                            <span class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-[#185338] dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/40 font-mono">
                                {{ form.primary_color }}
                            </span>
                        </div>

                        <div class="space-y-4">
                            <!-- Preset Swatches Grid -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2.5">
                                    Palet Warna Resmi Rekomendasi
                                </label>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                    <button
                                        v-for="preset in colorPresets"
                                        :key="preset.hex"
                                        type="button"
                                        @click="form.primary_color = preset.hex"
                                        :class="[
                                            'p-3 rounded-2xl border text-xs font-semibold flex items-center justify-between transition-all cursor-pointer text-left',
                                            form.primary_color.toUpperCase() === preset.hex.toUpperCase()
                                                ? 'border-[#185338] bg-[#eaf4ef]/70 dark:bg-emerald-950/40 text-[#185338] dark:text-emerald-200 ring-2 ring-emerald-500/20 shadow-2xs font-bold'
                                                : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/60 text-slate-700 dark:text-slate-300 hover:border-slate-300 dark:hover:border-slate-700'
                                        ]"
                                    >
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <span 
                                                class="w-5 h-5 rounded-full shadow-2xs shrink-0 ring-1 ring-black/10 flex items-center justify-center text-white text-[10px]"
                                                :style="{ backgroundColor: preset.hex }"
                                            >
                                                <Check v-if="form.primary_color.toUpperCase() === preset.hex.toUpperCase()" class="w-3 h-3 stroke-[3]" />
                                            </span>
                                            <div class="truncate">
                                                <p class="truncate text-xs">{{ preset.name }}</p>
                                                <p class="text-[10px] text-slate-400 font-mono">{{ preset.hex }}</p>
                                            </div>
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <!-- Custom HEX Color Picker & Live Strip -->
                            <div class="pt-2 p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200/80 dark:border-slate-700/80 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="relative">
                                        <input 
                                            v-model="form.primary_color" 
                                            type="color" 
                                            class="w-12 h-12 p-1 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-2xl cursor-pointer shadow-2xs" 
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                            Kode Warna HEX Custom
                                        </label>
                                        <input 
                                            v-model="form.primary_color" 
                                            type="text" 
                                            placeholder="#185338"
                                            class="w-32 text-xs font-mono font-bold px-3 py-1.5 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-xl focus:border-[#185338] focus:outline-none shadow-2xs" 
                                        />
                                    </div>
                                </div>

                                <!-- Dynamic Preview Ribbon -->
                                <div class="flex items-center gap-2">
                                    <div class="text-right hidden sm:block">
                                        <p class="text-[11px] font-semibold text-slate-600 dark:text-slate-300">Live Accent Ribbon</p>
                                        <p class="text-[10px] text-slate-400">Pratinjau aksen tombol & badge</p>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <div 
                                            class="px-3 py-1.5 rounded-full text-[11px] font-bold text-white shadow-2xs"
                                            :style="{ backgroundColor: form.primary_color }"
                                        >
                                            Tombol
                                        </div>
                                        <div 
                                            class="px-2.5 py-1.5 rounded-full text-[11px] font-bold border"
                                            :style="{ borderColor: form.primary_color, color: form.primary_color }"
                                        >
                                            Badge
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 4: Keamanan Safe Exam Browser (SEB) -->
                    <div 
                        v-show="activeTab === 'all' || activeTab === 'security'"
                        class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-7 border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-5 transition-all"
                    >
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-2xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center font-bold shrink-0 shadow-2xs">
                                    <ShieldCheck class="w-5 h-5" />
                                </div>
                                <div>
                                    <h2 class="text-base font-bold text-slate-900 dark:text-white tracking-tight">
                                        Integritas Ujian & Safe Exam Browser (SEB)
                                    </h2>
                                    <p class="text-xs text-slate-400 dark:text-slate-500">
                                        Proteksi anti-kecurangan peramban dengan mode lockdown dan validasi hash SHA-256.
                                    </p>
                                </div>
                            </div>
                            <span 
                                :class="[
                                    'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold transition-all',
                                    form.seb_required 
                                        ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-300 border border-rose-200 dark:border-rose-800/40' 
                                        : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400'
                                ]"
                            >
                                <span class="w-2 h-2 rounded-full" :class="form.seb_required ? 'bg-rose-500 animate-pulse' : 'bg-slate-400'"></span>
                                <span>{{ form.seb_required ? 'Lockdown Aktif' : 'Mode Standar' }}</span>
                            </span>
                        </div>

                        <!-- Sleek Toggle Switch Card -->
                        <div 
                            @click="form.seb_required = !form.seb_required"
                            :class="[
                                'p-4 sm:p-5 rounded-2xl border transition-all cursor-pointer flex items-center justify-between gap-4',
                                form.seb_required 
                                    ? 'bg-rose-50/40 dark:bg-rose-950/20 border-rose-200/80 dark:border-rose-900/60' 
                                    : 'bg-slate-50 dark:bg-slate-800/40 border-slate-200/80 dark:border-slate-700/80'
                            ]"
                        >
                            <div class="flex items-start gap-3.5">
                                <div 
                                    :class="[
                                        'w-9 h-9 rounded-xl flex items-center justify-center shrink-0 transition-colors',
                                        form.seb_required ? 'bg-rose-600 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300'
                                    ]"
                                >
                                    <Lock class="w-4 h-4" />
                                </div>
                                <div>
                                    <p class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">
                                        Wajibkan Safe Exam Browser (SEB) & Mode Kiosk Lockdown
                                    </p>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">
                                        Saat diaktifkan, peserta ujian hanya diizinkan mengerjakan melalui aplikasi SEB. Browser standar seperti Chrome/Edge akan otomatis ditolak.
                                    </p>
                                </div>
                            </div>

                            <!-- iOS / Pill Switch -->
                            <div 
                                :class="[
                                    'w-12 h-6 rounded-full transition-colors relative shrink-0 p-0.5',
                                    form.seb_required ? 'bg-rose-600' : 'bg-slate-300 dark:bg-slate-700'
                                ]"
                            >
                                <div 
                                    :class="[
                                        'w-5 h-5 rounded-full bg-white shadow-md transform transition-transform duration-200',
                                        form.seb_required ? 'translate-x-6' : 'translate-x-0'
                                    ]"
                                ></div>
                            </div>
                        </div>

                        <!-- Protection Features Grid (Pills) -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 pt-1">
                            <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200/70 dark:border-slate-800 flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 flex items-center justify-center shrink-0">
                                    <Check class="w-3.5 h-3.5 stroke-[2.5]" />
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-slate-900 dark:text-white truncate">Kunci Shortcut</p>
                                    <p class="text-[10px] text-slate-400">Blokir Alt+Tab & Copy</p>
                                </div>
                            </div>

                            <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200/70 dark:border-slate-800 flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 flex items-center justify-center shrink-0">
                                    <Check class="w-3.5 h-3.5 stroke-[2.5]" />
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-slate-900 dark:text-white truncate">Anti Tangkapan</p>
                                    <p class="text-[10px] text-slate-400">Cegah Screenshot/Screenrec</p>
                                </div>
                            </div>

                            <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200/70 dark:border-slate-800 flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 flex items-center justify-center shrink-0">
                                    <Check class="w-3.5 h-3.5 stroke-[2.5]" />
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-slate-900 dark:text-white truncate">Isolasi Jaringan</p>
                                    <p class="text-[10px] text-slate-400">Hanya izinkan server CBT</p>
                                </div>
                            </div>
                        </div>

                        <!-- Browser Exam Key (Hash) Input -->
                        <div v-if="form.seb_required" class="space-y-2 pt-2 transition-all">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                    Browser Exam Key (Hash SHA-256 SEB) <span class="text-slate-400 font-normal">(Opsional)</span>
                                </label>
                                <button
                                    v-if="form.seb_key_hash"
                                    type="button"
                                    @click="copyHash"
                                    class="text-[11px] font-semibold text-[#185338] dark:text-emerald-400 hover:underline flex items-center gap-1 cursor-pointer"
                                >
                                    <Copy class="w-3 h-3" />
                                    <span>{{ copiedHash ? 'Tersalin!' : 'Salin Hash' }}</span>
                                </button>
                            </div>
                            <input 
                                v-model="form.seb_key_hash" 
                                type="text" 
                                placeholder="Masukkan 64-karakter kode SHA-256 Browser Exam Key..."
                                class="w-full text-xs font-mono px-4 py-3 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-2xl focus:border-[#185338] focus:ring-4 focus:ring-[#185338]/10 focus:outline-none transition-all shadow-2xs" 
                            />
                            <p class="text-[11px] text-slate-400 leading-relaxed">
                                Header HTTP <code class="font-mono text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 px-1 py-0.5 rounded">X-SafeExamBrowser-RequestHash</code> akan divalidasi oleh CBT Server untuk memastikan berkas konfigurasi tidak diedit siswa.
                            </p>
                        </div>
                    </div>

                    <!-- Bottom Action Bar / Submit Section -->
                    <div class="p-5 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-2.5 text-xs text-slate-500 dark:text-slate-400">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Perubahan akan langsung diterapkan ke seluruh sistem ujian.</span>
                        </div>

                        <div class="flex items-center gap-3">
                            <button
                                type="button"
                                @click="resetForm"
                                :disabled="form.processing"
                                class="px-5 py-2.5 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-semibold text-xs rounded-full hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors cursor-pointer disabled:opacity-50"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-7 py-2.5 bg-[#185338] hover:bg-[#12422c] text-white font-bold text-xs rounded-full shadow-xs hover:shadow-emerald-950/20 transition-all flex items-center gap-2 cursor-pointer disabled:opacity-50"
                            >
                                <CheckCircle2 v-if="!form.processing" class="w-4 h-4" />
                                <span>{{ form.processing ? 'Menyimpan...' : 'Simpan Semua Pengaturan' }}</span>
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Right Column: Live Pratinjau KOP & Server Diagnostics (Sticky) -->
                <div class="space-y-6 lg:sticky lg:top-4">
                    
                    <!-- Live Preview Card: KOP & Kartu Peserta Ujian -->
                    <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                            <div class="flex items-center gap-2 text-xs font-bold text-slate-900 dark:text-white">
                                <Eye class="w-4 h-4 text-[#185338] dark:text-emerald-400" />
                                <span>Pratinjau KOP & Kartu</span>
                            </div>
                            <span class="text-[10px] uppercase font-bold text-[#185338] dark:text-emerald-300 bg-[#eaf4ef] dark:bg-emerald-950/60 px-2.5 py-0.5 rounded-full font-mono flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#185338] dark:bg-emerald-400 animate-pulse"></span>
                                Live Preview
                            </span>
                        </div>

                        <!-- Real-life Student Exam Card Mock -->
                        <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700 bg-white dark:bg-slate-850 shadow-sm overflow-hidden text-slate-900 dark:text-slate-100">
                            <!-- Top Color Accent Bar -->
                            <div class="h-2 w-full transition-colors duration-300" :style="{ backgroundColor: form.primary_color || '#185338' }"></div>

                            <div class="p-4 space-y-3.5">
                                <!-- KOP Header -->
                                <div class="flex items-center gap-3 pb-3 border-b-2 border-slate-800/80 dark:border-slate-700">
                                    <div 
                                        class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-bold text-xs shadow-2xs shrink-0 transition-colors"
                                        :style="{ backgroundColor: form.primary_color || '#185338' }"
                                    >
                                        <GraduationCap class="w-5 h-5" />
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">
                                            KARTU PESERTA ASESMEN / CBT
                                        </p>
                                        <p class="text-xs font-extrabold text-slate-900 dark:text-white uppercase truncate tracking-tight">
                                            {{ form.school_name || 'NAMA RESMI SEKOLAH' }}
                                        </p>
                                        <p class="text-[10px] text-slate-500 dark:text-slate-400 font-mono truncate">
                                            NPSN: {{ form.npsn || '10203040' }} &bull; TA {{ form.active_academic_year }} ({{ form.active_semester }})
                                        </p>
                                    </div>
                                </div>

                                <!-- Student Details Simulation -->
                                <div class="flex items-start gap-3 text-[11px]">
                                    <!-- Photo Avatar Box -->
                                    <div class="w-14 h-16 rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex flex-col items-center justify-center text-slate-400 shrink-0">
                                        <UserCheck class="w-6 h-6 text-slate-300 dark:text-slate-600" />
                                        <span class="text-[8px] font-mono mt-1">2 x 3</span>
                                    </div>

                                    <!-- Key Value Info -->
                                    <div class="min-w-0 flex-1 space-y-1 font-sans">
                                        <div class="flex justify-between text-[10px]">
                                            <span class="text-slate-400">No. Peserta:</span>
                                            <span class="font-mono font-bold text-slate-800 dark:text-slate-200">26-01-08-0012</span>
                                        </div>
                                        <div class="flex justify-between text-[10px]">
                                            <span class="text-slate-400">Nama Siswa:</span>
                                            <span class="font-bold text-slate-900 dark:text-white truncate">AHMAD SYARIF H.</span>
                                        </div>
                                        <div class="flex justify-between text-[10px]">
                                            <span class="text-slate-400">Kelas / Ruang:</span>
                                            <span class="font-medium text-slate-700 dark:text-slate-300">XII MIPA 1 / Lab 01</span>
                                        </div>
                                        <div class="flex justify-between text-[10px]">
                                            <span class="text-slate-400">Sesi Ujian:</span>
                                            <span class="font-bold text-emerald-600 dark:text-emerald-400">Sesi 1 (Pagi)</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Headmaster Digital Signature Block -->
                                <div class="pt-2 border-t border-dashed border-slate-200 dark:border-slate-700 flex items-end justify-between">
                                    <!-- Barcode Mock -->
                                    <div class="space-y-0.5">
                                        <div class="flex items-center gap-0.5 h-6">
                                            <div class="w-0.5 h-full bg-slate-800 dark:bg-slate-300"></div>
                                            <div class="w-1 h-full bg-slate-800 dark:bg-slate-300"></div>
                                            <div class="w-0.5 h-full bg-slate-400 dark:bg-slate-600"></div>
                                            <div class="w-1.5 h-full bg-slate-800 dark:bg-slate-300"></div>
                                            <div class="w-0.5 h-full bg-slate-800 dark:bg-slate-300"></div>
                                            <div class="w-1 h-full bg-slate-800 dark:bg-slate-300"></div>
                                            <div class="w-2 h-full bg-slate-800 dark:bg-slate-300"></div>
                                            <div class="w-0.5 h-full bg-slate-400"></div>
                                            <div class="w-1 h-full bg-slate-800 dark:bg-slate-300"></div>
                                        </div>
                                        <p class="text-[8px] font-mono text-slate-400">CBT-VALID-2026</p>
                                    </div>

                                    <!-- Signature -->
                                    <div class="text-right text-[10px]">
                                        <p class="text-slate-400 text-[9px]">Kepala Sekolah,</p>
                                        <p class="font-bold text-slate-900 dark:text-white mt-3 truncate max-w-[130px]">
                                            {{ form.headmaster_name || 'Dr. H. Ahmad Fauzi, M.Pd.' }}
                                        </p>
                                        <p v-if="form.headmaster_nip" class="text-[9px] font-mono text-slate-400 truncate max-w-[130px]">
                                            NIP. {{ form.headmaster_nip }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <p class="text-[11px] text-slate-400 text-center">
                            Pratinjau otomatis menyesuaikan dengan data form yang Anda ketik secara langsung.
                        </p>
                    </div>

                    <!-- Server Diagnostics & Platform Info -->
                    <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                            <div class="flex items-center gap-2 text-xs font-bold text-slate-900 dark:text-white">
                                <Cpu class="w-4 h-4 text-[#185338] dark:text-emerald-400" />
                                <span>Diagnostik Server CBT</span>
                            </div>
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        </div>

                        <div class="space-y-2.5 text-xs font-mono">
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
                                <span class="font-bold text-slate-900 dark:text-white truncate max-w-[140px]">{{ serverInfo?.os }}</span>
                            </div>
                            <div class="flex items-center justify-between py-1">
                                <span class="text-slate-400 font-sans">Status Database</span>
                                <span class="text-emerald-600 dark:text-emerald-400 font-bold flex items-center gap-1 font-sans">
                                    <span>Aktif & Terhubung</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Guidance & Quick Documentation -->
                    <div class="p-5 rounded-3xl bg-slate-50/80 dark:bg-slate-800/40 border border-slate-200/60 dark:border-slate-800 text-xs space-y-2.5">
                        <div class="flex items-center gap-2 font-bold text-slate-800 dark:text-slate-200">
                            <HelpCircle class="w-4 h-4 text-[#185338] dark:text-emerald-400" />
                            <span>Panduan Pengaturan</span>
                        </div>
                        <ul class="space-y-1.5 text-slate-500 dark:text-slate-400 text-[11px] leading-relaxed">
                            <li class="flex items-start gap-1.5">
                                <span class="text-[#185338] dark:text-emerald-400 font-bold">&bull;</span>
                                <span>Perubahan <strong>Tahun Ajaran</strong> memicu pengelompokan rapor dan riwayat sesi ujian.</span>
                            </li>
                            <li class="flex items-start gap-1.5">
                                <span class="text-[#185338] dark:text-emerald-400 font-bold">&bull;</span>
                                <span>Saat <strong>SEB diaktifkan</strong>, pastikan seluruh komputer lab telah dipasangi konfigurasi .seb terbaru.</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
