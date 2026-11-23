<script setup lang="ts">
import { Link, usePage } from "@inertiajs/vue3";
import { computed, h, onBeforeUnmount, onMounted, reactive, ref, useId, watch } from "vue";
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
    copy: '<path d="M13.5 4.5H7A2.5 2.5 0 0 0 4.5 7v10A2.5 2.5 0 0 0 7 19.5h10a2.5 2.5 0 0 0 2.5-2.5v-6.5" /><circle cx="18.5" cy="5.5" r="2.3" />',
    squiggle: '<path d="M3.5 15c1.8-3.2 3.6-3.2 5.4 0s3.6 3.2 5.4 0 3.6-3.2 5.4 0" />',
    doc: '<path d="M14 3.5H7.5a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h9a2 2 0 0 0 2-2V8z" /><path d="M14 3.5V8h4.5M8.8 12.5h6.4M8.8 16h4.2" />',
    globe: '<circle cx="12" cy="12" r="8.5" /><path d="M3.5 12h17M12 3.5c2.5 2.6 3.6 5.4 3.6 8.5s-1.1 5.9-3.6 8.5c-2.5-2.6-3.6-5.4-3.6-8.5S9.5 6.1 12 3.5z" />',
    link: '<path d="M10.5 13.5a3.5 3.5 0 0 0 5 0l3-3a3.5 3.5 0 0 0-5-5l-1 1M13.5 10.5a3.5 3.5 0 0 0-5 0l-3 3a3.5 3.5 0 0 0 5 5l1-1" />',
    user: '<circle cx="12" cy="8.5" r="3.6" /><path d="M4.8 20c.9-3.6 3.7-5.4 7.2-5.4s6.3 1.8 7.2 5.4" />',
};

/* Small functional components: an icon and a link that picks Inertia <Link> for internal paths. */
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

const A: FunctionalComponent<{ href?: string; label?: string }> = (props, { slots }) => {
    const href = props.href;
    const common = { title: props.label, "aria-label": props.label };
    if (href && href.startsWith("/")) {
        return h(Link, { href, ...common }, slots);
    }
    const external = /^https?:/.test(href || "");
    return h(
        "a",
        {
            href: href || "#",
            ...common,
            onClick: (e: MouseEvent) => {
                if (!href || href === "#") e.preventDefault();
            },
            ...(external ? { target: "_blank", rel: "noopener noreferrer" } : {}),
        },
        slots.default?.(),
    );
};
A.props = ["href", "label"];

/* --------------------------------------------------------------- props --- */
const props = withDefaults(
    defineProps<{
        brand?: string;
        name?: string;
        logoHref?: string;
        email?: string;
        tagline?: string;
        status?: { label: string; href?: string };
        mark?: string;
        timeZone?: string;
        timeLabel?: string;
        copyright?: string;
        credit?: string;
        sheet?: string;
        paper?: string;
        sheetInk?: string;
        paperInk?: string;
        seed?: number;
        height?: string | number;
        fontSans?: string;
    }>(),
    {
        brand: "football.",
        name: "Majoros Péter",
        logoHref: "/",
        email: "",
        tagline: "",
        status: () => ({ label: "", href: "/items" }),
        mark: "",
        timeZone: "Europe/Budapest",
        timeLabel: "BUD",
        copyright: "© 2026 Majoros Péter",
        credit: "",
        sheet: "#DC2626",
        paper: "#f3f3f1",
        sheetInk: "#ffffff",
        paperInk: "",
        seed: 7,
        height: "52svh",
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
const toast = ref("");
const grain = ref("");
const clock = reactive({ h: "--", m: "--" });

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
let clockId = 0;

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

    // clock
    if (props.timeZone) {
        let f: Intl.DateTimeFormat;
        try {
            f = new Intl.DateTimeFormat("en-GB", { hour: "2-digit", minute: "2-digit", hourCycle: "h23", timeZone: props.timeZone });
        } catch {
            f = new Intl.DateTimeFormat("en-GB", { hour: "2-digit", minute: "2-digit", hourCycle: "h23" });
        }
        const tick = () => {
            const [hh = "--", mm = "--"] = f.format(new Date()).split(":");
            clock.h = hh;
            clock.m = mm;
        };
        tick();
        clockId = window.setInterval(tick, 10000);
    }
});

