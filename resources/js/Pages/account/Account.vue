<template>
    <Head>
        <title>{{ t("account.head_title") }}</title>
        <meta
            name="description"
            :content="t('account.head_desc')"
        />
    </Head>
    <section class="w-full max-w-5xl">
        <header class="mb-8">
            <h1 class="page-title">
                {{ t("account.greeting", { name }) }}
            </h1>
            <p class="mt-2 text-muted">
                {{ t("account.subtitle") }}
            </p>
        </header>

        <div class="grid gap-4 sm:grid-cols-2">
            <Link
                href="/account/order"
                class="group flex min-h-44 flex-col justify-between rounded-3xl border border-line bg-raised p-6 shadow-sm transition duration-300 ease-out hover:-translate-y-1 hover:border-focus hover:shadow-lg focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus"
            >
                <span class="flex items-start justify-between">
                    <span class="flex size-12 items-center justify-center rounded-2xl bg-chip">
                        <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M6 7h12l1 13H5L6 7Z" />
                            <path d="M9 7a3 3 0 0 1 6 0" />
                        </svg>
                    </span>
                    <svg class="size-5 text-icon transition duration-300 group-hover:translate-x-1 group-hover:text-current" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 12h14M13 6l6 6-6 6" />
                    </svg>
                </span>
                <span>
                    <span class="block text-xl font-semibold">{{ tCount("account.orders", orderCount) }}</span>
                    <span class="mt-1 block text-sm text-muted tabular-nums">
                        {{ tCount("account.orders_count", orderCount) }}
                    </span>
                </span>
            </Link>

            <Link
                href="/account/favourites"
                class="group flex min-h-44 flex-col justify-between rounded-3xl border border-line bg-raised p-6 shadow-sm transition duration-300 ease-out hover:-translate-y-1 hover:border-focus hover:shadow-lg focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus"
            >
                <span class="flex items-start justify-between">
                    <span class="flex size-12 items-center justify-center rounded-2xl bg-tile text-brand dark:bg-brand dark:text-white">
                        <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.6-7 10-7 10Z" />
                        </svg>
                    </span>
                    <svg class="size-5 text-icon transition duration-300 group-hover:translate-x-1 group-hover:text-current" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 12h14M13 6l6 6-6 6" />
                    </svg>
                </span>
                <span>
                    <span class="block text-xl font-semibold">{{ tCount("account.favourites", favouriteCount) }}</span>
                    <span class="mt-1 block text-sm text-muted tabular-nums">
                        {{ tCount("account.favourites_count", favouriteCount) }}
                    </span>
                </span>
            </Link>
        </div>

        <Link
            as="button"
            method="delete"
            href="/account/logout"
            class="group mt-4 flex w-full cursor-pointer items-center gap-4 rounded-2xl border border-line px-5 py-4 text-left transition duration-300 hover:border-brand hover:bg-hover-soft focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600 dark:hover:border-brand"
        >
            <span class="flex size-10 items-center justify-center rounded-xl bg-tile text-brand dark:bg-brand dark:text-white">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M10 5H6a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h4" />
                    <path d="M14 8l4 4-4 4M18 12H9" />
                </svg>
            </span>
            <span class="flex flex-col">
                <span class="font-semibold">{{ t("account.logout") }}</span>
                <span class="text-sm text-muted">{{ t("account.logout_desc") }}</span>
            </span>
        </Link>
    </section>
</template>

<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import { Head } from "@inertiajs/vue3";
import { useTranslation } from "@/useTranslation";

defineProps<{
    name: string;
    orderCount: number;
    favouriteCount: number;
}>();

const { t, tCount } = useTranslation();
</script>
