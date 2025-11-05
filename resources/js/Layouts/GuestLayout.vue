<script setup>
import {Link, usePage} from "@inertiajs/vue3";
import SwitchDarkMode from "@/Components/SwitchDarkMode.vue";
import SwitchLangNavbar from "@/Components/SwitchLangNavbar.vue";
import MetaTags from "@/Components/MetaTags.vue";

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

const currentLang = usePage().props.locale;
</script>

<template>
    <MetaTags :title="title" :description="description" />

    <div
        class="min-h-screen flex flex-col-2 sm:justify-center items-center pt-6 sm:pt-0 px-4 sm:px-6 bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-950 text-slate-700 dark:text-slate-200"
    >

        <div
            class="w-full sm:max-w-md lg:max-w-4xl my-4 bg-white dark:bg-slate-800/50 dark:backdrop-blur-xl shadow-xl dark:shadow-2xl dark:shadow-slate-900/50 overflow-hidden sm:rounded-2xl border border-slate-200 dark:border-slate-700/50"
        >
            <div class="grid grid-cols-1 lg:grid-cols-2">
                <div class="space-y-6 px-6 py-4 lg:py-16">
                    <div class="flex justify-between items-center">
                        <Link :href="route('dashboard')"
                              class="flex items-center gap-2 text-slate-800 dark:text-white hover:text-primary dark:hover:text-primary transition-colors duration-300">
                            <img src="/images/main_logo.png" alt="Logo" class="rounded-full w-8 h-auto"/>
                            <span class="brand-text text-xl font-semibold">
                                ACDF
                            </span>
                        </Link>
                        <div class="flex space-x-2 items-center">
                            <SwitchLangNavbar/>
                            <SwitchDarkMode/>
                        </div>
                    </div>
                    <slot/>
                </div>
                <div
                    class="hidden lg:flex lg:flex-col px-6 py-4 justify-center items-center space-y-2 bg-gradient-to-br from-sky-500 to-blue-600 dark:from-sky-600 dark:to-blue-700 text-white relative overflow-hidden"
                >
                    <div class="absolute inset-0 bg-gradient-to-t from-black/10 to-transparent"></div>
                    <div class="absolute -top-24 -right-24 w-48 h-48 bg-white/10 rounded-full blur-3xl"></div>
                    <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-white/10 rounded-full blur-3xl"></div>

                    <div class="relative z-10">
                        <img
                            v-if="currentLang === 'ru'"
                            src="/images/logo_ru.png"
                            alt="Логотип на русском"
                            class="drop-shadow-2xl"
                        />
                        <img
                            v-else-if="currentLang === 'uz'"
                            src="/images/logo_uz.png"
                            alt="Логотип на узбекском"
                            class="drop-shadow-2xl"
                        />
                        <img
                            v-else-if="currentLang === 'en'"
                            src="/images/logo_en.png"
                            alt="Logo"
                            class="drop-shadow-2xl"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
