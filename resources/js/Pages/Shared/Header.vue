<script setup lang="ts">
import { Link, router, usePage } from "@inertiajs/vue3";
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import { useTheme } from "../../useTheme";
import { readCartLines } from "@/cart";
import { LOCALES, useTranslation } from "@/useTranslation";

const { isDark, toggleTheme } = useTheme();
const { t, locale, setLocale } = useTranslation();
const page = usePage();
const isLoggedIn = computed(() => Boolean(page.props.auth));
const currentPath = computed(() => page.url.split("?")[0]);
const currentSearch = computed(
    () => new URLSearchParams(page.url.split("?")[1] ?? "").get("search") ?? "",
);

interface Category {
    labelKey: string;
    search: string;
}

const categories: Category[] = [
    { labelKey: "cat.all", search: "" },
    { labelKey: "cat.home", search: "Home" },
    { labelKey: "cat.away", search: "Away" },
    { labelKey: "cat.adidas", search: "Adidas" },
    { labelKey: "cat.nike", search: "Nike" },
];

const isShopPage = computed(() => currentPath.value === "/items" || currentPath.value === "/");

const isCategoryActive = (category: Category): boolean => currentSearch.value === category.search;

const categoryHref = (category: Category): string =>
    category.search ? "/items?search=" + encodeURIComponent(category.search) : "/items";

const cartCount = ref(0);
const readCart = () => {
    cartCount.value = readCartLines().reduce((sum, row) => sum + (Number(row.quantity) || 1), 0);
};

const query = ref("");
const searchRef = ref<HTMLInputElement | null>(null);

const clearSearch = (): void => {
    query.value = "";
    searchRef.value?.focus();
};

const submitSearch = () => {
    const term = query.value.trim();
    router.get("/items", term ? { search: term } : {});
};

let stopNavigate: (() => void) | null = null;
onMounted(() => {
    readCart();
    query.value = currentSearch.value;
    window.addEventListener("storage", readCart);
    window.addEventListener("cart-updated", readCart);
    stopNavigate = router.on("navigate", readCart);
});
onBeforeUnmount(() => {
    window.removeEventListener("storage", readCart);
    window.removeEventListener("cart-updated", readCart);
    stopNavigate?.();
});
</script>

