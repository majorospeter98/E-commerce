<template>
    <Head>
        <title>Order page</title>
        <meta name="description" content="Displaying your orders" />
    </Head>
    <h1 class="text-3xl font-bold">Rendeléseim:</h1>
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
                    <p>{{ item.type }}</p>

                    <p>Méret: {{ item.pivot.size }}</p>
                    <p>Mennyiség: {{ item.pivot.quantity }}</p>
                </div>
            </li>
        </ul>
    </section>
    <p class="mt-5 text-2xl" v-else>Nincs rendelésed.</p>
</template>

<script setup>
import { Head } from "@inertiajs/vue3";
const props = defineProps({ orders: Object });
</script>

<style></style>
