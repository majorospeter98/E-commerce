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
            @submit.prevent="itemsToDatabase(items)"
        >
            <ul class="flex flex-col gap-4">
                <li
                    v-for="item in items"
                    :key="item.id"
                    class="bg-white rounded-lg p-4 shadow-sm"
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
                                 
                                <div>
                                    <label>Mennyiség:</label>
                                     
                                    <input
                                        type="number"
                                        min="1"
                                        v-model.number="item.quantity"
                                        class="border rounded px-2 py-1 ml-2 w-20"
                                    />
                                </div>
                            </div>
                        </div>
                         
                        <button
                            type="button"
                            @click="deleteItem(item.id)"
                            class="text-red-500"
                        >
                            X
                        </button>
                         
                    </div>
                </li>
            </ul>
            <div>
                <button class="w-[100px] bg-[#DC2626] text-white rounded-lg py-3" type="submit">Rendelés</button>
            </div>
        </form>
        <h1 class="mt-5 font-bold text-4xl" v-else>Üres a kosarad</h1>
    </section>

     
</template>

<script setup>
import { onMounted, ref } from "vue";
import { Link } from "@inertiajs/vue3";
import { router } from "@inertiajs/vue3";
import { Head } from "@inertiajs/vue3";
const items = ref([]);
onMounted(() => {
items.value = JSON.parse(localStorage.getItem("items")) || [];
});

function deleteItem(id) {
    items.value = items.value.filter((item) => item.id !== id);
    localStorage.setItem("items", JSON.stringify(items.value));
   items.value = JSON.parse(localStorage.getItem("items")) || [];
}
function itemsToDatabase() {
    const orders = [];
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

    localStorage.removeItem("items");
   items.value = JSON.parse(localStorage.getItem("items")) || [];
}
</script>
