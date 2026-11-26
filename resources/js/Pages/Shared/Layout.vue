<script setup lang="ts">
import Header from "./Header.vue";
import FlashMessage from "./FlashMessage.vue";
import Footer from "./Footer.vue";
import { useTranslation } from "@/useTranslation";

const { t } = useTranslation();
</script>

<template>
    <div class="flex min-h-screen flex-col">
        <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-neutral-950 focus:px-4 focus:py-3 focus:font-bold focus:text-white">
            {{ t("layout.skip") }}
        </a>
        <Header />
        <FlashMessage />
        <main id="main" tabindex="-1" class="page-container mt-6 flex-1 outline-none">
            <slot />
        </main>
        <Footer class="mt-16" />
    </div>
</template>
