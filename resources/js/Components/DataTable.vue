<script setup>
import { ref, computed, watch } from 'vue';
import { 
    Search, 
    ArrowUpDown, 
    ArrowUp, 
    ArrowDown, 
    ChevronLeft, 
    ChevronRight, 
    ChevronsLeft, 
    ChevronsRight, 
    X, 
    Inbox 
} from 'lucide-vue-next';

const props = defineProps({
    items: {
        type: [Array, Object],
        default: () => [],
    },
    columns: {
        type: Array,
        required: true,
    },
    title: {
        type: String,
        default: '',
    },
    showHeader: {
        type: Boolean,
        default: true,
    },
    subtitle: {
        type: String,
        default: '',
    },
    searchable: {
        type: Boolean,
        default: true,
    },
    searchPlaceholder: {
        type: String,
        default: 'Cari data...',
    },
    searchFields: {
        type: Array,
        default: null,
    },
    perPage: {
        type: Number,
        default: 10,
    },
    perPageOptions: {
        type: Array,
        default: () => [10, 25, 50, 100],
    },
    defaultSortKey: {
        type: String,
        default: '',
    },
    defaultSortDir: {
        type: String,
        default: 'asc',
    },
    emptyText: {
        type: String,
        default: 'Tidak ada data yang ditemukan',
    },
    emptySubtext: {
        type: String,
        default: 'Coba sesuaikan kata kunci pencarian atau filter yang aktif.',
    },
    rowKey: {
        type: String,
        default: 'id',
    },
    striped: {
        type: Boolean,
        default: false,
    },
    hoverable: {
        type: Boolean,
        default: true,
    },
    cardClass: {
        type: String,
        default: '',
    },
    selectable: {
        type: Boolean,
        default: false,
    },
    selected: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['update:selected', 'update:modelValue']);

// Selection state
const selectedIds = ref([...props.selected]);

watch(() => props.selected, (val) => {
    selectedIds.value = [...(val || [])];
}, { deep: true });

// Normalize raw items
const rawItems = computed(() => {
    if (!props.items) return [];
    if (Array.isArray(props.items)) return props.items;
    if (props.items.data && Array.isArray(props.items.data)) return props.items.data;
    return [];
});

// Search & Sorting state
const searchQuery = ref('');
const sortKey = ref(props.defaultSortKey);
const sortDir = ref(props.defaultSortDir);

// Pagination state
const currentPage = ref(1);
const currentPerPage = ref(props.perPage);

// Deep value extractor
const getDeepValue = (obj, path) => {
    if (!obj || !path) return '';
    return path.split('.').reduce((acc, part) => {
        if (acc === null || acc === undefined) return '';
        return acc[part] !== undefined && acc[part] !== null ? acc[part] : '';
    }, obj);
};

// Toggle sorting for column
const toggleSort = (col) => {
    if (col.sortable === false || col.key === 'actions' || col.key === 'action') return;

    if (sortKey.value === col.key) {
        if (sortDir.value === 'asc') {
            sortDir.value = 'desc';
        } else if (sortDir.value === 'desc') {
            sortKey.value = '';
            sortDir.value = 'asc';
        }
    } else {
        sortKey.value = col.key;
        sortDir.value = 'asc';
    }
    currentPage.value = 1;
};

// Clear search input
const clearSearch = () => {
    searchQuery.value = '';
    currentPage.value = 1;
};

watch(searchQuery, () => {
    currentPage.value = 1;
});

// Filter & Sort items
const filteredItems = computed(() => {
    let result = [...rawItems.value];
    const query = searchQuery.value.trim().toLowerCase();

    // 1. Search Filter
    if (query) {
        const fieldsToSearch = props.searchFields || props.columns
            .filter(col => col.key !== 'actions' && col.key !== 'action')
            .map(col => col.key);

        result = result.filter(item => {
            return fieldsToSearch.some(field => {
                const val = getDeepValue(item, field);
                if (val === null || val === undefined) return false;
                return String(val).toLowerCase().includes(query);
            });
        });
    }

    // 2. Sorting
    if (sortKey.value) {
        const colDef = props.columns.find(c => c.key === sortKey.value);
        const dirMultiplier = sortDir.value === 'desc' ? -1 : 1;

        result.sort((a, b) => {
            if (colDef && typeof colDef.sortFn === 'function') {
                return colDef.sortFn(a, b, sortDir.value);
            }

            const valA = getDeepValue(a, sortKey.value);
            const valB = getDeepValue(b, sortKey.value);

            if (valA === '' || valA === null || valA === undefined) return 1;
            if (valB === '' || valB === null || valB === undefined) return -1;

            const numA = Number(valA);
            const numB = Number(valB);
            if (!isNaN(numA) && !isNaN(numB) && typeof valA !== 'boolean' && typeof valB !== 'boolean') {
                return (numA - numB) * dirMultiplier;
            }

            return String(valA).localeCompare(String(valB), undefined, {
                numeric: true,
                sensitivity: 'base',
            }) * dirMultiplier;
        });
    }

    return result;
});

const totalFilteredCount = computed(() => filteredItems.value.length);
const totalRawCount = computed(() => rawItems.value.length);

const paginatedItems = computed(() => {
    if (!currentPerPage.value || currentPerPage.value <= 0) {
        return filteredItems.value;
    }
    const start = (currentPage.value - 1) * currentPerPage.value;
    return filteredItems.value.slice(start, start + currentPerPage.value);
});

const totalPages = computed(() => {
    if (!currentPerPage.value || currentPerPage.value <= 0) return 1;
    return Math.max(1, Math.ceil(totalFilteredCount.value / currentPerPage.value));
});

const setPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
};

