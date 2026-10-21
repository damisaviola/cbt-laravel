import { ref, computed } from 'vue';

const THEME_STORAGE_KEY = 'cbt_theme';

// Shared state across all component instances
const currentTheme = ref('system');
const systemDark = ref(false);
let initialized = false;

const applyThemeToDOM = (dark) => {
    if (typeof document === 'undefined') return;
    const root = document.documentElement;
    if (dark) {
        root.classList.add('dark');
    } else {
        root.classList.remove('dark');
    }
};

const updateEffectiveTheme = () => {
    if (typeof window === 'undefined') return;
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    systemDark.value = mediaQuery.matches;

    const shouldBeDark = currentTheme.value === 'dark' || (currentTheme.value === 'system' && systemDark.value);
    applyThemeToDOM(shouldBeDark);
};

export function useTheme() {
    if (!initialized && typeof window !== 'undefined') {
        try {
            const saved = localStorage.getItem(THEME_STORAGE_KEY);
            if (saved === 'light' || saved === 'dark' || saved === 'system') {
                currentTheme.value = saved;
            } else {
                currentTheme.value = 'system';
            }
        } catch (e) {
            currentTheme.value = 'system';
        }

        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        systemDark.value = mediaQuery.matches;

        // Listen for OS dark mode changes
        const handleMediaChange = (e) => {
            systemDark.value = e.matches;
            if (currentTheme.value === 'system') {
                applyThemeToDOM(e.matches);
            }
        };

        if (mediaQuery.addEventListener) {
            mediaQuery.addEventListener('change', handleMediaChange);
        } else if (mediaQuery.addListener) {
            mediaQuery.addListener(handleMediaChange);
        }

        // Apply immediately
        updateEffectiveTheme();
        initialized = true;
    }

    const isDark = computed(() => {
        if (currentTheme.value === 'dark') return true;
        if (currentTheme.value === 'light') return false;
        return systemDark.value;
    });

    const setTheme = (theme) => {
        if (!['light', 'dark', 'system'].includes(theme)) return;
        currentTheme.value = theme;
        try {
            localStorage.setItem(THEME_STORAGE_KEY, theme);
        } catch (e) {}
        updateEffectiveTheme();
    };

    const toggleTheme = () => {
        // Simple toggle between light and dark
        if (isDark.value) {
            setTheme('light');
        } else {
            setTheme('dark');
        }
    };

    return {
        theme: currentTheme,
        isDark,
        setTheme,
        toggleTheme,
    };
}
