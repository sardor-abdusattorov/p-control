<template>
    <div
        class="min-h-screen flex flex-col-2 sm:justify-center items-center pt-6 sm:pt-0 px-4 sm:px-6 bg-slate-100 dark:bg-slate-900 text-slate-500 dark:text-slate-300"
    >

    <div
            class="w-full sm:max-w-md lg:max-w-4xl my-4 bg-white dark:bg-slate-800 shadow-md overflow-hidden sm:rounded-lg"
        >
            <div class="grid grid-cols-1 lg:grid-cols-2">
                <div class="space-y-6 px-6 py-4 lg:py-16">
                    <div class="flex justify-between items-center">
                        <Link :href="route('dashboard')" class="flex items-center gap-2 hover:text-primary transition-colors duration-300">
                            <img src="/images/main_logo.png" alt="Logo" class="rounded-full w-8 h-auto" />
                            <span class="brand-text text-xl font-semibold">
                                ACDF
                            </span>
                        </Link>
                        <div class="flex space-x-2 items-center">
                            <SwitchLangNavbar />
                            <SwitchDarkMode />
                        </div>
                    </div>
                    <slot />
                </div>
                <div
                    class="hidden lg:flex lg:flex-col px-6 py-4 justify-center items-center space-y-2 bg-primary text-white"
                >
                    <img
                        v-if="currentLang === 'ru'"
                        src="/images/logo_ru.png"
                        alt="Логотип на русском"
                    />
                    <img
                        v-else-if="currentLang === 'uz'"
                        src="/images/logo_uz.png"
                        alt="Логотип на узбекском"
                    />
                    <img
                        v-else-if="currentLang === 'en'"
                        src="/images/logo_en.png"
                        alt="Logo"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link, usePage } from "@inertiajs/vue3";
import SwitchDarkMode from "@/Components/SwitchDarkMode.vue";
import SwitchLangNavbar from "@/Components/SwitchLangNavbar.vue";

const currentLang = usePage().props.locale;
</script>