<template>
    <header class="relative left-1/2 mt-0 w-screen -translate-x-1/2 bg-white dark:bg-neutral-950">
        <div class="bg-neutral-950 text-[12.5px] font-semibold text-white dark:border-b dark:border-neutral-800 dark:bg-neutral-900">
            <div class="mx-auto flex w-[90%] max-w-[1300px] items-center justify-between py-2">
                <p class="flex items-center gap-2">
                    <span class="size-1.5 rounded-full bg-brand" aria-hidden="true"></span>
                    {{ t("header.announcement") }}
                </p>
                <div class="flex items-center" role="group" :aria-label="t('header.language')">
                    <template v-for="(code, index) in LOCALES" :key="code">
                        <span v-if="index" class="mx-1.5 h-3 w-px bg-white/30" aria-hidden="true"></span>
                        <button
                            type="button"
                            :lang="code"
                            :aria-pressed="locale === code"
                            :class="[
                                'rounded px-2 py-1 text-xs font-bold uppercase tracking-wide transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white',
                                locale === code ? 'text-white' : 'text-neutral-400 hover:text-white',
                            ]"
                            @click="setLocale(code)"
                        >
                            {{ code }}
                        </button>
                    </template>
                </div>
            </div>
        </div>

        <div class="mx-auto flex w-[90%] max-w-[1300px] items-center gap-4 py-4 sm:gap-8">
            <Link href="/" class="shrink-0" :aria-label="t('header.logo_label')">
                <img
                    src="/public/ball.webp"
                    :alt="t('header.logo_alt')"
                    width="48"
                    height="48"
                    class="size-12 object-contain transition-transform duration-500 ease-out hover:rotate-[24deg]"
                />
            </Link>

            <form
                class="group/search flex h-11 min-w-0 max-w-[620px] flex-1 items-center gap-3 rounded-full bg-neutral-100 pl-4 pr-2 text-neutral-500 ring-1 ring-transparent transition-[background-color,box-shadow] duration-200 hover:bg-neutral-200/70 focus-within:bg-white focus-within:text-neutral-900 focus-within:ring-neutral-300 focus-within:shadow-[0_8px_24px_-10px_rgb(0_0_0/0.3)] dark:bg-neutral-900 dark:text-neutral-400 dark:hover:bg-neutral-800 dark:focus-within:bg-neutral-800 dark:focus-within:text-white dark:focus-within:ring-white/15 dark:focus-within:shadow-[0_8px_24px_-10px_rgb(0_0_0/0.8)]"
                role="search"
                @submit.prevent="submitSearch"
            >
                <svg viewBox="0 0 24 24" class="size-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="11" cy="11" r="6.5" />
                    <path d="m16 16 4 4" />
                </svg>
                <input
                    ref="searchRef"
                    v-model="query"
                    type="search"
                    :placeholder="t('header.search_placeholder')"
                    :aria-label="t('header.search_label')"
                    class="min-w-0 flex-1 bg-transparent py-2 text-sm text-neutral-900 outline-none placeholder:text-neutral-500 dark:text-neutral-100 dark:placeholder:text-neutral-400"
                />
                <button
                    v-if="query"
                    type="button"
                    class="grid size-7 shrink-0 place-items-center rounded-full text-neutral-500 transition-colors hover:bg-neutral-200 hover:text-neutral-900 focus-visible:outline-2 focus-visible:outline-neutral-900 dark:hover:bg-neutral-700 dark:hover:text-white dark:focus-visible:outline-white"
                    :aria-label="t('header.search_clear')"
                    @click="clearSearch"
                >
                    <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                        <path d="M6 6l12 12M18 6 6 18" />
                    </svg>
                </button>
            </form>

            <div class="ml-auto flex items-center gap-5 sm:gap-7">
                <button
                    type="button"
                    class="group flex flex-col items-center gap-0.5 rounded-md text-xs font-semibold focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand"
                    :aria-label="isDark ? t('header.to_light') : t('header.to_dark')"
                    :aria-pressed="isDark"
                    @click="toggleTheme"
                >
                    <svg v-if="isDark" viewBox="0 0 24 24" class="size-6 transition-transform group-hover:-translate-y-0.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="4" />
                        <path d="M12 2.5v2M12 19.5v2M4.6 4.6 6 6M18 18l1.4 1.4M2.5 12h2M19.5 12h2M4.6 19.4 6 18M18 6l1.4-1.4" />
                    </svg>
                    <svg v-else viewBox="0 0 24 24" class="size-6 transition-transform group-hover:-translate-y-0.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M20 14.5A8 8 0 0 1 9.5 4a8 8 0 1 0 10.5 10.5z" />
                    </svg>
                    <span class="hidden sm:block">{{ isDark ? t("header.light") : t("header.dark") }}</span>
                </button>
                <Link href="/account" class="group flex flex-col items-center gap-0.5 text-xs font-semibold" :aria-label="t('header.account')">
                    <svg viewBox="0 0 24 24" class="size-6 transition-transform group-hover:-translate-y-0.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="8.5" r="3.6" />
                        <path d="M4.8 20c.9-3.6 3.7-5.4 7.2-5.4s6.3 1.8 7.2 5.4" />
                    </svg>
                    <span class="hidden sm:block">{{ t("header.account") }}</span>
                </Link>
                <Link v-if="isLoggedIn" href="/account/favourites" class="group hidden flex-col items-center gap-0.5 text-xs font-semibold sm:flex" :aria-label="t('header.saved')">
                    <svg viewBox="0 0 24 24" class="size-6 transition-transform group-hover:-translate-y-0.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.6-7 10-7 10z" />
                    </svg>
                    <span>{{ t("header.saved") }}</span>
                </Link>
                <Link href="/cart" class="group relative flex flex-col items-center gap-0.5 text-xs font-semibold" :aria-label="t('header.cart')">
                    <svg viewBox="0 0 24 24" class="size-6 transition-transform group-hover:-translate-y-0.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 7h14l-1.4 11H6.4zM9 7a3 3 0 0 1 6 0" />
                    </svg>
                    <span class="hidden sm:block">{{ t("header.cart") }}</span>
                    <span
                        class="absolute -right-2.5 -top-1.5 grid h-5 min-w-5 place-items-center rounded-full bg-brand px-1.5 text-[11px] font-extrabold text-white"
                    >
                        {{ cartCount }}
                    </span>
                </Link>
            </div>
        </div>

        <nav
            v-if="isShopPage"
            class="border-t border-neutral-200 dark:border-neutral-800"
            :aria-label="t('header.categories')"
        >
            <ul class="mx-auto flex w-[90%] max-w-[1300px] gap-7 max-sm:overflow-x-auto text-sm font-semibold sm:gap-9">
                <li v-for="category in categories" :key="category.labelKey" class="shrink-0">
                    <Link
                        :href="categoryHref(category)"
                        :class="[
                            'relative block py-3.5 transition-colors after:absolute after:inset-x-0 after:bottom-0 after:h-[3px] after:origin-left after:bg-brand after:transition-transform after:duration-300 hover:after:scale-x-100',
                            isCategoryActive(category)
                                ? 'text-neutral-950 after:scale-x-100 dark:text-white'
                                : 'text-neutral-600 after:scale-x-0 hover:text-neutral-950 dark:text-neutral-400 dark:hover:text-white',
                        ]"
                    >
                        {{ t(category.labelKey) }}
                    </Link>
                </li>
            </ul>
        </nav>
    </header>
</template>
