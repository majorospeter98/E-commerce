<template>
    <Head>
        <title>{{ t("login.head_title") }}</title>
        <meta name="description" :content="t('login.head_title')" />
    </Head>
    <AuthShell
        :title="t('login.title')"
        :subtitle="t('login.subtitle')"
        :points="[t('auth.point_favourites'), t('auth.point_orders')]"
    >
        <form class="flex flex-col gap-4" @submit.prevent="submitForm">
            <FloatingInput
                id="email"
                type="email"
                :label="t('auth.email')"
                autocomplete="email"
                v-model="form.email"
                :error="errors.email"
            />
            <FloatingInput
                id="password"
                type="password"
                :label="t('auth.password')"
                autocomplete="current-password"
                v-model="form.password"
                :error="errors.password"
            />
            <button
                type="submit"
                :disabled="isProcessing"
                :aria-busy="isProcessing"
                class="mt-2 h-14 w-full rounded-xl bg-brand text-base font-bold text-white shadow-[0_8px_20px_-8px_rgba(220,38,38,0.7)] transition-[background-color,transform,box-shadow] duration-200 hover:bg-brand-strong active:translate-y-px focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand disabled:cursor-wait disabled:opacity-70"
            >
                {{ isProcessing ? t("login.processing") : t("login.submit") }}
            </button>
        </form>
        <template #switch>
            {{ t("login.no_account") }}
            <Link
                href="/register"
                class="font-bold text-neutral-950 dark:text-white dark:hover:text-red-400 underline decoration-brand decoration-2 underline-offset-4 hover:text-brand-strong"
            >
                {{ t("login.register_link") }}
            </Link>
        </template>
    </AuthShell>
</template>

<script setup lang="ts">
import { Head, Link, router } from "@inertiajs/vue3";
import { ref } from "vue";
import type { FormErrors } from "@/types";
import AuthShell from "../Shared/AuthShell.vue";
import FloatingInput from "../Shared/FloatingInput.vue";
import { useTranslation } from "@/useTranslation";

defineProps<{ errors: FormErrors }>();
const { t } = useTranslation();

const form = ref({
    email: "",
    password: "",
});
const isProcessing = ref(false);

const submitForm = function () {
    router.post("/login", form.value, {
        onStart: () => (isProcessing.value = true),
        onFinish: () => (isProcessing.value = false),
    });
};
</script>
