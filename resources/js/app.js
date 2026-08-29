import '../css/app.css'
import { createInertiaApp } from '@inertiajs/vue3'
import Layout from '../js/Pages/Shared/Layout.vue'

createInertiaApp({
    layout: () => Layout,

})