<script setup>
import { Link } from "@inertiajs/vue3"
import { usePage } from "@inertiajs/vue3";
import { computed } from "vue";
const page = usePage();
const auth = computed(() => page.props.auth);
</script>

<template class="container-custom">
         <header class="flex gap-2 justify-center">
            <Link href="/">Home</Link>
            <Link v-if="!auth" href="/login">Login</Link>
            <Link v-if="!auth" href="/register">Register</Link>
            
            <Link v-if="auth" method="delete" href="/logout">Logout</Link>
            <Link href="/favourites">Favourites</Link>
             <Link href="/favourites">Cart</Link>
                   
        </header>
    <main class="mt-6">
   
     
            <slot />
        
    </main>
</template>