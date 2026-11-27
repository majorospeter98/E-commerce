import { router, usePage } from "@inertiajs/vue3";
import { computed, type ComputedRef } from "vue";

export type Locale = "hu" | "en";

export const LOCALES: Locale[] = ["hu", "en"];

type Replacements = Record<string, string | number>;

interface Translation {
    locale: ComputedRef<Locale>;
    t: (key: string, replacements?: Replacements) => string;
    kitType: (type: string) => string;
    garment: (image: string) => string;
    formatPrice: (amount: number) => string;
    setLocale: (locale: Locale) => void;
}

/**
 * Reads the active locale and the translation strings the server shares
 * through Inertia (see HandleInertiaRequests), and switches the locale.
 */
export const useTranslation = (): Translation => {
    const page = usePage();
    const locale = computed(() => (page.props.locale as Locale | undefined) ?? "hu");
    const strings = computed(() => (page.props.translations as Record<string, string> | undefined) ?? {});

    const t = (key: string, replacements: Replacements = {}): string =>
        (strings.value[key] ?? key).replace(/:(\w+)/g, (match, name: string) =>
            name in replacements ? String(replacements[name]) : match,
        );

    /** Kit types ("Home", "Away") are data values; translate them when a label exists. */
    const kitType = (type: string): string => strings.value[`kit.type.${type}`] ?? type;

    /** The catalogue has no garment column; shorts are recognised by their image name. */
    const garment = (image: string): string => t(image.startsWith("short") ? "kit.shorts" : "kit.shirt");

    /** Prices are whole forints, written with "Ft" in every language. */
    const formatPrice = (amount: number): string =>
        `${new Intl.NumberFormat(locale.value === "hu" ? "hu-HU" : "en-GB").format(amount)} Ft`;

    const setLocale = (next: Locale): void => {
        if (next === locale.value) {
            return;
        }
        router.post("/locale", { locale: next }, { preserveScroll: true, preserveState: true });
    };

    return { locale, t, kitType, garment, formatPrice, setLocale };
};
