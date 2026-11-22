<script setup>
import { Link, router, usePage } from "@inertiajs/vue3";
import { computed, onBeforeUnmount, onMounted, ref } from "vue";

const page = usePage();
const isLoggedIn = computed(() => Boolean(page.props.auth));
const currentPath = computed(() => page.url.split("?")[0]);
const currentSearch = computed(
    () => new URLSearchParams(page.url.split("?")[1] ?? "").get("search") ?? "",
);

const categories = [
    { label: "All", search: "" },
    { label: "Home kits", search: "Home" },
    { label: "Away kits", search: "Away" },
    { label: "Adidas", search: "Adidas" },
    { label: "Nike", search: "Nike" },
];

const isShopPage = computed(() => currentPath.value === "/items" || currentPath.value === "/");

const isCategoryActive = (category) => currentSearch.value === category.search;

const categoryHref = (category) =>
    category.search ? "/items?search=" + encodeURIComponent(category.search) : "/items";

const cartCount = ref(0);
const readCart = () => {
    try {
        const stored = JSON.parse(localStorage.getItem("items") ?? "[]");
        cartCount.value = Array.isArray(stored)
            ? stored.reduce((sum, row) => sum + (Number(row.quantity) || 1), 0)
            : 0;
    } catch {
        cartCount.value = 0;
    }
};

const query = ref("");
const searchRef = ref(null);

const submitSearch = () => {
    const term = query.value.trim();
    router.get("/items", term ? { search: term } : {});
};

const onKeydown = (event) => {
    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === "k") {
        event.preventDefault();
        searchRef.value?.focus();
    }
};

let stopNavigate = null;
onMounted(() => {
    readCart();
    query.value = currentSearch.value;
    window.addEventListener("storage", readCart);
    window.addEventListener("cart-updated", readCart);
    window.addEventListener("keydown", onKeydown);
    stopNavigate = router.on("navigate", readCart);
});
onBeforeUnmount(() => {
    window.removeEventListener("storage", readCart);
    window.removeEventListener("cart-updated", readCart);
    window.removeEventListener("keydown", onKeydown);
    stopNavigate?.();
});
</script>

<template>
    <header class="relative left-1/2 mt-0 w-screen -translate-x-1/2 bg-white">
        <div class="bg-neutral-950 text-[12.5px] font-semibold text-white">
            <div class="mx-auto flex w-[90%] max-w-[1300px] items-center justify-between py-2">
                <p class="flex items-center gap-2">
                    <span class="size-1.5 rounded-full bg-brand" aria-hidden="true"></span>
                    26/27 season starts now
                </p>
            </div>
        </div>

        <div class="mx-auto flex w-[90%] max-w-[1300px] items-center gap-4 py-4 sm:gap-8">
            <Link href="/" class="shrink-0" aria-label="football. home">
                <img
                    src="/public/ball.webp"
                    alt="football. logo"
                    width="48"
                    height="48"
                    class="size-12 object-contain transition-transform duration-500 ease-out hover:rotate-[24deg]"
                />
            </Link>

            <form
                class="flex min-w-0 max-w-[620px] flex-1 items-center overflow-hidden rounded-xl border-[1.5px] border-neutral-950 focus-within:ring-2 focus-within:ring-brand focus-within:ring-offset-2"
                role="search"
                @submit.prevent="submitSearch"
            >
                <input
                    ref="searchRef"
                    v-model="query"
                    type="search"
                    placeholder="Search for a club, brand or kit"
                    aria-label="Search products"
                    class="min-w-0 flex-1 bg-transparent px-4 py-3 text-sm text-neutral-900 outline-none placeholder:text-neutral-500"
                />
                <button
                    type="submit"
                    class="grid self-stretch bg-neutral-950 px-5 text-white transition-colors hover:bg-brand"
                    aria-label="Search"
                >
                    <svg viewBox="0 0 24 24" class="size-5 place-self-center" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="11" cy="11" r="6.5" />
                        <path d="m16 16 4 4" />
                    </svg>
                </button>
            </form>

            <div class="ml-auto flex items-center gap-5 sm:gap-7">
                <Link href="/account" class="group flex flex-col items-center gap-0.5 text-xs font-semibold" aria-label="Account">
                    <svg viewBox="0 0 24 24" class="size-6 transition-transform group-hover:-translate-y-0.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="8.5" r="3.6" />
                        <path d="M4.8 20c.9-3.6 3.7-5.4 7.2-5.4s6.3 1.8 7.2 5.4" />
                    </svg>
                    <span class="hidden sm:block">Account</span>
                </Link>
                <Link v-if="isLoggedIn" href="/account/favourites" class="group hidden flex-col items-center gap-0.5 text-xs font-semibold sm:flex" aria-label="Saved">
                    <svg viewBox="0 0 24 24" class="size-6 transition-transform group-hover:-translate-y-0.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.6-7 10-7 10z" />
                    </svg>
                    <span>Saved</span>
                </Link>
                <Link href="/cart" class="group relative flex flex-col items-center gap-0.5 text-xs font-semibold" aria-label="Cart">
                    <svg viewBox="0 0 24 24" class="size-6 transition-transform group-hover:-translate-y-0.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 7h14l-1.4 11H6.4zM9 7a3 3 0 0 1 6 0" />
                    </svg>
                    <span class="hidden sm:block">Cart</span>
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
            class="border-t border-neutral-200"
            aria-label="Categories"
        >
            <ul class="mx-auto flex w-[90%] max-w-[1300px] gap-7 max-sm:overflow-x-auto text-sm font-semibold sm:gap-9">
                <li v-for="category in categories" :key="category.label" class="shrink-0">
                    <Link
                        :href="categoryHref(category)"
                        :class="[
                            'relative block py-3.5 transition-colors after:absolute after:inset-x-0 after:bottom-0 after:h-[3px] after:origin-left after:bg-brand after:transition-transform after:duration-300 hover:after:scale-x-100',
                            isCategoryActive(category)
                                ? 'text-neutral-950 after:scale-x-100'
                                : 'text-neutral-600 after:scale-x-0 hover:text-neutral-950',
                        ]"
                    >
                        {{ category.label }}
                    </Link>
                </li>
            </ul>
        </nav>
    </header>
</template>
