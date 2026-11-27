<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3";
import { useTranslation } from "@/useTranslation";

const { t } = useTranslation();
</script>

<template>
    <Head>
        <title>{{ t("missing.title") }}</title>
    </Head>
    <section class="py-12">
        <h1 class="page-title">{{ t("missing.title") }}</h1>
        <p class="mt-4 max-w-prose text-muted">{{ t("missing.text") }}</p>
        <Link href="/items" class="btn-primary mt-8">{{ t("cart.continue") }}</Link>
    </section>
</template>
