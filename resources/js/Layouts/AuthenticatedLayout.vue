<script setup>
import { provide, reactive, ref } from "vue";
import NavbarVue from "@/Components/Navbar.vue";
import SideBarVue from "@/Components/SideBar.vue";
import Toast from "@/Components/Toast.vue";
import Footer from "@/Components/Footer.vue";
import MetaTags from "@/Components/MetaTags.vue";
import { usePage } from "@inertiajs/vue3";

defineProps({
    title: {
        type: String,
        default: null,
    },
    description: {
        type: String,
        default: null,
    },
});

const sidebarOpened = ref(false);
const authUserProp = usePage().props.auth.user;
const is_expanded = ref(
    localStorage.getItem("is_expanded") ? JSON.parse(localStorage.getItem("is_expanded")) : true
);
const toggleSidebar = () => {
    sidebarOpened.value = !sidebarOpened.value;
};
const toggleMenu = () => {
    is_expanded.value = !is_expanded.value;
    localStorage.setItem("is_expanded", JSON.stringify(is_expanded.value));
};
const authUser = reactive(authUserProp);
provide("globalUser", authUser);
</script>

<template>
    <MetaTags :title="title" :description="description" />

    <div class="flex w-full overflow-hidden">
        <SideBarVue :open="sidebarOpened" :is_expanded="is_expanded" @close="sidebarOpened = false" @toggleMenu="toggleMenu"/>

        <div class="w-full min-h-screen block bg-slate-100 dark:bg-[#09090b] transition-all duration-300"
             :class="is_expanded ? 'lg:pl-64 sm:pl-0' : 'pl-0'">
            <Toast :flash="$page.props.flash"/>
            <NavbarVue :open="sidebarOpened" :is_expanded="is_expanded" @open="sidebarOpened = true" @toggleMenu="toggleMenu"/>
            <main class="mx-auto px-4 sm:px-6 lg:px-8 pb-10 text-slate-900 dark:text-slate-100 text-sm">
                <slot/>
            </main>
            <Footer/>
        </div>
    </div>
</template>


<style>
.custom-option{
    white-space: pre-wrap !important;
}
.custom-overlay-class {
    width: 100%;
    max-width: 300px;
}

.parent-wrapper-class{
    width: 1%;
    left: 0;
    right: auto;
}
</style>
