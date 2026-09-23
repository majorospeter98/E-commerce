<template>
     <Head>
    <title>Order page</title>
    <meta name="description" content="Displaying your orders">
</Head>
  <section>
    My orders

   
    <div v-for="order in orders" :key="order.id">
    <div class="mb-4" v-for="item in order.items" :key="item.id">
        <p>{{ item.team }}</p>
        <p>{{ item.type }}</p>

        <p>Méret: {{ item.pivot.size }}</p>
        <p>Mennyiség: {{ item.pivot.quantity }}</p>
    </div>
</div>
  </section>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
defineProps({orders:Object})
</script>

<style>

</style>