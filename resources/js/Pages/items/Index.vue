

<script setup>
import { Head, router } from '@inertiajs/vue3';
import {Link} from '@inertiajs/vue3';

defineProps({items:Array})

</script>


<template>
<Head>
    <title>Index page</title>
    <meta name="description" content="Index page showing all items">
</Head>
<section>
  <ul class="mt-6 flex flex-wrap text-center">
    <li v-for="item in items" :key="item.id">
   <img class="hover:cursor-pointer" @click="router.get('/items/' + item.id)" :src="`/Teams/${item.team}/${item.image}`">
   <p class="mt-4">{{ item.type }} {{ item.brand }}</p>
    <Link :href="'/items/' + item.id">Részletek</Link>
    </li>
  </ul>
</section>
</template>
<style>

</style>>