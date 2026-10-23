<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    title: {
        type: String,
        default: 'Enrollment Trends',
    },
    subtitle: {
        type: String,
        default: '',
    },
    filterOptions: {
        type: Array,
        default: () => ['This Year', 'Last 6 Months', 'This Month'],
    },
    dataSets: {
        type: Object,
        default: () => ({
            'This Year': [
                { label: 'Jan', value: 65 },
                { label: 'Feb', value: 78 },
                { label: 'Mar', value: 60 },
                { label: 'Apr', value: 85 },
                { label: 'May', value: 92 },
                { label: 'Jun', value: 88 },
                { label: 'Jul', value: 105 },
                { label: 'Aug', value: 118 },
            ],
            'Last 6 Months': [
                { label: 'Mar', value: 50 },
                { label: 'Apr', value: 72 },
                { label: 'May', value: 84 },
                { label: 'Jun', value: 80 },
                { label: 'Jul', value: 95 },
                { label: 'Aug', value: 110 },
            ],
            'This Month': [
                { label: 'W1', value: 20 },
                { label: 'W2', value: 45 },
                { label: 'W3', value: 68 },
                { label: 'W4', value: 95 },
            ],
        }),
    },
    chartId: {
        type: String,
        default: () => 'chart-' + Math.random().toString(36).substring(2, 9),
    },
    strokeColor: {
        type: String,
        default: '#2563EB', // Vibrant royal blue matching contoh.jpg
    },
});

const selectedFilter = ref(props.filterOptions[0] || 'This Year');
const hoveredPoint = ref(null);
const showDropdown = ref(false);

const currentData = computed(() => {
    return props.dataSets[selectedFilter.value] || props.dataSets[Object.keys(props.dataSets)[0]] || [];
});

// Chart Dimensions
const viewBoxWidth = 640;
const viewBoxHeight = 240;
const padding = { top: 25, right: 30, bottom: 45, left: 45 };

const width = viewBoxWidth - padding.left - padding.right;
const height = viewBoxHeight - padding.top - padding.bottom;

const yTicks = [0, 20, 40, 60, 80, 100, 120];
const maxVal = 120;

// Coordinate mapping
const points = computed(() => {
    const data = currentData.value;
    if (!data.length) return [];
    
    return data.map((d, index) => {
        const x = padding.left + (index / (data.length - 1)) * width;
        const normalizedVal = Math.min(Math.max(d.value, 0), maxVal);
        const y = padding.top + height - (normalizedVal / maxVal) * height;
        return {
            x,
            y,
            label: d.label,
            value: d.value,
        };
    });
});

// Calculate smooth spline curve through points using cubic Bezier
const curvePaths = computed(() => {
    const pts = points.value;
    if (pts.length < 2) return { line: '', area: '' };

    let d = `M ${pts[0].x} ${pts[0].y}`;

    for (let i = 0; i < pts.length - 1; i++) {
        const p0 = i > 0 ? pts[i - 1] : pts[i];
        const p1 = pts[i];
        const p2 = pts[i + 1];
        const p3 = i < pts.length - 2 ? pts[i + 2] : p2;

        const cp1x = p1.x + (p2.x - p0.x) / 6;
        const cp1y = p1.y + (p2.y - p0.y) / 6;

        const cp2x = p2.x - (p3.x - p1.x) / 6;
        const cp2y = p2.y - (p3.y - p1.y) / 6;

        d += ` C ${cp1x} ${cp1y}, ${cp2x} ${cp2y}, ${p2.x} ${p2.y}`;
    }

    const firstPt = pts[0];
    const lastPt = pts[pts.length - 1];
    const groundY = padding.top + height;

    const areaD = `${d} L ${lastPt.x} ${groundY} L ${firstPt.x} ${groundY} Z`;

    return {
        line: d,
        area: areaD,
    };
});

const selectFilter = (opt) => {
    selectedFilter.value = opt;
    showDropdown.value = false;
};
</script>

