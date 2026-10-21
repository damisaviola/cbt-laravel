<script setup>
import { computed } from 'vue';
import { usePage, Link, router } from '@inertiajs/vue3';
import { 
    LayoutDashboard, 
    Users, 
    BookOpen, 
    GraduationCap, 
    Calendar, 
    ShieldCheck, 
    LogOut, 
    Award, 
    Layers,
    Clock,
    School,
    X,
    ChevronRight
} from 'lucide-vue-next';

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close', 'update:isOpen']);

const page = usePage();
const auth = computed(() => page.props.auth?.user);
const school = computed(() => page.props.school);
const currentUrl = computed(() => page.url);

const navigation = computed(() => {
    const role = auth.value?.role;
    if (role === 'admin') {
        return [
            { name: 'Dashboard & Pengaturan', href: '/admin/dashboard', icon: LayoutDashboard, color: 'text-blue-600 dark:text-blue-400', bg: 'bg-blue-50 dark:bg-blue-950/50', activeBg: 'bg-blue-50/90 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800/80' },
            { name: 'Rombel / Kelas', href: '/admin/classes', icon: Layers, color: 'text-purple-600 dark:text-purple-400', bg: 'bg-purple-50 dark:bg-purple-950/50', activeBg: 'bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border-purple-200 dark:border-purple-800/80' },
            { name: 'Mata Pelajaran', href: '/admin/subjects', icon: BookOpen, color: 'text-indigo-600 dark:text-indigo-400', bg: 'bg-indigo-50 dark:bg-indigo-950/50', activeBg: 'bg-indigo-50/90 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800/80' },
            { name: 'Guru & Pengawas', href: '/admin/staff', icon: Users, color: 'text-teal-600 dark:text-teal-400', bg: 'bg-teal-50 dark:bg-teal-950/50', activeBg: 'bg-teal-50/90 dark:bg-teal-950/60 text-teal-700 dark:text-teal-300 border-teal-200 dark:border-teal-800/80' },
            { name: 'Data Siswa & Kartu', href: '/admin/students', icon: GraduationCap, color: 'text-emerald-600 dark:text-emerald-400', bg: 'bg-emerald-50 dark:bg-emerald-950/50', activeBg: 'bg-emerald-50/90 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/80' },
            { name: 'Jadwal & Sesi Ujian', href: '/admin/schedules', icon: Calendar, color: 'text-amber-600 dark:text-amber-400', bg: 'bg-amber-50 dark:bg-amber-950/50', activeBg: 'bg-amber-50/90 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800/80' },
            { name: 'Ruang Pengawas (Control Room)', href: '/proctor/dashboard', icon: ShieldCheck, color: 'text-rose-600 dark:text-rose-400', bg: 'bg-rose-50 dark:bg-rose-950/50', activeBg: 'bg-rose-50/90 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800/80' },
            { name: 'Pratinjau Ujian Siswa', href: '/student/dashboard', icon: Clock, color: 'text-cyan-600 dark:text-cyan-400', bg: 'bg-cyan-50 dark:bg-cyan-950/50', activeBg: 'bg-cyan-50/90 dark:bg-cyan-950/60 text-cyan-700 dark:text-cyan-300 border-cyan-200 dark:border-cyan-800/80' },
        ];
    } else if (role === 'teacher') {
        return [
            { name: 'Dashboard Guru', href: '/teacher/dashboard', icon: LayoutDashboard, color: 'text-blue-600 dark:text-blue-400', bg: 'bg-blue-50 dark:bg-blue-950/50', activeBg: 'bg-blue-50/90 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800/80' },
            { name: 'Soal Ujian', href: '/teacher/banks', icon: BookOpen, color: 'text-indigo-600 dark:text-indigo-400', bg: 'bg-indigo-50 dark:bg-indigo-950/50', activeBg: 'bg-indigo-50/90 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800/80' },
            { name: 'Ruang Pengawas (Control Room)', href: '/proctor/dashboard', icon: ShieldCheck, color: 'text-rose-600 dark:text-rose-400', bg: 'bg-rose-50 dark:bg-rose-950/50', activeBg: 'bg-rose-50/90 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800/80' },
            { name: 'Meja Koreksi & Leger', href: '/teacher/grading', icon: Award, color: 'text-amber-600 dark:text-amber-400', bg: 'bg-amber-50 dark:bg-amber-950/50', activeBg: 'bg-amber-50/90 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800/80' },
        ];
    } else if (role === 'proctor') {
        return [
            { name: 'Ruang Pengawas (Control Room)', href: '/proctor/dashboard', icon: ShieldCheck, color: 'text-emerald-600 dark:text-emerald-400', bg: 'bg-emerald-50 dark:bg-emerald-950/50', activeBg: 'bg-emerald-50/90 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/80' },
        ];
    } else if (role === 'student') {
        return [
            { name: 'Portal Ujian Siswa', href: '/student/dashboard', icon: Clock, color: 'text-blue-600 dark:text-blue-400', bg: 'bg-blue-50 dark:bg-blue-950/50', activeBg: 'bg-blue-50/90 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800/80' },
        ];
    }
    return [];
});