// Alignment helper
const getAlignmentClass = (align) => {
    if (align === 'center') return 'text-center justify-center';
    if (align === 'right') return 'text-right justify-end';
    return 'text-left justify-start';
};

// Selection logic
const isAllSelected = computed(() => {
    if (paginatedItems.value.length === 0) return false;
    return paginatedItems.value.every(row => selectedIds.value.includes(row[props.rowKey]));
});

const isSomeSelected = computed(() => {
    return selectedIds.value.length > 0 && !isAllSelected.value;
});

const toggleSelectAll = () => {
    const pageIds = paginatedItems.value.map(r => r[props.rowKey]);
    if (isAllSelected.value) {
        selectedIds.value = selectedIds.value.filter(id => !pageIds.includes(id));
    } else {
        const set = new Set([...selectedIds.value, ...pageIds]);
        selectedIds.value = Array.from(set);
    }
    emit('update:selected', selectedIds.value);
    emit('update:modelValue', selectedIds.value);
};

const toggleSelectRow = (row) => {
    const id = row[props.rowKey];
    const idx = selectedIds.value.indexOf(id);
    if (idx > -1) {
        selectedIds.value.splice(idx, 1);
    } else {
        selectedIds.value.push(id);
    }
    emit('update:selected', selectedIds.value);
    emit('update:modelValue', selectedIds.value);
};

const clearSelection = () => {
    selectedIds.value = [];
    emit('update:selected', []);
    emit('update:modelValue', []);
};

// Smart visible page numbers for pagination buttons
const visiblePages = computed(() => {
    const total = totalPages.value;
    const current = currentPage.value;
    if (total <= 7) {
        return Array.from({ length: total }, (_, i) => i + 1);
    }
    if (current <= 4) {
        return [1, 2, 3, 4, 5, '...', total];
    }
    if (current >= total - 3) {
        return [1, '...', total - 4, total - 3, total - 2, total - 1, total];
    }
    return [1, '...', current - 1, current, current + 1, '...', total];
});
</script>

