<template>
<Head>
    <title>Favourites page</title>
    <meta name="description" content="Showing the user favourite items">
</Head>
  <h1>Kedvenceid </h1>
  <section>
    {{ fav }}
  </section>
  
</template>

<script setup>
import {Head} from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
defineProps({fav:Object})
</script>

<style>

</style>