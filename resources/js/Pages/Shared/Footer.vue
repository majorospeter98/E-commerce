<script setup lang="ts">
import { Link, usePage } from "@inertiajs/vue3";
import { computed, h, onBeforeUnmount, onMounted, ref, useId, watch } from "vue";
import type { FunctionalComponent } from "vue";
import { useTranslation } from "@/useTranslation";
import { useTheme } from "../../useTheme";

/* ---------------------------------------------------------------- tear --- */
const RIDGE_N = 84;
const START = 1.06;
const FLAPS = 6;

type Point = [number, number];

interface Flap {
    at: number;
    span: number;
    len: number;
    skew: number;
    dir: number;
    jag: number[];
}

interface Tear {
    ys: number[];
    rim: number[];
    lag: number[];
    flaps: Flap[];
}

type TearMode = "wait" | "intro" | "morph" | "rest";

interface TearState {
    W: number;
    H: number;
    tear: Tear;
    prev: Tear | null;
    from: number[];
    ys: number[];
    mode: TearMode;
    t0: number;
    dur: number;
    raf: number;
    px: number;
    py: number;
    lift: number[];
    busy: boolean;
    seed: number;
}

const clamp01 = (v: number): number => (Number.isFinite(v) ? (v < 0 ? 0 : v > 1 ? 1 : v) : 0);

function rng(seed: number): () => number {
    let a = (Number.isFinite(seed) ? Math.floor(seed) : 0) >>> 0;
    return () => {
        a = (a + 0x6d2b79f5) >>> 0;
        let t = a;
        t = Math.imul(t ^ (t >>> 15), t | 1);
        t ^= t + Math.imul(t ^ (t >>> 7), t | 61);
        return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
    };
}

const XS = (() => {
    const r = rng(911);
    const out: number[] = [];
    for (let i = 0; i < RIDGE_N; i++) {
        const u = i / (RIDGE_N - 1);
        const j = i > 0 && i < RIDGE_N - 1 ? (r() - 0.5) * (0.8 / RIDGE_N) : 0;
        out.push(-0.02 + u * 1.04 + j);
    }
    return out;
})();

function ridgeFloor(x: number): number {
    const d = Math.abs(x - 0.5);
    if (d < 0.13) return 0.56;
    if (d < 0.25) return 0.58;
    return 0.6;
}

function makeTear(seed: number, n: number = RIDGE_N): Tear {
    const r = rng(seed);
    const ph = [r() * 6.283, r() * 6.283, r() * 6.283];
    const ys: number[] = [];
    const rim: number[] = [];
    const lag: number[] = [];
    for (let i = 0; i < n; i++) {
        const x = XS[Math.min(i, XS.length - 1)];
        const d = Math.min(1, Math.abs(x - 0.5) * 2);
        const base = 0.6 + 0.2 * Math.pow(d, 1.35);
        const wander =
            (0.022 * Math.sin(x * 7 + ph[0]) +
                0.016 * Math.sin(x * 13 + ph[1]) +
                0.01 * Math.sin(x * 29 + ph[2])) *
            (0.45 + 0.55 * d);
        const spike = r() < 0.16 ? 2.3 : 1;
        const jag = (r() - 0.5) * 0.022 * spike;
        ys.push(Math.min(0.88, Math.max(ridgeFloor(x), base + wander + jag)));
        rim.push(1.2 + r() * 3.6);
        lag.push(Math.min(0.48, d * 0.4 + 0.04 + 0.03 * Math.sin(x * 23 + ph[1]) + r() * 0.012));
    }
    const slots = [0.05, 0.19, 0.33, 0.67, 0.81, 0.95];
    const up = r() < 0.5 ? [2, 4] : [1, 3];
    const flaps = slots.map((slot, k) => {
        const x = slot + (r() - 0.5) * 0.05;
        const span = 3 + Math.floor(r() * 5);
        const at = Math.max(0, Math.min(n - 1 - span, Math.round(x * (n - 1))));
        const dir = up.includes(k) ? -1 : 1;
        const outer = k === 0 || k === slots.length - 1;
        const len = dir < 0 ? 0.022 + r() * 0.022 : outer ? 0.085 + r() * 0.04 : 0.045 + r() * 0.05;
        return { at, span, len, skew: (r() - 0.5) * 1.1, dir, jag: [r() - 0.5, r() - 0.5, r() - 0.5, r() - 0.5] };
    });
    return { ys, rim, lag, flaps };
}

