<template>
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
defineProps({orders:Object})
</script>

<style>

</style>