<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import { 
    CheckCircle2, 
    Menu,
    AlertCircle,
    Search,
    Bell,
    Mail,
    ChevronRight,
    PanelLeftClose,
    PanelLeftOpen
} from 'lucide-vue-next';
import Sidebar from '@/Components/Sidebar.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import { useSidebar } from '@/Composables/useSidebar';

const props = defineProps({
    breadcrumbs: {
        type: Array,
        default: () => [
            { label: 'Home', href: '/' },
            { label: 'Dashboard', href: null }
        ]
    }
});

const page = usePage();
const auth = computed(() => page.props.auth?.user);

const { sidebarOpen, toggleSidebar, setSidebarOpen } = useSidebar();
const showSearchModal = ref(false);
const searchQuery = ref('');

let lastIsMobile = false;

const handleKeyDown = (e) => {
    // Ctrl+B or Cmd+B to toggle sidebar
    if ((e.ctrlKey || e.metaKey) && (e.key === 'b' || e.key === 'B')) {
        e.preventDefault();
        toggleSidebar();
    }
};

const handleResize = () => {
    if (typeof window === 'undefined') return;
    const isMobile = window.innerWidth < 1024;
    if (isMobile !== lastIsMobile) {
        lastIsMobile = isMobile;
        if (isMobile) {
            setSidebarOpen(false);
        } else {
            try {
                const saved = localStorage.getItem('smartcbt_sidebar_open');
                setSidebarOpen(saved !== null ? saved === 'true' : true);
            } catch (e) {
                setSidebarOpen(true);
            }
        }
    }
};

onMounted(() => {
    if (typeof window !== 'undefined') {
        lastIsMobile = window.innerWidth < 1024;
        window.addEventListener('keydown', handleKeyDown);
        window.addEventListener('resize', handleResize);
    }
});

