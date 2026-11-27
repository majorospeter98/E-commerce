<script setup lang="ts">
import { Link, router, usePage } from "@inertiajs/vue3";
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from "vue";
import { useTheme } from "../../useTheme";
import { readCartLines } from "@/cart";
import { LOCALES, useTranslation } from "@/useTranslation";

const ballPhoto = "/ball.webp";
const { isDark, toggleTheme } = useTheme();
const { t, locale, setLocale } = useTranslation();
const page = usePage();
const isLoggedIn = computed(() => Boolean(page.props.auth));
const currentPath = computed(() => page.url.split("?")[0]);
const currentCategory = computed(
    () => new URLSearchParams(page.url.split("?")[1] ?? "").get("category") ?? "",
);
const currentSearch = computed(
    () => new URLSearchParams(page.url.split("?")[1] ?? "").get("search") ?? "",
);

interface Category {
    labelKey: string;
    value: string;
}

const categories: Category[] = [
    { labelKey: "cat.all", value: "" },
    { labelKey: "cat.home", value: "Home" },
    { labelKey: "cat.away", value: "Away" },
    { labelKey: "cat.shorts", value: "Shorts" },
    { labelKey: "cat.adidas", value: "Adidas" },
    { labelKey: "cat.nike", value: "Nike" },
    { labelKey: "cat.joma", value: "Joma" },
    { labelKey: "cat.hummel", value: "Hummel" },
];

const isShopPage = computed(() => currentPath.value === "/items" || currentPath.value === "/");

const isCategoryActive = (category: Category): boolean =>
    !currentSearch.value && currentCategory.value === category.value;

const categoryHref = (category: Category): string =>
    category.value ? "/items?category=" + encodeURIComponent(category.value) : "/items";

const categoryList = ref<HTMLUListElement | null>(null);

const revealActiveCategory = async (): Promise<void> => {
    await nextTick();
    const list = categoryList.value;
    const link = list?.querySelector('[aria-current="page"]');
    if (!list || !link) {
        return;
    }
    const listBox = list.getBoundingClientRect();
    const linkBox = link.getBoundingClientRect();
    list.scrollLeft += linkBox.left - listBox.left - (list.clientWidth - linkBox.width) / 2;
};
watch([currentPath, currentSearch, currentCategory], revealActiveCategory);

const cartCount = ref(0);
const readCart = () => {
    cartCount.value = readCartLines().reduce((sum, row) => sum + (Number(row.quantity) || 1), 0);
};

const query = ref("");
const searchRef = ref<HTMLInputElement | null>(null);

watch(currentSearch, (value) => {
    if (value !== query.value.trim()) {
        query.value = value;
    }
});

const SEARCH_DELAY_MS = 300;
let searchTimer: number | undefined;
const cancelPendingSearch = (): void => window.clearTimeout(searchTimer);

const showResults = (): void => {
    cancelPendingSearch();
    const term = query.value.trim();
    if (term === currentSearch.value) {
        return;
    }
    router.replace({
        url: term ? "/items?search=" + encodeURIComponent(term) : "/items",
        preserveState: true,
        preserveScroll: true,
    });
};

const dropTypedSearch = (): void => {
    cancelPendingSearch();
    query.value = "";
};

const onSearchInput = (): void => {
    cancelPendingSearch();
    if (!isShopPage.value) {
        return;
    }
    if (!query.value) {
        showResults();
        return;
    }
    searchTimer = window.setTimeout(showResults, SEARCH_DELAY_MS);
};

const clearSearch = (): void => {
    query.value = "";
    onSearchInput();
    searchRef.value?.focus();
};

const submitSearch = () => {
    cancelPendingSearch();
    const term = query.value.trim();
    if (!term) {
        showResults();
        return;
    }
    router.get("/items", { search: term });
};

const isNarrow = ref(false);
let stopNavigate: (() => void) | null = null;
let stopBefore: (() => void) | null = null;
onMounted(() => {
    readCart();
    revealActiveCategory();
    query.value = currentSearch.value;
    isNarrow.value = window.matchMedia("(max-width: 639px)").matches;
    window.addEventListener("storage", readCart);
    window.addEventListener("cart-updated", readCart);
    stopNavigate = router.on("navigate", readCart);
    stopBefore = router.on("before", cancelPendingSearch);
});
onBeforeUnmount(() => {
    window.removeEventListener("storage", readCart);
    window.removeEventListener("cart-updated", readCart);
    stopNavigate?.();
    stopBefore?.();
    cancelPendingSearch();
});
</script>

