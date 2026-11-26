<template>
    <ul class="kits">
        <li v-for="(kit, index) in kits" :key="kit.key" class="kit">
            <Link :href="kit.href" class="kit-link">
                <span class="kit-stage">
                    <img
                        class="kit-img"
                        :src="`/Teams/450/${kit.team}/${kit.image}`"
                        :alt="t('kit.alt', { team: kit.team, type: kitType(kit.type), garment: garment(kit.image) })"
                        :loading="index < 4 ? 'eager' : 'lazy'"
                    />
                </span>
                <span class="kit-caption">
                    <span class="kit-team">{{ kit.team }}</span>
                    <span class="kit-meta">{{ garment(kit.image) }} · {{ kitType(kit.type) }} · {{ kit.brand }}</span>
                    <span v-for="line in kit.details" :key="line" class="kit-meta">{{ line }}</span>
                </span>
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

defineProps<{
    kits: Kit[];
    /** When set, each card shows a remove button labelled by this function. */
    removeLabel?: (kit: Kit) => string;
}>();
const emit = defineEmits<{ remove: [kit: Kit] }>();
const { t, kitType, garment } = useTranslation();
</script>

<style scoped>
.kits {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(min(260px, 100%), 1fr));
    gap: 3rem 1.5rem;
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
    text-align: center;
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
    border-radius: 1.25rem;
}
/* a deliberate rounded plate instead of a hard white rectangle */
:global(html.dark .kit-stage) {
    background: #fff;
}
.kit-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}
.kit-caption {
    display: block;
    margin-top: 0.75rem;
}
/* hover and focus only underline the name: the photo stays still */
.kit-team {
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
.kit-meta {
    display: block;
    color: var(--page-muted);
}
</style>