function easeTear(t: number): number {
    const x = clamp01(t);
    if (x === 0 || x === 1) return x;
    const c1 = 0.9;
    return 1 + (c1 + 1) * Math.pow(x - 1, 3) + c1 * Math.pow(x - 1, 2);
}

const easeInOut = (t: number): number => {
    const x = clamp01(t);
    return x < 0.5 ? 4 * x * x * x : 1 - Math.pow(-2 * x + 2, 3) / 2;
};

const introYs = (tear: Tear, t: number): number[] => tear.ys.map((y, i) => START + (y - START) * easeTear((t - tear.lag[i]) / 0.5));

const morphYs = (from: number[], to: Tear, t: number): number[] =>
    to.ys.map((y, i) => {
        const a = from[i] ?? y;
        return a + (y - a) * easeInOut((t - to.lag[i] * 0.6) / 0.6);
    });

const flapOpen = (tear: Tear, f: Flap, t: number): number => easeTear((t - tear.lag[f.at] - 0.18) / 0.32);

function flapPoly(ys: number[], f: Flap, W: number, H: number, open: number): Point[] {
    const b = Math.min(ys.length - 1, f.at + f.span);
    const top: Point[] = [];
    for (let k = f.at; k <= b; k++) top.push([XS[k] * W, ys[k] * H]);
    const A = top[0];
    const B = top[top.length - 1];
    const mx = (A[0] + B[0]) / 2;
    const my = (A[1] + B[1]) / 2;
    const L = f.len * H * Math.max(0, open);
    const tip: Point = [mx + f.skew * L, my + f.dir * L];
    const side = (P: Point, Q: Point, j0: number, j1: number): Point[] => {
        const dx = Q[0] - P[0];
        const dy = Q[1] - P[1];
        const len = Math.hypot(dx, dy) || 1;
        const nx = -dy / len;
        const ny = dx / len;
        return [
            [P[0] + dx / 3 + nx * j0 * 0.22 * L, P[1] + dy / 3 + ny * j0 * 0.22 * L],
            [P[0] + (dx * 2) / 3 + nx * j1 * 0.22 * L, P[1] + (dy * 2) / 3 + ny * j1 * 0.22 * L],
        ];
    };
    return [...top, ...side(B, tip, f.jag[0], f.jag[1]), tip, ...side(tip, A, f.jag[2], f.jag[3])];
}

const n1 = (v: number): number => Math.round(v * 10) / 10;
const polyD = (pts: Point[]): string => (pts.length ? "M" + pts.map((p) => n1(p[0]) + " " + n1(p[1])).join("L") + "Z" : "");

function sheetD(ys: number[], W: number, H: number, off?: number[]): string {
    let d = "M-20 -20L" + (W + 20) + " -20";
    for (let i = ys.length - 1; i >= 0; i--) {
        d += "L" + n1(XS[i] * W) + " " + n1(ys[i] * H - (off ? off[i] : 0));
    }
    return d + "Z";
}

function mixHex(a: string, b: string, t: number): string {
    const p = (hex: string): number[] => {
        const s = hex.replace("#", "");
        const f = s.length === 3 ? s.split("").map((c) => c + c).join("") : s.padEnd(6, "0").slice(0, 6);
        return [0, 2, 4].map((i) => parseInt(f.slice(i, i + 2), 16) || 0);
    };
    const x = p(a);
    const y = p(b);
    const k = clamp01(t);
    return "#" + x.map((v, i) => Math.round(v + (y[i] - v) * k).toString(16).padStart(2, "0")).join("");
}

/* --------------------------------------------------------------- icons --- */
const ICONS: Record<string, string> = {
    chevL: '<path d="M14.5 5.5 8 12l6.5 6.5" />',
    squiggle: '<path d="M3.5 15c1.8-3.2 3.6-3.2 5.4 0s3.6 3.2 5.4 0 3.6-3.2 5.4 0" />',
};

/* Small functional icon component. */
const Icon: FunctionalComponent<{ name: string }> = (props) =>
    h("svg", {
        viewBox: "0 0 24 24",
        class: "tpf-ico",
        "aria-hidden": "true",
        fill: "none",
        stroke: "currentColor",
        "stroke-width": 1.8,
        "stroke-linecap": "round",
        "stroke-linejoin": "round",
        innerHTML: ICONS[props.name] ?? "",
    });
Icon.props = ["name"];

