<template>
    <Head>
        <title>Favourites page</title>
        <meta name="description" content="Showing the user favourite items" />
    </Head>

    <section>
        <h1 class="font-bold text-3xl">Kedvenceid:</h1>
        <ul v-if="props.fav.length > 0">
            <li v-for="item in fav" :key="item.id">
                <div
                    class="mt-5 mb-5 flex flex-col justify-center items-center text-center"
                >
                    <p>
                        <img
                            class="h-[350px] w-[350px]"
                            :src="`/Teams/350/${item.item.team}/${item.item.image}`"
                        />
                    </p>
                    <p>{{ item.item.team }}</p>
                    <p>{{ item.item.type }}</p>
                    <p>{{ item.item.brand }}</p>
                </div>
            </li>
        </ul>
        <p class="mt-5 text-2xl" v-else>Nincs kedvenced jelenleg.</p>
    </section>
</template>

<script setup>
import { Head } from "@inertiajs/vue3";
const props = defineProps({ fav: Object });
</script>

<style></style>
