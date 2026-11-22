<template>
    <Head>
        <title>Main page</title>
        <meta name="description" content="Main page showing all items" />
    </Head>

    <h1 class="text-4xl text-bold">26/27 Season starts now</h1>
    <section>
        <ul class="mt-8 flex flex-wrap justify-around text-center gap-4 mb-9">
            <li v-if="!filteredItems.length" class="text-neutral-600">
                No products match your search.
            </li>
            <li v-for="item in filteredItems" :key="item.id">
                <img
                    class="hover:cursor-pointer w-[350px] h-[350px] object-contain aspect-square"
                    @click="router.get('/items/' + item.id)"
                    :src="`/Teams/350/${item.team}/${item.image}`"
                />
                <p class="mt-4">{{ item.type }} {{ item.brand }}</p>
            </li>
        </ul>
    </section>
</template>
<script setup>
import { Head, router, usePage } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({ items: Array });
const page = usePage();

const filteredItems = computed(() => {
    const term = (new URLSearchParams(page.url.split("?")[1] ?? "").get("search") ?? "")
        .trim()
        .toLowerCase();
    if (!term) {
        return props.items;
    }
    return props.items.filter((item) =>
        [item.team, item.type, item.brand].some((field) =>
            String(field ?? "").toLowerCase().includes(term),
        ),
    );
});
</script>
<style></style>
>
