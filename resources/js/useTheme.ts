import { onMounted, ref, type Ref } from "vue";

const STORAGE_KEY = "theme";

const isDark = ref(false);

const applyTheme = (dark: boolean): void => {
    const root = document.documentElement;
    root.classList.add("theme-transition");
    root.classList.toggle("dark", dark);
    isDark.value = dark;
    window.setTimeout(() => root.classList.remove("theme-transition"), 300);
};

const toggleTheme = () => {
    const next = !isDark.value;
    applyTheme(next);
    try {
        localStorage.setItem(STORAGE_KEY, next ? "dark" : "light");
    } catch {
        // storage unavailable: the choice just won't persist
    }
};

export const useTheme = (): { isDark: Ref<boolean>; toggleTheme: () => void } => {
    // the same first render as the server avoids hydration mismatches
    onMounted(() => (isDark.value = document.documentElement.classList.contains("dark")));

    return { isDark, toggleTheme };
};
