<script setup>
const props = defineProps({
    title: {
        type: String,
        default: 'Top Performing Categories',
    },
    subtitle: {
        type: String,
        default: '',
    },
    categories: {
        type: Array,
        default: () => [
            {
                name: 'Web Development',
                percent: 45,
                color: 'bg-blue-600',
                iconBg: 'bg-blue-50 dark:bg-blue-950/60',
                iconColor: 'text-blue-600 dark:text-blue-400',
                iconType: 'code',
            },
            {
                name: 'Design',
                percent: 28,
                color: 'bg-amber-400',
                iconBg: 'bg-amber-50 dark:bg-amber-950/60',
                iconColor: 'text-amber-600 dark:text-amber-400',
                iconType: 'pen',
            },
            {
                name: 'Business',
                percent: 18,
                color: 'bg-emerald-500',
                iconBg: 'bg-emerald-50 dark:bg-emerald-950/60',
                iconColor: 'text-emerald-600 dark:text-emerald-400',
                iconType: 'briefcase',
            },
            {
                name: 'Marketing',
                percent: 9,
                color: 'bg-rose-500',
                iconBg: 'bg-rose-50 dark:bg-rose-950/60',
                iconColor: 'text-rose-500 dark:text-rose-400',
                iconType: 'megaphone',
            },
        ],
    },
});
</script>

<template>
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm transition-colors duration-150 flex flex-col justify-between">
        <div>
            <!-- Header -->
            <div class="mb-5">
                <h3 class="text-base font-bold text-slate-900 dark:text-white tracking-tight">
                    {{ title }}
                </h3>
                <p v-if="subtitle" class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    {{ subtitle }}
                </p>
            </div>

            <!-- Categories List -->
            <div class="space-y-4">
                <div 
                    v-for="(item, idx) in categories" 
                    :key="idx"
                    class="group flex items-center gap-3.5"
                >
                    <!-- Pastel Icon Box -->
                    <div 
                        :class="[
                            'w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition-transform duration-150 group-hover:scale-105',
                            item.iconBg,
                            item.iconColor
                        ]"
                    >
                        <!-- Inline SVGs for crisp lightness and 0 dependencies -->
                        <!-- Code </ > -->
                        <svg v-if="item.iconType === 'code'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                        <!-- Pen / Draft -->
                        <svg v-else-if="item.iconType === 'pen'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                        <!-- Briefcase / Store -->
                        <svg v-else-if="item.iconType === 'briefcase'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <!-- Megaphone -->
                        <svg v-else-if="item.iconType === 'megaphone'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                        </svg>
                        <!-- Book / Default -->
                        <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>

                    <!-- Category Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-1.5">
                            <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 truncate">
                                {{ item.name }}
                            </p>
                            <span class="text-xs font-bold text-slate-900 dark:text-white ml-2 font-mono">
                                {{ item.percent }}%
                            </span>
                        </div>

                        <!-- Progress Bar Track -->
                        <div class="w-full h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                            <div 
                                :class="['h-full rounded-full transition-all duration-500 ease-out', item.color]"
                                :style="{ width: `${item.percent}%` }"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
