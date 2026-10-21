<script setup>
import { computed, onMounted, onUnmounted, watch } from 'vue';
import { useConfirm } from '@/Composables/useConfirm';
import { Trash2, AlertTriangle, AlertCircle, HelpCircle, X } from 'lucide-vue-next';

const props = defineProps({
    show: {
        type: Boolean,
        default: undefined,
    },
    title: {
        type: String,
        default: '',
    },
    message: {
        type: String,
        default: '',
    },
    confirmText: {
        type: String,
        default: '',
    },
    cancelText: {
        type: String,
        default: '',
    },
    type: {
        type: String,
        default: '', // 'danger' | 'warning' | 'info'
    },
});

const emit = defineEmits(['confirm', 'cancel', 'update:show']);

const { state, handleConfirm: storeConfirm, handleCancel: storeCancel } = useConfirm();

// Resolve whether component is controlled via props or global composable
const isVisible = computed(() => {
    return props.show !== undefined ? props.show : state.value.isOpen;
});

const modalTitle = computed(() => {
    return props.title || state.value.title;
});

const modalMessage = computed(() => {
    return props.message || state.value.message;
});

const modalConfirmText = computed(() => {
    return props.confirmText || state.value.confirmText;
});

const modalCancelText = computed(() => {
    return props.cancelText || state.value.cancelText;
});

const modalType = computed(() => {
    return props.type || state.value.type || 'danger';
});

const onConfirm = () => {
    if (props.show !== undefined) {
        emit('confirm');
        emit('update:show', false);
    } else {
        storeConfirm();
    }
};

const onCancel = () => {
    if (props.show !== undefined) {
        emit('cancel');
        emit('update:show', false);
    } else {
        storeCancel();
    }
};

// Handle Escape key
const onKeyDown = (e) => {
    if (e.key === 'Escape' && isVisible.value) {
        onCancel();
    }
};

// Lock body scrolling when modal is open
watch(isVisible, (val) => {
    if (typeof document !== 'undefined') {
        if (val) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    }
});

onMounted(() => {
    if (typeof window !== 'undefined') {
        window.addEventListener('keydown', onKeyDown);
    }
});

onUnmounted(() => {
    if (typeof window !== 'undefined') {
        window.removeEventListener('keydown', onKeyDown);
        document.body.style.overflow = '';
    }
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="isVisible"
                class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6 overflow-y-auto"
                aria-modal="true"
                role="dialog"
            >
                <!-- Lightweight Backdrop (No blur) -->
                <div
                    class="fixed inset-0 bg-slate-900/50 dark:bg-slate-950/70"
                    @click="onCancel"
                />

                <!-- Modal Dialog Window -->
                <div
                    class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xl p-6 sm:p-7 overflow-hidden z-10 text-left select-none"
                    @click.stop
                >
                    <!-- Close 'X' Button -->
                    <button
                        @click="onCancel"
                        class="absolute top-4 right-4 p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors cursor-pointer"
                        title="Tutup dialog"
                    >
                        <X class="w-4 h-4" />
                    </button>

                    <div class="flex items-start gap-4">
                        <!-- Visual Status Icon -->
                        <div
                            :class="[
                                'w-11 h-11 rounded-2xl flex items-center justify-center shrink-0 shadow-2xs',
                                modalType === 'danger'
                                    ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-200/80 dark:border-rose-900/60'
                                    : modalType === 'warning'
                                    ? 'bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border border-amber-200/80 dark:border-amber-900/60'
                                    : 'bg-blue-50 dark:bg-blue-950/60 text-[#0F52BA] dark:text-blue-400 border border-blue-200/80 dark:border-blue-900/60'
                            ]"
                        >
                            <Trash2 v-if="modalType === 'danger'" class="w-5 h-5" />
                            <AlertTriangle v-else-if="modalType === 'warning'" class="w-5 h-5" />
                            <HelpCircle v-else class="w-5 h-5" />
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0 pr-2">
                            <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white tracking-tight leading-snug">
                                {{ modalTitle }}
                            </h3>
                            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed mt-1.5">
                                {{ modalMessage }}
                            </p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2.5">
                        <button
                            type="button"
                            @click="onCancel"
                            class="px-4 py-2 text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xs transition-colors cursor-pointer"
                        >
                            {{ modalCancelText }}
                        </button>
                        <button
                            type="button"
                            @click="onConfirm"
                            :class="[
                                'px-4 py-2 text-xs sm:text-sm font-semibold text-white rounded-xl shadow-xs transition-colors cursor-pointer flex items-center gap-2',
                                modalType === 'danger'
                                    ? 'bg-rose-600 hover:bg-rose-700'
                                    : modalType === 'warning'
                                    ? 'bg-amber-600 hover:bg-amber-700'
                                    : 'bg-[#0F52BA] hover:bg-blue-700'
                            ]"
                        >
                            <span>{{ modalConfirmText }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
