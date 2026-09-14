<script setup>
import { Link } from "@inertiajs/vue3"
import { usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faCircleUser as faCircleUserRegular } from '@fortawesome/free-regular-svg-icons'
const page = usePage();
const auth = computed(() => page.props.auth);
</script>

<template class="container-custom">
         <header class="flex gap-2 justify-between items-center mt-5">
            <div>
            <Link class="relative opacity:0 after:absolute after:-bottom-4 after:left-0 after:right-0 after:h-0.5 after:w-full after:bg-black after:opacity-0 hover:after:opacity-100"  href="/">Home</Link>
    
        </div>
            <div class="flex gap-2">
           
            <Link href="/account"><FontAwesomeIcon class="text-3xl" :icon="faCircleUserRegular" /> </Link>
            <Link v-if="auth" method="delete" href="/logout">Logout</Link>
            <Link href="/favourites">Favourites</Link>
             <Link href="/favourites">Cart</Link>
            </div>
                   
        </header>
    <main class="mt-6">
   
     
            <slot />
        
    </main>
</template>