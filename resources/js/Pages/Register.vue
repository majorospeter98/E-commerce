<template>
    <Head>
    <title>Register page</title>
    <meta name="description" content="Register page">
</Head>
  <section class="mx-auto w-[650px]">
    <Form @submit.prevent="submitForm" action="/register" method="post" class="min-h-[60%] flex flex-col mx-auto border-1 border-gray shadow-sm  w-full">
      <h1 class="mt-12 text-4xl mx-auto">Regisztráció</h1>
       <div class="mt-8 w-[90%] mx-auto">
            <label for="name">
        <input type="text" name="name" id ="name" required placeholder="Add meg a neved" v-model="form.name" class="border border-2 w-[100%] p-1 pl-[10px] rounded-sm border-[#ced4da]" />
        </label>
     <div
                        class="text-center text-red-500 mt-3"
                        v-if="errors.name"
                    >
                        {{ errors.name }}
                    </div>  
      </div>
       <div class="mt-8 w-[90%] mx-auto">
            <label for="email">
        <input type="email" name="email" id ="email" required placeholder="Add meg az email címed" v-model="form.email" class="border border-2 w-[100%] p-1 pl-[10px] rounded-sm border-[#ced4da]" />
      </label>
        <div
                        class="text-center text-red-500 mt-3"
                        v-if="errors.email"
                    >
                        {{ errors.email }}
                    </div>    
  

        </div>
        <div class="w-[90%] mx-auto mt-8">
    <input type="password" name="password" required v-model="form.password" placeholder="Add meg a jelszavad" class="border border-2 w-[100%] p-1 pl-[10px] rounded-sm border-[#ced4da]"  />
  <div
                        class="text-center text-red-500 mt-3"
                        v-if="errors.password"
                    >
                        {{ errors.password }}
                    </div>       
</div>
        

        <div class="w-[90%] mx-auto mt-8">
               <button class="w-[100%]  block p-4 bg-black text-white transition-opacity duration-250  hover:opacity-50 " type="submit">Bejelentkezés</button>
               
        </div>
        <hr class="border border-t-1 mt-7 border-b-[#00000020] w-[90%] opacity-25 mx-auto ">
          <div class="w-[90%] mx-auto mt-8">
               <button class="w-[100%]  block p-4 bg-[#00000020] text-black transition-opacity duration-250  hover:opacity-75 " type="submit">Regisztráció</button>
              
        </div>

  </Form>
</section>
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