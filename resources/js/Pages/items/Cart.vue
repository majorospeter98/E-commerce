<template>
  <section>
  <h1 class="text-3xl">Kosaram:</h1>
  <ul v-if=items.length> 
    <li v-for="item in items" :key="item.id" >
      <form class="flex justify-between mt-5  last:mb-8 ">
      <div class="flex gap-8 ">
        <img :src="`/Teams/${item.team}/${item.image}`">
       
        <div>
                <p>{{ item.id }} </p>
       <p>{{ item.team }} </p>
        <p>{{ item.type }} </p>
         <p>{{ item.brand }} </p>
         <p>{{ item.quantity }} </p>
      </div>
      </div>
     <div>
        <button @click="deleteItem(item.id)">Delete</button>
      </div>
    </form>
    </li>
  </ul>
  <h1 class="mt-5 font-bold text-4xl" v-else>Üres a kosarad</h1>
</section>
</template>

<script setup>
 
import { onMounted,ref } from 'vue';
const items = ref([]);
onMounted(() =>{
items.value=JSON.parse(localStorage.getItem('items'))
console.log(items)
})

function deleteItem(id){
items.value=items.value.filter(item => item.id !== id)
localStorage.setItem('items', JSON.stringify(items.value))
}
</script>

<style>

</style>