onUnmounted(() => {
    if (typeof window !== 'undefined') {
        window.removeEventListener('keydown', handleKeyDown);
        window.removeEventListener('resize', handleResize);
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
</script>

<template>
    <div class="min-h-screen flex bg-[#F4F5F6] dark:bg-slate-950 text-slate-900 dark:text-slate-100 font-sans antialiased transition-colors duration-150">
        <!-- Sidebar Navigation matching contoh.webp -->
        <Sidebar 
            :is-open="sidebarOpen" 
            @close="setSidebarOpen(false)" 
            @update:is-open="val => setSidebarOpen(val)" 
        />

        <!-- Main Workspace Canvas -->
        <main class="flex-1 flex flex-col min-w-0 overflow-y-auto bg-[#F4F5F6] dark:bg-slate-950 transition-all duration-300">
            <!-- Header Topbar matching contoh.webp -->
            <header class="h-20 bg-transparent px-6 sm:px-8 flex items-center justify-between gap-4 shrink-0">
                <!-- Left: Sidebar Toggle & Wide Search Task Pill Input matching contoh.webp -->
                <div class="flex items-center gap-3 flex-1 max-w-lg min-w-0">
                    <button
                        @click="toggleSidebar"
                        class="p-2.5 rounded-full bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white shadow-2xs shrink-0 cursor-pointer transition-all hover:scale-105 active:scale-95"
                        :title="sidebarOpen ? 'Tutup Sidebar (Ctrl+B)' : 'Buka Sidebar (Ctrl+B)'"
                        :aria-label="sidebarOpen ? 'Tutup Sidebar' : 'Buka Sidebar'"
                    >
                        <PanelLeftClose v-if="sidebarOpen" class="w-5 h-5 hidden lg:block" />
                        <PanelLeftOpen v-if="!sidebarOpen" class="w-5 h-5 hidden lg:block" />
                        <Menu class="w-5 h-5 lg:hidden" />
                    </button>

                    <!-- Rounded-Full Search Input matching contoh.webp -->
                    <div 
                        @click="showSearchModal = true"
                        class="w-full max-w-md h-11 px-4 rounded-full bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 flex items-center justify-between text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 shadow-2xs cursor-pointer transition-all"
                    >
                        <div class="flex items-center gap-2.5 min-w-0">
                            <Search class="w-4 h-4 text-slate-400 shrink-0" />
                            <span class="text-xs text-slate-400 select-none truncate">Search task...</span>
                        </div>
                        <kbd class="hidden sm:inline-flex items-center px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-[10px] font-mono text-slate-400 border border-slate-200/60 dark:border-slate-700">
                            ⌘F
                        </kbd>
                    </div>
                </div>

                <!-- Right Action Controls matching contoh.webp -->
                <div class="flex items-center gap-2.5 sm:gap-3 shrink-0">
                    <!-- Mail / Message Icon Button (Circular) matching contoh.webp -->
                    <button
                        type="button"
                        @click="showSearchModal = true"
                        title="Pesan & Pengumuman"
                        class="w-10 h-10 rounded-full bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white flex items-center justify-center shadow-2xs transition-all cursor-pointer"
                    >
                        <Mail class="w-4 h-4" />
                    </button>

                    <!-- Notification Bell Button with Indicator (Circular) matching contoh.webp -->
                    <button
                        type="button"
                        title="Pemberitahuan Sistem"
                        class="relative w-10 h-10 rounded-full bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white flex items-center justify-center shadow-2xs transition-all cursor-pointer"
                    >
                        <Bell class="w-4 h-4" />
                        <span class="absolute top-2 right-2 w-2 h-2 rounded-full bg-rose-500 ring-2 ring-white dark:ring-slate-900"></span>
                    </button>

                    <!-- Theme Switcher (Circular) -->
                    <div class="w-10 h-10 rounded-full bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 flex items-center justify-center shadow-2xs">
                        <ThemeToggle compact />
                    </div>

                    <!-- User Profile Card matching Totok Michael in contoh.webp -->
                    <div class="hidden sm:flex items-center gap-3 pl-2 border-l border-slate-200/60 dark:border-slate-800">
                        <div class="w-10 h-10 rounded-full bg-[#185338] text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-2xs ring-2 ring-emerald-500/20">
                            {{ userInitials }}
                        </div>
                        <div class="min-w-0 text-left">
                            <p class="text-xs font-bold text-slate-900 dark:text-white truncate leading-snug">
                                {{ auth?.name || 'Totok Michael' }}
                            </p>
                            <p class="text-[10px] text-slate-400 dark:text-slate-500 truncate">
                                {{ auth?.email || 'admin@smartcbt.sch.id' }}
                            </p>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Flash Alerts -->
            <div v-if="$page.props.flash?.success" class="mx-6 sm:mx-8 mb-4 p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/80 rounded-2xl text-emerald-900 dark:text-emerald-200 text-sm font-medium flex items-center gap-3 shadow-2xs">
                <CheckCircle2 class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0" />
                <span class="flex-1">{{ $page.props.flash.success }}</span>
            </div>

            <div v-if="$page.props.flash?.error" class="mx-6 sm:mx-8 mb-4 p-4 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800/80 rounded-2xl text-red-900 dark:text-red-200 text-xs font-medium flex items-center gap-3 shadow-2xs">
                <AlertCircle class="w-5 h-5 text-red-600 dark:text-red-400 shrink-0" />
                <span class="flex-1">{{ $page.props.flash.error }}</span>
            </div>

            <div v-if="$page.props.flash?.warning" class="mx-6 sm:mx-8 mb-4 p-4 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/80 rounded-2xl text-amber-900 dark:text-amber-200 text-xs font-medium flex items-center gap-3 shadow-2xs">
                <AlertCircle class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0" />
                <span class="flex-1">{{ $page.props.flash.warning }}</span>
            </div>

            <!-- Page Content Slot -->
            <div class="px-6 sm:px-8 pb-10 flex-1">
                <slot />
            </div>
        </main>

        <!-- Quick Search Modal (Lightweight) -->
        <div 
            v-if="showSearchModal" 
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-xs"
            @click.self="showSearchModal = false"
        >
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-2xl max-w-lg w-full space-y-4">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <Search class="w-5 h-5 text-slate-400" />
                    <input 
                        v-model="searchQuery" 
                        type="text" 
                        placeholder="Cari siswa, jadwal, soal ujian, atau menu..." 
                        class="w-full text-sm bg-transparent border-none focus:outline-none text-slate-900 dark:text-white placeholder:text-slate-400"
                        autofocus
                    />
                    <button @click="showSearchModal = false" class="text-[11px] font-bold text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 px-2 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg cursor-pointer">
                        ESC
                    </button>
                </div>
                <div class="text-xs text-slate-400 space-y-2.5">
                    <p class="font-bold text-slate-500 uppercase tracking-wider text-[10px]">Pintas Navigasi</p>
                    <div class="grid grid-cols-2 gap-2 text-slate-700 dark:text-slate-300">
                        <Link href="/admin/students" @click="showSearchModal = false" class="p-2.5 rounded-2xl hover:bg-slate-50 dark:hover:bg-slate-800/60 border border-transparent hover:border-slate-200/80 dark:hover:border-slate-700 flex items-center gap-2 transition-all">
                            <span>Siswa & Kartu</span>
                        </Link>
                        <Link href="/admin/schedules" @click="showSearchModal = false" class="p-2.5 rounded-2xl hover:bg-slate-50 dark:hover:bg-slate-800/60 border border-transparent hover:border-slate-200/80 dark:hover:border-slate-700 flex items-center gap-2 transition-all">
                            <span>Jadwal Ujian</span>
                        </Link>
                        <Link href="/proctor/dashboard" @click="showSearchModal = false" class="p-2.5 rounded-2xl hover:bg-slate-50 dark:hover:bg-slate-800/60 border border-transparent hover:border-slate-200/80 dark:hover:border-slate-700 flex items-center gap-2 transition-all">
                            <span>Ruang Pengawas</span>
                        </Link>
                        <Link href="/teacher/banks" @click="showSearchModal = false" class="p-2.5 rounded-2xl hover:bg-slate-50 dark:hover:bg-slate-800/60 border border-transparent hover:border-slate-200/80 dark:hover:border-slate-700 flex items-center gap-2 transition-all">
                            <span>Bank Soal</span>
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Global Action Confirmation Modal -->
        <ConfirmModal />
    </div>
</template>
