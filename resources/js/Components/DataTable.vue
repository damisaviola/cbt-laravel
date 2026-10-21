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
        // Example: [{ key: 'name', label: 'Nama Siswa', sortable: true, align: 'left', width: 'w-48' }]
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
        default: null, // If null, searches across all visible columns
    },
    perPage: {
        type: Number,
        default: 10, // 0 to show all without pagination
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
        default: 'asc', // 'asc' | 'desc'
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

// Normalize raw items from either plain Array or Laravel paginator object (.data)
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

// Deep value extractor for dotted keys (e.g., 'class_room.name', 'homeroom_teacher.name')
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

// Reset to first page on search change
watch(searchQuery, () => {
    currentPage.value = 1;
});

// Filter & Sort items (Client-side, Fast & Safe)
const filteredItems = computed(() => {
    let result = [...rawItems.value];
    const query = searchQuery.value.trim().toLowerCase();

    // 1. Safe Search Filter
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

    // 2. Safe Sorting
    if (sortKey.value) {
        const colDef = props.columns.find(c => c.key === sortKey.value);
        const dirMultiplier = sortDir.value === 'desc' ? -1 : 1;

        result.sort((a, b) => {
            if (colDef && typeof colDef.sortFn === 'function') {
                return colDef.sortFn(a, b, sortDir.value);
            }

            const valA = getDeepValue(a, sortKey.value);
            const valB = getDeepValue(b, sortKey.value);

            // Nulls / empty values always pushed to bottom
            if (valA === '' || valA === null || valA === undefined) return 1;
            if (valB === '' || valB === null || valB === undefined) return -1;

            // Numeric check
            const numA = Number(valA);
            const numB = Number(valB);
            if (!isNaN(numA) && !isNaN(numB) && typeof valA !== 'boolean' && typeof valB !== 'boolean') {
                return (numA - numB) * dirMultiplier;
            }

            // Natural string collation ("Kelas 2" before "Kelas 10")
            return String(valA).localeCompare(String(valB), undefined, {
                numeric: true,
                sensitivity: 'base',
            }) * dirMultiplier;
        });
    }

    return result;
});

// Total count
const totalFilteredCount = computed(() => filteredItems.value.length);
const totalRawCount = computed(() => rawItems.value.length);

// Paginated items
const paginatedItems = computed(() => {
    if (!currentPerPage.value || currentPerPage.value <= 0) {
        return filteredItems.value;
    }
    const start = (currentPage.value - 1) * currentPerPage.value;
    return filteredItems.value.slice(start, start + currentPerPage.value);
});

// Total pages
const totalPages = computed(() => {
    if (!currentPerPage.value || currentPerPage.value <= 0) return 1;
    return Math.max(1, Math.ceil(totalFilteredCount.value / currentPerPage.value));
});

// Page change handler
const setPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
};

// Alignment helper for TH and TD
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
</script>