onBeforeUnmount(() => {
    ro?.disconnect();
    io?.disconnect();
    if (mq && onMq) mq.removeEventListener("change", onMq);
    clearInterval(clockId);
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

function flash(msg: string): void {
    toast.value = msg;
    later(() => (toast.value = ""), 1600);
}

function copyEmail() {
    if (navigator.clipboard?.writeText) {
        navigator.clipboard.writeText(props.email).then(
            () => flash(t("footer.email_copied")),
            () => flash(props.email),
        );
    } else {
        flash(props.email);
    }
}

function toTop() {
    window.scrollTo({ top: 0, behavior: still.value ? "auto" : "smooth" });
}

/* ------------------------------------------------------------- colours --- */
const HEX = /^#([0-9a-f]{3}|[0-9a-f]{6})$/i;
const pinBase = computed(() => (HEX.test(props.sheet) ? props.sheet : "#DC2626"));
const { isDark } = useTheme();
const paperColor = computed(() => (isDark.value ? "#171717" : props.paper));
const paperInkColor = computed(() => (isDark.value ? "#f87171" : props.paperInk));
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

const sealD = (() => {
    let d = "";
    for (let i = 0; i <= 24; i++) {
        const a = (i / 24) * Math.PI * 2 - Math.PI / 2;
        const r = i % 2 ? 8.6 : 10;
        d += (i ? "L" : "M") + (12 + Math.cos(a) * r).toFixed(2) + " " + (12 + Math.sin(a) * r).toFixed(2);
    }
    return d + "Z";
})();

const page = usePage();
const isLoggedIn = computed(() => Boolean(page.props.auth));

const mailto = computed(() => (props.email ? "mailto:" + props.email : "/items"));
interface FooterColumn {
    title: string;
    links: { label: string; href: string }[];
    cta?: { label: string; href: string };
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
const idx = (i: number) => ({ "--i": i });
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
        <div class="tpf-z tpf-top">
            <div class="tpf-row tpf-h" :style="idx(0)">
                <button type="button" class="tpf-ib tpf-up" :aria-label="t('footer.top')" :title="t('footer.top')" @click="toTop">
                    <Icon name="chevL" />
                </button>
            </div>
            <div class="tpf-h" :style="{ ...idx(2), position: 'relative' }">
                <template v-if="email">
                    <button type="button" class="tpf-mail" :aria-label="t('footer.copy_email') + ' ' + email" :title="t('footer.copy_email')" @click="copyEmail">
                        <span>{{ email }}</span>
                        <Icon name="copy" />
                    </button>
                    <span :class="'tpf-toast' + (toast ? ' on' : '')" role="status" aria-live="polite">{{ toast }}</span>
                </template>
            </div>
        </div>

        <p v-if="tagline" class="tpf-z tpf-tag tpf-h" :style="idx(3)">{{ tagline }}</p>

        <span v-if="mark" class="tpf-z tpf-flank tpf-h" :style="{ ...idx(4), left: '24%' }">{{ mark }}</span>
        <span
            v-if="timeZone"
            class="tpf-z tpf-flank tpf-h"
            :style="{ ...idx(4), left: '76%' }"
            :title="t('footer.local_time') + ' · ' + timeZone"
        >
            <span class="tpf-clock">{{ timeLabel }} {{ clock.h }}<i aria-hidden="true">:</i>{{ clock.m }}</span>
        </span>

        <template v-if="status">
            <A :href="status.href ?? mailto" class="tpf-z tpf-status tpf-h" :style="idx(5)">
                <i class="tpf-live" aria-hidden="true" />
                <span>{{ status.label || t('footer.status') }}</span>
            </A>
            <span class="tpf-z tpf-badge" aria-hidden="true">
                <span class="tpf-h" :style="{ ...idx(6), display: 'block', width: '100%', height: '100%' }">
                    <svg viewBox="0 0 24 24" class="tpf-seal" aria-hidden="true">
                        <path :d="sealD" fill="currentColor" stroke-linejoin="round" stroke="currentColor" stroke-width="2.2" />
                        <path
                            d="m8.3 12.2 2.5 2.5 5-5.2"
                            fill="none"
                            stroke="var(--tpf-sheet)"
                            stroke-width="2.2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </span>
            </span>
        </template>

        <nav
            v-for="(c, i) in cols"
            :key="c.title"
            :class="'tpf-z tpf-col tpf-h ' + (i ? 'r' : 'l')"
            :style="idx(6 + i)"
            :aria-label="c.title"
        >
            <p class="tpf-title">
                {{ c.title }}
                <Icon name="squiggle" />
            </p>
            <ul>
                <li v-for="l in c.links" :key="l.label">
                    <A :href="l.href">{{ l.label }}</A>
                </li>
            </ul>
            <A v-if="c.cta" :href="c.cta.href" class="tpf-big">{{ c.cta.label }}</A>
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
            <span class="tpf-p" :style="idx(3)">{{ copyright }}</span>
            <span v-if="credit" class="tpf-credit tpf-p" :style="idx(4)">{{ credit }}</span>
        </div>
        </div>
    </footer>
</template>

<style>
.tpf{position:relative;overflow:hidden;container-type:size;min-height:520px;background:var(--tpf-sheet);color:var(--tpf-on-sheet);font-family:var(--tpf-sans);isolation:isolate;-webkit-font-smoothing:antialiased;text-rendering:optimizeLegibility;--ease:cubic-bezier(.22,1,.36,1);--u:1.4cqmin;--fs:max(16px,calc(2.6*var(--u)))}
.tpf{width:100vw;margin-left:calc(50% - 50vw)}
.tpf-inner{position:absolute;top:0;bottom:0;left:0;right:0;width:min(1300px,90%);margin:0 auto}
.tpf *,.tpf *::before,.tpf *::after{box-sizing:border-box}
:where(.tpf) a{color:inherit;text-decoration:none}
:where(.tpf) button{font:inherit;color:inherit;background:none;border:0;padding:0;margin:0;cursor:pointer}
:where(.tpf) :focus-visible{outline:1.5px solid currentColor;outline-offset:4px;border-radius:4px}
.tpf-stage{position:absolute;left:0;top:0;max-width:none;display:block;pointer-events:none;z-index:0}
.tpf.is-hit .tpf-sheet-g{animation:tpf-hit .32s cubic-bezier(.3,.7,.4,1)}
.tpf-sheet{fill:var(--tpf-sheet)}.tpf-paper{fill:var(--tpf-paper)}.tpf-rim{fill:var(--tpf-rim)}
.tpf-ico{width:1.35em;height:1.35em;display:block;max-width:none;flex:none}
.tpf-seal{width:100%;height:100%;display:block;max-width:none}
.tpf-stop{transition:stop-color .7s ease}
.tpf-z{position:absolute;z-index:2}
.tpf-ink{color:var(--tpf-on-paper)}
.tpf-h{transition:opacity .7s var(--ease),translate .8s var(--ease),filter .7s var(--ease);transition-delay:calc(var(--i,0)*45ms)}
.tpf:not(.s1) .tpf-h{opacity:0;translate:0 10px;filter:blur(6px)}
.tpf-p{transition:opacity .8s var(--ease),translate .9s var(--ease),filter .8s var(--ease);transition-delay:calc(480ms + var(--i,0)*70ms)}
.tpf:not(.s3) .tpf-p{opacity:0;translate:0 18px;filter:blur(8px);transition-delay:0s}
.tpf-top{left:0;right:0;top:10.8%;transform:translateY(-50%);display:flex;align-items:center;justify-content:space-between;padding:0 11%;font-size:max(15px,calc(2.6*var(--u)))}
.tpf-row{display:flex;align-items:center;gap:.55em}
.tpf-ib{display:grid;place-items:center;width:1.9em;height:1.9em;margin:-.3em;border-radius:50%;transition:transform .35s var(--ease),background-color .25s}
.tpf-ib:hover{transform:translateY(-2px);background:color-mix(in srgb,currentColor 14%,transparent)}
.tpf-ib:active{transform:scale(.9)}
.tpf-up .tpf-ico{transform:rotate(90deg)}
.tpf-logo{position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);display:grid;place-items:center}
.tpf-mono{display:grid;place-items:center;width:2.4em;height:2.4em;border:.12em solid currentColor;border-radius:50%;font-size:1em;font-weight:700;letter-spacing:-.04em;transition:transform .5s var(--ease)}
.tpf-logo:hover .tpf-mono{transform:rotate(-8deg) scale(1.08)}
.tpf-mail{display:flex;align-items:center;gap:.6em;font-size:var(--fs);letter-spacing:.01em;padding:.35em .2em;transition:transform .35s var(--ease)}
.tpf-mail:hover{transform:translateY(-2px)}
.tpf-mail span{background:linear-gradient(currentColor,currentColor) 0 100%/0 1px no-repeat;transition:background-size .4s var(--ease)}
.tpf-mail:hover span{background-size:100% 1px}
.tpf-mail .tpf-ico{width:1.5em;height:1.5em}
.tpf-toast{position:absolute;top:calc(100% + .5em);right:0;font-size:max(11px,calc(1.6*var(--u)));letter-spacing:.04em;white-space:nowrap;padding:.35em .8em;border-radius:99px;background:var(--tpf-on-sheet);color:var(--tpf-sheet);opacity:0;transform:translateY(-4px);transition:opacity .25s,transform .35s var(--ease);pointer-events:none}
.tpf-toast.on{opacity:1;transform:none}
.tpf-tag{left:50%;top:16%;width:max-content;max-width:86%;transform:translateX(-50%);text-align:center;font-size:max(15px,calc(2.6*var(--u)));letter-spacing:-.01em}
.tpf-flank{top:16.5%;transform:translate(-50%,-50%);font-size:var(--fs);letter-spacing:.04em;white-space:nowrap;text-transform:uppercase}
.tpf-clock{font-variant-numeric:tabular-nums}.tpf-clock i{font-style:normal;animation:tpf-blink 2s steps(1) infinite}
.tpf-status{left:50%;top:26%;transform:translate(-50%,-50%);display:flex;align-items:center;gap:.6em;font-size:max(14px,calc(2.25*var(--u)));white-space:nowrap;letter-spacing:.01em}
.tpf-live{position:relative;width:.5em;height:.5em;border-radius:50%;background:currentColor}
.tpf-live::after{content:'';position:absolute;inset:0;border-radius:50%;border:1.5px solid currentColor;animation:tpf-ping 2.2s var(--ease) infinite}
.tpf a.tpf-status:hover span{text-decoration:underline;text-underline-offset:.2em}
.tpf-badge{left:50%;top:31%;transform:translate(-50%,-50%);width:max(15px,calc(2.3*var(--u)));height:max(15px,calc(2.3*var(--u)))}
.tpf-col{top:22%;display:grid;gap:max(5px,calc(.9*var(--u)));font-size:var(--fs)}
.tpf-col.l{left:14%;justify-items:start}.tpf-col.r{right:14%;justify-items:end;text-align:right}
.tpf-col .tpf-title{all:unset;display:flex;align-items:center;gap:1em;margin-bottom:max(4px,calc(.6*var(--u)))}
.tpf-col.r .tpf-title{flex-direction:row-reverse}
.tpf-col .tpf-title .tpf-ico{width:1.1em;height:1.1em}
.tpf-col ul{all:unset;display:grid;gap:max(4px,calc(.7*var(--u)));justify-items:inherit}
.tpf-col li{list-style:none}
.tpf-col li a{display:inline-block;transition:transform .35s var(--ease),opacity .25s}
.tpf-col.l li a:hover{transform:translateX(4px)}.tpf-col.r li a:hover{transform:translateX(-4px)}
.tpf-big{margin-top:max(6px,calc(1*var(--u)));font-size:max(26px,calc(4.6*var(--u)));letter-spacing:-.025em;line-height:1;text-decoration:underline;text-decoration-thickness:max(1.5px,.04em);text-underline-offset:.12em;transition:text-underline-offset .35s var(--ease)}
.tpf .tpf-big:hover{text-underline-offset:.22em}
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
.tpf-brand{left:50%;top:72%;width:max-content;transform:translateX(-50%);display:grid;justify-items:stretch}
.tpf-brand-top{display:flex;align-items:center;justify-content:space-between;gap:1em;padding:0 .2em 0 .5em}
.tpf-eye{font-size:max(15px,calc(2.6*var(--u)));letter-spacing:-.02em}
.tpf-word{display:block;overflow:hidden;padding:0 .04em .14em;font-size:max(44px,min(calc(9.5*var(--u)),16cqw));font-weight:500;letter-spacing:-.055em;line-height:.98;white-space:nowrap}
.tpf-word span{display:block;position:relative;transition:transform 1s var(--ease) .5s}
.tpf-word span::after{content:'';position:absolute;left:.02em;right:.04em;bottom:-.02em;height:max(2px,.045em);background:currentColor;transform-origin:0 50%;transition:transform .9s var(--ease) 1.05s,height .3s var(--ease)}
.tpf:not(.s3) .tpf-word span{transform:translateY(125%);transition-delay:0s}
.tpf:not(.s3) .tpf-word span::after{transform:scaleX(0);transition-delay:0s}
.tpf-word:hover span::after{height:max(4px,.075em)}
.tpf-side{top:76%;display:grid;justify-items:center;gap:max(10px,calc(1.9*var(--u)));font-size:max(13px,calc(2.1*var(--u)));transform:translateX(-50%)}
.tpf-side .tpf-ico{width:1.35em;height:1.35em;transition:transform .35s var(--ease)}
.tpf-side:hover .tpf-ico{transform:translateY(-2px) rotate(-8deg)}
.tpf-side span{background:linear-gradient(currentColor,currentColor) 0 100%/0 1px no-repeat;transition:background-size .4s var(--ease)}
.tpf-side:hover span{background-size:100% 1px}
.tpf-foot{left:0;right:0;bottom:4%;display:flex;align-items:center;justify-content:space-between;gap:1.5em;padding:0 15%;font-size:max(15px,calc(2.3*var(--u)))}
.tpf-credit{position:absolute;left:50%;transform:translateX(-50%);font-size:max(10px,calc(1.4*var(--u)));letter-spacing:.08em;text-transform:uppercase;opacity:.62;white-space:nowrap}
.tpf-handle:hover{text-decoration:underline;text-underline-offset:.2em}
@container (max-width: 620px){
.tpf-top{padding:0 7%}.tpf-flank,.tpf-mail span{display:none}
.tpf-tag{top:17%}
.tpf-status{top:20%}.tpf-badge{top:24.5%}
.tpf-col{top:29%}.tpf-col.l{left:7%}.tpf-col.r{right:7%}.tpf-col li:nth-child(n+5){display:none}
.tpf-side{top:86%}.tpf-side.l{left:14%!important}.tpf-side.r{left:86%!important}
.tpf-foot{padding:0 7%;bottom:3.5%}.tpf-credit{bottom:2.4em}
}
@keyframes tpf-hit{0%{transform:translateY(0)}30%{transform:translateY(2px)}60%{transform:translateY(-.5px)}100%{transform:translateY(0)}}
@keyframes tpf-drop{0%{transform:translateY(-62cqh) rotate(-28deg) scale(1.5);opacity:0}25%{opacity:1}80%{transform:translateY(0) rotate(3deg) scale(1)}90%{transform:scale(1.04,.95)}100%{transform:none;opacity:1}}
@keyframes tpf-shade{0%{transform:rotate(16deg) translateX(-60%) scale(2.4);opacity:0}80%{transform:rotate(16deg);opacity:1}100%{transform:rotate(16deg);opacity:1}}
@keyframes tpf-stab{0%{transform:translateY(-22%) rotate(-10deg)}70%{transform:translateY(1.5%) rotate(1deg)}100%{transform:none}}
@keyframes tpf-blink{0%{opacity:1}50%{opacity:.25}}
@keyframes tpf-ping{0%{transform:scale(1);opacity:.9}80%,100%{transform:scale(2.6);opacity:0}}
@media (prefers-reduced-motion: reduce){.tpf *,.tpf *::before,.tpf *::after{animation:none!important;transition-duration:.01s!important;transition-delay:0s!important}}
</style>
