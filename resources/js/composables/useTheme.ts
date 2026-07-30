import { ref } from 'vue';

type Theme = 'light' | 'dark';

function readInitialTheme(): Theme {
    if (typeof document === 'undefined') {
        return 'dark';
    }

    return document.documentElement.classList.contains('dark') ? 'dark' : 'light';
}

const theme = ref<Theme>(readInitialTheme());

function apply(value: Theme) {
    theme.value = value;
    document.documentElement.classList.toggle('dark', value === 'dark');
    localStorage.setItem('theme', value);
}

export function useTheme() {
    function toggleTheme() {
        apply(theme.value === 'dark' ? 'light' : 'dark');
    }

    return { theme, toggleTheme };
}
