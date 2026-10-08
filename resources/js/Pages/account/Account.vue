<template>
   <Head>
    <title>Account Page</title>
    <meta name="description" content="Your account, you can check orders, logout">
</Head>
<section class="flex flex-wrap justify-around gap-2 w-full">
   <div class="rounded border-1 border-gray-400 min-w-[400px] w-[20%] h-[150px]">
      <Link href="/account/order">My orders</Link>
   </div>
   <div class="rounded border-1 border-gray-400 min-w-[400px] w-[20%] h-[150px]">
   <Link method="delete" href="/account/logout">Logout</Link>
   </div>
   <div class="rounded border-1 border-gray-400 min-w-[400px] w-[20%] h-[150px]">
      <Link method="delete" href="/account/logout">Favourite</Link>
      </div>
</section>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3'
</script>

<style>

</style>