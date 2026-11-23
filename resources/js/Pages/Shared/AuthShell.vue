<script setup lang="ts">
withDefaults(
    defineProps<{
        title: string;
        subtitle: string;
        headline?: string;
        points?: string[];
    }>(),
    { headline: "", points: () => [] },
);
</script>

<template>
    <section
        class="auth-enter mx-auto my-4 grid w-full max-w-[1040px] overflow-hidden rounded-3xl bg-white shadow-[0_24px_60px_-20px_rgba(10,10,10,0.25)] ring-1 ring-black/5 dark:bg-neutral-900 dark:shadow-none dark:ring-white/10 lg:min-h-[640px] lg:grid-cols-[1fr_1.05fr]"
    >
        <aside
            class="relative hidden overflow-hidden bg-neutral-950 p-12 text-white lg:flex lg:flex-col lg:justify-between"
        >
            <div
                class="pointer-events-none absolute -bottom-40 -left-32 size-[520px] rounded-full bg-[radial-gradient(closest-side,rgba(220,38,38,0.55),transparent)]"
                aria-hidden="true"
            ></div>
            <svg
                viewBox="0 0 400 600"
                class="pointer-events-none absolute inset-0 size-full text-white/12"
                preserveAspectRatio="xMidYMid slice"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                aria-hidden="true"
            >
                <rect x="30" y="30" width="340" height="540" rx="4" />
                <line x1="30" y1="300" x2="370" y2="300" />
                <circle cx="200" cy="300" r="64" />
                <circle cx="200" cy="300" r="3" fill="currentColor" />
                <rect x="110" y="30" width="180" height="84" />
                <rect x="110" y="486" width="180" height="84" />
                <path d="M160 114a46 46 0 0 0 80 0" />
                <path d="M160 486a46 46 0 0 1 80 0" />
            </svg>

            <div class="relative mt-auto">
                <p
                    v-if="headline"
                    class="max-w-[12ch] text-balance text-5xl font-extrabold leading-[1.05] tracking-tight"
                >
                    {{ headline }}
                </p>
                <ul class="mt-8 space-y-3 text-base text-neutral-200">
                    <li
                        v-for="point in points"
                        :key="point"
                        class="flex items-center gap-3"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            class="size-5 shrink-0 text-[#F87171]"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <path d="m5 12 4.5 4.5L19 7" />
                        </svg>
                        {{ point }}
                    </li>
                </ul>
            </div>
        </aside>

        <div
            class="flex flex-col justify-center px-6 py-10 sm:px-12 lg:px-14 lg:py-14"
        >
            <h1
                class="text-balance text-4xl font-extrabold tracking-tight text-neutral-950 dark:text-white"
            >
                {{ title }}
            </h1>
            <p class="mt-2 text-base text-neutral-600 dark:text-neutral-400">{{ subtitle }}</p>
            <div class="mt-8">
                <slot />
            </div>
            <p class="mt-8 text-center text-sm text-neutral-600 dark:text-neutral-400">
                <slot name="switch" />
            </p>
        </div>
    </section>
</template>

<style scoped>
.auth-enter {
    animation: auth-enter 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
}

@keyframes auth-enter {
    from {
        opacity: 0.001;
        transform: translateY(14px);
    }
}

@media (prefers-reduced-motion: reduce) {
    .auth-enter {
        animation: none;
    }
}
</style>