/* --------------------------------------------------------------- props --- */
const props = withDefaults(
    defineProps<{
        copyright?: string;
        sheet?: string;
        paper?: string;
        sheetInk?: string;
        paperInk?: string;
        seed?: number;
        height?: string | number;
        fontSans?: string;
    }>(),
    {
        copyright: "© 2026 Majoros Péter",
        sheet: "#DC2626",
        paper: "#f3f3f1",
        sheetInk: "#ffffff",
        paperInk: "",
        seed: 7,
        height: "44svh",
        fontSans: '"Barlow Condensed", "Helvetica Neue", Helvetica, Arial, system-ui, sans-serif',
    },
);

/* --------------------------------------------------------------- state --- */
const uid = useId().replace(/[^a-zA-Z0-9]/g, "");
const rootRef = ref<HTMLElement | null>(null);
const svgRef = ref<SVGSVGElement | null>(null);
const sheetRef = ref<SVGPathElement | null>(null);
const rimRef = ref<SVGPathElement | null>(null);
const pinRef = ref<HTMLElement | null>(null);
const flapEls: (SVGPathElement | null)[] = [];
const gradEls: (SVGLinearGradientElement | null)[] = [];
const timers: number[] = [];

const still = ref(false);
const stage = ref(0);
const hit = ref(false);
const visible = ref(false);
const pin = ref<"idle" | "lift" | "stab">("idle");
const grain = ref("");

const s: TearState = {
    W: 0,
    H: 0,
    tear: makeTear(props.seed),
    prev: null,
    from: [],
    ys: [],
    mode: "wait",
    t0: 0,
    dur: 1,
    raf: 0,
    px: -1e4,
    py: -1e4,
    lift: new Array(FLAPS).fill(0),
    busy: false,
    seed: props.seed,
};

const later = (fn: () => void, ms: number): void => {
    timers.push(window.setTimeout(fn, ms));
};

/* ------------------------------------------------------------- drawing --- */
function frame(now: number): void {
    s.raf = 0;
    const { W, H, tear } = s;
    if (!W || !H) return;
    let busy = false;
    let ys: number[];
    const open = new Array(FLAPS).fill(1);
    const openOld = new Array(FLAPS).fill(0);
    if (s.mode === "wait") {
        ys = tear.ys.map(() => START);
        open.fill(0);
    } else if (s.mode === "intro") {
        const p = (now - s.t0) / s.dur;
        ys = introYs(tear, Math.min(1, p));
        tear.flaps.forEach((f, k) => (open[k] = flapOpen(tear, f, Math.min(1, p))));
        if (p >= 1) s.mode = "rest";
        else busy = true;
    } else if (s.mode === "morph") {
        const p = (now - s.t0) / s.dur;
        ys = morphYs(s.from, tear, Math.min(1, p));
        open.fill(easeTear((p - 0.5) / 0.5));
        openOld.fill(1 - easeInOut(p / 0.35));
        if (p >= 1) s.mode = "rest";
        else busy = true;
    } else {
        ys = tear.ys;
    }
    s.ys = ys;

    tear.flaps.forEach((f, k) => {
        const b = Math.min(ys.length - 1, f.at + f.span);
        const mx = ((XS[f.at] + XS[b]) / 2) * W;
        const my = ((ys[f.at] + ys[b]) / 2) * H;
        const target = still.value ? 0 : clamp01(1 - Math.hypot(s.px - mx, s.py - my) / (0.2 * Math.max(W, 520)));
        s.lift[k] += (target - s.lift[k]) * 0.14;
        if (Math.abs(target - s.lift[k]) > 0.003) busy = true;
        open[k] *= 1 + 0.32 * s.lift[k];
    });

    sheetRef.value?.setAttribute("d", sheetD(ys, W, H, tear.rim));
    rimRef.value?.setAttribute("d", sheetD(ys, W, H));
    const draw = (k: number, f: Flap | null | undefined, o: number): void => {
        const el = flapEls[k];
        const g = gradEls[k];
        if (!el || !g) return;
        if (!f || o < 0.01) return el.setAttribute("d", "");
        const pts = flapPoly(ys, f, W, H, o);
        el.setAttribute("d", polyD(pts));
        const b = Math.min(ys.length - 1, f.at + f.span);
        const tip = pts[f.span + 3] as Point;
        g.setAttribute("x1", String(((XS[f.at] + XS[b]) / 2) * W));
        g.setAttribute("y1", String(((ys[f.at] + ys[b]) / 2) * H));
        g.setAttribute("x2", String(tip[0]));
        g.setAttribute("y2", String(tip[1]));
    };
    for (let k = 0; k < FLAPS; k++) {
        draw(k, tear.flaps[k], open[k]);
        draw(FLAPS + k, s.prev?.flaps[k], openOld[k]);
    }
    if (busy) s.raf = requestAnimationFrame(frame);
}

