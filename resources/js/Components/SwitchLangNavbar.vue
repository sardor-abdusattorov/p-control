<script setup>
import { usePage } from "@inertiajs/vue3";
import { ChevronDownIcon, GlobeAltIcon } from "@heroicons/vue/24/solid";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";

const lang = usePage().props.locale;
</script>

<template>
    <Dropdown align="right" width="48">
        <template #trigger>
                <span class="inline-flex rounded-md">
                    <button
                        type="button"
                        class="hover:text-slate-400 hover:bg-slate-900 focus:bg-slate-900 focus:text-slate-400 inline-flex items-center justify-center p-2 rounded-md lg:hover:text-slate-500 dark:hover:text-slate-400 lg:hover:bg-slate-100 dark:hover:bg-slate-900 focus:outline-none lg:focus:bg-slate-100 dark:focus:bg-slate-900 lg:focus:text-slate-500 dark:focus:text-slate-400 transition duration-150 ease-in-out sm:hidden"
                    >
                        <GlobeAltIcon class="h-5 w-5" />
                    </button>
                    <button
                        type="button"
                        class="hover:text-slate-400 hover:bg-slate-900 focus:bg-slate-900 focus:text-slate-400 items-center justify-center p-2 rounded-md lg:hover:text-slate-500 dark:hover:text-slate-400 lg:hover:bg-slate-100 dark:hover:bg-slate-900 focus:outline-none lg:focus:bg-slate-100 dark:focus:bg-slate-900 lg:focus:text-slate-500 dark:focus:text-slate-400 transition duration-150 ease-in-out truncate w-fit hidden sm:inline-flex"
                    >
                         <GlobeAltIcon class="h-5 w-5 mr-1"/>
                        <span class="flex justify-between items-center">
                            {{ lang === 'ru' ? 'Русский' : lang === 'uz' ? 'O\'zbekcha' : 'English' }}
                        </span>
                        <ChevronDownIcon class="ml-2 h-5 w-5 fill-current" />
                    </button>
                </span>
        </template>

        <template #content>
            <div>
                <DropdownLink :href="route('setlang', 'en')" :class="{ 'font-semibold': lang === 'en' }">
                    English
                </DropdownLink>
                <DropdownLink :href="route('setlang', 'ru')" :class="{ 'font-semibold': lang === 'ru' }">
                    Русский
                </DropdownLink>
                <DropdownLink :href="route('setlang', 'uz')" :class="{ 'font-semibold': lang === 'uz' }">
                    O'zbekcha
                </DropdownLink>
            </div>
        </template>
    </Dropdown>
</template>
