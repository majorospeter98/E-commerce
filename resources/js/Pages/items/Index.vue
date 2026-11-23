<template>
    <Head>
        <title>Main page</title>
        <meta name="description" content="Main page showing all items" />
    </Head>

    <h1 class="text-4xl font-extrabold tracking-tight text-balance">Available items:</h1>

    <p v-if="!filteredItems.length" class="mt-12 mb-9 text-neutral-600 dark:text-neutral-400">
        No products match your search.
    </p>

    <ul v-else class="kits mt-10 mb-12">
        <li v-for="(item, index) in filteredItems" :key="item.id" class="kit" :style="{ '--i': index }">
            <a
                :href="'/items/' + item.id"
                class="kit-link"
                @click.prevent="router.get('/items/' + item.id)"
            >
                <span class="kit-stage">
                    <img
                        class="kit-img"
                        :src="kitSrc(item)"
                        :alt="`${item.team} ${item.type} kit`"
                        loading="lazy"
                    />
                </span>
                <span class="kit-caption">
                    <span class="kit-team">{{ item.team }}</span>
                    <span class="kit-meta">{{ item.type }} · {{ item.brand }}</span>
                </span>
            </a>
        </li>
    </ul>
</template>

<script setup>
import { Head, router, usePage } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({ items: Array });
const page = usePage();

const kitSrc = (item) => `/Teams/450/${item.team}/${item.image}`;

const filteredItems = computed(() => {
    const term = (new URLSearchParams(page.url.split("?")[1] ?? "").get("search") ?? "")
        .trim()
        .toLowerCase();
    if (!term) {
        return props.items;
    }
    return props.items.filter((item) =>
        [item.team, item.type, item.brand].some((field) =>
            String(field ?? "")
                .toLowerCase()
                .includes(term),
        ),
    );
});
</script>

<style scoped>
.kits {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 3rem 1.5rem;
}
.kit-link {
    display: block;
    text-align: center;
    outline-offset: 6px;
}
.kit-link:focus-visible {
    outline: 2px solid #dc2626;
    border-radius: 1rem;
}
.kit-stage {
    display: block;
    aspect-ratio: 1;
}
.kit-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    transition: transform 0.8s cubic-bezier(0.16, 1, 0.3, 1), filter 0.6s, opacity 0.6s;
}
.kit-caption {
    display: block;
    margin-top: 0.75rem;
    transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}
.kit-link:hover .kit-caption,
.kit-link:focus-visible .kit-caption {
    transform: translateY(1.25rem);
}
.kit-team {
    display: inline;
    font-weight: 800;
    letter-spacing: -0.01em;
    background: linear-gradient(#dc2626, #dc2626) left bottom / 0 2px no-repeat;
    transition: background-size 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}
.kit-meta {
    display: block;
    font-size: 0.875rem;
    color: var(--page-muted);
}

/* Focus: the hovered kit comes forward, the rest recede into soft blur */
.kit-link:hover .kit-img,
.kit-link:focus-visible .kit-img {
    transform: scale(1.1);
    filter: drop-shadow(0 22px 20px rgb(0 0 0 / 0.22));
}
.kit-link:hover .kit-team,
.kit-link:focus-visible .kit-team {
    background-size: 100% 2px;
}
.kits:has(.kit-link:hover) .kit:not(:hover) .kit-img,
.kits:has(.kit-link:focus-visible) .kit:not(:focus-within) .kit-img {
    filter: grayscale(1) blur(2px);
    opacity: 0.4;
    transform: scale(0.94);
}

@media (prefers-reduced-motion: no-preference) {
    .kit {
        animation: rise 0.9s cubic-bezier(0.16, 1, 0.3, 1) both;
        animation-delay: calc(var(--i) * 60ms);
    }
}
@keyframes rise {
    from {
        opacity: 0;
        transform: translateY(24px);
        filter: blur(6px);
    }
}
@media (prefers-reduced-motion: reduce) {
    .kit-img,
    .kit-caption,
    .kit-team {
        transition: none;
    }
}
</style>
