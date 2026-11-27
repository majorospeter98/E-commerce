import { ref } from "vue";

export type Locale = "hu" | "en";

export const LOCALES: Locale[] = ["hu", "en"];

const STORAGE_KEY = "locale";

export const locale = ref<Locale>("hu");

export function restoreLocale(): void {
    try {
        const stored = localStorage.getItem(STORAGE_KEY) as Locale | null;
        if (stored && LOCALES.includes(stored)) {
            locale.value = stored;
        }
    } catch {
        locale.value = "hu";
    }
}

export function saveLocale(next: Locale): void {
    locale.value = next;
    try {
        localStorage.setItem(STORAGE_KEY, next);
    } catch {
        return;
    }
}
