<template>
  <Head>
    <title>Show items</title>
    <meta name="description" content="Show items">
</Head>
<div>
{{page}}
</div>
  <h1>Valami</h1>
  {{ item }}

  <Link>Kosárba</Link>
  <Link v-if="isFavourite" method="delete" href="/deleteFavourite" :data="{item_id: item.id}"><FontAwesomeIcon  :icon="faHeartSolid" @click="toggleIsFavourite"/></Link>

   <Link v-if="!isFavourite" method="post" href="/toFavourites" :data="{item_id: item.id}">   <FontAwesomeIcon  :icon="faHeartRegular" @click="toggleIsFavourite" href="/toDeleteFavourites" /></Link>


</template>

<script setup>
import { ref } from 'vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faHeart as faHeartSolid } from '@fortawesome/free-solid-svg-icons'
import { faHeart as faHeartRegular } from '@fortawesome/free-regular-svg-icons'
import {Head} from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3'

const page = usePage()
defineProps({item:Object});
let isFavourite= ref(false);
function toggleIsFavourite(){
  isFavourite.value = !isFavourite.value;
}
</script>

<style>

</style>