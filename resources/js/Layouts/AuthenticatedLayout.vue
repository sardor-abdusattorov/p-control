<script setup>
import {provide, reactive, ref} from "vue";
import NavbarVue from "@/Components/Navbar.vue";
import SideBarVue from "@/Components/SideBar.vue";
import Toast from "@/Components/Toast.vue";
import Footer from "@/Components/Footer.vue";
import {usePage} from "@inertiajs/vue3";

const sidebarOpened = ref(false);
const emit = defineEmits(["close", "open"]);
const authUserProp = usePage().props.auth.user;

const authUser = reactive(authUserProp);
provide('globalUser', authUser);

</script>

<template>
    <div class="flex w-full overflow-hidden">
        <SideBarVue :open="sidebarOpened" @close="sidebarOpened = false" />
        <div class="pl-0 lg:pl-64 w-full min-h-screen block bg-slate-100 dark:bg-[#09090b]">
            <Toast :flash="$page.props.flash" />
            <NavbarVue :open="sidebarOpened" @open="sidebarOpened = true" />
            <!-- Page Content -->
            <main
                class="mx-auto px-4 sm:px-6 lg:px-8 pb-10 text-slate-900 dark:text-slate-100 text-sm"
            >
                <slot />
            </main>
            <Footer />
        </div>
    </div>
</template>
