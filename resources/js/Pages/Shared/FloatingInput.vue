<script setup lang="ts">
import { computed, ref } from "vue";
import { useTranslation } from "@/useTranslation";

const props = withDefaults(
    defineProps<{
        id: string;
        label: string;
        type?: string;
        autocomplete?: string;
        error?: string | null;
        hint?: string | null;
        minlength?: number;
    }>(),
    { type: "text", autocomplete: "off", error: null, hint: null, minlength: undefined },
);

const { t } = useTranslation();
const model = defineModel<string>({ default: "" });

const isPasswordVisible = ref(false);
const isPasswordField = computed(() => props.type === "password");
const inputType = computed(() =>
    isPasswordField.value && isPasswordVisible.value ? "text" : props.type,
);
const errorId = computed(() => `${props.id}-error`);
const hintId = computed(() => `${props.id}-hint`);
const describedBy = computed(() => {
    const ids = [props.error ? errorId.value : null, props.hint ? hintId.value : null];
    return ids.filter(Boolean).join(" ") || undefined;
});
</script>

<template>
    <div>
        <label :for="id" class="mb-1.5 block text-sm font-semibold text-label">
            {{ label }}
        </label>
        <div class="relative">
            <input
                :id="id"
                :name="id"
                :type="inputType"
                :autocomplete="autocomplete"
                v-model="model"
                required
                :minlength="minlength"
                :aria-invalid="error ? 'true' : 'false'"
                :aria-describedby="describedBy"
                class="block h-13 w-full rounded-xl border bg-page px-4 text-base text-ink caret-brand outline-none transition-[border-color,box-shadow] duration-200 focus:border-brand focus:ring-4 focus:ring-brand/15"
                :class="[
                    error ? 'border-brand-strong dark:border-brand' : 'border-control',
                    isPasswordField ? 'pr-14' : '',
                ]"
            />
            <button
                v-if="isPasswordField"
                type="button"
                class="absolute right-1.5 top-1 flex size-11 items-center justify-center rounded-lg text-muted transition-colors hover:text-strong focus-visible:outline-2 focus-visible:outline-brand"
                :aria-label="
                    isPasswordVisible ? t('auth.hide_password') : t('auth.show_password')
                "
                :aria-pressed="isPasswordVisible"
                @click="isPasswordVisible = !isPasswordVisible"
            >
                <svg
                    viewBox="0 0 24 24"
                    class="size-5"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.75"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <path
                        d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"
                    />
                    <circle cx="12" cy="12" r="3" />
                    <path v-if="isPasswordVisible" d="M4 4l16 16" />
                </svg>
            </button>
        </div>
        <p
            v-if="error"
            :id="errorId"
            role="alert"
            class="error-text mt-2 text-sm text-brand-strong"
        >
            {{ error }}
        </p>
        <p v-if="hint" :id="hintId" class="mt-2 text-sm text-muted">
            {{ hint }}
        </p>
    </div>
</template>