const roleBadge = computed(() => {
    const role = auth.value?.role;
    switch (role) {
        case 'admin': return { label: 'Administrator', class: 'bg-purple-100/80 dark:bg-purple-950/60 text-purple-800 dark:text-purple-300 border-purple-200 dark:border-purple-800', avatarBg: 'bg-gradient-to-tr from-purple-600 to-indigo-600' };
        case 'teacher': return { label: 'Guru Mapel', class: 'bg-blue-100/80 dark:bg-blue-950/60 text-blue-800 dark:text-blue-300 border-blue-200 dark:border-blue-800', avatarBg: 'bg-gradient-to-tr from-blue-600 to-cyan-600' };
        case 'proctor': return { label: 'Pengawas Ruang', class: 'bg-emerald-100/80 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800', avatarBg: 'bg-gradient-to-tr from-emerald-600 to-teal-600' };
        case 'student': return { label: 'Peserta Ujian', class: 'bg-indigo-100/80 dark:bg-indigo-950/60 text-indigo-800 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800', avatarBg: 'bg-gradient-to-tr from-indigo-600 to-blue-600' };
        default: return { label: 'Pengguna', class: 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700', avatarBg: 'bg-slate-600' };
    }
});

const userInitials = computed(() => {
    if (!auth.value?.name) return 'U';
    return auth.value.name
        .split(' ')
        .map(n => n[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();
});

const isNavActive = (href) => {
    if (href === '/admin/dashboard' || href === '/teacher/dashboard' || href === '/proctor/dashboard' || href === '/student/dashboard') {
        return currentUrl.value === href || currentUrl.value === '/admin' || currentUrl.value.startsWith(href + '?');
    }
    return currentUrl.value.startsWith(href);
};

const handleLogout = () => {
    router.post('/logout');
};

const closeSidebar = () => {
    emit('close');
    emit('update:isOpen', false);
};
</script>

<template>
    <div class="sidebar-root shrink-0 lg:w-68 lg:sticky lg:top-0 lg:h-screen">
        <!-- Mobile Sidebar Backdrop -->
        <div 
            v-if="isOpen" 
            @click="closeSidebar" 
            class="fixed inset-0 bg-slate-900/50 dark:bg-black/70 z-40 lg:hidden transition-opacity"
        />

        <!-- Modern Clean Sidebar with Rounded Cards -->
        <aside 
            :class="[
                'fixed inset-y-0 left-0 z-50 w-68 bg-white dark:bg-slate-900 border-r border-slate-200/90 dark:border-slate-800 flex flex-col shrink-0 select-none shadow-[2px_0_12px_rgba(0,0,0,0.02)] transition-transform duration-200 lg:static lg:translate-x-0 h-full',
                isOpen ? 'translate-x-0' : '-translate-x-full'
            ]"
        >
            <!-- School Brand / Header -->
            <div class="h-16 flex items-center justify-between px-5 border-b border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 transition-colors duration-150">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#0F52BA] to-blue-700 text-white flex items-center justify-center font-bold text-sm shadow-md shadow-blue-600/25 shrink-0">
                        <School class="w-4.5 h-4.5 text-white" />
                    </div>
                    <div class="min-w-0">
                        <h1 class="text-sm font-bold text-slate-900 dark:text-white truncate uppercase tracking-wider">
                            {{ school?.school_name || 'SmartCBT' }}
                        </h1>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-medium truncate">
                            TA {{ school?.active_academic_year || '2026/2027' }} &bull; {{ school?.active_semester || 'Ganjil' }}
                        </p>
                    </div>
                </div>

                <button 
                    type="button"
                    @click="closeSidebar" 
                    class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer"
                    aria-label="Tutup Menu"
                >
                    <X class="w-4 h-4" />
                </button>
            </div>

            <!-- User Card (Vibrant & Friendly Rounded-2xl) -->
            <div class="p-3.5 mx-3.5 my-3 rounded-2xl bg-gradient-to-br from-slate-50 via-blue-50/20 to-white dark:from-slate-800/80 dark:via-slate-800/40 dark:to-slate-800/60 border border-slate-200/80 dark:border-slate-700/80 shadow-2xs">
                <div class="flex items-center gap-2.5">
                    <div :class="['w-9 h-9 rounded-xl font-bold text-sm flex items-center justify-center text-white shadow-xs shrink-0', roleBadge.avatarBg]">
                        {{ userInitials }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-slate-900 dark:text-white truncate leading-tight">{{ auth?.name }}</p>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <span :class="['px-2 py-0.5 rounded-md text-[11px] font-bold tracking-wide uppercase border', roleBadge.class]">
                                {{ roleBadge.label }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation Links -->
            <div class="px-5 pt-2 pb-1">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                    Menu Navigasi
                </p>
            </div>

            <nav class="flex-1 px-3 py-1 space-y-1.5 overflow-y-auto">
                <Link
                    v-for="item in navigation"
                    :key="item.name"
                    :href="item.href"
                    @click="closeSidebar"
                    :class="[
                        isNavActive(item.href)
                            ? `${item.activeBg} font-bold shadow-2xs border`
                            : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-800/60 font-medium',
                        'group flex items-center justify-between px-3 py-2 text-sm rounded-xl transition-all duration-150'
                    ]"
                >
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div :class="['w-7 h-7 rounded-lg flex items-center justify-center shrink-0 transition-transform group-hover:scale-105', item.bg, item.color]">
                            <component :is="item.icon" class="w-4 h-4" />
                        </div>
                        <span class="truncate">{{ item.name }}</span>
                    </div>
                    <ChevronRight 
                        v-if="isNavActive(item.href)" 
                        :class="['w-3.5 h-3.5 shrink-0', item.color]" 
                    />
                </Link>
            </nav>

            <!-- Bottom System Status & Logout -->
            <div class="p-3.5 border-t border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 space-y-2 transition-colors duration-150">
                <div class="flex items-center justify-between px-2 text-xs text-slate-500 dark:text-slate-400 font-medium">
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Server CBT</span>
                    </span>
                    <span class="font-mono text-xs text-emerald-700 dark:text-emerald-300 font-bold uppercase bg-emerald-50 dark:bg-emerald-950/50 px-2.5 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-800">
                        Online
                    </span>
                </div>

                <button
                    type="button"
                    @click="handleLogout"
                    class="w-full flex items-center justify-center gap-2 px-3 py-2 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:text-rose-700 dark:hover:text-rose-300 bg-slate-50 dark:bg-slate-800/70 hover:bg-rose-50 dark:hover:bg-rose-950/40 border border-slate-200 dark:border-slate-700 hover:border-rose-200 dark:hover:border-rose-800 rounded-xl transition-all duration-150 cursor-pointer active:scale-[0.98]"
                >
                    <LogOut class="w-3.5 h-3.5 text-slate-400 group-hover:text-rose-600 dark:group-hover:text-rose-400" />
                    <span>Keluar Sistem</span>
                </button>
            </div>
        </aside>
    </div>
</template>
