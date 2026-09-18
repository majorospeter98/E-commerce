<template>
  <section class="">
  <h1 class="text-3xl">Kosaram:</h1>
  <ul v-if=items.length class="flex-col flex " > 
    <li v-for="item in items" :key="item.id" class="" >
      <form>
      <div class="flex flex-col gap-8 ">
     <div class="">
        <img :src="`/Teams/${item.team}/${item.image}`">
     </div>
                  <div>
              
                      <p>{{ item.team }} </p>
        <p>{{ item.type }} </p>
         <p>{{ item.brand }} </p>
         <p>{{ item.size}} </p>
         <p>mennyiség:<input min=1 type="number" v-model.number="item.quantity"></p>
          </div>
          <div>
        <button @click="deleteItem(item.id)">Delete</button>
      </div>
      
      </div>
    
    </form>
  
    </li>
         <div>
        <Link @click="itemsToDatabase(items)" :href='"/order"' >Rendelés</Link>
      </div>
  </ul>
    <h1 class="mt-5 font-bold text-4xl" v-else>Üres a kosarad</h1>


</section>
</template>

<script setup>
 
import { onMounted,ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
const items = ref([]);
onMounted(() =>{
items.value=JSON.parse(localStorage.getItem('items'))
console.log(items)
})

function deleteItem(id){
items.value=items.value.filter(item => item.id !== id)
localStorage.setItem('items', JSON.stringify(items.value))
}
function itemsToDatabase(){
  const orders=[]
  for(const item of items.value)
  {
    
 orders.push({
id : item.id,
item_id: item.item_id,
size: item.size,
quantity: item.quantity
   })
    }

 router.post('/order', {
  orders:orders

 })
}

</script>

<style>

</style>