function kick() {
    if (!s.raf && typeof requestAnimationFrame !== "undefined") s.raf = requestAnimationFrame(frame);
}

/* ------------------------------------------------------------ lifecycle --- */
let ro: ResizeObserver | null = null;
let io: IntersectionObserver | null = null;
let mq: MediaQueryList | null = null;
let onMq: (() => void) | null = null;

onMounted(() => {
    // reduced motion
    if (typeof matchMedia !== "undefined") {
        const query = matchMedia("(prefers-reduced-motion: reduce)");
        mq = query;
        onMq = () => (still.value = query.matches);
        onMq();
        query.addEventListener("change", onMq);
    }

    // size
    const el = rootRef.value;
    if (!el) return;
    ro = new ResizeObserver(() => {
        s.W = el.clientWidth;
        s.H = el.clientHeight;
        const svg = svgRef.value;
        if (svg) {
            svg.setAttribute("width", String(s.W));
            svg.setAttribute("height", String(s.H));
            svg.setAttribute("viewBox", "0 0 " + s.W + " " + s.H);
        }
        kick();
    });
    ro.observe(el);

    // visibility
    if (typeof IntersectionObserver === "undefined") {
        visible.value = true;
    } else {
        io = new IntersectionObserver(([e]) => (visible.value = e.isIntersecting), { threshold: 0.22 });
        io.observe(el);
    }

    // grain tile
    const c = document.createElement("canvas");
    c.width = c.height = 160;
    const g = c.getContext("2d");
    if (g) {
        const img = g.createImageData(160, 160);
        const r = rng(42);
        for (let i = 0; i < img.data.length; i += 4) {
            const v = r() < 0.5 ? 0 : 255;
            img.data[i] = img.data[i + 1] = img.data[i + 2] = v;
            img.data[i + 3] = Math.floor(r() * 46);
        }
        g.putImageData(img, 0, 0);
        grain.value = c.toDataURL();
    }
});

onBeforeUnmount(() => {
    ro?.disconnect();
    io?.disconnect();
    if (mq && onMq) mq.removeEventListener("change", onMq);
    cancelAnimationFrame(s.raf);
    s.raf = 0;
    timers.forEach((id) => clearTimeout(id));
});

// the opening: chrome, pin, rip
watch(visible, (isVisible) => {
    if (!isVisible || stage.value > 0) return;
    if (still.value) {
        stage.value = 3;
        s.mode = "rest";
        kick();
        return;
    }
    stage.value = 1;
    later(() => (stage.value = 2), 640);
    later(() => {
        hit.value = true;
        stage.value = 3;
        s.mode = "intro";
        s.t0 = performance.now();
        s.dur = 1550;
        kick();
    }, 1140);
    later(() => (hit.value = false), 1480);
});

// a new seed from outside re-tears in place
watch(
    () => props.seed,
    (seed) => {
        if (seed === s.seed) return;
        s.seed = seed;
        s.prev = s.tear;
        s.from = s.ys.length ? s.ys : s.tear.ys;
        s.tear = makeTear(seed);
        if (s.mode !== "wait") {
            s.mode = still.value ? "rest" : "morph";
            s.t0 = performance.now();
            s.dur = 1100;
        }
        kick();
    },
);

/* ------------------------------------------------------------- actions --- */
function onPointerMove(e: PointerEvent): void {
    const el = rootRef.value;
    if (!el || still.value) return;
    const r = el.getBoundingClientRect();
    s.px = e.clientX - r.left;
    s.py = e.clientY - r.top;
    const tilt = Math.max(-9, Math.min(9, (s.px / r.width - 0.5) * 18));
    pinRef.value?.style.setProperty("--tilt", tilt.toFixed(2) + "deg");
    kick();
}

function onPointerLeave() {
    s.px = s.py = -1e4;
    pinRef.value?.style.setProperty("--tilt", "0deg");
    kick();
}

