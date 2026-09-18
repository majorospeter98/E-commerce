<template>

  <Head>
    <title>Show items</title>
    <meta name="description" content="Show items">
</Head>
   <section class="flex container justify-between align-center text-center mt-6">
   <div>
  
 <img class="h-[450px] w-[450px]" :src="`/Teams/${item.team}/${item.image}`">
 </div>
 <div>
  <form @submit.prevent="toCart" class="flex flex-col gap-3">
<p>{{ item.type }}</p>
<p>{{ item.team }}</p>
<select v-model="selected">
  <option disabled value="">Válassz egyet</option>
    <option v-for="size in item.size" :key="size.id">{{ size }}</option>
</select>
<div class="text-red-500 text-center" v-if="error">{{ error }}</div>
  <button type="submit">Kosárba</button>
  <Link v-if="isFavourite" method="delete" href="/deleteFavourite" :data="{item_id: item.id}"><FontAwesomeIcon  :icon="faHeartSolid" @click="toggleIsFavourite"/></Link>

   <Link v-if="!isFavourite" method="post" href="/toFavourites" :data="{item_id: item.id}">   <FontAwesomeIcon  :icon="faHeartRegular" @click="toggleIsFavourite" href="/toDeleteFavourites" /></Link>
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
    quantity: 0,
  }
  const items= JSON.parse(localStorage.getItem('items') ?? '[]')
  items.push(sendItemtoStorage)
   localStorage.setItem('items', JSON.stringify(items))
  router.get('/cart')
}

</script>

<style>

</style>