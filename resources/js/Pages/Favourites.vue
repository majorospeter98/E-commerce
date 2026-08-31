<template>
<Head>
    <title>Favourites page</title>
    <meta name="description" content="Showing the user favourite items">
</Head>
  <h1>Kedvenceid </h1>
  <section>
   
   <ul>
    <li v-for="item in fav">
            <div>
                 <img class="h-[450px] w-[450px]" :src="`/Teams/${item.item.team}/${item.item.image}`">
         <p>{{ item.item.team }}  {{ item.item.type }}  {{ item.item.brand }}</p>
              </div>
    </li>
   </ul>
  </section>
  
</template>

<script setup>
import {Head} from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
defineProps({fav:Object})
</script>

<style>

</style>