<template>
    <Head>
        <title>{{ item.team }} {{ kitType(item.type) }}</title>
        <meta name="description" :content="t('show.head_desc', { team: item.team, type: kitType(item.type), brand: item.brand })" />
    </Head>

    <section class="show">
        <div
            ref="stageRef"
            class="show-main"
            :class="{ zoom: isZoomed }"
            @pointerenter="startZoom"
            @pointermove="moveZoom"
            @pointerleave="stopZoom"
        >
            <img
                class="photo"
                :style="{ transformOrigin: zoomOrigin }"
                :src="`/Teams/450/${item.team}/${item.image}`"
                :alt="t('kit.alt', { team: item.team, type: kitType(item.type) })"
            />
            <span class="hint" :style="{ opacity: isZoomed ? 0 : 1 }">{{ t("show.hover_zoom") }}</span>
        </div>

        <form class="show-info" @submit.prevent="toCart">
            <div>
                <h1>{{ item.team }} {{ kitType(item.type) }}</h1>
                <p class="sub">{{ item.brand }}</p>
            </div>

            <fieldset class="show-size">
                <legend>{{ t("show.size") }}</legend>
                <div class="sizes">
                    <button
                        v-for="size in sizes"
                        :key="size"
                        type="button"
                        :aria-pressed="selected === size"
                        @click="selectSize(size)"
                    >
                        {{ size }}
                    </button>
                </div>
                <p class="err" role="alert">{{ error }}</p>
            </fieldset>

            <div class="row">
                <button ref="addButtonRef" type="submit" class="cta">{{ t("show.add") }}</button>
                <Link
                    :method="isFavourite ? 'delete' : 'post'"
                    :href="isFavourite ? '/deleteFavourite' : '/toFavourites'"
                    :data="{ item_id: item.id }"
                    as="button"
                    type="button"
                    class="heart"
                    :aria-pressed="isFavourite"
                    :aria-label="isFavourite ? t('show.fav_remove') : t('show.fav_add')"
                    @click="toggleIsFavourite"
                >
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.6-7 10-7 10z" />
                    </svg>
                </Link>
            </div>

            <details open>
                <summary>{{ t("show.details") }}</summary>
                <dl>
                    <dt>{{ t("show.team") }}</dt>
                    <dd>{{ item.team }}</dd>
                    <dt>{{ t("show.type") }}</dt>
                    <dd>{{ kitType(item.type) }}</dd>
                    <dt>{{ t("show.brand") }}</dt>
                    <dd>{{ item.brand }}</dd>
                    <dt>{{ t("show.sizes") }}</dt>
                    <dd>{{ sizes.join(", ") }}</dd>
                </dl>
            </details>
        </form>
    </section>

    <Teleport to="body">
        <div class="show-scrim" :class="{ open: isDrawerOpen }" aria-hidden="true" @click="closeDrawer"></div>
        <aside class="show-drawer" :class="{ open: isDrawerOpen }" role="dialog" aria-modal="true" :aria-label="t('show.cart_label')">
            <header>
                <span>{{ t("show.cart_count", { count: cartLines.length }) }}</span>
                <button ref="closeButtonRef" type="button" @click="closeDrawer">{{ t("show.close") }}</button>
            </header>

            <div class="lines">
                <div v-for="line in cartLines" :key="line.id" class="line">
                    <img
                        class="photo"
                        :src="`/Teams/150/${line.team}/${line.image}`"
                        :alt="`${line.team} ${kitType(line.type)}`"
                    />
                    <div>
                        <b>{{ line.team }}</b>
                        <span>{{ t("show.line_meta", { type: kitType(line.type), brand: line.brand, size: line.size }) }}</span>
                    </div>
                    <button type="button" :aria-label="t('show.remove_label', { team: line.team, type: kitType(line.type) })" @click="removeLine(line.id)">
                        {{ t("show.remove") }}
                    </button>
                </div>
                <p v-if="!cartLines.length" class="empty">{{ t("show.empty") }}</p>
            </div>

            <footer>
                <button type="button" class="cta" :disabled="!cartLines.length" @click="router.get('/cart')">
                    {{ t("show.to_cart") }}
                </button>
                <button type="button" class="ghost" @click="closeDrawer">{{ t("show.continue") }}</button>
            </footer>
        </aside>
    </Teleport>
</template>

<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { readCartLines, writeCartLines } from "@/cart";
import type { CartLine, Favourite, Item } from "@/types";
import { useTranslation } from "@/useTranslation";

const props = defineProps<{ item: Item; isFavourites: Pick<Favourite, "item_id">[] }>();

const { t, kitType } = useTranslation();

const sizes = computed(() => (Array.isArray(props.item.size) ? props.item.size : []));
const error = ref("");
const selected = ref("");
const isFavourite = ref(Boolean(props.isFavourites.find((fav) => fav.item_id === props.item.id)));

function toggleIsFavourite() {
    isFavourite.value = !isFavourite.value;
}

function selectSize(size: string): void {
    selected.value = size;
    error.value = "";
}

