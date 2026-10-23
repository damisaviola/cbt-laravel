<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import { 
    CheckCircle2, 
    Menu,
    AlertCircle,
    Search,
    Bell,
    ChevronRight
} from 'lucide-vue-next';
import Sidebar from '@/Components/Sidebar.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';

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

const mobileMenuOpen = ref(false);
const showSearchModal = ref(false);
const searchQuery = ref('');

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
    <div class="min-h-screen flex bg-[#F4F6FA] dark:bg-slate-950 text-slate-900 dark:text-slate-100 font-sans antialiased transition-colors duration-150">
        <!-- Sidebar Navigation matching contoh.jpg -->
        <Sidebar :is-open="mobileMenuOpen" @close="mobileMenuOpen = false" />

        <!-- Main Workspace Canvas -->
        <main class="flex-1 flex flex-col min-w-0 overflow-y-auto bg-[#F4F6FA] dark:bg-slate-950">
            <!-- Clean Header Topbar matching contoh.jpg -->
            <header class="h-20 bg-transparent px-6 sm:px-8 flex items-center justify-between shrink-0">
                <!-- Left: Mobile Menu Toggle & Breadcrumbs -->
                <div class="flex items-center gap-3 min-w-0">
                    <button
                        @click="mobileMenuOpen = true"
                        class="lg:hidden p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 text-slate-600 dark:text-slate-300 hover:text-slate-900 shadow-2xs"
                        aria-label="Buka Menu"
                    >
                        <Menu class="w-5 h-5" />
                    </button>

                    <!-- Breadcrumbs matching 'Home > Dashboard' in contoh.jpg -->
                    <slot name="breadcrumbs">
                        <nav class="flex items-center gap-2 text-sm text-slate-400 dark:text-slate-500 font-medium select-none">
                            <span class="hover:text-slate-600 dark:hover:text-slate-300 transition-colors">Home</span>
                            <ChevronRight class="w-3.5 h-3.5 text-slate-400" />
                            <span class="text-slate-900 dark:text-white font-semibold">Dashboard</span>
                        </nav>
                    </slot>
                </div>

                <!-- Right Action Icons: Search, ThemeToggle, Notifications, User Avatar -->
                <div class="flex items-center gap-2.5 sm:gap-3 shrink-0">
                    <!-- Search Icon Button -->
                    <button
                        type="button"
                        @click="showSearchModal = true"
                        title="Pencarian Cepat"
                        class="w-10 h-10 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:border-slate-300 dark:hover:border-slate-700 flex items-center justify-center shadow-2xs transition-all cursor-pointer"
                    >
                        <Search class="w-4 h-4" />
                    </button>

                    <!-- Theme Switcher (Rounded) -->
                    <div class="w-10 h-10 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 flex items-center justify-center shadow-2xs">
                        <ThemeToggle compact />
                    </div>

                    <!-- Notification Bell with Active Indicator Dot -->
                    <button
                        type="button"
                        title="Pemberitahuan"
                        class="relative w-10 h-10 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:border-slate-300 dark:hover:border-slate-700 flex items-center justify-center shadow-2xs transition-all cursor-pointer"
                    >
                        <Bell class="w-4 h-4" />
                        <!-- Red active dot indicator -->
                        <span class="absolute top-2.5 right-2.5 w-2 h-2 rounded-full bg-rose-500 ring-2 ring-white dark:ring-slate-900"></span>
                    </button>

                    <!-- User Profile Avatar Circle matching Alex Morgan in contoh.jpg -->
                    <div class="w-10 h-10 rounded-full bg-slate-200 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 flex items-center justify-center font-bold text-xs text-slate-700 dark:text-slate-200 shadow-2xs ml-1">
                        {{ userInitials }}
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
