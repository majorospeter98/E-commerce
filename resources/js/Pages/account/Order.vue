<template>
    <Head>
        <title>{{ t("orders.head_title") }}</title>
        <meta name="description" :content="t('orders.head_desc')" />
    </Head>
    <h1 class="text-3xl font-bold">{{ t("orders.heading") }}</h1>
    <section v-if="props.orders.length > 0">
        <ul class="flex flex-wrap gap-4 justify-center text-center mt-5">
            <li
                class="flex flex-wrap gap-4 justify-center mb-5"
                v-for="order in orders"
                :key="order.id"
            >
                <div
                    class="border-amber-950"
                    v-for="item in order.items"
                    :key="item.id"
                >
                    <p>
                        <img
                            class="h-[350px] w-[350px]"
                            :src="`/Teams/350/${item.team}/${item.image}`"
                        />
                    </p>
                    <p>{{ item.team }}</p>
                    <p>{{ kitType(item.type) }}</p>

                    <p>{{ t("orders.size", { size: item.pivot.size }) }}</p>
                    <p>{{ t("orders.quantity", { quantity: item.pivot.quantity }) }}</p>
                </div>
            </li>
        </ul>
    </section>
    <p class="mt-5 text-2xl" v-else>{{ t("orders.empty") }}</p>
</template>

<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import type { Order } from "@/types";
import { useTranslation } from "@/useTranslation";
const props = defineProps<{ orders: Order[] }>();
const { t, kitType } = useTranslation();
</script>

<style></style>