/* hover zoom (mouse only) */
const stageRef = ref<HTMLElement | null>(null);
const isZoomed = ref(false);
const zoomOrigin = ref("50% 50%");
const canZoom = (event: PointerEvent): boolean =>
    event.pointerType === "mouse" && !window.matchMedia("(prefers-reduced-motion: reduce)").matches;

function startZoom(event: PointerEvent): void {
    isZoomed.value = canZoom(event);
}
function moveZoom(event: PointerEvent): void {
    if (!isZoomed.value || !stageRef.value) {
        return;
    }
    const box = stageRef.value.getBoundingClientRect();
    const x = ((event.clientX - box.left) / box.width) * 100;
    const y = ((event.clientY - box.top) / box.height) * 100;
    zoomOrigin.value = `${x}% ${y}%`;
}
function stopZoom() {
    isZoomed.value = false;
}

/* cart drawer (cart lives in localStorage under "items") */
const cartLines = ref<CartLine[]>([]);
const isDrawerOpen = ref(false);
const addButtonRef = ref<HTMLButtonElement | null>(null);
const closeButtonRef = ref<HTMLButtonElement | null>(null);

function readCart() {
    cartLines.value = readCartLines();
}

function openDrawer() {
    readCart();
    isDrawerOpen.value = true;
    document.body.style.overflow = "hidden";
    nextTick(() => closeButtonRef.value?.focus());
}

function closeDrawer() {
    isDrawerOpen.value = false;
    document.body.style.overflow = "";
    addButtonRef.value?.focus();
}

function removeLine(id: string): void {
    const remaining = cartLines.value.filter((line) => line.id !== id);
    writeCartLines(remaining);
    cartLines.value = remaining;
}

function toCart() {
    if (!selected.value) {
        error.value = t("show.pick_size");
        return;
    }

    const sendItemtoStorage: CartLine = {
        id: crypto.randomUUID(),
        item_id: props.item.id,
        image: props.item.image,
        team: props.item.team,
        type: props.item.type,
        brand: props.item.brand,
        size: selected.value,
        quantity: 1,
    };
    writeCartLines([...readCartLines(), sendItemtoStorage]);
    openDrawer();
}

function onKeydown(event: KeyboardEvent): void {
    if (event.key === "Escape" && isDrawerOpen.value) {
        closeDrawer();
    }
}

onMounted(() => window.addEventListener("keydown", onKeydown));
onBeforeUnmount(() => {
    window.removeEventListener("keydown", onKeydown);
    document.body.style.overflow = "";
});
</script>

<style scoped>
.show,
.show-drawer {
    --surface: #f5f5f4;
    --line: #e5e5e5;
    --ease: cubic-bezier(0.16, 1, 0.3, 1);
}
:global(html.dark) .show,
:global(html.dark) .show-drawer {
    --surface: #171717;
    --line: #262626;
}

/* product photos have opaque white backgrounds: multiply melts them into light surfaces, dark mode keeps a clean photo plate */
.photo {
    mix-blend-mode: multiply;
}
:global(html.dark) .photo {
    mix-blend-mode: normal;
    border-radius: 1.25rem;
}