function repin() {
    if (s.busy || stage.value < 3) return;
    const next = s.seed + 1;
    const swap = () => {
        s.seed = next;
        s.prev = s.tear;
        s.from = s.ys.length ? s.ys : s.tear.ys;
        s.tear = makeTear(next);
    };
    if (still.value) {
        swap();
        s.prev = null;
        s.mode = "rest";
        kick();
        return;
    }
    s.busy = true;
    pin.value = "lift";
    later(() => {
        swap();
        s.mode = "morph";
        s.t0 = performance.now();
        s.dur = 1150;
        kick();
    }, 300);
    later(() => (pin.value = "stab"), 1280);
    later(() => {
        pin.value = "idle";
        s.busy = false;
    }, 1720);
}

function toTop() {
    window.scrollTo({ top: 0, behavior: still.value ? "auto" : "smooth" });
}

/* ------------------------------------------------------------- colours --- */
const HEX = /^#([0-9a-f]{3}|[0-9a-f]{6})$/i;
const pinBase = computed(() => (HEX.test(props.sheet) ? props.sheet : "#DC2626"));
const { isDark } = useTheme();
const paperColor = computed(() => (isDark.value ? "#171717" : props.paper));
const paperInkColor = computed(() => (isDark.value ? "#38bdf8" : props.paperInk));
const flapBase = computed(() =>
    mixHex(HEX.test(paperColor.value) ? paperColor.value : "#f3f3f1", "#ffffff", isDark.value ? 0.1 : 0.35),
);

const pinColors = computed(() => ({
    hi: mixHex(pinBase.value, "#ffffff", 0.62),
    mid: pinBase.value,
    lo: mixHex(pinBase.value, "#000000", 0.42),
    deep: mixHex(pinBase.value, "#000000", 0.66),
}));

const flapStops = computed(() => [
    mixHex(flapBase.value, "#1c2a44", 0.34),
    mixHex(flapBase.value, "#1c2a44", 0.08),
]);

const rootStyle = computed(() => ({
    height: typeof props.height === "number" ? props.height + "px" : props.height,
    "--tpf-sheet": props.sheet,
    "--tpf-paper": paperColor.value,
    "--tpf-on-sheet": props.sheetInk,
    "--tpf-on-paper": paperInkColor.value || mixHex(props.sheet, "#000000", 0.15),
    "--tpf-rim": mixHex(flapBase.value, "#ffffff", 0.6),
    "--tpf-sans": props.fontSans,
}));

const rootClass = computed(() =>
    ["tpf", stage.value >= 1 && "s1", stage.value >= 2 && "s2", stage.value >= 3 && "s3", hit.value && "is-hit"]
        .filter(Boolean)
        .join(" "),
);

const page = usePage();
const isLoggedIn = computed(() => Boolean(page.props.auth));

interface FooterColumn {
    title: string;
    links: { label: string; href: string }[];
}

const { t } = useTranslation();

const cols = computed((): FooterColumn[] => {
    const pages: FooterColumn = {
        title: t("footer.pages"),
        links: [
            { label: t("footer.home"), href: "/" },
            { label: t("footer.cart"), href: "/cart" },
            ...(isLoggedIn.value
                ? []
                : [
                      { label: t("footer.login"), href: "/login" },
                      { label: t("footer.register"), href: "/register" },
                  ]),
        ],
    };
    if (!isLoggedIn.value) return [pages];
    return [
        pages,
        {
            title: t("footer.account"),
            links: [
                { label: t("footer.favourites"), href: "/account/favourites" },
                { label: t("footer.orders"), href: "/account/order" },
            ],
        },
    ];
});
</script>