<template>
    <div :class="['bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden flex flex-col transition-colors duration-150', cardClass]">
        <!-- Top Toolbar Header -->
        <div v-if="showHeader" class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 bg-slate-50/40 dark:bg-slate-900/60">
            <!-- Left Header: Title or Custom Left Slot -->
            <div class="flex items-center gap-2.5 min-w-0">
                <slot name="header-left">
                    <div v-if="title || subtitle" class="min-w-0">
                        <h3 v-if="title" class="text-sm font-bold text-slate-900 dark:text-white truncate">
                            {{ title }}
                        </h3>
                        <p v-if="subtitle" class="text-xs text-slate-500 dark:text-slate-400 truncate">
                            {{ subtitle }}
                        </p>
                    </div>
                </slot>
            </div>

            <!-- Right Header: Search Bar & Actions Slot -->
            <div class="flex items-center gap-2.5 flex-wrap sm:flex-nowrap justify-between sm:justify-end">
                <!-- Search Input -->
                <div v-if="searchable" class="relative flex-1 sm:w-64">
                    <Search class="w-4 h-4 text-slate-400 dark:text-slate-500 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        :placeholder="searchPlaceholder"
                        class="w-full pl-9 pr-8 py-2 text-xs sm:text-sm bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl focus:border-[#0F52BA] dark:focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 focus:outline-none transition-all placeholder:text-slate-400 dark:placeholder:text-slate-500 text-slate-900 dark:text-white"
                    />
                    <button
                        v-if="searchQuery"
                        @click="clearSearch"
                        class="absolute right-2.5 top-1/2 -translate-y-1/2 p-0.5 rounded-md text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors cursor-pointer"
                        title="Hapus pencarian"
                    >
                        <X class="w-3.5 h-3.5" />
                    </button>
                </div>

                <!-- Custom Header Actions Slot (e.g. Filters, Create Button) -->
                <slot name="header-actions" />

                <!-- Total Count Badge -->
                <div class="text-xs font-mono text-slate-500 dark:text-slate-400 shrink-0 bg-white dark:bg-slate-800/90 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-2xs">
                    <span v-if="searchQuery && totalFilteredCount !== totalRawCount">
                        <strong class="text-blue-700 dark:text-blue-400 font-bold tabular-nums">{{ totalFilteredCount }}</strong> / <span class="tabular-nums">{{ totalRawCount }}</span> Data
                    </span>
                    <span v-else>
                        Total: <strong class="text-slate-900 dark:text-slate-100 font-bold tabular-nums">{{ totalRawCount }}</strong>
                    </span>
                </div>
            </div>
        </div>

        <!-- Bulk Actions Bar -->
        <div v-if="selectable && selectedIds.length > 0" class="px-5 py-2.5 bg-blue-50/80 dark:bg-blue-950/50 border-b border-blue-200 dark:border-blue-900/60 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center min-w-5 h-5 px-1.5 rounded-full bg-[#0F52BA] text-white text-[11px] font-bold">
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
                <thead class="bg-slate-50/80 dark:bg-slate-800/90 border-b border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-200 text-[11px] font-bold uppercase tracking-wider select-none">
                    <tr>
                        <th v-if="selectable" class="w-10 px-4 py-3.5 text-center">
                            <input
                                type="checkbox"
                                :checked="isAllSelected"
                                :indeterminate="isSomeSelected"
                                @change="toggleSelectAll"
                                class="w-4 h-4 rounded border-slate-300 dark:border-slate-700 text-[#0F52BA] focus:ring-blue-500 cursor-pointer"
                                title="Pilih semua data pada halaman ini"
                            />
                        </th>
                        <th
                            v-for="col in columns"
                            :key="col.key"
                            :class="[
                                'px-5 py-3.5 transition-colors',
                                col.width || '',
                                col.headerClass || col.class || '',
                                getAlignmentClass(col.align),
                                (col.sortable !== false && col.key !== 'actions' && col.key !== 'action') ? 'cursor-pointer hover:bg-slate-100/80 dark:hover:bg-slate-700/60 group' : ''
                            ]"
                            @click="toggleSort(col)"
                        >
                            <div :class="['inline-flex items-center gap-1.5', getAlignmentClass(col.align)]">
                                <span class="text-slate-700 dark:text-slate-200">{{ col.label }}</span>
                                <span v-if="col.sortable !== false && col.key !== 'actions' && col.key !== 'action'" class="shrink-0">
                                    <ArrowUp v-if="sortKey === col.key && sortDir === 'asc'" class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400 stroke-[2.5]" />
                                    <ArrowDown v-else-if="sortKey === col.key && sortDir === 'desc'" class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400 stroke-[2.5]" />
                                    <ArrowUpDown v-else class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-200 transition-colors" />
                                </span>
                            </div>
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <!-- Empty State -->
                    <tr v-if="paginatedItems.length === 0">
                        <td :colspan="selectable ? columns.length + 1 : columns.length" class="px-5 py-12 text-center text-slate-500 dark:text-slate-400">
                            <slot name="empty">
                                <div class="flex flex-col items-center justify-center gap-2 max-w-sm mx-auto">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 flex items-center justify-center mb-1">
                                        <Inbox class="w-6 h-6" />
                                    </div>
                                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ emptyText }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ emptySubtext }}</p>
                                    <button
                                        v-if="searchQuery"
                                        @click="clearSearch"
                                        class="mt-2 px-3 py-1.5 text-xs font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 bg-blue-50 dark:bg-blue-950/50 hover:bg-blue-100 dark:hover:bg-blue-900/50 rounded-xl transition-colors cursor-pointer"
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
                            hoverable ? 'hover:bg-slate-50/70 dark:hover:bg-slate-800/60' : '',
                            selectedIds.includes(row[rowKey]) ? 'bg-blue-50/40 dark:bg-blue-950/30' : (striped && rowIndex % 2 === 1 ? 'bg-slate-50/30 dark:bg-slate-800/30' : 'bg-white dark:bg-slate-900'),
                            'transition-colors duration-100'
                        ]"
                    >
                        <td v-if="selectable" class="w-10 px-4 py-3.5 text-center" @click.stop>
                            <input
                                type="checkbox"
                                :checked="selectedIds.includes(row[rowKey])"
                                @change="toggleSelectRow(row)"
                                class="w-4 h-4 rounded border-slate-300 dark:border-slate-700 text-[#0F52BA] focus:ring-blue-500 cursor-pointer"
                            />
                        </td>
                        <td
                            v-for="col in columns"
                            :key="col.key"
                            :class="[
                                'px-5 py-3.5 text-slate-800 dark:text-slate-200',
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

        <!-- Footer / Pagination Controls -->
        <div 
            v-if="totalFilteredCount > 0 && (currentPerPage > 0 && (totalFilteredCount > currentPerPage || perPageOptions.length > 0))"
            class="px-5 py-3 border-t border-slate-100 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-900/60 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs sm:text-sm text-slate-600 dark:text-slate-300 select-none"
        >
            <!-- Page Summary & Per Page Dropdown -->
            <div class="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-start">
                <span class="text-slate-600 dark:text-slate-300 font-medium">
                    Menampilkan <strong class="text-slate-900 dark:text-white font-bold">{{ Math.min((currentPage - 1) * currentPerPage + 1, totalFilteredCount) }}</strong> - 
                    <strong class="text-slate-900 dark:text-white font-bold">{{ Math.min(currentPage * currentPerPage, totalFilteredCount) }}</strong> dari 
                    <strong class="text-slate-900 dark:text-white font-bold">{{ totalFilteredCount }}</strong>
                </span>

                <div v-if="perPageOptions && perPageOptions.length > 1" class="flex items-center gap-1.5">
                    <span class="text-xs text-slate-400 dark:text-slate-400">Baris:</span>
                    <select
                        v-model="currentPerPage"
                        @change="currentPage = 1"
                        class="text-xs px-2 py-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:border-[#0F52BA] dark:focus:border-blue-500 focus:outline-none font-semibold text-slate-700 dark:text-slate-100"
                    >
                        <option v-for="opt in perPageOptions" :key="opt" :value="opt">
                            {{ opt }}
                        </option>
                        <option :value="0">Semua</option>
                    </select>
                </div>
            </div>

            <!-- Page Navigation Buttons -->
            <div v-if="totalPages > 1" class="flex items-center gap-1 shrink-0">
                <button
                    @click="setPage(1)"
                    :disabled="currentPage === 1"
                    class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 disabled:opacity-40 disabled:hover:bg-white dark:disabled:hover:bg-slate-800 text-slate-600 dark:text-slate-200 transition-colors cursor-pointer disabled:cursor-not-allowed"
                    title="Halaman Pertama"
                >
                    <ChevronsLeft class="w-4 h-4" />
                </button>
                <button
                    @click="setPage(currentPage - 1)"
                    :disabled="currentPage === 1"
                    class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 disabled:opacity-40 disabled:hover:bg-white dark:disabled:hover:bg-slate-800 text-slate-600 dark:text-slate-200 transition-colors cursor-pointer disabled:cursor-not-allowed"
                    title="Sebelumnya"
                >
                    <ChevronLeft class="w-4 h-4" />
                </button>

                <!-- Page numbers (compact window of +/- 1) -->
                <div class="flex items-center gap-1 px-1 font-mono text-xs font-semibold text-slate-700 dark:text-slate-200">
                    <span>Hal {{ currentPage }} / {{ totalPages }}</span>
                </div>

                <button
                    @click="setPage(currentPage + 1)"
                    :disabled="currentPage === totalPages"
                    class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 disabled:opacity-40 disabled:hover:bg-white dark:disabled:hover:bg-slate-800 text-slate-600 dark:text-slate-200 transition-colors cursor-pointer disabled:cursor-not-allowed"
                    title="Selanjutnya"
                >
                    <ChevronRight class="w-4 h-4" />
                </button>
                <button
                    @click="setPage(totalPages)"
                    :disabled="currentPage === totalPages"
                    class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 disabled:opacity-40 disabled:hover:bg-white dark:disabled:hover:bg-slate-800 text-slate-600 dark:text-slate-200 transition-colors cursor-pointer disabled:cursor-not-allowed"
                    title="Halaman Terakhir"
                >
                    <ChevronsRight class="w-4 h-4" />
                </button>
            </div>
        </div>
    </div>
</template>
