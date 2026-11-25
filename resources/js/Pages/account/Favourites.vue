<template>
    <Head>
        <title>{{ t("fav.head_title") }}</title>
        <meta name="description" :content="t('fav.head_desc')" />
    </Head>

    <h1 class="text-5xl tracking-tight text-balance">{{ t("fav.heading") }}</h1>

    <KitGrid
        v-if="props.fav.length > 0"
        class="mt-10 mb-12"
        :kits="kits"
        :remove-label="removeLabel"
        @remove="removeFavourite"
    />
    <p v-else class="mt-12 mb-9 text-neutral-600 dark:text-neutral-400">{{ t("fav.empty") }}</p>
</template>

<script setup lang="ts">
import { Head, router } from "@inertiajs/vue3";
import { computed } from "vue";
import KitGrid from "@/Pages/Shared/KitGrid.vue";
import type { Favourite, Kit } from "@/types";
import { useTranslation } from "@/useTranslation";

const props = defineProps<{ fav: Favourite[] }>();
const { t } = useTranslation();

const kits = computed(() =>
    props.fav.map((favourite) => ({
        key: favourite.id,
        href: `/items/${favourite.item.id}`,
        team: favourite.item.team,
        type: favourite.item.type,
        brand: favourite.item.brand,
        image: favourite.item.image,
    })),
);

const removeLabel = (kit: Kit): string => t("fav.remove", { team: kit.team });

function removeFavourite(kit: Kit): void {
    const favourite = props.fav.find((row) => row.id === kit.key);
    if (!favourite) {
        return;
    }
    router.delete("/deleteFavourite", {
        data: { item_id: favourite.item_id },
        preserveScroll: true,
    });
}
</script>
