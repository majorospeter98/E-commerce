<template>
    <Head>
        <title>{{ t("items.head_title") }}</title>
        <meta name="description" :content="t('items.head_desc')" />
    </Head>

    <h1 class="page-title">{{ tCount("items.heading", filteredItems.length) }}</h1>

    <p v-if="!filteredItems.length" class="mt-12 mb-9 text-muted">
        {{ t("items.empty") }}
    </p>

    <template v-else>
        <p class="mt-2 text-muted tabular-nums" role="status">{{ tCount("items.count", kits.length) }}</p>
        <KitGrid class="mt-8 mb-12" :kits="kits" />
    </template>
</template>

<script setup lang="ts">
import { Head, usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import KitGrid from "@/Pages/Shared/KitGrid.vue";
import type { Item } from "@/types";
import { useTranslation } from "@/useTranslation";

const props = defineProps<{ items: Item[] }>();
const page = usePage();
const { t, tCount, kitType, garment } = useTranslation();

const kitTypeAliases: Record<string, string> = { Hazai: "Home", Vendég: "Away" };

const isShorts = (item: Item): boolean => item.image.startsWith("short");

const matchesCategory = (item: Item, category: string): boolean => {
    if (!category) {
        return true;
    }
    if (category === "Shorts") {
        return isShorts(item);
    }
    if (category === "Home" || category === "Away") {
        return !isShorts(item) && (kitTypeAliases[item.type] ?? item.type) === category;
    }
    return item.brand === category;
};

const filteredItems = computed(() => {
    const query = new URLSearchParams(page.url.split("?")[1] ?? "");
    const term = (query.get("search") ?? "").trim().toLowerCase();
    const inCategory = props.items.filter((item) => matchesCategory(item, query.get("category") ?? ""));
    if (!term) {
        return inCategory;
    }
    return inCategory.filter((item) =>
        [
            item.team,
            ...(isShorts(item) ? [] : [item.type, kitTypeAliases[item.type], kitType(item.type)]),
            item.brand,
            garment(item.image),
            isShorts(item) ? "Shorts" : "",
        ].some((field) =>
            String(field ?? "")
                .toLowerCase()
                .includes(term),
        ),
    );
});

const kits = computed(() =>
    filteredItems.value.map((item) => ({
        key: item.id,
        href: `/items/${item.id}`,
        team: item.team,
        type: item.type,
        brand: item.brand,
        image: item.image,
        price: item.price,
    })),
);
</script>
