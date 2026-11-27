<template>
    <ul class="kits">
        <li v-for="(kit, index) in kits" :key="kit.key" class="kit">
            <Link :href="kit.href" class="kit-link">
                <span class="kit-stage">
                    <img
                        class="kit-img"
                        :src="`/Teams/450/${kit.team}/${kit.image}`"
                        :srcset="tileSources(kit)"
                        sizes="(min-width: 1445px) 416px, (min-width: 768px) 29vw, 44vw"
                        alt=""
                        width="450"
                        height="450"
                        decoding="async"
                        :loading="index < 4 ? 'eager' : 'lazy'"
                    />
                </span>
                <div class="kit-caption">
                    <component :is="headingLevel" class="kit-team">{{ kit.team }}</component>
                    <span class="kit-meta">{{ garment(kit.image) }} · {{ kitType(kit.type) }} · {{ kit.brand }}</span>
                    <span v-if="kit.price !== undefined" class="kit-price">{{ formatPrice(kit.price) }}</span>
                    <span v-for="line in kit.details" :key="line" class="kit-meta">{{ line }}</span>
                </div>
            </Link>
            <button
                v-if="removeLabel"
                type="button"
                class="kit-remove"
                :aria-label="removeLabel(kit)"
                @click="emit('remove', kit)"
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3" />
                </svg>
            </button>
        </li>
    </ul>
</template>

<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import type { Kit } from "@/types";
import { useTranslation } from "@/useTranslation";

withDefaults(
    defineProps<{
    kits: Kit[];
    headingLevel?: "h2" | "h3";
    removeLabel?: (kit: Kit) => string;
    }>(),
    { headingLevel: "h2", removeLabel: undefined },
);
const emit = defineEmits<{ remove: [kit: Kit] }>();
const { kitType, garment, formatPrice } = useTranslation();

const tileSources = (kit: Kit): string =>
    [360, 450].map((width) => `${encodeURI(`/Teams/${width}/${kit.team}/${kit.image}`)} ${width}w`).join(", ");
</script>

<style scoped>
.kits {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 2rem 0.75rem;
}
@media (min-width: 768px) {
    .kits {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 3rem 1.5rem;
    }
}
.kit {
    position: relative;
}
.kit-remove {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.75rem;
    height: 2.75rem;
    border-radius: 9999px;
    cursor: pointer;
    color: var(--color-brand);
    background: var(--page-bg);
    box-shadow: 0 2px 8px rgb(0 0 0 / 0.15);
    transition: background-color 0.2s, color 0.2s;
}
.kit-remove svg {
    width: 1.25rem;
    height: 1.25rem;
}
.kit-remove:hover,
.kit-remove:focus-visible {
    color: #fff;
    background: var(--color-brand);
}
.kit-remove:focus-visible {
    outline: 2px solid var(--color-brand);
    outline-offset: 2px;
}
.kit-link {
    display: block;
    border-radius: 1rem;
    outline-offset: 4px;
}
.kit-link:focus-visible {
    outline: 2px solid var(--color-brand);
}
.kit-stage {
    display: block;
    aspect-ratio: 1;
    overflow: hidden;
    border: 1px solid var(--page-line);
    border-radius: 1.25rem;
    background: var(--page-surface);
    transition: border-color 0.2s;
}
.kit-link:hover .kit-stage,
.kit-link:focus-visible .kit-stage {
    border-color: color-mix(in srgb, var(--page-ink) 40%, transparent);
}
:global(html.dark .kit-stage) {
    background: #fff;
}
.kit-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    mix-blend-mode: var(--photo-blend);
}
.kit-caption {
    display: block;
    margin-top: 0.75rem;
    padding-inline: 0.25rem;
    line-height: 1.35;
}
.kit-team {
    letter-spacing: normal;
    font-weight: 800;
    font-size: 1.266rem;
    text-decoration: underline 2px transparent;
    text-underline-offset: 0.3em;
    transition: text-decoration-color 0.2s;
}
.kit-link:hover .kit-team,
.kit-link:focus-visible .kit-team {
    text-decoration-color: var(--color-brand);
}
.kit-price {
    display: block;
    margin-top: 0.375rem;
    font-size: 1.266rem;
    font-weight: 800;
    font-variant-numeric: tabular-nums;
}
.kit-meta {
    display: block;
    color: var(--page-muted);
}
</style>
