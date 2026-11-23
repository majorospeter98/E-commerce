<template>
    <Head>
        <title>Cart page</title>
        <meta
            name="description"
            content="Your cart, you can add/delete items"
        />
    </Head>
    <section class="mt-7">
        <h1 class="text-4xl font-bold">Kosaram:</h1>
        <form
            class="flex flex-col md:flex md:flex-row md:justify-between mt-10"
            v-if="items.length > 0"
            @submit.prevent="itemsToDatabase"
        >
            <ul class="flex flex-col gap-4">
                <li
                    v-for="item in items"
                    :key="item.id"
                    class="bg-white rounded-lg p-4 shadow-sm dark:bg-neutral-900 dark:ring-1 dark:ring-white/10"
                >
                    <div class="flex justify-between items-start">
                         
                        <div class="flex gap-7">
                            <img
                                :src="`/Teams/150/${item.team}/${item.image}`"
                                class="w-32 h-32 object-contain"
                            />
                             
                            <div class="flex flex-col gap-2">
                                <h2 class="font-bold text-lg">
                                    {{ item.team }}
                                </h2>
                                 
                                <p>{{ item.type }}</p>
                                <p>{{ item.brand }}</p>
                                <p>Méret: {{ item.size }}</p>
                            </div>
                        </div>
                         
                        <button
                            type="button"
                            @click="deleteItem(item.id)"
                            class="text-red-500 dark:text-red-400"
                        >
                            X
                        </button>
                         
                    </div>
                </li>
            </ul>
            <div>
                <button
                    class="w-[100px] bg-[#DC2626] text-white rounded-lg py-3"
                    type="submit"
                >
                    Rendelés
                </button>
            </div>
        </form>
        <h1 class="mt-5 text-3xl" v-else>Üres a kosarad.</h1>
    </section>

     
</template>

<script setup lang="ts">
import { onMounted, ref } from "vue";
import { Link } from "@inertiajs/vue3";
import { router } from "@inertiajs/vue3";
import { Head } from "@inertiajs/vue3";
import { readCartLines, writeCartLines } from "@/cart";
import type { CartLine } from "@/types";
const items = ref<CartLine[]>([]);
onMounted(() => {
    items.value = readCartLines();
});

function deleteItem(id: string) {
    writeCartLines(items.value.filter((item) => item.id !== id));
    items.value = readCartLines();
}
function itemsToDatabase() {
    const orders: Pick<CartLine, "id" | "item_id" | "size" | "quantity">[] = [];
    for (const item of items.value) {
        orders.push({
            id: item.id,
            item_id: item.item_id,
            size: item.size,
            quantity: item.quantity,
        });
    }

    router.post("/account/order", {
        orders: orders,
    });

    writeCartLines([]);
    items.value = [];
}
</script>
