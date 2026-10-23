<script setup>
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
        default: '',
    },
    iconBg: {
        type: String,
        default: 'bg-blue-50 dark:bg-blue-950/60',
    },
    iconColor: {
        type: String,
        default: 'text-blue-600 dark:text-blue-400',
    },
});
</script>

<template>
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-100 dark:border-slate-800 shadow-sm hover:shadow transition-all duration-150 flex flex-col justify-between">
        <!-- Top Row: Icon Container on Left, Trend Badge Pill on Right -->
        <div class="flex items-center justify-between mb-4">
            <!-- Pastel Rounded Icon Box -->
            <div :class="['w-10 h-10 rounded-xl flex items-center justify-center shrink-0', iconBg, iconColor]">
                <slot name="icon">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </slot>
            </div>

            <!-- Trend Badge Pill matching contoh.jpg -->
            <div 
                v-if="trend" 
                :class="[
                    'px-2.5 py-0.5 rounded-full text-xs font-semibold flex items-center gap-1',
                    trendType === 'down' 
                        ? 'bg-rose-50 text-rose-500 dark:bg-rose-950/50 dark:text-rose-400' 
                        : 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400'
                ]"
            >
                <!-- Upward Arrow -->
                <svg v-if="trendType !== 'down'" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25" />
                </svg>
                <!-- Downward Arrow -->
                <svg v-else class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 4.5l-15 15m0 0h11.25m-11.25 0V8.25" />
                </svg>
                <span>{{ trend }}</span>
                <span v-if="trendLabel" class="font-normal opacity-80 text-[10px] hidden sm:inline">{{ trendLabel }}</span>
            </div>
        </div>

        <!-- Bottom: Label and Large Bold Value -->
        <div>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1 truncate">
                {{ title }}
            </p>
            <div class="flex items-baseline justify-between gap-2">
                <h3 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                    {{ value }}
                </h3>
                <slot name="extra" />
            </div>
        </div>
    </div>
</template>
