<template>
  <h1>Valami</h1>
  {{ item }}

  <Link>Kosárba</Link>
  <Link>Kedvencek</Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
defineProps({item:Object});
</script>

<style>

</style>