<template>
    <Head>
        <title>Account Page</title>
        <meta
            name="description"
            content="Your account, you can check orders, logout"
        />
    </Head>
    <section class="flex flex-wrap justify-around gap-2 w-full">
        <div
            class="rounded border-1 border-gray-400 min-w-[400px] w-[20%] h-[150px] flex justify-center items-center"
        >
            <Link href="/account/order">Rendeléseim</Link>
        </div>
        <div
            class="rounded border-1 border-gray-400 min-w-[400px] w-[20%] h-[150px] flex justify-center items-center"
        >
            <Link href="/account/favourites">Kedvencek</Link>
        </div>
        <div
            class="rounded border-1 border-gray-400 min-w-[400px] w-[20%] h-[150px] flex justify-center items-center"
        >
            <Link class="cursor-pointer" method="delete" href="/account/logout"
                >Kijelentkezés</Link
            >
        </div>
    </section>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import { Head } from "@inertiajs/vue3";
</script>
