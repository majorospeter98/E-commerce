import "../css/app.css";
import { createInertiaApp } from "@inertiajs/vue3";
import Layout from "../js/Pages/Shared/Layout.vue";
createInertiaApp({
    layout: () => Layout,
    progress: {
        delay: 0,
        color: "#DC2626",
        includeCSS: false,
        showSpinner: false,
    },
});
