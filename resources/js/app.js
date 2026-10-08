import '../css/app.css'
import { createInertiaApp } from '@inertiajs/vue3'
import Layout from '../js/Pages/Shared/Layout.vue'
/* add fontawesome core */
import { library } from '@fortawesome/fontawesome-svg-core'

/* import all the icons in Free Solid, Free Regular, and Brands styles */
import { fas } from '@fortawesome/free-solid-svg-icons'
import { far } from '@fortawesome/free-regular-svg-icons'
import { fab } from '@fortawesome/free-brands-svg-icons'

library.add(fas, far, fab)



createInertiaApp({
    layout: () => Layout,
 
})