<script setup>
import { computed, ref } from "vue";

const props = defineProps({
    id: { type: String, required: true },
    label: { type: String, required: true },
    type: { type: String, default: "text" },
    autocomplete: { type: String, default: "off" },
    error: { type: String, default: null },
    hint: { type: String, default: null },
    minlength: { type: Number, default: undefined },
});

const model = defineModel({ type: String, default: "" });

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
        <div class="relative">
            <input
                :id="id"
                :name="id"
                :type="inputType"
                :autocomplete="autocomplete"
                v-model="model"
                placeholder=" "
                required
                :minlength="minlength"
                :aria-invalid="error ? 'true' : 'false'"
                :aria-describedby="describedBy"
                class="peer block h-14 w-full rounded-xl border bg-white px-4 pt-5 text-base text-neutral-900 outline-none dark:bg-neutral-950 dark:text-neutral-100 transition-[border-color,box-shadow] duration-200 placeholder:text-transparent focus:border-brand focus:ring-4 focus:ring-brand/15"
                :class="[
                    error ? 'border-brand-strong dark:border-red-400' : 'border-neutral-300 dark:border-neutral-600',
                    isPasswordField ? 'pr-12' : '',
                ]"
            />
            <label
                :for="id"
                class="pointer-events-none absolute left-4 top-4 origin-left text-base text-neutral-600 dark:text-neutral-400 transition-transform duration-200 ease-out peer-focus:-translate-y-2.5 peer-focus:scale-75 peer-[:not(:placeholder-shown)]:-translate-y-2.5 peer-[:not(:placeholder-shown)]:scale-75 motion-reduce:transition-none"
            >
                {{ label }}
            </label>
            <button
                v-if="isPasswordField"
                type="button"
                class="absolute right-1.5 top-1.5 flex size-11 items-center justify-center rounded-lg text-neutral-600 transition-colors hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white focus-visible:outline-2 focus-visible:outline-brand"
                :aria-label="
                    isPasswordVisible ? 'Jelszó elrejtése' : 'Jelszó megjelenítése'
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
            class="mt-2 text-sm text-brand-strong dark:text-red-400"
        >
            {{ error }}
        </p>
        <p v-if="hint" :id="hintId" class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">
            {{ hint }}
        </p>
    </div>
</template>
