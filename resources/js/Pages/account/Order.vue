<template>
    <Head>
        <title>{{ t("orders.head_title") }}</title>
        <meta name="description" :content="t('orders.head_desc')" />
    </Head>

    <h1 class="text-5xl tracking-tight text-balance">{{ t("orders.heading") }}</h1>

    <KitGrid v-if="props.orders.length > 0" class="mt-10 mb-12" :kits="kits" />
    <p v-else class="mt-12 mb-9 text-neutral-600 dark:text-neutral-400">{{ t("orders.empty") }}</p>
</template>

<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import { computed } from "vue";
import KitGrid from "@/Pages/Shared/KitGrid.vue";
import type { Order } from "@/types";
import { useTranslation } from "@/useTranslation";

const props = defineProps<{ orders: Order[] }>();
const { t } = useTranslation();

const kits = computed(() =>
    props.orders.flatMap((order) =>
        order.items.map((item) => ({
            key: `${order.id}-${item.id}`,
            href: `/items/${item.id}`,
            team: item.team,
            type: item.type,
            brand: item.brand,
            image: item.image,
            details: [
                t("orders.size", { size: item.pivot.size }),
                t("orders.quantity", { quantity: item.pivot.quantity }),
            ],
        })),
    ),
);
</script>
