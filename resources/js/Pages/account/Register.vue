<template>
    <Head>
        <title>{{ t("register.head_title") }}</title>
        <meta name="description" :content="t('register.subtitle')" />
    </Head>
    <AuthShell
        :title="t('register.title')"
        :subtitle="t('register.subtitle')"
        :points="[t('auth.point_favourites'), t('auth.point_orders')]"
    >
        <form class="flex flex-col gap-4" @submit.prevent="submitForm">
            <FloatingInput
                id="name"
                :label="t('auth.name')"
                autocomplete="name"
                :hint="t('auth.name_hint')"
                :minlength="5"
                v-model.trim="form.name"
                :error="errors.name"
            />
            <FloatingInput
                id="email"
                type="email"
                :label="t('auth.email')"
                autocomplete="email"
                v-model.trim="form.email"
                :error="errors.email"
            />
            <FloatingInput
                id="password"
                type="password"
                :label="t('auth.password')"
                autocomplete="new-password"
                :hint="t('auth.password_hint')"
                :minlength="8"
                v-model="form.password"
                :error="errors.password"
            />
            <button
                type="submit"
                :disabled="isProcessing"
                :aria-busy="isProcessing"
                class="btn-primary mt-2 w-full"
            >
                {{ isProcessing ? t("register.processing") : t("register.submit") }}
            </button>
        </form>
        <template #switch>
            {{ t("register.has_account") }}
            <Link
                href="/login"
                class="font-bold text-neutral-950 dark:text-white dark:hover:text-accent-dark underline decoration-brand decoration-2 underline-offset-4 hover:text-brand-strong"
            >
                {{ t("register.login_link") }}
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
    name: "",
    email: "",
    password: "",
});
const isProcessing = ref(false);

const submitForm = function () {
    router.post("/register", form.value, {
        onStart: () => (isProcessing.value = true),
        onFinish: () => (isProcessing.value = false),
    });
};
</script>