<template>
    <!-- Modern Clean Card Container matching contoh.jpg (EduPro Design System) -->
    <div :class="['bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col transition-colors duration-150', cardClass]">
        
        <!-- Top Toolbar Header -->
        <div v-if="showHeader" class="px-6 py-4.5 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 bg-transparent">
            <!-- Left Header: Title or Custom Left Slot -->
            <div class="flex items-center gap-3 min-w-0">
                <slot name="header-left">
                    <div v-if="title || subtitle" class="min-w-0">
                        <h3 v-if="title" class="text-base font-bold text-slate-900 dark:text-white tracking-tight truncate">
                            {{ title }}
                        </h3>
                        <p v-if="subtitle" class="text-xs text-slate-400 dark:text-slate-500 mt-0.5 truncate">
                            {{ subtitle }}
                        </p>
                    </div>
                </slot>
            </div>

            <!-- Right Header: Search Bar & Actions -->
            <div class="flex items-center gap-3 flex-wrap sm:flex-nowrap justify-between sm:justify-end">
                <!-- Clean Pill Search Input matching contoh.jpg -->
                <div v-if="searchable" class="relative flex-1 sm:w-64">
                    <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        :placeholder="searchPlaceholder"
                        class="w-full pl-10 pr-8 py-2 text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700 rounded-full focus:border-blue-600 dark:focus:border-blue-500 focus:bg-white dark:focus:bg-slate-800 focus:outline-none transition-all placeholder:text-slate-400 dark:placeholder:text-slate-500 text-slate-900 dark:text-white shadow-2xs"
                    />
                    <button
                        v-if="searchQuery"
                        @click="clearSearch"
                        class="absolute right-3 top-1/2 -translate-y-1/2 p-0.5 rounded-full text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-200/60 dark:hover:bg-slate-700 transition-colors cursor-pointer"
                        title="Hapus pencarian"
                    >
                        <X class="w-3.5 h-3.5" />
                    </button>
                </div>

                <!-- Custom Header Actions Slot (e.g. Filters, Create Button) -->
                <slot name="header-actions" />

                <!-- Total Count Pill Badge -->
                <div class="text-xs text-slate-500 dark:text-slate-400 shrink-0 bg-slate-50 dark:bg-slate-800 px-3.5 py-1.5 rounded-full border border-slate-200/70 dark:border-slate-700/80 font-medium">
                    <span v-if="searchQuery && totalFilteredCount !== totalRawCount">
                        <strong class="text-blue-600 dark:text-blue-400 font-bold tabular-nums">{{ totalFilteredCount }}</strong> / <span class="tabular-nums">{{ totalRawCount }}</span> Data
                    </span>
                    <span v-else>
                        Total: <strong class="text-slate-900 dark:text-slate-100 font-bold tabular-nums">{{ totalRawCount }}</strong>
                    </span>
                </div>
            </div>
        </div>

        <!-- Bulk Actions Pill Banner -->
        <div v-if="selectable && selectedIds.length > 0" class="px-6 py-2.5 bg-blue-50/70 dark:bg-blue-950/40 border-b border-blue-100 dark:border-blue-900/40 flex flex-wrap items-center justify-between gap-3 animate-in fade-in duration-150">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center min-w-5 h-5 px-2 rounded-full bg-blue-600 text-white text-[11px] font-bold">
                    {{ selectedIds.length }}
                </span>
                <span class="text-xs font-semibold text-blue-950 dark:text-blue-200">
                    {{ selectedIds.length }} baris dipilih
                </span>
                <button
                    type="button"
                    @click="clearSelection"
                    class="text-xs text-blue-600 dark:text-blue-400 hover:underline ml-1 cursor-pointer font-medium"
                >
                    Batalkan Pilihan
                </button>
            </div>
            <div class="flex items-center gap-2">
                <slot name="bulk-actions" :selected="selectedIds" :clear-selection="clearSelection" />
            </div>
        </div>

        <!-- Table Canvas with Horizontal Scroll -->
        <div class="overflow-x-auto min-h-[160px]">
            <table class="w-full text-left text-xs sm:text-sm text-slate-700 dark:text-slate-200">
                <!-- Clean Modern Table Header -->
                <thead class="bg-slate-50/60 dark:bg-slate-800/40 border-b border-slate-100 dark:border-slate-800 text-slate-400 dark:text-slate-400 text-[11px] font-bold uppercase tracking-wider select-none">
                    <tr>
                        <th v-if="selectable" class="w-12 px-5 py-3.5 text-center">
                            <input
                                type="checkbox"
                                :checked="isAllSelected"
                                :indeterminate="isSomeSelected"
                                @change="toggleSelectAll"
                                class="w-4 h-4 rounded border-slate-300 dark:border-slate-600 text-blue-600 focus:ring-0 cursor-pointer"
                                title="Pilih semua data pada halaman ini"
                            />
                        </th>
                        <th
                            v-for="col in columns"
                            :key="col.key"
                            :class="[
                                'px-6 py-3.5 transition-colors',
                                col.width || '',
                                col.headerClass || col.class || '',
                                getAlignmentClass(col.align),
                                (col.sortable !== false && col.key !== 'actions' && col.key !== 'action') ? 'cursor-pointer hover:text-slate-800 dark:hover:text-white group' : ''
                            ]"
                            @click="toggleSort(col)"
                        >
                            <div :class="['inline-flex items-center gap-1.5', getAlignmentClass(col.align)]">
                                <span>{{ col.label }}</span>
                                <span v-if="col.sortable !== false && col.key !== 'actions' && col.key !== 'action'" class="shrink-0">
                                    <ArrowUp v-if="sortKey === col.key && sortDir === 'asc'" class="w-3.5 h-3.5 text-blue-600 stroke-[2.5]" />
                                    <ArrowDown v-else-if="sortKey === col.key && sortDir === 'desc'" class="w-3.5 h-3.5 text-blue-600 stroke-[2.5]" />
                                    <ArrowUpDown v-else class="w-3.5 h-3.5 text-slate-300 dark:text-slate-600 group-hover:text-slate-500 transition-colors" />
                                </span>
                            </div>
                        </th>
                    </tr>
                </thead>

                <!-- Clean Divide Table Body -->
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/70">
                    <!-- Empty State -->
                    <tr v-if="paginatedItems.length === 0">
                        <td :colspan="selectable ? columns.length + 1 : columns.length" class="px-6 py-16 text-center text-slate-500 dark:text-slate-400">
                            <slot name="empty">
                                <div class="flex flex-col items-center justify-center gap-2 max-w-sm mx-auto">
                                    <!-- Pastel Empty Icon Box -->
                                    <div class="w-14 h-14 rounded-2xl bg-blue-50 dark:bg-blue-950/40 text-blue-500 flex items-center justify-center mb-1 shadow-2xs">
                                        <Inbox class="w-7 h-7" />
                                    </div>
                                    <p class="text-sm font-bold text-slate-900 dark:text-white">{{ emptyText }}</p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500">{{ emptySubtext }}</p>
                                    <button
                                        v-if="searchQuery"
                                        @click="clearSearch"
                                        class="mt-3 px-4 py-1.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-full shadow-2xs transition-colors cursor-pointer"
                                    >
                                        Reset Pencarian
                                    </button>
                                </div>
                            </slot>
                        </td>
                    </tr>

                    <!-- Data Rows -->
                    <tr
                        v-for="(row, rowIndex) in paginatedItems"
                        :key="row[rowKey] || rowIndex"
                        :class="[
                            hoverable ? 'hover:bg-slate-50/70 dark:hover:bg-slate-800/50' : '',
                            selectedIds.includes(row[rowKey]) ? 'bg-blue-50/40 dark:bg-blue-950/30' : (striped && rowIndex % 2 === 1 ? 'bg-slate-50/30 dark:bg-slate-800/20' : 'bg-white dark:bg-slate-900'),
                            'transition-colors duration-100'
                        ]"
                    >
                        <td v-if="selectable" class="w-12 px-5 py-4 text-center" @click.stop>
                            <input
                                type="checkbox"
                                :checked="selectedIds.includes(row[rowKey])"
                                @change="toggleSelectRow(row)"
                                class="w-4 h-4 rounded border-slate-300 dark:border-slate-600 text-blue-600 focus:ring-0 cursor-pointer"
                            />
                        </td>
                        <td
                            v-for="col in columns"
                            :key="col.key"
                            :class="[
                                'px-6 py-4 text-slate-800 dark:text-slate-200',
                                col.cellClass || col.class || '',
                                col.align === 'center' ? 'text-center' : col.align === 'right' ? 'text-right' : 'text-left'
                            ]"
                        >
                            <!-- Named Cell Slot (e.g. #cell-name or #cell-role) -->
                            <slot 
                                :name="'cell-' + col.key" 
                                :row="row" 
                                :value="getDeepValue(row, col.key)" 
                                :index="rowIndex"
                            >
                                <!-- Quick Actions Slot for 'actions' / 'action' -->
                                <template v-if="col.key === 'actions' || col.key === 'action'">
                                    <slot name="actions" :row="row" :index="rowIndex" />
                                </template>
                                <!-- Default Value Rendering -->
                                <template v-else>
                                    <span class="text-slate-800 dark:text-slate-100 font-medium">{{ getDeepValue(row, col.key) || '-' }}</span>
                                </template>
                            </slot>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Footer / Pagination Controls matching EduPro Style -->
        <div 
            v-if="totalFilteredCount > 0 && (currentPerPage > 0 && (totalFilteredCount > currentPerPage || perPageOptions.length > 0))"
            class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-transparent flex flex-col sm:flex-row items-center justify-between gap-4 text-xs select-none"
        >
            <!-- Page Summary & Per Page Dropdown -->
            <div class="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-start">
                <span class="text-slate-500 dark:text-slate-400">
                    Menampilkan <strong class="text-slate-900 dark:text-white font-bold">{{ Math.min((currentPage - 1) * currentPerPage + 1, totalFilteredCount) }}</strong> - 
                    <strong class="text-slate-900 dark:text-white font-bold">{{ Math.min(currentPage * currentPerPage, totalFilteredCount) }}</strong> dari 
                    <strong class="text-slate-900 dark:text-white font-bold">{{ totalFilteredCount }}</strong> data
                </span>

                <div v-if="perPageOptions && perPageOptions.length > 1" class="flex items-center gap-1.5">
                    <span class="text-slate-400">Baris:</span>
                    <select
                        v-model="currentPerPage"
                        @change="currentPage = 1"
                        class="px-2.5 py-1 bg-slate-50 dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700 rounded-full focus:border-blue-600 focus:outline-none font-semibold text-slate-700 dark:text-slate-200 cursor-pointer"
                    >
                        <option v-for="opt in perPageOptions" :key="opt" :value="opt">
                            {{ opt }}
                        </option>
                        <option :value="0">Semua</option>
                    </select>
                </div>
            </div>

            <!-- Modern Pill Page Navigation Buttons -->
            <div v-if="totalPages > 1" class="flex items-center gap-1.5 shrink-0">
                <!-- First Page -->
                <button
                    @click="setPage(1)"
                    :disabled="currentPage === 1"
                    class="w-8 h-8 rounded-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-750 disabled:opacity-30 disabled:pointer-events-none text-slate-600 dark:text-slate-300 flex items-center justify-center transition-all cursor-pointer shadow-2xs"
                    title="Halaman Pertama"
                >
                    <ChevronsLeft class="w-3.5 h-3.5" />
                </button>

                <!-- Previous Page -->
                <button
                    @click="setPage(currentPage - 1)"
                    :disabled="currentPage === 1"
                    class="w-8 h-8 rounded-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-750 disabled:opacity-30 disabled:pointer-events-none text-slate-600 dark:text-slate-300 flex items-center justify-center transition-all cursor-pointer shadow-2xs"
                    title="Sebelumnya"
                >
                    <ChevronLeft class="w-3.5 h-3.5" />
                </button>

                <!-- Page Number Buttons -->
                <div class="flex items-center gap-1">
                    <template v-for="(p, pIdx) in visiblePages" :key="pIdx">
                        <span v-if="p === '...'" class="px-1 text-slate-400 font-medium">...</span>
                        <button
                            v-else
                            type="button"
                            @click="setPage(p)"
                            :class="[
                                'w-8 h-8 rounded-full text-xs font-semibold flex items-center justify-center transition-all cursor-pointer',
                                currentPage === p
                                    ? 'bg-blue-600 text-white font-bold shadow-xs shadow-blue-600/30'
                                    : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white'
                            ]"
                        >
                            {{ p }}
                        </button>
                    </template>
                </div>

                <!-- Next Page -->
                <button
                    @click="setPage(currentPage + 1)"
                    :disabled="currentPage === totalPages"
                    class="w-8 h-8 rounded-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-750 disabled:opacity-30 disabled:pointer-events-none text-slate-600 dark:text-slate-300 flex items-center justify-center transition-all cursor-pointer shadow-2xs"
                    title="Selanjutnya"
                >
                    <ChevronRight class="w-3.5 h-3.5" />
                </button>

                <!-- Last Page -->
                <button
                    @click="setPage(totalPages)"
                    :disabled="currentPage === totalPages"
                    class="w-8 h-8 rounded-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-750 disabled:opacity-30 disabled:pointer-events-none text-slate-600 dark:text-slate-300 flex items-center justify-center transition-all cursor-pointer shadow-2xs"
                    title="Halaman Terakhir"
                >
                    <ChevronsRight class="w-3.5 h-3.5" />
                </button>
            </div>
        </div>
    </div>
</template>