<template>
    <footer
        ref="rootRef"
        :class="rootClass"
        :style="rootStyle"
        @pointermove="onPointerMove"
        @pointerleave="onPointerLeave"
    >
        <svg ref="svgRef" class="tpf-stage" aria-hidden="true">
            <defs>
                <radialGradient :id="uid + 'v'" cx=".5" cy=".78" r=".75">
                    <stop offset=".45" stop-color="#fff" stop-opacity="0" />
                    <stop offset="1" stop-color="#2a0808" stop-opacity=".07" />
                </radialGradient>
                <linearGradient :id="uid + 'l'" x1="0" y1="0" x2=".35" y2="1">
                    <stop offset="0" stop-color="#fff" stop-opacity=".13" />
                    <stop offset=".55" stop-color="#fff" stop-opacity="0" />
                    <stop offset="1" stop-color="#330000" stop-opacity=".12" />
                </linearGradient>
                <pattern v-if="grain" :id="uid + 'g'" width="160" height="160" patternUnits="userSpaceOnUse">
                    <image :href="grain" width="160" height="160" />
                </pattern>
                <filter :id="uid + 'd'" x="-5%" y="-5%" width="110%" height="120%">
                    <feDropShadow dx="0" dy="3" stdDeviation="3.5" flood-color="#2a0808" flood-opacity=".3" />
                </filter>
                <filter :id="uid + 'f'" x="-30%" y="-30%" width="160%" height="170%">
                    <feDropShadow dx="-2" dy="4" stdDeviation="4" flood-color="#2a0808" flood-opacity=".26" />
                </filter>
                <linearGradient
                    v-for="n in FLAPS * 2"
                    :key="n"
                    :id="uid + 'fg' + (n - 1)"
                    :ref="(el) => (gradEls[n - 1] = el as SVGLinearGradientElement | null)"
                    gradientUnits="userSpaceOnUse"
                >
                    <stop offset="0" :stop-color="flapStops[0]" />
                    <stop offset=".5" :stop-color="flapStops[1]" />
                    <stop offset="1" stop-color="#ffffff" />
                </linearGradient>
                <path :id="uid + 's'" ref="sheetRef" />
            </defs>
            <rect class="tpf-paper" width="100%" height="100%" />
            <rect width="100%" height="100%" :fill="'url(#' + uid + 'v)'" style="mix-blend-mode: multiply" />
            <rect v-if="grain" width="100%" height="100%" :fill="'url(#' + uid + 'g)'" opacity=".32" />
            <g class="tpf-sheet-g" :filter="'url(#' + uid + 'd)'">
                <path ref="rimRef" class="tpf-rim" />
                <use :href="'#' + uid + 's'" class="tpf-sheet" />
            </g>
            <use :href="'#' + uid + 's'" :fill="'url(#' + uid + 'l)'" />
            <use v-if="grain" :href="'#' + uid + 's'" :fill="'url(#' + uid + 'g)'" opacity=".5" />
            <g :filter="'url(#' + uid + 'f)'">
                <path
                    v-for="n in FLAPS * 2"
                    :key="n"
                    :ref="(el) => (flapEls[n - 1] = el as SVGPathElement | null)"
                    :fill="'url(#' + uid + 'fg' + (n - 1) + ')'"
                />
            </g>
        </svg>

        <div class="tpf-inner">
        <!-- on the sheet -->
        <button type="button" class="tpf-z tpf-ib tpf-up" :aria-label="t('footer.top')" :title="t('footer.top')" @click="toTop">
            <Icon name="chevL" />
        </button>

        <nav v-for="(c, i) in cols" :key="c.title" :class="'tpf-z tpf-col ' + (i ? 'r' : 'l')" :aria-label="c.title">
            <p class="tpf-title">
                {{ c.title }}
                <Icon name="squiggle" />
            </p>
            <ul>
                <li v-for="l in c.links" :key="l.label">
                    <Link :href="l.href">{{ l.label }}</Link>
                </li>
            </ul>
        </nav>

        <!-- the pin -->
        <button
            ref="pinRef"
            type="button"
            class="tpf-pin"
            :data-pin="pin"
            :aria-label="t('footer.repin')"
            :title="t('footer.pin')"
            @click="repin"
        >
            <span class="tpf-shadow" aria-hidden="true" />
            <span class="tpf-tilt">
                <span class="tpf-drop">
                    <span class="tpf-body">
                        <svg viewBox="0 0 100 200" class="tpf-pin-svg" aria-hidden="true">
                            <defs>
                                <linearGradient :id="uid + 'ps'" x1="0" x2="1" y1="0" y2="0">
                                    <stop offset="0" class="tpf-stop" :style="{ stopColor: pinColors.lo }" />
                                    <stop offset=".24" class="tpf-stop" :style="{ stopColor: pinColors.mid }" />
                                    <stop offset=".4" class="tpf-stop" :style="{ stopColor: pinColors.hi }" />
                                    <stop offset=".58" class="tpf-stop" :style="{ stopColor: pinColors.mid }" />
                                    <stop offset="1" class="tpf-stop" :style="{ stopColor: pinColors.deep }" />
                                </linearGradient>
                                <radialGradient :id="uid + 'pt'" cx=".38" cy=".3" r=".8">
                                    <stop offset="0" class="tpf-stop" :style="{ stopColor: pinColors.hi }" />
                                    <stop offset=".55" class="tpf-stop" :style="{ stopColor: pinColors.mid }" />
                                    <stop offset="1" class="tpf-stop" :style="{ stopColor: pinColors.lo }" />
                                </radialGradient>
                                <linearGradient :id="uid + 'pn'" x1="0" x2="1" y1="0" y2="0">
                                    <stop offset="0" stop-color="#5b616b" />
                                    <stop offset=".4" stop-color="#f4f5f7" />
                                    <stop offset=".7" stop-color="#a3a9b2" />
                                    <stop offset="1" stop-color="#4a4f57" />
                                </linearGradient>
                                <filter :id="uid + 'pb'" x="-50%" y="-50%" width="200%" height="200%">
                                    <feGaussianBlur stdDeviation="1.4" />
                                </filter>
                            </defs>
                            <path d="M48.3 112h3.4l-.7 76-1 4-1-4z" :fill="'url(#' + uid + 'pn)'" />
                            <ellipse cx="50" cy="191.5" rx="3.2" ry="1.1" fill="#2a0808" opacity=".35" />
                            <path d="M28 103v6.5a22 7 0 0 0 44 0V103z" :fill="'url(#' + uid + 'ps)'" />
                            <ellipse cx="50" cy="103" rx="22" ry="7" :fill="'url(#' + uid + 'pt)'" />
                            <path
                                d="M41.5 38c1.6 16 4.6 26 3 36-1.8 10-13 19-14.6 29h40.2c-1.6-10-12.8-19-14.6-29-1.6-10 1.4-20 3-36z"
                                :fill="'url(#' + uid + 'ps)'"
                            />
                            <path d="M16 22v14a34 13 0 0 0 68 0V22z" :fill="'url(#' + uid + 'ps)'" />
                            <ellipse cx="50" cy="22" rx="34" ry="13" :fill="'url(#' + uid + 'pt)'" />
                            <ellipse cx="50" cy="22" rx="33" ry="12.3" fill="none" stroke="#fff" stroke-opacity=".28" stroke-width=".8" />
                            <ellipse cx="37" cy="17.5" rx="12" ry="4" fill="#fff" opacity=".7" :filter="'url(#' + uid + 'pb)'" />
                            <path
                                d="M22 38c4 4.5 9.5 6.4 15 7"
                                stroke="#fff"
                                stroke-opacity=".35"
                                stroke-width="2"
                                fill="none"
                                stroke-linecap="round"
                                :filter="'url(#' + uid + 'pb)'"
                            />
                            <path d="M45 50c.8 8 1.6 14 .8 22" stroke="#fff" stroke-opacity=".45" stroke-width="1.6" fill="none" stroke-linecap="round" />
                        </svg>
                    </span>
                </span>
            </span>
        </button>


        <div class="tpf-z tpf-foot tpf-ink">
            <span>{{ copyright }}</span>
        </div>
        </div>
    </footer>
