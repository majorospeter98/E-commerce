<script setup lang="ts">
import Header from "./Header.vue";
import Footer from "./Footer.vue";
</script>

<template class="container-custom">
    <Header />
    <main class="mt-6">
        <slot />
    </main>
    <Footer class="mt-16" />
</template>
