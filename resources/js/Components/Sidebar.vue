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
    ChevronRight,
    PanelLeftClose
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

const handleNavClick = () => {
    if (typeof window !== 'undefined' && window.innerWidth < 1024) {
        closeSidebar();
    }
};
</script>

<template>
    <div 
        :class="[
            'sidebar-root shrink-0 lg:sticky lg:top-0 lg:h-screen transition-[width] duration-300 ease-in-out',
            isOpen ? 'lg:w-64' : 'lg:w-20'
        ]"
    >
        <!-- Mobile Sidebar Backdrop -->
        <div 
            v-if="isOpen" 
            @click="closeSidebar" 
            class="fixed inset-0 bg-slate-900/40 dark:bg-black/60 z-40 lg:hidden backdrop-blur-xs transition-opacity"
        />

        <!-- Modern Clean Sidebar matching contoh.webp -->
        <aside 
            :class="[
                'fixed inset-y-0 left-0 z-50 bg-white dark:bg-slate-900 dark:border-r dark:border-slate-800/80 flex flex-col justify-between shrink-0 select-none shadow-[2px_0_15px_rgba(0,0,0,0.02)] transition-all duration-300 ease-in-out lg:static lg:translate-x-0 h-full',
                isOpen 
                    ? 'w-64 translate-x-0 lg:w-64' 
                    : 'w-64 -translate-x-full lg:w-20 lg:translate-x-0'
            ]"
        >
            <!-- Top Section: Brand + Navigation -->
            <div class="flex-1 flex flex-col min-h-0 overflow-y-auto overflow-x-hidden">
                <!-- Brand Logo & Header matching Donezo logo in contoh.webp -->
                <div 
                    :class="[
                        'h-20 flex items-center shrink-0 transition-all duration-300',
                        isOpen ? 'justify-between px-6' : 'justify-center px-2'
                    ]"
                >
                    <Link 
                        href="/" 
                        :class="['flex items-center min-w-0 group', isOpen ? 'gap-3' : 'justify-center']"
                        :title="school?.school_name || 'SmartCBT'"
                    >
                        <!-- Forest Green loop logo mark matching contoh.webp -->
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 text-[#185338] dark:text-emerald-400 transition-transform group-hover:scale-105">
                            <svg class="w-8 h-8" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="16" cy="16" r="13" stroke="currentColor" stroke-width="2.75" />
                                <circle cx="16" cy="16" r="8" stroke="currentColor" stroke-width="2.75" />
                                <circle cx="16" cy="16" r="3" fill="currentColor" />
                            </svg>
                        </div>
                        <div v-if="isOpen" class="min-w-0">
                            <span class="text-xl font-extrabold text-slate-900 dark:text-white tracking-tight truncate block">
                                {{ school?.school_name ? school.school_name.split(' ')[0] : 'SmartCBT' }}
                            </span>
                        </div>
                    </Link>

                    <!-- Close/Collapse Button (when expanded) -->
                    <button 
                        v-if="isOpen"
                        type="button"
                        @click="closeSidebar" 
                        class="p-2 rounded-xl text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                        title="Tutup Sidebar (Ctrl+B)"
                        aria-label="Tutup Sidebar"
                    >
                        <PanelLeftClose class="w-5 h-5 hidden lg:block" />
                        <X class="w-5 h-5 lg:hidden" />
                    </button>
                </div>

                <!-- Navigation Sections (MENU & GENERAL matching contoh.webp) -->
                <div 
                    :class="[
                        'py-2 flex-1 overflow-y-auto overflow-x-hidden transition-all duration-300',
                        isOpen ? 'px-3 space-y-6' : 'px-2 space-y-4'
                    ]"
                >
                    <div 
                        v-for="(section, sIdx) in menuSections" 
                        :key="sIdx"
                        class="space-y-1"
                    >
                        <!-- Section Header (Expanded) -->
                        <div v-if="isOpen" class="px-4 pb-1">
                            <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                                {{ section.heading === 'SYSTEM' ? 'GENERAL' : section.heading }}
                            </span>
                        </div>
                        <!-- Section Divider (Collapsed) -->
                        <div v-else-if="sIdx > 0" class="my-2 mx-2 border-t border-slate-100 dark:border-slate-800" />

                        <!-- Menu Items -->
                        <div class="space-y-1">
                            <Link
                                v-for="item in section.items"
                                :key="item.name"
                                :href="item.href"
                                @click="handleNavClick"
                                :title="!isOpen ? item.name : undefined"
                                :class="[
                                    'relative flex items-center rounded-xl transition-all duration-150 cursor-pointer group',
                                    isOpen 
                                        ? (isNavActive(item.href)
                                            ? 'justify-between px-4 py-2.5 text-sm text-slate-900 dark:text-white font-bold'
                                            : 'justify-between px-4 py-2.5 text-sm text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-800/60 font-medium')
                                        : (isNavActive(item.href)
                                            ? 'justify-center w-11 h-11 mx-auto bg-[#eaf4ef] dark:bg-emerald-950/60 text-[#185338] dark:text-emerald-400 font-bold shadow-2xs'
                                            : 'justify-center w-11 h-11 mx-auto text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100/80 dark:hover:bg-slate-800/60 font-medium')
                                ]"
                            >
                                <!-- Expanded item layout -->
                                <template v-if="isOpen">
                                    <div class="flex items-center gap-3.5 min-w-0">
                                        <!-- Left Active Vertical Pill Bar Indicator matching contoh.webp -->
                                        <span 
                                            v-if="isNavActive(item.href)" 
                                            class="absolute left-0 top-1/2 -translate-y-1/2 w-1.25 h-6 rounded-r-full bg-[#185338] dark:bg-emerald-400"
                                        />

                                        <component 
                                            :is="item.icon" 
                                            :class="[
                                                'w-5 h-5 shrink-0 transition-colors',
                                                isNavActive(item.href) 
                                                    ? 'text-[#185338] dark:text-emerald-400' 
                                                    : 'text-slate-400 group-hover:text-slate-700 dark:text-slate-400'
                                            ]" 
                                        />
                                        <span class="truncate">{{ item.name }}</span>
                                    </div>

                                    <!-- Pill Badge for Tasks/Exams matching 12+ badge in contoh.webp -->
                                    <span 
                                        v-if="item.name === 'Jadwal Ujian' || item.name === 'Tasks'" 
                                        class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-900 text-white dark:bg-slate-800 dark:text-slate-300 shrink-0"
                                    >
                                        Live
                                    </span>
                                </template>

                                <!-- Collapsed icon-only layout -->
                                <template v-else>
                                    <component 
                                        :is="item.icon" 
                                        :class="[
                                            'w-5 h-5 shrink-0 transition-colors',
                                            isNavActive(item.href) 
                                                ? 'text-[#185338] dark:text-emerald-400' 
                                                : 'text-slate-500 group-hover:text-slate-900 dark:text-slate-400 dark:group-hover:text-white'
                                        ]" 
                                    />

                                    <!-- Left Active Indicator Pill in Collapsed Mode -->
                                    <span 
                                        v-if="isNavActive(item.href)" 
                                        class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 rounded-r-full bg-[#185338] dark:bg-emerald-400"
                                    />

                                    <!-- Pulsing Live Dot on Collapsed Item -->
                                    <span 
                                        v-if="item.name === 'Jadwal Ujian' || item.name === 'Tasks'" 
                                        class="absolute top-2 right-2 w-2 h-2 rounded-full bg-emerald-500 ring-2 ring-white dark:ring-slate-900 animate-pulse"
                                    />

                                    <!-- Floating Tooltip Flyout on Hover -->
                                    <span class="fixed left-20 ml-2 px-2.5 py-1 bg-slate-900 text-white text-xs font-semibold rounded-lg shadow-xl opacity-0 pointer-events-none group-hover:opacity-100 transition-opacity z-50 whitespace-nowrap hidden lg:block">
                                        {{ item.name }}
                                    </span>
                                </template>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Bottom Forest Green Mobile App Card -->
                <div :class="['shrink-0 transition-all duration-300', isOpen ? 'p-4' : 'p-2 flex justify-center']">
                    <!-- Expanded Card -->
                    <div v-if="isOpen" class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#0c2e1f] via-[#103a27] to-[#185338] p-4 text-white shadow-sm border border-emerald-950/20">
                        <!-- Organic wave lines overlay -->
                        <div class="absolute -right-4 -bottom-6 w-28 h-28 rounded-full border border-white/10 pointer-events-none"></div>
                        <div class="absolute -right-8 -bottom-10 w-36 h-36 rounded-full border border-white/5 pointer-events-none"></div>
                        
                        <!-- Top Icon inside small translucent circle -->
                        <div class="w-7 h-7 rounded-full bg-white/15 backdrop-blur-xs flex items-center justify-center text-white mb-2.5">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <rect x="5" y="2" width="14" height="20" rx="3" />
                                <circle cx="12" cy="18" r="1" fill="currentColor" />
                            </svg>
                        </div>

                        <!-- Card text -->
                        <h4 class="text-xs font-bold text-white tracking-tight">
                            Download our Mobile App
                        </h4>
                        <p class="text-[10px] text-emerald-100/75 mt-0.5 leading-relaxed">
                            Get easy in another way
                        </p>

                        <!-- Pill Download Button matching contoh.webp -->
                        <a
                            href="/proctor/dashboard"
                            class="mt-3 block w-full py-1.5 bg-[#237751] hover:bg-[#2c8d61] text-white text-xs font-semibold rounded-full text-center transition-all shadow-xs"
                        >
                            Download
                        </a>
                    </div>

                    <!-- Collapsed Icon View -->
                    <a
                        v-else
                        href="/proctor/dashboard"
                        title="Download our Mobile App"
                        class="w-11 h-11 rounded-2xl bg-gradient-to-br from-[#0c2e1f] via-[#103a27] to-[#185338] text-white flex items-center justify-center shadow-xs hover:scale-105 transition-transform"
                    >
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <rect x="5" y="2" width="14" height="20" rx="3" />
                            <circle cx="12" cy="18" r="1" fill="currentColor" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- User Profile Row at bottom with Logout Option -->
            <div :class="['border-t border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 shrink-0 transition-all duration-300', isOpen ? 'p-4' : 'p-3 flex flex-col items-center gap-2']">
                <!-- Expanded User Profile -->
                <div v-if="isOpen" class="flex items-center justify-between p-1.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                    <div class="flex items-center gap-3 min-w-0">
                        <!-- Round Avatar matching Totok Michael avatar in contoh.webp -->
                        <div class="w-9 h-9 rounded-full bg-[#eaf4ef] dark:bg-emerald-950 text-[#185338] dark:text-emerald-300 font-bold text-xs flex items-center justify-center shrink-0 border border-emerald-200/60 dark:border-emerald-800 shadow-2xs">
                            {{ userInitials }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-slate-900 dark:text-white truncate leading-snug">
                                {{ auth?.name || 'Pengguna' }}
                            </p>
                            <p class="text-[10px] text-slate-400 dark:text-slate-500 truncate">
                                {{ auth?.email || roleLabel }}
                            </p>
                        </div>
                    </div>

                    <!-- Logout Button Icon -->
                    <button
                        type="button"
                        @click="handleLogout"
                        title="Keluar dari sistem"
                        class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition-colors cursor-pointer"
                    >
                        <LogOut class="w-4 h-4" />
                    </button>
                </div>

                <!-- Collapsed User Profile -->
                <template v-else>
                    <div 
                        :title="auth?.name ? `${auth.name} (${roleLabel})` : 'Profil Pengguna'" 
                        class="w-9 h-9 rounded-full bg-[#eaf4ef] dark:bg-emerald-950 text-[#185338] dark:text-emerald-300 font-bold text-xs flex items-center justify-center shrink-0 border border-emerald-200/60 dark:border-emerald-800 shadow-2xs cursor-default"
                    >
                        {{ userInitials }}
                    </div>
                    <button
                        type="button"
                        @click="handleLogout"
                        title="Keluar dari sistem"
                        class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-xl transition-colors cursor-pointer"
                    >
                        <LogOut class="w-4 h-4" />
                    </button>
                </template>
            </div>
        </aside>
    </div>
</template>