</template>

<style>
.tpf{position:relative;overflow:hidden;container-type:size;min-height:460px;background:var(--tpf-sheet);color:var(--tpf-on-sheet);font-family:var(--tpf-sans);font-weight:600;isolation:isolate;-webkit-font-smoothing:antialiased;--ease:cubic-bezier(.22,1,.36,1);--u:1.4cqmin;--fs:max(19px,calc(3.1*var(--u)))}
.tpf-inner{position:absolute;inset:0;width:min(1300px,90%);margin:0 auto}
.tpf *,.tpf *::before,.tpf *::after{box-sizing:border-box}
:where(.tpf) a{color:inherit;text-decoration:none}
:where(.tpf) button{font:inherit;color:inherit;background:none;border:0;padding:0;margin:0;cursor:pointer}
:where(.tpf) :focus-visible{outline:2px solid currentColor;outline-offset:4px;border-radius:4px}
.tpf-stage{position:absolute;left:0;top:0;max-width:none;display:block;pointer-events:none;z-index:0}
.tpf.is-hit .tpf-sheet-g{animation:tpf-hit .32s cubic-bezier(.3,.7,.4,1)}
.tpf-sheet{fill:var(--tpf-sheet)}.tpf-paper{fill:var(--tpf-paper)}.tpf-rim{fill:var(--tpf-rim)}
.tpf-ico{width:1.35em;height:1.35em;display:block;max-width:none;flex:none}
.tpf-stop{transition:stop-color .7s ease}
.tpf-z{position:absolute;z-index:2}
.tpf-ink{color:var(--tpf-on-paper)}
.tpf-ib{display:grid;place-items:center;width:44px;height:44px;border-radius:50%;transition:background-color .2s}
.tpf-ib:hover{background:color-mix(in srgb,currentColor 14%,transparent)}
.tpf-up{right:0;top:6%}
.tpf-up .tpf-ico{transform:rotate(90deg)}
.tpf-col{top:7%;display:grid;gap:8px;font-size:var(--fs)}
.tpf-col.l{left:0;justify-items:start}.tpf-col.r{top:calc(6% + 56px);right:0;justify-items:end;text-align:right}
.tpf-col .tpf-title{display:flex;align-items:center;gap:1em;margin:0}
.tpf-col.r .tpf-title{flex-direction:row-reverse}
.tpf-col ul{list-style:none;margin:0;padding:0;display:grid;gap:0;justify-items:inherit}
.tpf-col li a{display:inline-flex;align-items:center;min-height:44px;text-underline-offset:.25em}
.tpf-col li a:hover{text-decoration:underline}
.tpf-pin{position:absolute;z-index:3;left:50%;top:68%;width:max(46px,calc(9*var(--u)));height:max(92px,calc(18*var(--u)));transform:translate(-50%,-100%);cursor:pointer;-webkit-tap-highlight-color:transparent;touch-action:manipulation}
.tpf-pin:focus-visible{outline-offset:6px}
.tpf-tilt{position:absolute;inset:0;transform-origin:50% 100%;transform:rotate(calc(5deg + var(--tilt,0deg)));transition:transform .7s var(--ease)}
.tpf-drop,.tpf-body{position:absolute;inset:0;transform-origin:50% 100%}
.tpf-body{transition:transform .32s var(--ease)}
.tpf-pin-svg{position:absolute;inset:0;width:100%;height:100%;max-width:none;display:block;overflow:visible}
.tpf-shadow{position:absolute;right:50%;bottom:-.5%;width:150%;height:24%;transform-origin:100% 50%;transform:rotate(16deg);transition:transform .45s var(--ease),opacity .45s}
.tpf-shadow::before,.tpf-shadow::after{content:'';position:absolute;top:50%;border-radius:50%;transform:translateY(-50%)}
.tpf-shadow::before{right:0;width:100%;height:24%;background:linear-gradient(90deg,transparent,rgba(42,8,8,.26) 60%,rgba(42,8,8,.42));filter:blur(2px)}
.tpf-shadow::after{left:-6%;width:60%;height:100%;background:radial-gradient(closest-side,rgba(42,8,8,.22),transparent);filter:blur(3px)}
.tpf:not(.s2) .tpf-pin{opacity:0;pointer-events:none}
.tpf.s2 .tpf-drop{animation:tpf-drop .62s cubic-bezier(.5,0,.75,0) both}
.tpf.s2 .tpf-shadow{animation:tpf-shade .62s cubic-bezier(.5,0,.75,0) both}
.tpf-pin:hover .tpf-body{transform:translateY(-3%)}
.tpf-pin:hover .tpf-shadow{transform:rotate(16deg) translateX(-5%) scaleX(1.06)}
.tpf-pin[data-pin=lift] .tpf-body{transform:translateY(-22%) rotate(-10deg);transition-duration:.3s}
.tpf-pin[data-pin=lift] .tpf-shadow{transform:rotate(16deg) translateX(-26%) scale(1.25);opacity:.55}
.tpf-pin[data-pin=stab] .tpf-body{animation:tpf-stab .42s cubic-bezier(.55,0,.8,.2) both}
.tpf-foot span{background:var(--tpf-paper);border-radius:99px;padding:.1em .9em}
.tpf-foot{left:0;right:0;bottom:4%;display:flex;justify-content:center;font-size:var(--fs)}
@keyframes tpf-hit{0%{transform:translateY(0)}30%{transform:translateY(2px)}60%{transform:translateY(-.5px)}100%{transform:translateY(0)}}
@keyframes tpf-drop{0%{transform:translateY(-62cqh) rotate(-28deg) scale(1.5);opacity:0}25%{opacity:1}80%{transform:translateY(0) rotate(3deg) scale(1)}90%{transform:scale(1.04,.95)}100%{transform:none;opacity:1}}
@keyframes tpf-shade{0%{transform:rotate(16deg) translateX(-60%) scale(2.4);opacity:0}80%{transform:rotate(16deg);opacity:1}100%{transform:rotate(16deg);opacity:1}}
@keyframes tpf-stab{0%{transform:translateY(-22%) rotate(-10deg)}70%{transform:translateY(1.5%) rotate(1deg)}100%{transform:none}}
@media (prefers-reduced-motion: reduce){.tpf *,.tpf *::before,.tpf *::after{animation:none!important;transition-duration:.01s!important;transition-delay:0s!important}}
</style>
