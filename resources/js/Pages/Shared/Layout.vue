<script setup>
import { Link, router } from "@inertiajs/vue3"
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
         <img class="cursor-pointer" src="/public/ball.webp" @click="router.get('/')" />
    
        </div>
            <div class="flex gap-2 mt-5 items-center">
           
            <Link href="/account"><FontAwesomeIcon class="text-[32px] w-8 h-8" :icon="faCircleUserRegular" /> </Link>
           <Link href="/cart">Cart</Link>
            </div>
                   
        </header>
    <main class="mt-6">
   
     
            <slot />
        
    </main>
</template>