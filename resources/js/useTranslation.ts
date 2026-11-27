import { computed, type Ref } from "vue";
import en from "../../lang/en.json";
import hu from "../../lang/hu.json";
import { locale, saveLocale, type Locale } from "@/locale";

export { LOCALES, type Locale } from "@/locale";

const MESSAGES: Record<Locale, Record<string, string>> = { hu, en };

type Replacements = Record<string, string | number>;

interface Translation {
    locale: Ref<Locale>;
    t: (key: string, replacements?: Replacements) => string;
    tCount: (key: string, count: number) => string;
    kitType: (type: string) => string;
    garment: (image: string) => string;
    formatPrice: (amount: number) => string;
    setLocale: (locale: Locale) => void;
}

export const useTranslation = (): Translation => {
    const strings = computed(() => MESSAGES[locale.value]);

    const t = (key: string, replacements: Replacements = {}): string =>
        (strings.value[key] ?? key).replace(/:(\w+)/g, (match, name: string) =>
            name in replacements ? String(replacements[name]) : match,
        );

    const tCount = (key: string, count: number): string => t(count === 1 ? `${key}_one` : key, { count });

    const kitType = (type: string): string => strings.value[`kit.type.${type}`] ?? type;

    const garment = (image: string): string => t(image.startsWith("short") ? "kit.shorts" : "kit.shirt");

    const formatPrice = (amount: number): string =>
        `${new Intl.NumberFormat(locale.value === "hu" ? "hu-HU" : "en-GB").format(amount)} Ft`;

    const setLocale = saveLocale;

    return { locale, t, tCount, kitType, garment, formatPrice, setLocale };
};
