<template>
    <Head>
        <title>Register page</title>
        <meta name="description" content="Register page" />
    </Head>
    <AuthShell
        title="Regisztráció"
        subtitle="Hozd létre a fiókod pár másodperc alatt."
        :points="['Mentett kedvencek', 'Rendeléseid egy helyen']"
    >
        <form class="flex flex-col gap-4" @submit.prevent="submitForm">
            <FloatingInput
                id="name"
                label="Név"
                autocomplete="name"
                hint="Legalább 5 karakter"
                :minlength="5"
                v-model.trim="form.name"
                :error="errors.name"
            />
            <FloatingInput
                id="email"
                type="email"
                label="Email cím"
                autocomplete="email"
                v-model.trim="form.email"
                :error="errors.email"
            />
            <FloatingInput
                id="password"
                type="password"
                label="Jelszó"
                autocomplete="new-password"
                hint="Legalább 8 karakter"
                :minlength="8"
                v-model.trim="form.password"
                :error="errors.password"
            />
            <button
                type="submit"
                :disabled="isProcessing"
                :aria-busy="isProcessing"
                class="mt-2 h-14 w-full rounded-xl bg-brand text-base font-bold text-white shadow-[0_8px_20px_-8px_rgba(220,38,38,0.7)] transition-[background-color,transform,box-shadow] duration-200 hover:bg-brand-strong active:translate-y-px focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand disabled:cursor-wait disabled:opacity-70"
            >
                {{ isProcessing ? "Regisztráció…" : "Regisztráció" }}
            </button>
        </form>
        <template #switch>
            Már van fiókod?
            <Link
                href="/login"
                class="font-bold text-neutral-950 dark:text-white dark:hover:text-red-400 underline decoration-brand decoration-2 underline-offset-4 hover:text-brand-strong"
            >
                Bejelentkezés
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
