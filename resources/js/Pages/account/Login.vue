<template>
    <Head>
        <title>Login page</title>
        <meta name="description" content="Login page" />
    </Head>
    <AuthShell
        title="Bejelentkezés"
        subtitle="Üdv újra! Add meg az adataidat a folytatáshoz."
        :points="['Mentett kedvencek', 'Rendeléseid egy helyen']"
    >
        <form class="flex flex-col gap-4" @submit.prevent="submitForm">
            <FloatingInput
                id="email"
                type="email"
                label="Email cím"
                autocomplete="email"
                v-model="form.email"
                :error="errors.email"
            />
            <FloatingInput
                id="password"
                type="password"
                label="Jelszó"
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
                {{ isProcessing ? "Bejelentkezés…" : "Bejelentkezés" }}
            </button>
        </form>
        <template #switch>
            Még nincs fiókod?
            <Link
                href="/register"
                class="font-bold text-neutral-950 dark:text-white dark:hover:text-red-400 underline decoration-brand decoration-2 underline-offset-4 hover:text-brand-strong"
            >
                Regisztráció
            </Link>
        </template>
    </AuthShell>
</template>

<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { ref } from "vue";
import AuthShell from "../Shared/AuthShell.vue";
import FloatingInput from "../Shared/FloatingInput.vue";

defineProps({ errors: Object });

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
