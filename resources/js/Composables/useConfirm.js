import { ref } from 'vue';

const state = ref({
    isOpen: false,
    title: 'Konfirmasi Tindakan',
    message: 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
    confirmText: 'Ya, Lanjutkan',
    cancelText: 'Batal',
    type: 'danger', // 'danger' | 'warning' | 'info'
    resolve: null,
});

export function useConfirm() {
    const confirm = (options = {}) => {
        return new Promise((resolve) => {
            state.value = {
                isOpen: true,
                title: options.title || 'Konfirmasi Tindakan',
                message: options.message || 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
                confirmText: options.confirmText || (options.type === 'danger' || !options.type ? 'Ya, Hapus' : 'Ya, Lanjutkan'),
                cancelText: options.cancelText || 'Batal',
                type: options.type || 'danger',
                resolve,
            };
        });
    };

    const handleConfirm = () => {
        state.value.isOpen = false;
        if (state.value.resolve) {
            state.value.resolve(true);
            state.value.resolve = null;
        }
    };

    const handleCancel = () => {
        state.value.isOpen = false;
        if (state.value.resolve) {
            state.value.resolve(false);
            state.value.resolve = null;
        }
    };

    return {
        state,
        confirm,
        handleConfirm,
        handleCancel,
    };
}
