<template>
  

    <Form @submit.prevent="submitForm" action="/login" method="post">
        <input type="email" name="email" v-model="form.email" />
    <input type="password" name="password" v-model="form.password" />
    <button type="submit">Create User</button>
  </Form>
</template>

<script setup>
import { Form,router } from "@inertiajs/vue3";
import { ref } from "vue";

let form =ref({
       'email': '',
    'password': ''
})
let submitForm = function (){
    router.post('/Login',form)
}
</script>

<style>

</style>>