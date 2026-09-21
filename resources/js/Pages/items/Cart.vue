<template>
    <section class="">
        <h1 class="text-3xl">Kosaram:</h1>
           <form  @submit.prevent="itemsToDatabase(items)">
        <ul v-if="items" class="flex-col flex">
            <li v-for="item in items" :key="item.id" class="">
             
                    <div class="flex flex-col gap-8">
                        <div class="">
                            <img :src="`/Teams/${item.team}/${item.image}`" />
                        </div>
                        <div>
                            <p>{{ item.team }}</p>
                            <p>{{ item.type }}</p>
                            <p>{{ item.brand }}</p>
                            <p>{{ item.size }}</p>
                            <p>
                                mennyiség:<input
                                    required
                                    min="1"
                                    type="number"
                                    v-model.number="item.quantity"
                                />
                            </p>
                        </div>
                        <div>
                            <button @click="deleteItem(item.id)">Delete</button>
                        </div>
                    </div>
                
            </li>
            <div>
                <button type="submit" >Rendelés</button>
            </div>
        </ul>
              <h1 class="mt-5 font-bold text-4xl" v-else>Üres a kosarad</h1>
        </form>
  
    </section>
</template>

<script setup>
import { onMounted, ref } from "vue";
import { Link } from "@inertiajs/vue3";
import { router } from "@inertiajs/vue3";
const items = ref([]);
onMounted(() => {
    items.value = JSON.parse(localStorage.getItem("items"));
    console.log(items);
});

function deleteItem(id) {
    items.value = items.value.filter((item) => item.id !== id);
    localStorage.setItem("items", JSON.stringify(items.value));
    items.value= JSON.parse(localStorage.getItem("items"));
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
    })
    
    localStorage.removeItem("items");
     items.value = JSON.parse(localStorage.getItem("items"));
}
</script>
