<template>
    <Head>
        <title>{{ t("orders.head_title") }}</title>
        <meta name="description" :content="t('orders.head_desc')" />
    </Head>

    <h1 class="page-title">{{ t("orders.heading") }}</h1>

    <section
        v-for="order in orders"
        :key="order.id"
        class="mt-10 mb-12 border-b border-neutral-200 pb-8 dark:border-neutral-800"
    >
        <div class="mb-6 flex flex-wrap items-baseline justify-between gap-2">
            <h2 class="text-xl font-semibold">{{ t("orders.placed_on", { date: order.date }) }}</h2>
            <p v-if="order.total !== null" class="text-lg font-bold tabular-nums">
                {{ t("orders.total", { total: formatPrice(order.total) }) }}
            </p>
        </div>
        <KitGrid :kits="order.kits" />
    </section>
    <p v-if="!orders.length" class="mt-12 mb-9 text-neutral-600 dark:text-neutral-400">{{ t("orders.empty") }}</p>
</template>

<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import { computed } from "vue";
import KitGrid from "@/Pages/Shared/KitGrid.vue";
import type { Order } from "@/types";
import { useTranslation } from "@/useTranslation";

const props = defineProps<{ orders: Order[] }>();
const { t, locale, formatPrice } = useTranslation();

/** Orders placed before prices existed have no total rather than a guessed one. */
const orderTotal = (order: Order): number | null =>
    order.items.some((item) => item.pivot.unit_price === null)
        ? null
        : order.items.reduce((sum, item) => sum + (item.pivot.unit_price ?? 0) * item.pivot.quantity, 0);

const orders = computed(() =>
    props.orders.map((order) => ({
        id: order.id,
        date: new Date(order.created_at).toLocaleDateString(locale.value === "hu" ? "hu-HU" : "en-GB", {
            dateStyle: "long",
        }),
        total: orderTotal(order),
        kits: order.items.map((item, index) => ({
            key: `${order.id}-${index}`,
            href: `/items/${item.id}`,
            team: item.team,
            type: item.type,
            brand: item.brand,
            image: item.image,
            price: item.pivot.unit_price ?? undefined,
            details: [
                t("orders.size", { size: item.pivot.size }),
                t("orders.quantity", { quantity: item.pivot.quantity }),
            ],
        })),
    })),
);
</script>