<template>
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm transition-colors duration-150">
        <!-- Header with Title and Filter Dropdown -->
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white tracking-tight">
                    {{ title }}
                </h3>
                <p v-if="subtitle" class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    {{ subtitle }}
                </p>
            </div>

            <!-- Filter Dropdown Pill -->
            <div class="relative">
                <button
                    type="button"
                    @click="showDropdown = !showDropdown"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-750 border border-slate-200/80 dark:border-slate-700 transition-colors cursor-pointer"
                >
                    <span>{{ selectedFilter }}</span>
                    <svg class="w-3.5 h-3.5 text-slate-400 transition-transform" :class="{ 'rotate-180': showDropdown }" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                    </svg>
                </button>

                <div 
                    v-if="showDropdown"
                    class="absolute right-0 mt-1.5 w-36 bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-100 dark:border-slate-700 py-1 z-20 text-xs font-medium"
                >
                    <button
                        v-for="opt in filterOptions"
                        :key="opt"
                        type="button"
                        @click="selectFilter(opt)"
                        class="w-full text-left px-3 py-2 text-slate-700 dark:text-slate-200 hover:bg-blue-50 dark:hover:bg-blue-950/50 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                        :class="{ 'text-blue-600 dark:text-blue-400 font-bold bg-blue-50/50 dark:bg-blue-950/30': opt === selectedFilter }"
                    >
                        {{ opt }}
                    </button>
                </div>
            </div>
        </div>

        <!-- SVG Line Chart Canvas (Ultra-lightweight, 0 dependency, instant render) -->
        <div class="relative w-full aspect-[21/9] sm:aspect-[24/9] min-h-[190px]">
            <svg 
                class="w-full h-full overflow-visible select-none"
                :viewBox="`0 0 ${viewBoxWidth} ${viewBoxHeight}`"
                preserveAspectRatio="none"
            >
                <defs>
                    <!-- Soft Gradient fill under curve matching contoh.jpg -->
                    <linearGradient :id="`grad-${chartId}`" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" :stop-color="strokeColor" stop-opacity="0.22" />
                        <stop offset="85%" :stop-color="strokeColor" stop-opacity="0.02" />
                        <stop offset="100%" :stop-color="strokeColor" stop-opacity="0" />
                    </linearGradient>
                </defs>

                <!-- Horizontal Y Gridlines & Y Labels -->
                <g class="grid-lines">
                    <g 
                        v-for="tick in yTicks" 
                        :key="tick" 
                        class="text-[11px] font-sans fill-slate-400 dark:fill-slate-500"
                    >
                        <!-- Gridline -->
                        <line 
                            :x1="padding.left" 
                            :y1="padding.top + height - (tick / maxVal) * height" 
                            :x2="padding.left + width" 
                            :y2="padding.top + height - (tick / maxVal) * height" 
                            stroke="currentColor" 
                            stroke-opacity="0.08" 
                            stroke-width="1"
                        />
                        <!-- Y Label -->
                        <text 
                            :x="padding.left - 10" 
                            :y="padding.top + height - (tick / maxVal) * height + 4" 
                            text-anchor="end"
                        >
                            {{ tick }}
                        </text>
                    </g>
                </g>

                <!-- Gradient Area Fill -->
                <path 
                    v-if="curvePaths.area"
                    :d="curvePaths.area" 
                    :fill="`url(#grad-${chartId})`" 
                />

                <!-- Smooth Vibrant Blue Curve Stroke -->
                <path 
                    v-if="curvePaths.line"
                    :d="curvePaths.line" 
                    fill="none" 
                    :stroke="strokeColor" 
                    stroke-width="3" 
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />

                <!-- Data Points & Labels -->
                <g v-for="(pt, idx) in points" :key="idx">
                    <!-- X-Axis Label -->
                    <text 
                        :x="pt.x" 
                        :y="padding.top + height + 24" 
                        text-anchor="middle" 
                        class="text-[11px] font-medium fill-slate-400 dark:fill-slate-500"
                    >
                        {{ pt.label }}
                    </text>

                    <!-- Circular Point matching contoh.jpg (white inner, blue ring) -->
                    <circle 
                        :cx="pt.x" 
                        :cy="pt.y" 
                        :r="hoveredPoint === idx ? 6.5 : 4.5" 
                        fill="#ffffff" 
                        :stroke="strokeColor" 
                        stroke-width="3"
                        class="transition-all duration-150 cursor-pointer"
                        @mouseenter="hoveredPoint = idx"
                        @mouseleave="hoveredPoint = null"
                    />

                    <!-- Interactive point hit target -->
                    <circle 
                        :cx="pt.x" 
                        :cy="pt.y" 
                        r="14" 
                        fill="transparent" 
                        class="cursor-pointer"
                        @mouseenter="hoveredPoint = idx"
                        @mouseleave="hoveredPoint = null"
                    />
                </g>
            </svg>

            <!-- Floating Tooltip on Hover -->
            <div 
                v-if="hoveredPoint !== null && points[hoveredPoint]" 
                class="absolute pointer-events-none transform -translate-x-1/2 -translate-y-full bg-slate-900 text-white dark:bg-white dark:text-slate-900 text-[11px] font-bold px-2.5 py-1 rounded-lg shadow-md transition-all duration-150"
                :style="{
                    left: `${(points[hoveredPoint].x / viewBoxWidth) * 100}%`,
                    top: `${(points[hoveredPoint].y / viewBoxHeight) * 100}%`,
                    marginTop: '-8px'
                }"
            >
                <div class="flex items-center gap-1.5">
                    <span>{{ points[hoveredPoint].label }}:</span>
                    <span class="text-blue-400 dark:text-blue-600">{{ points[hoveredPoint].value }}</span>
                </div>
            </div>
        </div>
    </div>
</template>
