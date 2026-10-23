<script setup>
import { onMounted, onUnmounted, computed } from 'vue';
import { X } from 'lucide-vue-next';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: 'md', // sm, md, lg, xl, 2xl, 3xl, 4xl
    },
    closeable: {
        type: Boolean,
        default: true,
    },
    title: {
        type: String,
        default: '',
    },
    subtitle: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['close']);

const close = () => {
    if (props.closeable) {
        emit('close');
    }
};

const closeOnEscape = (e) => {
    if (e.key === 'Escape' && props.show && props.closeable) {
        close();
    }
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));
onUnmounted(() => document.removeEventListener('keydown', closeOnEscape));

const maxWidthClass = computed(() => {
    const map = {
        sm: 'max-w-sm',
        md: 'max-w-md',
        lg: 'max-w-lg',
        xl: 'max-w-xl',
        '2xl': 'max-w-2xl',
        '3xl': 'max-w-3xl',
        '4xl': 'max-w-4xl',
    };
    return map[props.maxWidth] || 'max-w-md';
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 overflow-y-auto"
                aria-modal="true"
                role="dialog"
            >
                <!-- Backdrop with subtle blur matching EduPro theme -->
                <div
                    class="fixed inset-0 bg-slate-900/40 dark:bg-slate-950/70 backdrop-blur-xs transition-opacity"
                    @click="close"
                />

                <!-- Dialog Window -->
                <div
                    :class="[
                        'relative w-full bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-2xl p-6 sm:p-7 z-10 my-auto text-left transition-all max-h-[92vh] flex flex-col',
                        maxWidthClass
                    ]"
                    @click.stop
                >
                    <!-- Header -->
                    <div v-if="$slots.header || title" class="flex items-start justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-800 shrink-0">
                        <slot name="header">
                            <div class="flex items-center gap-3">
                                <slot name="icon" />
                                <div>
                                    <h3 class="text-base font-bold text-slate-900 dark:text-white tracking-tight">
                                        {{ title }}
                                    </h3>
                                    <p v-if="subtitle" class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                        {{ subtitle }}
                                    </p>
                                </div>
                            </div>
                        </slot>

                        <button
                            v-if="closeable"
                            type="button"
                            @click="close"
                            class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer shrink-0 ml-auto"
                            title="Tutup dialog"
                        >
                            <X class="w-4 h-4" />
                        </button>
                    </div>

                    <!-- Body Content -->
                    <div class="overflow-y-auto flex-1 py-1">
                        <slot />
                    </div>

                    <!-- Footer -->
                    <div v-if="$slots.footer" class="pt-4 border-t border-slate-100 dark:border-slate-800 shrink-0">
                        <slot name="footer" />
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
