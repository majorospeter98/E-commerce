

<script setup>
import { Link,router } from '@inertiajs/vue3'
import Layout from '../Shared/Layout.vue'
import { Head } from '@inertiajs/vue3'

defineProps({items:Array})

</script>


<template>
  <Layout>
<Head>
    <title>Index page</title>
    <meta name="description" content="Your page description">
</Head>
  <ul class="mt-6">
    <li v-for="item in items" :key="item.id">{{ item }}
   <img class="hover:cursor-pointer" @click="router.get('/items/' + item.id)" :src="`/Teams/${item.team}/${item.image}`">
   <p>{{ item.type }} {{ item.brand }}</p>
    <Link :href="'/items/' + item.id">Részletek</Link>
    </li>
  </ul>
</Layout>
</template>
<style>

</style>>