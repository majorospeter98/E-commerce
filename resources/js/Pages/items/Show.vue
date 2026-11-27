<template>
    <Head>
        <title>{{ item.team }} {{ kitType(item.type) }} · {{ garment(item.image) }}</title>
        <meta name="description" :content="t('show.head_desc', { team: item.team, type: kitType(item.type), brand: item.brand })" />
    </Head>

    <section class="show">
        <div
            ref="stageRef"
            class="show-main"
            :class="{ zoom: isZoomed, pinned: isPinned }"
            :style="{ '--zoom': photoWidth >= 800 ? 1.9 : 1.35 }"
            @pointerenter="startZoom"
            @pointermove="moveZoom"
            @pointerdown="moveZoom"
            @pointerleave="stopZoom"
        >
            <img
                ref="photoRef"
                class="photo"
                :style="{ transformOrigin: zoomOrigin, maxWidth: photoLimit, maxHeight: photoLimit }"
                :src="largeImage ?? `/Teams/450/${item.team}/${item.image}`"
                :alt="t('kit.alt', { team: item.team, type: kitType(item.type), garment: garment(item.image) })"
                @load="photoWidth = ($event.target as HTMLImageElement).naturalWidth"
            />
            <span class="hint" :style="{ opacity: isZoomed ? 0 : 1 }">{{ t("show.hover_zoom") }}</span>
            <button
                type="button"
                class="zoom-button"
                :aria-pressed="isPinned"
                :aria-label="t('show.zoom')"
                :title="t('show.zoom_help')"
                @click="togglePinned"
                @keydown="panZoom"
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true">
                    <circle cx="11" cy="11" r="6.5" />
                    <path :d="isPinned ? 'm16 16 4 4M8 11h6' : 'm16 16 4 4M8 11h6M11 8v6'" />
                </svg>
            </button>
        </div>

        <form class="show-info" @submit.prevent="toCart">
            <div>
                <h1>{{ item.team }} {{ kitType(item.type) }}</h1>
                <p class="sub">{{ garment(item.image) }} · {{ item.brand }}</p>
                <p class="price">{{ formatPrice(item.price) }}</p>
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
                <p class="err error-text" role="alert">{{ error }}</p>
            </fieldset>

            <div class="row">
                <button ref="addButtonRef" type="submit" class="btn-primary">{{ t("show.add") }}</button>
                <Link
                    v-if="isLoggedIn"
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
                <button v-else type="button" class="heart" :aria-label="t('show.fav_add')" @click="askedToLogIn = true">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.6-7 10-7 10z" />
                    </svg>
                </button>
            </div>
            <p v-if="!isLoggedIn" class="fav-note" role="status">
                <template v-if="askedToLogIn">
                    {{ t("show.fav_login") }}
                    <Link href="/login">{{ t("footer.login") }}</Link>
                </template>
            </p>

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

    <Teleport v-if="isMounted" to="body">
        <div class="show-scrim" :class="{ open: isDrawerOpen }" aria-hidden="true" @click="closeDrawer"></div>
        <aside class="show-drawer" :class="{ open: isDrawerOpen }" role="dialog" aria-modal="true" :aria-label="t('show.cart_label')">
            <header>
                <h2>{{ t("show.cart_count", { count: cartCount }) }}</h2>
                <button ref="closeButtonRef" type="button" @click="closeDrawer">{{ t("show.close") }}</button>
            </header>

            <div class="lines">
                <TransitionGroup name="line">
                    <div v-for="line in cartLines" :key="line.id" class="line">
                        <img
                            class="photo"
                            :src="`/Teams/150/${line.team}/${line.image}`"
                            :alt="t('kit.alt', { team: line.team, type: kitType(line.type), garment: garment(line.image) })"
                        />
                        <div>
                            <b>{{ line.team }}</b>
                            <span>{{ garment(line.image) }} · {{ t("show.line_meta", { type: kitType(line.type), brand: line.brand, size: line.size }) }}</span>
                            <span>{{ t("orders.quantity", { quantity: Number(line.quantity) || 1 }) }}</span>
                            <b v-if="prices[line.item_id] !== undefined">{{ formatPrice(prices[line.item_id] * (Number(line.quantity) || 1)) }}</b>
                        </div>
                        <button type="button" :aria-label="t('show.remove_label', { team: line.team, type: kitType(line.type) })" @click="removeLine(line.id)">
                            {{ t("show.remove") }}
                        </button>
                    </div>
                </TransitionGroup>
                <p v-if="!cartLines.length" class="empty">{{ t("show.empty") }}</p>
            </div>

            <footer>
                <p v-if="cartLines.length" class="total">
                    <span>{{ t("cart.total") }}</span>
                    <span>{{ formatPrice(cartTotal) }}</span>
                </p>
                <button type="button" class="btn-primary" :disabled="!cartLines.length" @click="router.get('/cart')">
                    {{ t("show.to_cart") }}
                </button>
                <button type="button" class="btn-secondary" @click="closeDrawer">{{ t("show.continue") }}</button>
            </footer>
        </aside>
    </Teleport>
