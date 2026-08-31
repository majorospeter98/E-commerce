<script setup>
import { Link } from "@inertiajs/vue3"

</script>

<template class="container2">
         <header class="flex gap-2 justify-center">
            <Link href="/">Home</Link>
            <Link href="/login">Login</Link>
            <Link href="/register">Register</Link>
            
            <Link method="delete" href="/logout">Logout</Link>
            <Link href="/favourites">Favourites</Link>
                   
        </header>
    <main class="mt-6">
   
     
            <slot />
        
    </main>
</template>