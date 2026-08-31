<template>
  <Head>
    <title>Login page</title>
    <meta name="description" content="Login page">
</Head>
<section class="border-2 border-b-amber-800 h-full ">
    <Form @submit.prevent="submitForm" action="/login" method="post" class="flex flex-col gap-2">
        <div>
            <label for="email">Email
        <input type="email" name="email" id ="email" v-model="form.email" />
        </label>
        </div>
    <input type="password" name="password" v-model="form.password" />
    <button type="submit">Login</button>
  </Form>
</section>
</template>

<script setup>
import { Form,router } from "@inertiajs/vue3";
import { ref } from "vue";
import {Head} from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';

let form =ref({
       'email': '',
    'password': ''
})
let submitForm = function (){
    router.post('/Login',form)
}
</script>

<style>

</style>