</template>

<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { createLineId, readCartLines, writeCartLines } from "@/cart";
import type { CartLine, Favourite, Item, Prices } from "@/types";
import { useTranslation } from "@/useTranslation";

const props = defineProps<{
    item: Item;
    isFavourites: Pick<Favourite, "item_id">[];
    prices: Prices;
    largeImage: string | null;
}>();
const page = usePage();
const isLoggedIn = computed(() => Boolean(page.props.auth));
const askedToLogIn = ref(false);

const { t, kitType, garment, formatPrice } = useTranslation();

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

const photoRef = ref<HTMLImageElement | null>(null);
const photoWidth = ref(0);
const photoLimit = computed(() => (photoWidth.value ? `${photoWidth.value}px` : undefined));

const stageRef = ref<HTMLElement | null>(null);
const isHovering = ref(false);
const isPinned = ref(false);
const isZoomed = computed(() => isHovering.value || isPinned.value);
const zoomOrigin = ref("50% 50%");
const canZoom = (event: PointerEvent): boolean =>
    event.pointerType === "mouse" && !window.matchMedia("(prefers-reduced-motion: reduce)").matches;

function startZoom(event: PointerEvent): void {
    isHovering.value = canZoom(event);
}
function moveZoom(event: PointerEvent): void {
    if (!isZoomed.value || !stageRef.value || (event.target as Element).closest(".zoom-button")) {
        return;
    }
    const box = stageRef.value.getBoundingClientRect();
    const x = ((event.clientX - box.left) / box.width) * 100;
    const y = ((event.clientY - box.top) / box.height) * 100;
    zoomOrigin.value = `${x}% ${y}%`;
}
function stopZoom() {
    isHovering.value = false;
}
function togglePinned(): void {
    isPinned.value = !isPinned.value;
    isHovering.value = false;
    zoomOrigin.value = "50% 50%";
}
function panZoom(event: KeyboardEvent): void {
    const steps: Record<string, [number, number]> = { ArrowLeft: [-15, 0], ArrowRight: [15, 0], ArrowUp: [0, -15], ArrowDown: [0, 15] };
    if (!isPinned.value) {
        return;
    }
    if (event.key === "Escape") {
        togglePinned();
        return;
    }
    const step = steps[event.key];
    if (!step) {
        return;
    }
    event.preventDefault();
    const [x, y] = zoomOrigin.value.split(" ").map(parseFloat);
    const clamp = (value: number): number => Math.min(100, Math.max(0, value));
    zoomOrigin.value = `${clamp(x + step[0])}% ${clamp(y + step[1])}%`;
}

const cartLines = ref<CartLine[]>([]);
const cartCount = computed(() => cartLines.value.reduce((sum, line) => sum + (Number(line.quantity) || 1), 0));
const cartTotal = computed(() =>
    cartLines.value.reduce((sum, line) => sum + (props.prices[line.item_id] ?? 0) * (Number(line.quantity) || 1), 0),
);
const isDrawerOpen = ref(false);
const isMounted = ref(false);
const addButtonRef = ref<HTMLButtonElement | null>(null);
const closeButtonRef = ref<HTMLButtonElement | null>(null);

function readCart() {
    cartLines.value = readCartLines();
}

function setPageInert(inert: boolean): void {
    document.getElementById("app")?.toggleAttribute("inert", inert);
    document.body.style.overflow = inert ? "hidden" : "";
}

function openDrawer() {
    readCart();
    isDrawerOpen.value = true;
    setPageInert(true);
    nextTick(() => closeButtonRef.value?.focus());
}

function closeDrawer() {
    isDrawerOpen.value = false;
    setPageInert(false);
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

    const lines = readCartLines();
    const existingLine = lines.find((line) => line.item_id === props.item.id && line.size === selected.value);

    if (existingLine) {
        existingLine.quantity = (Number(existingLine.quantity) || 1) + 1;
    } else {
        lines.push({
            id: createLineId(),
            item_id: props.item.id,
            image: props.item.image,
            team: props.item.team,
            type: props.item.type,
            brand: props.item.brand,
            size: selected.value,
            quantity: 1,
        });
    }

    writeCartLines(lines);
    openDrawer();
}

function onKeydown(event: KeyboardEvent): void {
    if (event.key === "Escape" && isDrawerOpen.value) {
        closeDrawer();
    }
}

onMounted(() => {
    isMounted.value = true;
    if (photoRef.value?.complete) {
        photoWidth.value = photoRef.value.naturalWidth;
    }
    window.addEventListener("keydown", onKeydown);
});
onBeforeUnmount(() => {
    window.removeEventListener("keydown", onKeydown);
    setPageInert(false);
});
</script>

<style scoped>
.show,
.show-drawer {
    --ease: cubic-bezier(0.16, 1, 0.3, 1);
}

