import "../css/app.css";
import { createInertiaApp, router } from "@inertiajs/vue3";
import { locale } from "@/locale";
import Layout from "../js/Pages/Shared/Layout.vue";
router.on("before", (event) => {
    event.detail.visit.headers["X-Locale"] = locale.value;
});

createInertiaApp({
    layout: () => Layout,
    progress: {
        delay: 0,
        color: "#DC2626",
        includeCSS: false,
        showSpinner: false,
    },
});