<template>
    <header class="bg-page">
        <div class="bg-night text-[16px] font-semibold text-white dark:border-b dark:border-night-line dark:bg-night-raised">
            <div class="mx-auto flex w-[90%] max-w-[1300px] items-center justify-between">
                <p class="flex items-center gap-2">
                    <span class="size-1.5 rounded-full bg-brand" aria-hidden="true"></span>
                    <span class="max-[359px]:hidden">{{ t("header.announcement") }}</span>
                    <span class="min-[360px]:hidden">{{ t("header.announcement_short") }}</span>
                </p>
                <div class="flex items-center" role="group" :aria-label="t('header.language')">
                    <template v-for="(code, index) in LOCALES" :key="code">
                        <span v-if="index" class="mx-1.5 h-3 w-px bg-white/30" aria-hidden="true"></span>
                        <button
                            type="button"
                            :lang="code"
                            :aria-pressed="locale === code"
                            :class="[
                                'min-h-11 min-w-11 cursor-pointer rounded px-2 text-sm font-bold uppercase tracking-wide transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white',
                                locale === code ? 'text-white' : 'text-night-muted hover:text-white',
                            ]"
                            @click="setLocale(code)"
                        >
                            {{ code }}
                        </button>
                    </template>
                </div>
            </div>
        </div>

        <div class="mx-auto flex w-[90%] max-w-[1300px] items-center gap-2 py-3 sm:gap-8">
            <Link href="/" class="shrink-0" :aria-label="t('header.logo_label')">
                <svg viewBox="4.4 0.4 88.8 88.8" class="size-11 drop-shadow-md sm:size-12" aria-hidden="true">
                    <clipPath id="ball-clip">
                        <circle cx="48.8" cy="44.8" r="44" />
                    </clipPath>
                    <image :href="ballPhoto" width="100" height="100" clip-path="url(#ball-clip)" />
                </svg>
            </Link>

            <form
                class="group/search flex h-11 min-w-0 max-w-[620px] flex-1 items-center gap-2 rounded-full bg-field pl-3 pr-2 text-muted sm:gap-3 sm:pl-4 ring-2 ring-transparent transition-[background-color,box-shadow] duration-200 hover:bg-field-hover focus-within:bg-raised focus-within:text-strong focus-within:ring-focus focus-within:shadow-[0_8px_24px_-10px_rgb(0_0_0/0.3)] dark:focus-within:bg-chip dark:focus-within:shadow-[0_8px_24px_-10px_rgb(0_0_0/0.8)]"
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
                    @input="onSearchInput"
                    :placeholder="t(isNarrow ? 'header.search_placeholder_short' : 'header.search_placeholder')"
                    :aria-label="t('header.search_label')"
                    class="min-w-0 flex-1 self-stretch bg-transparent py-2 text-sm text-ink outline-none placeholder:text-hint"
                />
                <button
                    v-if="query"
                    type="button"
                    class="grid size-7 shrink-0 place-items-center rounded-full text-icon-strong transition-colors hover:bg-press hover:text-strong focus-visible:outline-2 focus-visible:outline-strong"
                    :aria-label="t('header.search_clear')"
                    @click="clearSearch"
                >
                    <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                        <path d="M6 6l12 12M18 6 6 18" />
                    </svg>
                </button>
            </form>

            <div class="ml-auto flex items-center sm:gap-4">
                <button
                    type="button"
                    class="group flex min-h-11 min-w-11 cursor-pointer flex-col items-center justify-center gap-0.5 rounded-md text-sm font-semibold text-indigo-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-current dark:text-amber-300"
                    :aria-label="isDark ? t('header.to_light') : t('header.to_dark')"
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
                <Link href="/account" class="group flex min-h-11 min-w-11 flex-col items-center justify-center gap-0.5 text-sm font-semibold" :aria-label="t('header.account')">
                    <svg viewBox="0 0 24 24" class="size-6 transition-transform group-hover:-translate-y-0.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="8.5" r="3.6" />
                        <path d="M4.8 20c.9-3.6 3.7-5.4 7.2-5.4s6.3 1.8 7.2 5.4" />
                    </svg>
                    <span class="hidden sm:block">{{ t("header.account") }}</span>
                </Link>
                <Link v-if="isLoggedIn" href="/account/favourites" class="group hidden min-h-11 min-w-11 flex-col items-center justify-center gap-0.5 text-sm font-semibold sm:flex" :aria-label="t('header.saved')">
                    <svg viewBox="0 0 24 24" class="size-6 transition-transform group-hover:-translate-y-0.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.6-7 10-7 10z" />
                    </svg>
                    <span>{{ t("header.saved") }}</span>
                </Link>
                <Link href="/cart" class="group relative flex min-h-11 min-w-11 flex-col items-center justify-center gap-0.5 text-sm font-semibold" :aria-label="t('header.cart_with_count', { count: cartCount })">
                    <svg viewBox="0 0 24 24" class="size-6 transition-transform group-hover:-translate-y-0.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 7h14l-1.4 11H6.4zM9 7a3 3 0 0 1 6 0" />
                    </svg>
                    <span class="hidden sm:block">{{ t("header.cart") }}</span>
                    <span
                        v-if="cartCount > 0"
                        aria-hidden="true"
                        class="absolute -right-1 -top-1 grid h-5 min-w-5 place-items-center rounded-full bg-brand px-1.5 text-[13px] font-extrabold text-white"
                    >
                        {{ cartCount }}
                    </span>
                </Link>
            </div>
        </div>

        <nav
            v-if="isShopPage"
            class="border-t border-line"
            :aria-label="t('header.categories')"
        >
            <ul ref="categoryList" class="mx-auto flex w-[90%] max-w-[1300px] gap-4 overflow-x-auto text-sm font-semibold sm:gap-6">
                <li v-for="category in categories" :key="category.labelKey" class="shrink-0">
                    <Link
                        :href="categoryHref(category)"
                        :aria-current="isCategoryActive(category) ? 'page' : undefined"
                        @click="dropTypedSearch"
                        :class="[
                            'relative block px-1.5 py-3.5 transition-colors focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-current after:absolute after:inset-x-0 after:bottom-0 after:h-[3px] after:origin-left after:bg-brand after:transition-transform after:duration-300 hover:after:scale-x-100',
                            isCategoryActive(category)
                                ? 'text-heading after:scale-x-100'
                                : 'text-muted after:scale-x-0 hover:text-title',
                        ]"
                    >
                        {{ t(category.labelKey) }}
                    </Link>
                </li>
            </ul>
        </nav>
    </header>
</template>