.photo {
    mix-blend-mode: var(--photo-blend);
    border-radius: var(--photo-radius);
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
    background: var(--page-surface);
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
    transform: scale(var(--zoom, 1.35));
}
.show-main.pinned {
    touch-action: none;
    cursor: move;
}
.zoom-button {
    position: absolute;
    right: 1rem;
    bottom: 1rem;
    z-index: 1;
    display: grid;
    place-items: center;
    width: 2.75rem;
    height: 2.75rem;
    border: 1.5px solid var(--color-control);
    border-radius: 999px;
    background: var(--page-bg);
    color: var(--page-ink);
    cursor: pointer;
}
.zoom-button svg {
    width: 1.4rem;
    height: 1.4rem;
}
.hint {
    position: absolute;
    left: 1rem;
    bottom: 1rem;
    font-size: 0.9rem;
    font-weight: 700;
    color: var(--page-muted);
    background: var(--page-bg);
    padding: 0.35rem 0.8rem;
    border-radius: 999px;
    border: 1px solid var(--page-line);
    transition: opacity 0.2s;
    pointer-events: none;
}
@media (hover: none) {
    .hint {
        display: none;
    }
    .show-main {
        cursor: default;
    }
}
.show-info {
    display: grid;
    gap: 1.4rem;
    position: sticky;
    top: 1rem;
}
.show-info h1 {
    font-size: clamp(2.25rem, 3.4vw, 3.375rem);
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
.price {
    font-size: 1.75rem;
    font-weight: 800;
    margin-top: 0.75rem;
    font-variant-numeric: tabular-nums;
}
.total {
    display: flex;
    justify-content: space-between;
    font-weight: 800;
    font-variant-numeric: tabular-nums;
}
.show-size {
    border: 0;
    padding: 0;
    margin: 0;
    min-width: 0;
}
.show-size legend {
    font-size: 0.956rem;
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
:where(.err) {
    color: var(--color-brand-strong);
    font-size: 0.984rem;
    font-weight: 600;
    min-height: 1.4em;
    margin-top: 0.875rem;
}
.row {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 0.6rem;
}
.heart {
    width: 3.5rem;
    height: 3.5rem;
    display: grid;
    place-items: center;
    border: 1.5px solid var(--color-control);
    border-radius: 0.9rem;
    cursor: pointer;
}
.fav-note {
    margin-top: -0.8rem;
    font-weight: 600;
}
.fav-note:empty {
    display: none;
}
.fav-note a {
    display: inline-block;
    padding-block: 0.6rem;
    text-decoration: underline 2px var(--color-brand);
    text-underline-offset: 4px;
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
    border-top: 1px solid var(--page-line);
    border-bottom: 1px solid var(--page-line);
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
    font-weight: 600;
    font-size: 1.406rem;
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
    font-size: 1.035rem;
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
    border-left: 1px solid var(--page-line);
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
    border-bottom: 1px solid var(--page-line);
    font-weight: 800;
    font-size: 1.294rem;
}
.show-drawer header h2 {
    font: inherit;
    letter-spacing: normal;
}
.show-drawer header button {
    font: inherit;
    min-width: 2.75rem;
    min-height: 2.75rem;
    cursor: pointer;
}
.lines {
    position: relative;
    padding: 1.25rem 1.5rem;
    display: grid;
    align-content: start;
    overflow: auto;
}
.line {
    position: relative;
    display: grid;
    grid-template-columns: 84px 1fr auto;
    gap: 1rem;
    align-items: center;
    padding: 0.75rem;
    margin-bottom: 1rem;
    border-radius: 1rem;
    background: var(--page-surface);
    overflow: hidden;
}

.line-enter-active,
.line-leave-active,
.line-move {
    transition: transform 0.35s var(--ease), opacity 0.25s;
}
.line-enter-from,
.line-leave-to {
    opacity: 0;
    transform: translateX(2rem);
}
.line-leave-active {
    position: absolute;
    left: 1.5rem;
    right: 1.5rem;
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
    display: block;
    color: var(--page-muted);
    font-size: 0.956rem;
}
.line button {
    min-width: 2.75rem;
    min-height: 2.75rem;
    color: var(--page-muted);
    font-weight: 700;
    font-size: 0.9rem;
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
    border-top: 1px solid var(--page-line);
    display: grid;
    gap: 0.6rem;
}

@media (max-width: 960px) {
    .show {
        grid-template-columns: 1fr;
    }
    .show-info {
        position: static;
    }
    .show-main {
        width: 100%;
        max-width: max(80svh, 17rem);
        justify-self: center;
    }
}
@media (prefers-reduced-motion: reduce) {
    .show-main > .photo,
    .show-drawer,
    .show-scrim,
    .line-enter-active,
    .line-leave-active,
    .line-move,
    .sizes button,
    .heart svg {
        transition-duration: 0.01ms !important;
        animation: none !important;
    }
}
</style>
