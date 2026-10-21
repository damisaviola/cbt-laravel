<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { 
    CheckCircle2, 
    Clock,
    Menu,
    AlertCircle
} from 'lucide-vue-next';
import Sidebar from '@/Components/Sidebar.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';

const page = usePage();
const auth = computed(() => page.props.auth?.user);

const mobileMenuOpen = ref(false);
const currentTime = ref('');

let clockInterval = null;
const updateClock = () => {
    const now = new Date();
    currentTime.value = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB';
};

onMounted(() => {
    updateClock();
    clockInterval = setInterval(updateClock, 1000);
});

onUnmounted(() => {
    if (clockInterval) clearInterval(clockInterval);
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
    <div class="min-h-screen flex bg-[#F8FAFC] dark:bg-slate-950 text-slate-900 dark:text-slate-100 font-sans antialiased transition-colors duration-150">
        <!-- Dedicated Modular Sidebar Component -->
        <Sidebar :is-open="mobileMenuOpen" @close="mobileMenuOpen = false" />

        <!-- Main Workspace Canvas -->
        <main class="flex-1 flex flex-col min-w-0 overflow-y-auto bg-[#F8FAFC] dark:bg-slate-950">
            <!-- Clean Header Topbar -->
            <header class="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 px-6 flex items-center justify-between shrink-0 shadow-2xs transition-colors duration-150">
                <div class="flex items-center gap-3.5 min-w-0">
                    <button
                        @click="mobileMenuOpen = true"
                        class="lg:hidden p-2 rounded-xl text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800"
                        aria-label="Buka Menu"
                    >
                        <Menu class="w-4.5 h-4.5" />
                    </button>

                    <slot name="header">
                        <div>
                            <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wide">
                                SmartCBT School Edition
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Sistem Ujian Berbasis Komputer & Evaluasi Terpadu</p>
                        </div>
                    </slot>
                </div>

                <div class="flex items-center gap-2.5 sm:gap-3 shrink-0">
                    <!-- Live Clock Widget (Rounded) -->
                    <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/80 text-sm font-mono text-slate-700 dark:text-slate-300">
                        <Clock class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" />
                        <span>{{ currentTime }}</span>
                    </div>

                    <!-- Theme Switcher -->
                    <ThemeToggle />

                    <!-- User Identity Badge (Rounded) -->
                    <div class="flex items-center gap-2 pl-2 border-l border-slate-200 dark:border-slate-800">
                        <div class="hidden md:block text-right">
                            <p class="text-sm font-bold text-slate-900 dark:text-white leading-tight">{{ auth?.name }}</p>
                            <p class="text-xs font-mono text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ auth?.username }}</p>
                        </div>
                        <div class="w-8 h-8 rounded-xl bg-blue-600 text-white font-bold text-sm flex items-center justify-center shadow-xs">
                            {{ userInitials }}
                        </div>
                    </div>
                </div>
            </header>

            <!-- Flash Alerts (Rounded) -->
            <div v-if="$page.props.flash?.success" class="mx-6 mt-4 p-3.5 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/80 rounded-xl text-emerald-900 dark:text-emerald-200 text-sm font-medium flex items-center gap-2.5 shadow-2xs">
                <CheckCircle2 class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" />
                <span class="flex-1">{{ $page.props.flash.success }}</span>
            </div>

            <div v-if="$page.props.flash?.error" class="mx-6 mt-4 p-3.5 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800/80 rounded-xl text-red-900 dark:text-red-200 text-xs font-medium flex items-center gap-2.5 shadow-2xs">
                <AlertCircle class="w-4 h-4 text-red-600 dark:text-red-400 shrink-0" />
                <span class="flex-1">{{ $page.props.flash.error }}</span>
            </div>

            <div v-if="$page.props.flash?.warning" class="mx-6 mt-4 p-3.5 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/80 rounded-xl text-amber-900 dark:text-amber-200 text-xs font-medium flex items-center gap-2.5 shadow-2xs">
                <AlertCircle class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0" />
                <span class="flex-1">{{ $page.props.flash.warning }}</span>
            </div>

            <!-- Page Content -->
            <div class="p-6 md:p-8 flex-1">
                <slot />
            </div>
        </main>

        <!-- Global Action Confirmation Modal -->
        <ConfirmModal />
    </div>
</template>
