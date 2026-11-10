<script setup>
import { Link, router } from "@inertiajs/vue3";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
</script>

<template class="container-custom">
    <header class="flex gap-2 justify-between items-center mt-5">
        <div>
            <img
                class="cursor-pointer"
                src="/public/ball.webp"
                @click="router.get('/')"
            />
        </div>
        <div class="flex gap-2 items-center">
            <div class="flex items-center text-center">
                <Link class="flex flex-col items-center" href="/account"
                    ><font-awesome-icon
                        class="text-[32px] w-8 h-8"
                        icon="fa-solid fa-circle-user"
                    />
                    <p
                        class="relative inline-block mt-2 after:content-[''] after:absolute after:left-0 after:bottom-0 after:h-[5px] after:top-8 after:w-full after:bg-[#DC2626] after:opacity-0 hover:after:opacity-100"
                    >
                        Account
                    </p>
                </Link>
            </div>
            <div>
                <Link class="flex flex-col items-center" href="/cart"
                    ><font-awesome-icon
                        class="text-[32px] w-8 h-8"
                        icon="fa-solid fa-cart-shopping"
                    />
                    <p
                        class="relative inline-block mt-2 after:content-[''] after:absolute after:left-0 after:bottom-0 after:h-[5px] after:top-8 after:w-full after:bg-[#DC2626] after:opacity-0 hover:after:opacity-100"
                    >
                        Cart
                    </p>
                </Link>
            </div>
        </div>
    </header>
    <main class="mt-6">
        <slot />
    </main>
</template>
