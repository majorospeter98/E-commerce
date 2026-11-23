<script setup lang="ts">
import Header from "./Header.vue";
import Footer from "./Footer.vue";
</script>

<template>
    <div class="flex min-h-screen flex-col">
        <Header />
        <main class="mt-6 flex-1">
            <slot />
        </main>
        <Footer class="mt-16" />
    </div>
</template>
