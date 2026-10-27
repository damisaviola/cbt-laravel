import { ref } from 'vue';

const SIDEBAR_STORAGE_KEY = 'smartcbt_sidebar_open';

// Shared singleton state across all component instances & Inertia page navigations
const sidebarOpen = ref(true);
let initialized = false;

const initSidebarState = () => {
    if (initialized || typeof window === 'undefined') return;

    if (window.innerWidth < 1024) {
        sidebarOpen.value = false;
    } else {
        try {
            const saved = localStorage.getItem(SIDEBAR_STORAGE_KEY);
            sidebarOpen.value = saved !== null ? saved === 'true' : true;
        } catch (e) {
            sidebarOpen.value = true;
        }
    }

    initialized = true;
};

// Immediate evaluation in browser runtime
if (typeof window !== 'undefined') {
    initSidebarState();
}

export function useSidebar() {
    initSidebarState();

    const toggleSidebar = () => {
        sidebarOpen.value = !sidebarOpen.value;
        if (typeof window !== 'undefined' && window.innerWidth >= 1024) {
            try {
                localStorage.setItem(SIDEBAR_STORAGE_KEY, String(sidebarOpen.value));
            } catch (e) {}
        }
    };

    const closeSidebar = () => {
        sidebarOpen.value = false;
        if (typeof window !== 'undefined' && window.innerWidth >= 1024) {
            try {
                localStorage.setItem(SIDEBAR_STORAGE_KEY, 'false');
            } catch (e) {}
        }
    };

    const openSidebar = () => {
        sidebarOpen.value = true;
        if (typeof window !== 'undefined' && window.innerWidth >= 1024) {
            try {
                localStorage.setItem(SIDEBAR_STORAGE_KEY, 'true');
            } catch (e) {}
        }
    };

    const setSidebarOpen = (val) => {
        sidebarOpen.value = Boolean(val);
        if (typeof window !== 'undefined' && window.innerWidth >= 1024) {
            try {
                localStorage.setItem(SIDEBAR_STORAGE_KEY, String(Boolean(val)));
            } catch (e) {}
        }
    };

    return {
        sidebarOpen,
        toggleSidebar,
        closeSidebar,
        openSidebar,
        setSidebarOpen,
    };
}
