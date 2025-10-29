<script setup>
import { provide, computed, ref, watch } from "vue";
import NavbarVue from "@/Components/Navbar.vue";
import SideBarVue from "@/Components/SideBar.vue";
import Toast from "@/Components/Toast.vue";
import Footer from "@/Components/Footer.vue";
import { usePage } from "@inertiajs/vue3";

// Реактивный доступ к auth user через computed
const authUser = computed(() => usePage().props.auth.user);
provide("globalUser", authUser);

const sidebarOpened = ref(false);
const is_expanded = ref(true);

if (typeof window !== 'undefined') {
    const saved = localStorage.getItem("is_expanded");
    if (saved !== null) {
        is_expanded.value = JSON.parse(saved);
    }
}

watch(is_expanded, (newValue) => {
    if (typeof window !== 'undefined') {
        localStorage.setItem("is_expanded", JSON.stringify(newValue));
    }
});

const toggleSidebar = () => {
    sidebarOpened.value = !sidebarOpened.value;
};

const toggleMenu = () => {
    is_expanded.value = !is_expanded.value;
};
</script>

<template>
    <div class="flex w-full overflow-hidden bg-slate-50 dark:bg-slate-950">
        <SideBarVue
            :open="sidebarOpened"
            :is_expanded="is_expanded"
            @close="sidebarOpened = false"
            @toggleMenu="toggleMenu"
        />

        <div
            class="w-full min-h-screen flex flex-col bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-950 dark:to-slate-900 transition-all duration-300"
            :class="is_expanded ? 'lg:pl-64 sm:pl-0' : 'pl-0'"
        >
            <Toast :flash="$page.props.flash"/>

            <NavbarVue
                :open="sidebarOpened"
                :is_expanded="is_expanded"
                @open="toggleSidebar"
                @toggleMenu="toggleMenu"
            />

            <main class="flex-1 mx-auto w-full px-4 sm:px-6 lg:px-8 py-6 lg:py-8 text-slate-900 dark:text-slate-50">
                <slot/>
            </main>

            <Footer/>
        </div>
    </div>
</template>

<style>
/* Убрать scoped */
.custom-option {
    white-space: pre-wrap !important;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
}

.parent-wrapper-class {
    max-width: 90vw !important;
    width: auto !important;
}

.p-select-overlay {
    max-width: 90vw !important;
}

.p-select-option {
    white-space: nowrap !important;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>
