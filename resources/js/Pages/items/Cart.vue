<template>
    <Head>
        <title>{{ t("cart.head_title") }}</title>
        <meta
            name="description"
            :content="t('cart.head_desc')"
        />
    </Head>
    <section class="mx-auto w-full max-w-6xl px-4 pb-28 lg:pb-0">
        <h1 class="text-3xl font-semibold tracking-tight text-balance sm:text-4xl">
            {{ t("cart.heading") }}
            <span
                v-if="items.length > 0"
                class="ml-2 text-xl font-normal text-neutral-500 tabular-nums dark:text-neutral-400"
            >
                {{ t("cart.items_count", { count: totalUnits }) }}
            </span>
        </h1>

        <form
            v-if="items.length > 0"
            class="mt-8 grid items-start gap-8 lg:grid-cols-[1fr_360px]"
            @submit.prevent="itemsToDatabase"
        >
            <TransitionGroup tag="ul" name="cart-line" class="relative flex flex-col gap-4">
                <li
                    v-for="item in items"
                    :key="item.id"
                    class="flex items-center gap-5 rounded-3xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900 sm:p-5"
                >
                    <div class="flex size-24 shrink-0 items-center justify-center rounded-2xl bg-neutral-100 dark:bg-neutral-800 sm:size-32">
                        <img
                            :src="`/Teams/150/${item.team}/${item.image}`"
                            :alt="item.team"
                            class="size-20 object-contain sm:size-28"
                        />
                    </div>

                    <div class="flex min-w-0 flex-1 flex-col gap-3">
                        <div>
                            <h2 class="truncate text-lg font-semibold">{{ item.team }}</h2>
                            <p class="text-sm text-neutral-600 dark:text-neutral-400">
                                {{ kitType(item.type) }} · {{ item.brand }} · {{ t("cart.size", { size: item.size }) }}
                            </p>
                        </div>

                        <div
                            class="inline-flex w-fit items-center rounded-full border border-neutral-200 dark:border-neutral-700"
                            role="group"
                            :aria-label="t('cart.quantity')"
                        >
                            <button
                                type="button"
                                class="flex size-9 cursor-pointer items-center justify-center rounded-full transition hover:bg-neutral-100 focus-visible:outline-2 focus-visible:outline-neutral-900 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-neutral-800 dark:focus-visible:outline-neutral-300"
                                :disabled="item.quantity <= 1"
                                :aria-label="t('cart.decrease')"
                                @click="changeQuantity(item.id, -1)"
                            >
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M5 12h14" /></svg>
                            </button>
                            <span class="min-w-8 text-center font-medium tabular-nums">{{ item.quantity }}</span>
                            <button
                                type="button"
                                class="flex size-9 cursor-pointer items-center justify-center rounded-full transition hover:bg-neutral-100 focus-visible:outline-2 focus-visible:outline-neutral-900 dark:hover:bg-neutral-800 dark:focus-visible:outline-neutral-300"
                                :aria-label="t('cart.increase')"
                                @click="changeQuantity(item.id, 1)"
                            >
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14" /></svg>
                            </button>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="flex size-10 shrink-0 cursor-pointer items-center justify-center self-start rounded-full text-neutral-500 transition hover:bg-red-50 hover:text-red-600 focus-visible:outline-2 focus-visible:outline-red-600 dark:hover:bg-red-950 dark:hover:text-red-400"
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
                class="fixed inset-x-0 bottom-0 z-20 border-t border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-950 lg:sticky lg:top-6 lg:rounded-3xl lg:border lg:p-6 lg:shadow-sm lg:dark:bg-neutral-900"
            >
                <div class="hidden lg:block">
                    <h2 class="text-xl font-semibold">{{ t("cart.summary") }}</h2>
                    <dl class="mt-5 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-neutral-600 dark:text-neutral-400">{{ t("cart.lines") }}</dt>
                            <dd class="font-medium tabular-nums">{{ items.length }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-neutral-600 dark:text-neutral-400">{{ t("cart.units") }}</dt>
                            <dd class="font-medium tabular-nums">{{ totalUnits }}</dd>
                        </div>
                    </dl>
                    <p class="mt-5 border-t border-neutral-200 pt-5 text-sm text-neutral-600 dark:border-neutral-800 dark:text-neutral-400">
                        {{ t("cart.summary_note") }}
                    </p>
                </div>
                <button
                    type="submit"
                    :disabled="processing"
                    class="group flex w-full cursor-pointer items-center justify-center gap-2 rounded-full bg-neutral-900 px-6 py-4 font-semibold text-white transition hover:bg-neutral-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-neutral-900 disabled:cursor-wait disabled:opacity-60 dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-200 dark:focus-visible:outline-white lg:mt-6"
                >
                    {{ t("cart.order") }}
                    <svg class="size-5 transition duration-300 group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 12h14M13 6l6 6-6 6" />
                    </svg>
                </button>
            </aside>
        </form>

        <div v-else class="mt-10 flex flex-col items-start gap-4">
            <p class="text-xl text-neutral-600 dark:text-neutral-400">{{ t("cart.empty") }}</p>
            <Link
                href="/items"
                class="rounded-full bg-neutral-900 px-6 py-3 font-semibold text-white transition hover:bg-neutral-700 dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-200"
            >
                {{ t("cart.continue") }}
            </Link>
        </div>
    </section>
</template>

<script setup lang="ts">
import { computed, onMounted, ref, TransitionGroup } from "vue";
import { Link, router, Head } from "@inertiajs/vue3";
import { readCartLines, writeCartLines } from "@/cart";
import type { CartLine } from "@/types";
import { useTranslation } from "@/useTranslation";

const { t, kitType } = useTranslation();
const items = ref<CartLine[]>([]);
const processing = ref(false);

const totalUnits = computed(() =>
    items.value.reduce((sum, item) => sum + (Number(item.quantity) || 1), 0),
);

onMounted(() => {
    items.value = readCartLines();
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
            },
            onSuccess: () => {
                writeCartLines([]);
                items.value = [];
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
