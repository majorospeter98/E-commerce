<template>

  <Head>
    <title>Show items</title>
    <meta name="description" content="Show items">
</Head>

  <h1>Valami</h1>
  <section class="flex container justify-between align-center text-center mt-6">
   <div>
  
 <img class="h-[450px] w-[450px]" :src="`/Teams/${item.team}/${item.image}`">
 </div>
 <div>
<p>{{ item.type }}</p>
<p>{{ item.team }}</p>
<button v-for="size in item.size" :key="size">
    {{ size }}
</button>
  <Link>Kosárba</Link>
  <Link v-if="isFavourite" method="delete" href="/deleteFavourite" :data="{item_id: item.id}"><FontAwesomeIcon  :icon="faHeartSolid" @click="toggleIsFavourite"/></Link>

   <Link v-if="!isFavourite" method="post" href="/toFavourites" :data="{item_id: item.id}">   <FontAwesomeIcon  :icon="faHeartRegular" @click="toggleIsFavourite" href="/toDeleteFavourites" /></Link>

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



defineProps({item:Object});
let isFavourite= ref(false);
function toggleIsFavourite(){
  isFavourite.value = !isFavourite.value;
}
</script>

<style>

</style>