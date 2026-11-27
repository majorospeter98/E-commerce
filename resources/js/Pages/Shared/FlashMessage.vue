<script setup lang="ts">
import { usePage } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";
import { useTranslation } from "@/useTranslation";

const page = usePage();
const { t } = useTranslation();

const text = computed(() => (page.flash as { message?: string } | undefined)?.message ?? "");
const dismissed = ref(false);

watch(
    () => page.flash,
    () => (dismissed.value = false),
);
</script>

<template>
    <div role="status" aria-live="polite" class="page-container">
        <div
            v-if="text && !dismissed"
            class="mt-4 flex items-center justify-between gap-4 rounded-xl border border-green-700 px-4 py-2 font-semibold text-green-800 dark:border-green-400 dark:text-green-300"
        >
            <p>{{ text }}</p>
            <button type="button" class="grid size-11 shrink-0 cursor-pointer place-items-center" :aria-label="t('flash.close')" @click="dismissed = true">
                <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <path d="M6 6l12 12M18 6 6 18" />
                </svg>
            </button>
        </div>
    </div>
</template>
