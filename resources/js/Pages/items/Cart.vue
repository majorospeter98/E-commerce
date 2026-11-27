<template>
    <Head>
        <title>{{ t("cart.head_title") }}</title>
        <meta
            name="description"
            :content="t('cart.head_desc')"
        />
    </Head>
    <section class="w-full pb-12">
        <h1 class="page-title">
            {{ t("cart.heading") }}
            <span
                v-if="items.length > 0"
                class="ml-2 text-xl font-normal text-faint tabular-nums"
            >
                {{ tCount("cart.items_count", totalUnits) }}
            </span>
        </h1>

        <form
            v-if="items.length > 0"
            class="mt-8 flex flex-col gap-8 lg:grid lg:grid-cols-[1fr_360px] lg:items-start"
            @submit.prevent="itemsToDatabase"
        >
            <TransitionGroup tag="ul" name="cart-line" class="relative flex flex-col gap-4">
                <li
                    v-for="item in items"
                    :key="item.id"
                    class="flex items-center gap-5 rounded-3xl border border-line bg-raised p-4 shadow-sm sm:p-5"
                >
                    <div class="flex size-24 shrink-0 items-center justify-center rounded-2xl bg-chip sm:size-32">
                        <img
                            :src="`/Teams/150/${item.team}/${item.image}`"
                            :alt="t('kit.alt', { team: item.team, type: kitType(item.type), garment: garment(item.image) })"
                            class="size-20 object-contain dark:rounded-xl sm:size-28"
                        />
                    </div>

                    <div class="flex min-w-0 flex-1 flex-col gap-3">
                        <div>
                            <h2 class="truncate text-lg font-semibold">{{ item.team }}</h2>
                            <p class="text-sm text-muted">
                                {{ garment(item.image) }} · {{ kitType(item.type) }} · {{ item.brand }} · {{ t("cart.size", { size: item.size }) }}
                            </p>
                            <p v-if="prices[item.item_id] !== undefined" class="mt-1 flex flex-wrap gap-x-2 text-sm whitespace-nowrap tabular-nums">
                                {{ t("cart.unit_price", { price: formatPrice(prices[item.item_id]) }) }}
                                <span class="text-base font-bold">{{ formatPrice(lineTotal(item)) }}</span>
                            </p>
                            <p v-if="unavailable.includes(item.id)" class="error-text mt-1 text-sm font-semibold text-brand-strong">
                                {{ t("cart.line_unavailable") }}
                            </p>
                        </div>

                        <div
                            class="inline-flex w-fit items-center rounded-full border border-control"
                            role="group"
                            :aria-label="t('cart.quantity')"
                        >
                            <button
                                type="button"
                                class="flex size-11 cursor-pointer items-center justify-center rounded-full transition hover:bg-chip focus-visible:outline-2 focus-visible:outline-focus disabled:cursor-not-allowed disabled:opacity-40"
                                :disabled="item.quantity <= 1"
                                :aria-label="t('cart.decrease')"
                                @click="changeQuantity(item.id, -1)"
                            >
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M5 12h14" /></svg>
                            </button>
                            <span class="min-w-8 text-center font-semibold tabular-nums">{{ item.quantity }}</span>
                            <button
                                type="button"
                                class="flex size-11 cursor-pointer items-center justify-center rounded-full transition hover:bg-chip focus-visible:outline-2 focus-visible:outline-focus"
                                :aria-label="t('cart.increase')"
                                @click="changeQuantity(item.id, 1)"
                            >
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14" /></svg>
                            </button>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="flex size-11 shrink-0 cursor-pointer items-center justify-center self-start rounded-full text-icon-strong transition hover:bg-chip hover:text-brand focus-visible:outline-2 focus-visible:outline-red-600 dark:hover:text-accent-dark"
                        :aria-label="t('cart.remove')"
                        @click="deleteItem(item.id)"
                    >
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3" />
                        </svg>
                    </button>
                </li>
            </TransitionGroup>

            <aside
                class="sticky bottom-0 z-20 border-t border-line bg-page py-4 lg:bottom-auto lg:top-6 lg:px-6 lg:rounded-3xl lg:border lg:p-6 lg:shadow-sm lg:bg-raised"
            >
                <div class="hidden lg:block">
                    <h2 class="text-xl font-semibold">{{ t("cart.summary") }}</h2>
                    <dl class="mt-5 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-muted">{{ tCount("cart.lines", items.length) }}</dt>
                            <dd class="font-semibold tabular-nums">{{ items.length }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted">{{ tCount("cart.units", totalUnits) }}</dt>
                            <dd class="font-semibold tabular-nums">{{ totalUnits }}</dd>
                        </div>
                    </dl>
                    <div class="mt-5 flex justify-between text-lg font-bold">
                        <span>{{ t("cart.total") }}</span>
                        <span class="tabular-nums">{{ formatPrice(total) }}</span>
                    </div>
                    <p class="mt-5 border-t border-line pt-5 text-sm text-muted">
                        {{ t(page.props.auth ? "cart.summary_note" : "cart.summary_note_guest") }}
                    </p>
                </div>
                <div class="mb-3 flex justify-between text-lg font-bold lg:hidden">
                    <span>{{ t("cart.total") }}</span>
                    <span class="tabular-nums">{{ formatPrice(total) }}</span>
                </div>
                <p v-if="failure" role="alert" class="error-text mb-3 text-sm font-semibold text-brand-strong lg:mt-4">
                    {{ failure }}
                </p>
                <button
                    type="submit"
                    :disabled="processing"
                    class="btn-primary w-full lg:mt-6"
                >
                    {{ t("cart.order") }}
                </button>
            </aside>
        </form>

        <div v-else-if="loaded" class="mx-auto mt-8 flex max-w-md flex-col items-center py-10 text-center">
            <span class="grid size-24 place-items-center rounded-full bg-field text-brand" aria-hidden="true">
                <svg viewBox="0 0 24 24" class="size-11" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path v-if="placed" d="m5 12.5 4.5 4.5L19 7.5" />
                    <path v-else d="M5 7h14l-1.4 13H6.4zM9 10V6.5a3 3 0 0 1 6 0V10" />
                </svg>
            </span>
            <p class="mt-6 text-2xl font-bold text-balance" role="status">
                {{ t(placed ? "cart.placed" : "cart.empty") }}
            </p>
            <p v-if="!placed" class="mt-2 text-muted text-pretty">
                {{ t("cart.empty_hint") }}
            </p>
            <Link href="/items" class="btn-primary mt-8">
                {{ t("cart.continue") }}
            </Link>
        </div>
    </section>
</template>

<script setup lang="ts">
import { computed, onMounted, ref, TransitionGroup } from "vue";
import { Link, router, Head, usePage } from "@inertiajs/vue3";
import { readCartLines, writeCartLines } from "@/cart";
import type { CartLine, Prices } from "@/types";
import { useTranslation } from "@/useTranslation";

const props = defineProps<{ prices: Prices }>();
const { t, tCount, kitType, garment, formatPrice } = useTranslation();
const items = ref<CartLine[]>([]);
const processing = ref(false);
const placed = ref(false);
const loaded = ref(false);
const failure = ref("");
const unavailable = ref<string[]>([]);
const page = usePage();

const totalUnits = computed(() =>
    items.value.reduce((sum, item) => sum + (Number(item.quantity) || 1), 0),
);

const lineTotal = (line: CartLine): number => (props.prices[line.item_id] ?? 0) * (Number(line.quantity) || 1);
const total = computed(() => items.value.reduce((sum, line) => sum + lineTotal(line), 0));

onMounted(() => {
    items.value = readCartLines();
    loaded.value = true;
});

function deleteItem(id: string) {
    writeCartLines(items.value.filter((item) => item.id !== id));
    items.value = readCartLines();
}

function changeQuantity(id: string, delta: number) {
    writeCartLines(
        items.value.map((item) =>
            item.id === id
                ? { ...item, quantity: Math.max(1, (Number(item.quantity) || 1) + delta) }
                : item,
        ),
    );
    items.value = readCartLines();
}

function itemsToDatabase() {
    if (processing.value) {
        return;
    }

    const orders: Pick<CartLine, "id" | "item_id" | "size" | "quantity">[] = items.value.map(
        (item) => ({
            id: item.id,
            item_id: item.item_id,
            size: item.size,
            quantity: item.quantity,
        }),
    );

    router.post(
        "/account/order",
        { orders },
        {
            onStart: () => {
                processing.value = true;
                failure.value = "";
                unavailable.value = [];
            },
            onSuccess: () => {
                writeCartLines([]);
                items.value = [];
                placed.value = true;
            },
            onError: (errors) => {
                failure.value = t("cart.failed");
                unavailable.value = Object.keys(errors).flatMap((key) => {
                    const index = key.match(/^orders\.(\d+)\.item_id$/)?.[1];
                    return index === undefined ? [] : [orders[Number(index)].id];
                });
            },
            onFinish: () => {
                processing.value = false;
            },
        },
    );
}
</script>

<style scoped>
.cart-line-move,
.cart-line-enter-active {
    transition: transform 0.55s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.4s;
}
.cart-line-leave-active {
    position: absolute;
    inset-inline: 0;
    z-index: 0;
    transition: transform 0.45s cubic-bezier(0.7, 0, 0.84, 0), opacity 0.35s ease-in,
        filter 0.35s ease-in;
}
.cart-line-leave-to {
    opacity: 0;
    transform: translateX(3rem) scale(0.94);
    filter: blur(6px);
}
.cart-line-enter-from {
    opacity: 0;
    transform: translateY(1rem);
}
@media (prefers-reduced-motion: reduce) {
    .cart-line-move,
    .cart-line-enter-active,
    .cart-line-leave-active {
        transition: opacity 0.2s;
    }
    .cart-line-leave-to {
        transform: none;
        filter: none;
    }
}
</style>
