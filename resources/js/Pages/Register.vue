<template>
  {{ page }}

    <Form @submit.prevent="submitForm" action="/register" method="post" class="h-400 border-2 border-b-blue-700">
    <input class="border border-amber-800 rounded-lg" type="text" name="name" v-model="form.name" />
     <div class="text-red-500 mt-1" v-if="errors.name">{{ errors.name }} </div>
    <input type="email" name="email" v-model="form.email" />
    <input type="password" name="password" v-model="form.password" />
    <button type="submit">Create User</button>
  </Form>
</template>

<script setup>
import { Form, router } from "@inertiajs/vue3";
import { ref } from "vue";
import {Head} from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3'
const page = usePage()
defineProps({errors:Object})
let form =ref({
    'name' : '',
    'email': '',
    'password': ''
})
let submitForm = function (){
    router.post('/Register',form)
}
</script>

<style>

</style>>