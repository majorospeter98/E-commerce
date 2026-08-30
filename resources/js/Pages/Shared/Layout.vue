<script setup>
import { Link } from "@inertiajs/vue3"

</script>

<template>
         <header>
            <Link href="/">Home</Link>
            <Link href="/login">Login</Link>
            <Link href="/register">Register</Link>
            
            <Link method="POST" href="/logout">Logout</Link>
            <Link href="/favourites">Favourites</Link>
                   
        </header>
    <main class="mt-6">
   
     
            <slot />
        
    </main>
</template>