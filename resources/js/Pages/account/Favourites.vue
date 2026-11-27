<template>
    <Head>
        <title>{{ t("fav.head_title") }}</title>
        <meta name="description" :content="t('fav.head_desc')" />
    </Head>

    <h1 class="page-title">{{ tCount("fav.heading", props.fav.length) }}</h1>

    <KitGrid
        v-if="props.fav.length > 0"
        class="mt-10 mb-12"
        :kits="kits"
        :remove-label="removeLabel"
        @remove="removeFavourite"
    />
    <p v-else class="mt-12 mb-9 text-muted">{{ t("fav.empty") }}</p>
</template>

<script setup lang="ts">
import { Head, router } from "@inertiajs/vue3";
import { computed, nextTick } from "vue";
import KitGrid from "@/Pages/Shared/KitGrid.vue";
import type { Favourite, Kit } from "@/types";
import { useTranslation } from "@/useTranslation";

const props = defineProps<{ fav: Favourite[] }>();
const { t, tCount } = useTranslation();

const kits = computed(() =>
    props.fav.map((favourite) => ({
        key: favourite.id,
        href: `/items/${favourite.item.id}`,
        team: favourite.item.team,
        type: favourite.item.type,
        brand: favourite.item.brand,
        image: favourite.item.image,
        price: favourite.item.price,
    })),
);

const removeLabel = (kit: Kit): string => t("fav.remove", { team: kit.team });

function removeFavourite(kit: Kit): void {
    const favourite = props.fav.find((row) => row.id === kit.key);
    if (!favourite) {
        return;
    }
    const position = props.fav.indexOf(favourite);
    router.delete("/deleteFavourite", {
        data: { item_id: favourite.item_id },
        preserveScroll: true,
        onSuccess: () =>
            nextTick(() => {
                const buttons = document.querySelectorAll<HTMLButtonElement>(".kit-remove");
                const next = buttons[Math.min(position, buttons.length - 1)];
                (next ?? document.getElementById("main"))?.focus();
            }),
    });
}
</script>