.show {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(300px, 420px);
    gap: 2rem;
    align-items: start;
    padding-bottom: 4rem;
    line-height: 1.5;
}
.show-main {
    position: relative;
    aspect-ratio: 1;
    border-radius: 2rem;
    background: var(--surface);
    overflow: hidden;
    cursor: zoom-in;
    display: grid;
    place-items: center;
}
.show-main > .photo {
    width: 82%;
    height: 82%;
    object-fit: contain;
    transition: transform 0.45s var(--ease), opacity 0.3s;
}
.show-main.zoom > .photo {
    transform: scale(1.9);
}
.hint {
    position: absolute;
    left: 1rem;
    bottom: 1rem;
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--page-muted);
    background: var(--page-bg);
    padding: 0.35rem 0.8rem;
    border-radius: 999px;
    border: 1px solid var(--line);
    transition: opacity 0.2s;
    pointer-events: none;
}
.show-info {
    display: grid;
    gap: 1.4rem;
    position: sticky;
    top: 1rem;
}
.show-info h1 {
    font-size: clamp(2rem, 3.4vw, 3rem);
    line-height: 1.02;
    letter-spacing: -0.035em;
    font-weight: 800;
    text-wrap: balance;
}
.sub {
    color: var(--page-muted);
    font-weight: 600;
    margin-top: 0.4rem;
}
.show-size {
    border: 0;
    padding: 0;
    margin: 0;
    min-width: 0;
}
.show-size legend {
    font-size: 0.85rem;
    font-weight: 700;
    margin-bottom: 0.6rem;
    padding: 0;
}
.sizes {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.sizes button {
    min-width: 3.25rem;
    height: 3rem;
    padding: 0 0.75rem;
    border: 1.5px solid var(--page-ink);
    border-radius: 0.75rem;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.2s, color 0.2s, transform 0.2s var(--ease);
}
.sizes button:hover {
    transform: translateY(-2px);
}
.sizes button[aria-pressed="true"] {
    background: var(--page-ink);
    color: var(--page-bg);
}
.err {
    color: var(--color-brand-strong);
    font-size: 0.875rem;
    font-weight: 600;
    min-height: 1.4em;
}
:global(html.dark) .err {
    color: #f87171;
}
.row {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 0.6rem;
}
.cta {
    height: 3.5rem;
    border-radius: 0.9rem;
    background: var(--color-brand);
    color: #fff;
    font-weight: 800;
    font-size: 1rem;
    padding: 0 1.75rem;
    cursor: pointer;
    box-shadow: 0 10px 24px -10px rgba(220, 38, 38, 0.7);
    transition: background 0.2s, transform 0.2s var(--ease);
}
.cta:hover {
    background: var(--color-brand-strong);
    transform: translateY(-1px);
}
.cta:active {
    transform: translateY(1px);
}
.cta:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}
.heart {
    width: 3.5rem;
    height: 3.5rem;
    display: grid;
    place-items: center;
    border: 1.5px solid var(--line);
    border-radius: 0.9rem;
    cursor: pointer;
}
.heart svg {
    width: 24px;
    height: 24px;
    fill: none;
    stroke: currentColor;
    stroke-width: 1.8;
    stroke-linejoin: round;
    transition: fill 0.2s, stroke 0.2s, transform 0.3s var(--ease);
}
.heart[aria-pressed="true"] svg {
    fill: var(--color-brand);
    stroke: var(--color-brand);
    transform: scale(1.15);
}
details {
    border-top: 1px solid var(--line);
    border-bottom: 1px solid var(--line);
    padding: 0.9rem 0;
}
summary {
    cursor: pointer;
    font-weight: 700;
    list-style: none;
    display: flex;
    justify-content: space-between;
}
summary::-webkit-details-marker {
    display: none;
}
summary::after {
    content: "+";
    font-weight: 500;
    font-size: 1.25rem;
    line-height: 1;
    transition: rotate 0.3s var(--ease);
}
details[open] summary::after {
    rotate: 45deg;
}
dl {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 0.35rem 1.5rem;
    margin-top: 0.6rem;
    font-size: 0.92rem;
}
dt {
    color: var(--page-muted);
}
dd {
    font-weight: 600;
}
:focus-visible {
    outline: 2px solid var(--color-brand);
    outline-offset: 3px;
}

/* drawer */
.show-scrim {
    position: fixed;
    inset: 0;
    background: rgb(0 0 0 / 0.5);
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.4s;
    z-index: 40;
}
.show-scrim.open {
    opacity: 1;
    pointer-events: auto;
}
.show-drawer {
    position: fixed;
    top: 0;
    right: 0;
    height: 100%;
    width: min(420px, 100%);
    background: var(--page-bg);
    color: var(--page-ink);
    border-left: 1px solid var(--line);
    translate: 100% 0;
    visibility: hidden;
    transition: translate 0.55s var(--ease), visibility 0s 0.55s;
    z-index: 41;
    display: grid;
    grid-template-rows: auto 1fr auto;
    box-shadow: -20px 0 50px -20px rgb(0 0 0 / 0.3);
    line-height: 1.5;
}
.show-drawer.open {
    translate: 0 0;
    visibility: visible;
    transition-delay: 0s;
}
.show-drawer header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--line);
    font-weight: 800;
    font-size: 1.15rem;
}
.show-drawer header button {
    font: inherit;
    cursor: pointer;
}
.lines {
    padding: 1.25rem 1.5rem;
    display: grid;
    gap: 1rem;
    align-content: start;
    overflow: auto;
}
.line {
    display: grid;
    grid-template-columns: 84px 1fr auto;
    gap: 1rem;
    align-items: center;
    padding: 0.75rem;
    border-radius: 1rem;
    background: var(--surface);
    animation: line-in 0.5s var(--ease) both;
}
@keyframes line-in {
    from {
        opacity: 0;
        translate: 20px 0;
    }
}
.line img {
    width: 84px;
    height: 84px;
    object-fit: contain;
}
.line b {
    display: block;
}
.line span {
    color: var(--page-muted);
    font-size: 0.85rem;
}
.line button {
    color: var(--page-muted);
    font-weight: 700;
    font-size: 0.8rem;
    text-decoration: underline;
    text-underline-offset: 3px;
    cursor: pointer;
}
.empty {
    color: var(--page-muted);
    text-align: center;
    padding: 3rem 0;
}
.show-drawer footer {
    padding: 1.25rem 1.5rem;
    border-top: 1px solid var(--line);
    display: grid;
    gap: 0.6rem;
}
.ghost {
    height: 3rem;
    border-radius: 0.9rem;
    border: 1.5px solid var(--line);
    font-weight: 700;
    cursor: pointer;
}

@media (max-width: 960px) {
    .show {
        grid-template-columns: 1fr;
    }
    .show-info {
        position: static;
    }
}
@media (prefers-reduced-motion: reduce) {
    .show-main > .photo,
    .show-drawer,
    .show-scrim,
    .line,
    .sizes button,
    .cta,
    .heart svg {
        transition-duration: 0.01ms !important;
        animation: none !important;
    }
}
</style>
