<template>
    <Head>
        <title>{{ t("items.head_title") }}</title>
        <meta name="description" :content="t('items.head_desc')" />
    </Head>

    <h1 class="text-5xl tracking-tight text-balance">{{ t("items.heading") }}</h1>

    <p v-if="!filteredItems.length" class="mt-12 mb-9 text-neutral-600 dark:text-neutral-400">
        {{ t("items.empty") }}
    </p>

    <KitGrid v-else class="mt-10 mb-12" :kits="kits" />
</template>

<script setup lang="ts">
import { Head, usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import KitGrid from "@/Pages/Shared/KitGrid.vue";
import type { Item } from "@/types";
import { useTranslation } from "@/useTranslation";

const props = defineProps<{ items: Item[] }>();
const page = usePage();
const { t, kitType } = useTranslation();

/** Header categories search by "Home"/"Away"; the database stores Hungarian values. */
const kitTypeAliases: Record<string, string> = { Hazai: "Home", Vendég: "Away" };

const filteredItems = computed(() => {
    const term = (new URLSearchParams(page.url.split("?")[1] ?? "").get("search") ?? "")
        .trim()
        .toLowerCase();
    if (!term) {
        return props.items;
    }
    return props.items.filter((item) =>
        [item.team, item.type, kitTypeAliases[item.type], kitType(item.type), item.brand].some((field) =>
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
    })),
);
</script>
