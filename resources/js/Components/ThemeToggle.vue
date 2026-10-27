<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useTheme } from '@/Composables/useTheme';
import { Sun, Moon, Laptop, Check } from 'lucide-vue-next';

const props = defineProps({
    compact: {
        type: Boolean,
        default: false,
    },
    align: {
        type: String,
        default: 'right', // 'left' | 'right'
    },
});

const { theme, isDark, setTheme, toggleTheme } = useTheme();
const isOpen = ref(false);
const dropdownRef = ref(null);

const options = [
    { value: 'light', label: 'Terang', icon: Sun },
    { value: 'dark', label: 'Gelap', icon: Moon },
    { value: 'system', label: 'Sistem', icon: Laptop },
];

const handleButtonClick = () => {
    if (props.compact) {
        toggleTheme();
    } else {
        isOpen.value = !isOpen.value;
    }
};

const selectTheme = (value) => {
    setTheme(value);
    isOpen.value = false;
};

const handleClickOutside = (e) => {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
        isOpen.value = false;
    }
};

const handleKeyDown = (e) => {
    if (e.key === 'Escape' && isOpen.value) {
        isOpen.value = false;
    }
};

onMounted(() => {
    if (typeof window !== 'undefined') {
        document.addEventListener('click', handleClickOutside);
        document.addEventListener('keydown', handleKeyDown);
    }
});

onUnmounted(() => {
    if (typeof window !== 'undefined') {
        document.removeEventListener('click', handleClickOutside);
        document.removeEventListener('keydown', handleKeyDown);
    }
});
</script>

<template>
    <div ref="dropdownRef" class="relative inline-flex items-center text-left">
        <!-- Toggle Button -->
        <button
            type="button"
            @click.stop="handleButtonClick"
            :title="compact ? (isDark ? 'Beralih ke Mode Terang' : 'Beralih ke Mode Gelap') : 'Pilih Tema Tampilan'"
            aria-label="Pilih Tema Tampilan"
            :class="[
                'p-2 rounded-full border transition-all duration-150 cursor-pointer flex items-center justify-center shrink-0 active:scale-95',
                'bg-slate-50 dark:bg-slate-800/80 border-slate-200 dark:border-slate-700/80 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 shadow-2xs'
            ]"
        >
            <Sun v-if="theme === 'light'" class="w-4 h-4 text-amber-500 transition-transform duration-200 rotate-0 scale-100" />
            <Moon v-else-if="theme === 'dark'" class="w-4 h-4 text-emerald-400 transition-transform duration-200 rotate-0 scale-100" />
            <Laptop v-else class="w-4 h-4 text-slate-500 dark:text-slate-400 transition-transform duration-200" />
        </button>

        <!-- Dropdown Menu (For full mode) -->
        <Transition
            enter-active-class="transition duration-100 ease-out"
            enter-from-class="opacity-0 scale-95 -translate-y-1"
            enter-to-class="opacity-100 scale-100 translate-y-0"
            leave-active-class="transition duration-75 ease-in"
            leave-from-class="opacity-100 scale-100 translate-y-0"
            leave-to-class="opacity-0 scale-95 -translate-y-1"
        >
            <div
                v-if="!compact && isOpen"
                :class="[
                    'absolute top-full mt-2 w-36 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 shadow-xl p-1.5 z-50 text-xs font-semibold select-none',
                    align === 'left' ? 'left-0 origin-top-left' : 'right-0 origin-top-right'
                ]"
            >
                <div class="px-2 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                    Mode Tampilan
                </div>

                <button
                    v-for="opt in options"
                    :key="opt.value"
                    type="button"
                    @click="selectTheme(opt.value)"
                    :class="[
                        'w-full flex items-center justify-between px-2.5 py-1.5 rounded-xl transition-colors cursor-pointer text-left',
                        theme === opt.value
                            ? 'bg-[#eaf4ef] dark:bg-[#185338]/20 text-[#185338] dark:text-emerald-400 font-bold'
                            : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'
                    ]"
                >
                    <div class="flex items-center gap-2">
                        <component
                            :is="opt.icon"
                            :class="[
                                'w-3.5 h-3.5',
                                opt.value === 'light' ? 'text-amber-500' : opt.value === 'dark' ? 'text-emerald-500' : 'text-slate-500 dark:text-slate-400'
                            ]"
                        />
                        <span>{{ opt.label }}</span>
                    </div>
                    <Check v-if="theme === opt.value" class="w-3.5 h-3.5 text-[#185338] dark:text-emerald-400" />
                </button>
            </div>
        </Transition>
    </div>
</template>
