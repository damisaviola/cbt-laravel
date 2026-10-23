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
    Settings,
    HelpCircle,
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

// Grouped Navigation matching contoh.jpg (MENU & SYSTEM sections)
const menuSections = computed(() => {
    const role = auth.value?.role;
    
    if (role === 'admin') {
        return [
            {
                heading: 'MENU',
                items: [
                    { name: 'Dashboard', href: '/admin/dashboard', icon: LayoutDashboard },
                    { name: 'Users / Siswa', href: '/admin/students', icon: Users },
                    { name: 'Mata Pelajaran', href: '/admin/subjects', icon: BookOpen },
                    { name: 'Rombel Kelas', href: '/admin/classes', icon: Layers },
                    { name: 'Tenaga Pendidik', href: '/admin/staff', icon: GraduationCap },
                    { name: 'Jadwal Ujian', href: '/admin/schedules', icon: Calendar },
                ],
            },
            {
                heading: 'SYSTEM',
                items: [
                    { name: 'Ruang Pengawas', href: '/proctor/dashboard', icon: ShieldCheck },
                    { name: 'Pratinjau Siswa', href: '/student/dashboard', icon: Clock },
                    { name: 'Pengaturan', href: '/admin/settings', icon: Settings },
                ],
            },
        ];
    } else if (role === 'teacher') {
        return [
            {
                heading: 'MENU',
                items: [
                    { name: 'Dashboard Guru', href: '/teacher/dashboard', icon: LayoutDashboard },
                    { name: 'Bank Soal Ujian', href: '/teacher/banks', icon: BookOpen },
                    { name: 'Meja Koreksi', href: '/teacher/grading', icon: Award },
                ],
            },
            {
                heading: 'SYSTEM',
                items: [
                    { name: 'Ruang Pengawas', href: '/proctor/dashboard', icon: ShieldCheck },
                    { name: 'Bantuan Sistem', href: '#support', icon: HelpCircle },
                ],
            },
        ];
    } else if (role === 'proctor') {
        return [
            {
                heading: 'MENU',
                items: [
                    { name: 'Ruang Pengawas', href: '/proctor/dashboard', icon: ShieldCheck },
                ],
            },
            {
                heading: 'SYSTEM',
                items: [
                    { name: 'Pusat Bantuan', href: '#support', icon: HelpCircle },
                ],
            },
        ];
    } else if (role === 'student') {
        return [
            {
                heading: 'MENU',
                items: [
                    { name: 'Portal Ujian', href: '/student/dashboard', icon: Clock },
                ],
            },
            {
                heading: 'SYSTEM',
                items: [
                    { name: 'Bantuan Siswa', href: '#support', icon: HelpCircle },
                ],
            },
        ];
    }
    return [];
});

const roleLabel = computed(() => {
    const role = auth.value?.role;
    switch (role) {
        case 'admin': return 'Administrator';
        case 'teacher': return 'Guru Pengajar';
        case 'proctor': return 'Pengawas Ruang';
        case 'student': return 'Peserta Siswa';
        default: return 'Pengguna';
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
    if (href.startsWith('#')) return false;
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
    <div class="sidebar-root shrink-0 lg:w-64 lg:sticky lg:top-0 lg:h-screen">
        <!-- Mobile Sidebar Backdrop -->
        <div 
            v-if="isOpen" 
            @click="closeSidebar" 
            class="fixed inset-0 bg-slate-900/40 dark:bg-black/60 z-40 lg:hidden backdrop-blur-xs transition-opacity"
        />

        <!-- Modern Clean Sidebar matching contoh.jpg (EduPro aesthetic) -->
        <aside 
            :class="[
                'fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-slate-900 border-r border-slate-100 dark:border-slate-800 flex flex-col justify-between shrink-0 select-none shadow-[2px_0_15px_rgba(0,0,0,0.02)] transition-transform duration-200 lg:static lg:translate-x-0 h-full',
                isOpen ? 'translate-x-0' : '-translate-x-full'
            ]"
        >
            <!-- Top Section: Brand + Navigation -->
            <div class="flex-1 flex flex-col min-h-0 overflow-y-auto">
                <!-- Brand Logo & Header matching contoh.jpg (EduPro style) -->
                <div class="h-20 flex items-center justify-between px-6 border-b border-transparent">
                    <Link href="/" class="flex items-center gap-3 min-w-0 group">
                        <!-- Blue square with graduation cap icon -->
                        <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold shadow-sm shadow-blue-500/30 shrink-0 group-hover:scale-105 transition-transform">
                            <GraduationCap class="w-5 h-5 text-white" />
                        </div>
                        <div class="min-w-0">
                            <span class="text-lg font-bold text-slate-900 dark:text-white tracking-tight truncate block">
                                {{ school?.school_name ? school.school_name : 'EduPro' }}
                            </span>
                            <span class="text-[11px] text-slate-400 font-medium tracking-wide truncate block">
                                Smart CBT Platform
                            </span>
                        </div>
                    </Link>

                    <!-- Mobile Close Button -->
                    <button 
                        type="button"
                        @click="closeSidebar" 
                        class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer"
                        aria-label="Tutup Menu"
                    >
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <!-- Navigation Sections (MENU & SYSTEM) -->
                <div class="px-4 py-2 space-y-6 flex-1">
                    <div 
                        v-for="(section, sIdx) in menuSections" 
                        :key="sIdx"
                        class="space-y-1.5"
                    >
                        <!-- Section Header (e.g. MENU, SYSTEM) -->
                        <div class="px-3 pb-1">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                                {{ section.heading }}
                            </span>
                        </div>

                        <!-- Menu Items -->
                        <div class="space-y-1">
                            <Link
                                v-for="item in section.items"
                                :key="item.name"
                                :href="item.href"
                                @click="closeSidebar"
                                :class="[
                                    isNavActive(item.href)
                                        ? 'bg-blue-600 text-white font-medium shadow-sm shadow-blue-600/20'
                                        : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-800/60 font-medium',
                                    'flex items-center gap-3.5 px-3.5 py-2.5 text-sm rounded-xl transition-all duration-150 cursor-pointer'
                                ]"
                            >
                                <component 
                                    :is="item.icon" 
                                    :class="[
                                        'w-5 h-5 shrink-0 transition-colors',
                                        isNavActive(item.href) ? 'text-white' : 'text-slate-400 group-hover:text-slate-600 dark:text-slate-400'
                                    ]" 
                                />
                                <span class="truncate">{{ item.name }}</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Section: User Profile Card matching contoh.jpg + Logout Option -->
            <div class="p-4 border-t border-slate-100 dark:border-slate-800/80 bg-white dark:bg-slate-900">
                <div class="flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                    <div class="flex items-center gap-3 min-w-0">
                        <!-- Round Avatar matching Alex Morgan avatar in contoh.jpg -->
                        <div class="w-10 h-10 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs flex items-center justify-center shrink-0 border border-slate-300 dark:border-slate-600 shadow-2xs">
                            {{ userInitials }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-slate-900 dark:text-white truncate leading-snug">
                                {{ auth?.name || 'Pengguna' }}
                            </p>
                            <p class="text-xs text-slate-400 dark:text-slate-500 truncate">
                                {{ roleLabel }}
                            </p>
                        </div>
                    </div>

                    <!-- Logout Button Icon -->
                    <button
                        type="button"
                        @click="handleLogout"
                        title="Keluar dari sistem"
                        class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition-colors cursor-pointer"
                    >
                        <LogOut class="w-4 h-4" />
                    </button>
                </div>
            </div>
        </aside>
    </div>
</template>
