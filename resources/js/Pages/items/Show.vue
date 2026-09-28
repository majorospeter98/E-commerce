<template>

  <Head>
    <title>Show items</title>
    <meta name="description" content="Show items">
</Head>

   <section class="flex flex-col  justify-between  mb-7 mt-6  md:flex-row">
     <img class="h-[450px]  max-w-[450px]  object-contain" :src="`/Teams/450/${item.team}/${item.image}`">
   <div>
  

 </div>
 <div class="border border-pink-700 w-[90%]  md:w-[50%]">
  <form @submit.prevent="toCart" class="flex flex-col   align-middle items-center  ">
<p class="text-4xl mt-8 text-center">{{ item.type }}  {{ item.team }}</p>
<select class="border-2 border-black w-[80%] min-h-[40px] pl-2 mt-8 " v-model="selected">
  <option disabled value="">Válassz egyet</option>
    <option  v-for="size in item.size" :key="size.id">{{ size }}</option>

</select>
    <div class="text-red-500 text-center mt-5" v-if="error">{{ error }}</div>
<div class="w-[80%] mt-8 mb-8 flex">
  <button class="w-[90%] py-5 text-white rounded-lg bg-gray-900" type="submit">Kosárba</button>
  <Link v-if="isFavourite" method="delete" href="/deleteFavourite" :data="{item_id: item.id}"><FontAwesomeIcon class="text-4xl text-red-500 ml-2 w-[10%]" :icon="faHeartSolid" @click="toggleIsFavourite"/></Link>
   <Link v-if="!isFavourite" method="post" href="/toFavourites" :data="{item_id: item.id}">   <FontAwesomeIcon class="text-4xl ml-2  w-[10%] hover:text-red-500"  :icon="faHeartRegular" @click="toggleIsFavourite" href="/toDeleteFavourites" /></Link>
</div>
  </form>
 </div>
  </section>
</template>

<script setup>
import { ref } from 'vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faHeart as faHeartSolid } from '@fortawesome/free-solid-svg-icons'
import { faHeart as faHeartRegular } from '@fortawesome/free-regular-svg-icons'
import {Head} from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
const props=defineProps({item:Object, isFavourites:Array});
const error= ref('');
const selected = ref("");
const isFavourite =ref(props.isFavourites.find(
    fav => fav.item_id === props.item.id 
))
function toggleIsFavourite(){
  isFavourite.value = !isFavourite.value;
}
function toCart(){
if(!selected.value){
  error.value = 'Válaszd ki a méretet'
  return
}


  const sendItemtoStorage= {
    id: crypto.randomUUID(),
    item_id: props.item.id,
    image : props.item.image,
    team : props.item.team,
    type : props.item.type,
    brand: props.item.brand,
    size : selected.value,
    quantity: 1,
  }
  const items= JSON.parse(localStorage.getItem('items') ?? '[]')
  items.push(sendItemtoStorage)
   localStorage.setItem('items', JSON.stringify(items))
  router.get('/cart')
}

</script>

<style>

</style>