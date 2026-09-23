<template>
   <Head>
    <title>Account Page</title>
    <meta name="description" content="Your account, you can check orders, logout">
</Head>
  My Orders
  <Link href="/account/order">My orders</Link>
   <Link method="delete" href="/account/logout">Logout</Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3'
</script>

<style>

</style>