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
                <!-- Backdrop with subtle blur matching EduPro theme -->
                <div
                    class="fixed inset-0 bg-slate-900/40 dark:bg-slate-950/70 backdrop-blur-xs transition-opacity"
                    @click="onCancel"
                />

                <!-- Modal Dialog Window -->
                <div
                    class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-2xl p-6 sm:p-7 overflow-hidden z-10 text-left select-none my-auto"
                    @click.stop
                >
                    <!-- Close 'X' Button -->
                    <button
                        @click="onCancel"
                        class="absolute top-4 right-4 w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                        title="Tutup dialog"
                    >
                        <X class="w-4 h-4" />
                    </button>

                    <div class="flex items-start gap-4">
                        <!-- Visual Status Icon -->
                        <div
                            :class="[
                                'w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 shadow-2xs',
                                modalType === 'danger'
                                    ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-200/80 dark:border-rose-900/60'
                                    : modalType === 'warning'
                                    ? 'bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border border-amber-200/80 dark:border-amber-900/60'
                                    : 'bg-[#eaf4ef] dark:bg-[#185338]/20 text-[#185338] dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/40'
                            ]"
                        >
                            <Trash2 v-if="modalType === 'danger'" class="w-5 h-5" />
                            <AlertTriangle v-else-if="modalType === 'warning'" class="w-5 h-5" />
                            <HelpCircle v-else class="w-5 h-5" />
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0 pr-6">
                            <h3 class="text-base font-bold text-slate-900 dark:text-white tracking-tight leading-snug">
                                {{ modalTitle }}
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed mt-1.5">
                                {{ modalMessage }}
                            </p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2.5">
                        <button
                            type="button"
                            @click="onCancel"
                            class="px-5 py-2.5 text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-750 border border-slate-200 dark:border-slate-700 rounded-full shadow-2xs transition-colors cursor-pointer"
                        >
                            {{ modalCancelText }}
                        </button>
                        <button
                            type="button"
                            @click="onConfirm"
                            :class="[
                                'px-6 py-2.5 text-xs font-semibold text-white rounded-full shadow-sm transition-all cursor-pointer flex items-center gap-2',
                                modalType === 'danger'
                                    ? 'bg-rose-600 hover:bg-rose-700 shadow-rose-600/25'
                                    : modalType === 'warning'
                                    ? 'bg-amber-500 hover:bg-amber-600 shadow-amber-500/25'
                                    : 'bg-[#185338] hover:bg-[#12422c] shadow-emerald-950/20'
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
