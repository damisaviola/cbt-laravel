<script setup>
import { Link } from '@inertiajs/vue3';
import { ArrowUpRight } from 'lucide-vue-next';

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    value: {
        type: [String, Number],
        required: true,
    },
    trend: {
        type: String,
        default: '', // e.g. '+12%' or '-2%'
    },
    trendType: {
        type: String,
        default: 'up', // 'up' | 'down' | 'neutral'
    },
    trendLabel: {
        type: String,
        default: 'Increased from last month',
    },
    isHero: {
        type: Boolean,
        default: false,
    },
    iconBg: {
        type: String,
        default: '',
    },
    iconColor: {
        type: String,
        default: '',
    },
    href: {
        type: String,
        default: null,
    },
});
</script>

<template>
    <!-- Card Root Container matching contoh.webp -->
    <component
        :is="href ? Link : 'div'"
        :href="href"
        :class="[
            'rounded-3xl p-5 sm:p-6 transition-all duration-200 flex flex-col justify-between shadow-2xs hover:shadow-xs group',
            href ? 'cursor-pointer hover:border-[#185338]/30 dark:hover:border-emerald-700/50' : '',
            isHero
                ? 'bg-gradient-to-br from-[#12422c] to-[#185338] text-white border-transparent'
                : 'bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 text-slate-900 dark:text-white'
        ]"
    >
        <!-- Top Row: Title on Left, Circular Diagonal Arrow Button on Right matching contoh.webp -->
        <div class="flex items-center justify-between gap-2">
            <span 
                :class="[
                    'text-xs sm:text-sm font-semibold truncate',
                    isHero ? 'text-emerald-100/90' : 'text-slate-800 dark:text-slate-200'
                ]"
            >
                {{ title }}
            </span>

            <!-- Circular Arrow Up-Right ↗ Button matching contoh.webp -->
            <div 
                :class="[
                    'w-7 h-7 sm:w-8 sm:h-8 rounded-full flex items-center justify-center shrink-0 transition-transform group-hover:scale-110 group-hover:translate-x-0.5 group-hover:-translate-y-0.5',
                    isHero 
                        ? 'border border-white/20 bg-white/10 text-white' 
                        : 'border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300'
                ]"
            >
                <ArrowUpRight class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
            </div>
        </div>

        <!-- Middle: Large Bold Value matching contoh.webp -->
        <div class="my-2 sm:my-3">
            <h3 
                :class="[
                    'text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight',
                    isHero ? 'text-white' : 'text-slate-900 dark:text-white'
                ]"
            >
                {{ value }}
            </h3>
        </div>

        <!-- Bottom: Trend or Status Badge Pill matching contoh.webp -->
        <div class="flex items-center gap-2 pt-1">
            <div 
                :class="[
                    'px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-md text-[11px] font-medium inline-flex items-center gap-1.5',
                    isHero 
                        ? 'bg-white/15 text-white' 
                        : 'border border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 bg-slate-50/60 dark:bg-slate-800/40'
                ]"
            >
                <!-- Small status / trend icon -->
                <span 
                    :class="[
                        'min-w-4 h-4 px-1 rounded flex items-center justify-center text-[10px] font-bold shrink-0',
                        isHero ? 'bg-white/20 text-white' : 'bg-slate-200/80 dark:bg-slate-700 text-slate-600 dark:text-slate-300'
                    ]"
                >
                    {{ trend || '↗' }}
                </span>
                <span class="truncate">{{ trendLabel || 'Updated live' }}</span>
            </div>
        </div>
    </component>
</template>
