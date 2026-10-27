<script setup>
import { Link, Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import { ArrowLeft, BarChart3, HelpCircle } from 'lucide-vue-next';

const props = defineProps({
    schedule: Object,
    totalStudents: Number,
    analysis: Array,
});

const columns = [
    { key: 'order_index', label: 'No', width: 'w-16', align: 'center', sortable: true },
    { key: 'content', label: 'Ringkasan Soal', sortable: true },
    { key: 'type', label: 'Tipe', width: 'w-28', sortable: true },
    { key: 'correct_count', label: 'Menjawab Benar', align: 'center', sortable: true },
    { key: 'difficulty_index', label: 'Indeks P', align: 'center', sortable: true },
    { key: 'difficulty_category', label: 'Kategori', align: 'center', sortable: true },
    { key: 'option_distribution', label: 'Distribusi Opsi Pilihan', sortable: false },
];
</script>

<template>
    <AppLayout>
        <Head :title="'Analisis Butir Soal - ' + schedule.title" />

        <template #header>
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-3">
                    <Link
                        href="/teacher/grading"
                        class="p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors"
                        title="Kembali ke Meja Koreksi"
                    >
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                    <div>
                        <h1 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white tracking-tight">Analisis Butir Soal & Daya Pembeda</h1>
                        <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ schedule.title }} &bull; Sampel: {{ totalStudents }} Siswa</p>
                    </div>
                </div>
            </div>
        </template>

        <div class="space-y-4">
            <!-- Summary Banner Card (Rounded-3xl) -->
            <div class="bg-white dark:bg-slate-900 p-5 sm:p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xs">
                <div class="flex flex-wrap items-center gap-y-2 text-xs text-slate-600 dark:text-slate-300">
                    <strong class="text-slate-800 dark:text-slate-100 font-bold">Kriteria Indeks Kesukaran (P):</strong>
                    <span class="ml-3 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#eaf4ef] dark:bg-emerald-950/40 text-[#185338] dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/50 font-semibold text-[11px]">
                        <span class="w-2 h-2 rounded-full bg-[#185338] dark:bg-emerald-400"></span> Mudah (&gt; 0.70)
                    </span>
                    <span class="ml-2 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 font-semibold text-[11px]">
                        <span class="w-2 h-2 rounded-full bg-slate-500"></span> Sedang (0.30 - 0.70)
                    </span>
                    <span class="ml-2 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-50 dark:bg-rose-950/40 text-rose-800 dark:text-rose-300 border border-rose-200/60 dark:border-rose-800/50 font-semibold text-[11px]">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span> Sukar (&lt; 0.30)
                    </span>
                </div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-mono shrink-0">
                    Total Butir: <strong class="text-slate-900 dark:text-white font-bold">{{ analysis?.length || 0 }}</strong> Soal
                </div>
            </div>

            <!-- Analysis Table Card -->
            <DataTable
                :items="analysis"
                :columns="columns"
                title="Tabel Daya Pembeda & Distribusi Respons Siswa"
                subtitle="Analisis tingkat kesukaran dan statistik respons butir soal peserta."
                search-placeholder="Cari teks soal, tipe, atau kategori..."
                :per-page="15"
            >
                <template #header-left>
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-2xl bg-[#eaf4ef] dark:bg-emerald-950/40 text-[#185338] dark:text-emerald-400 flex items-center justify-center shrink-0">
                            <BarChart3 class="w-4.5 h-4.5" />
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Tabel Daya Pembeda & Distribusi Respons Siswa</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Analisis tingkat kesukaran dan statistik respons butir soal peserta.</p>
                        </div>
                    </div>
                </template>

                <template #cell-order_index="{ row }">
                    <span class="font-mono font-bold text-slate-900 dark:text-white">{{ row.order_index }}</span>
                </template>

                <template #cell-content="{ row }">
                    <span class="text-slate-800 dark:text-slate-200 line-clamp-1 max-w-xs block font-medium">{{ row.content }}</span>
                </template>

                <template #cell-type="{ row }">
                    <span class="uppercase text-[11px] font-semibold text-slate-500 dark:text-slate-400 px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 border border-transparent dark:border-slate-700/60">
                        {{ row.type }}
                    </span>
                </template>

                <template #cell-correct_count="{ row }">
                    <span class="font-mono font-semibold text-slate-800 dark:text-slate-200 tabular-nums">
                        {{ row.correct_count }} / {{ row.total_answered }}
                    </span>
                </template>

                <template #cell-difficulty_index="{ row }">
                    <span class="font-mono font-bold tabular-nums text-slate-800 dark:text-slate-200">
                        {{ Number(row.difficulty_index).toFixed(2) }}
                    </span>
                </template>

                <template #cell-difficulty_category="{ row }">
                    <span
                        :class="[
                            row.difficulty_category === 'Mudah' ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60' : row.difficulty_category === 'Sedang' ? 'bg-blue-50 dark:bg-blue-950/40 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-800/60' : 'bg-rose-50 dark:bg-rose-950/40 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-800/60',
                            'px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider'
                        ]"
                    >
                        {{ row.difficulty_category }}
                    </span>
                </template>

                <template #cell-option_distribution="{ row }">
                    <span v-if="!row.option_distribution || Object.keys(row.option_distribution).length === 0" class="text-slate-400 dark:text-slate-500 italic">
                        -
                    </span>
                    <div v-else class="flex items-center gap-1.5 flex-wrap">
                        <span v-for="(cnt, opt) in row.option_distribution" :key="opt" class="px-2 py-0.5 bg-slate-100 dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-lg text-xs font-mono">
                            [{{ opt }}]: {{ cnt }}
                        </span>
                    </div>
                </template>
            </DataTable>
        </div>
    </AppLayout>
</template>
