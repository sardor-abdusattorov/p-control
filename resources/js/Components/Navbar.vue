<script setup>
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import {Bars3CenterLeftIcon, ChevronDownIcon, UserIcon, Bars4Icon} from "@heroicons/vue/24/solid";
import SwitchDarkModeNavbar from "@/Components/SwitchDarkModeNavbar.vue";
import SwitchLangNavbar from "@/Components/SwitchLangNavbar.vue";

const emit = defineEmits(["open", "toggleMenu"]);
</script>

<template>
    <nav
        class="sticky top-0 z-40 bg-white border-b border-slate-200
               dark:bg-gradient-to-r dark:from-slate-900 dark:to-slate-900 dark:border-slate-700/50
               shadow-sm transition-all duration-300"
    >
        <div class="mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Left Side -->
                <div class="flex items-center">
                    <!-- Desktop Menu Toggle -->
                    <div class="mr-4 shrink-0 items-center hidden lg:flex">
                        <button
                            @click="emit('toggleMenu')"
                            class="group inline-flex items-center justify-center p-2 rounded-lg
                                   text-slate-600 hover:text-slate-900 hover:bg-slate-100
                                   dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800/50
                                   transition-all duration-200 ease-in-out"
                        >
                            <Bars4Icon class="h-5 w-5 transition-transform duration-200" />
                        </button>
                    </div>

                    <!-- Mobile Menu Toggle -->
                    <div class="mr-4 shrink-0 flex items-center lg:hidden">
                        <button
                            @click="emit('open')"
                            class="group inline-flex items-center justify-center p-2 rounded-lg
                                   text-slate-300 hover:text-white hover:bg-slate-800/50
                                   dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800/50
                                   transition-all duration-200 ease-in-out"
                        >
                            <Bars3CenterLeftIcon class="h-5 w-5 transition-transform duration-200" />
                        </button>
                    </div>
                </div>

                <!-- Right Side -->
                <div class="flex items-center space-x-2">
                    <!-- Language Switcher -->
                    <SwitchLangNavbar />

                    <!-- Dark Mode Toggle -->
                    <SwitchDarkModeNavbar />

                    <!-- User Dropdown -->
                    <div class="relative">
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <span class="inline-flex rounded-lg">
                                    <!-- Mobile User Button (Icon Only) -->
                                    <button
                                        type="button"
                                        class="inline-flex items-center justify-center p-2 rounded-lg
                                               text-slate-600 hover:text-slate-900 hover:bg-slate-100
                                               dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800/50
                                               transition-all duration-200 ease-in-out sm:hidden"
                                    >
                                        <UserIcon class="h-5 w-5" />
                                    </button>

                                    <!-- Desktop User Button (With Name) -->
                                    <button
                                        type="button"
                                        class="hidden sm:inline-flex items-center gap-2 px-3 py-2 rounded-lg
                                               text-slate-700 hover:text-slate-900 hover:bg-slate-100
                                               dark:text-slate-300 dark:hover:text-white dark:hover:bg-slate-800/50
                                               transition-all duration-200 ease-in-out font-medium"
                                    >
                                        <span class="truncate max-w-[150px]">
                                            {{ $page.props.auth.user.name.split(" ")[0] }}
                                        </span>
                                        <ChevronDownIcon class="h-4 w-4" />
                                    </button>
                                </span>
                            </template>

                            <template #content>
                                <!-- User Info Header -->
                                <div
                                    class="py-3 px-4 border-b border-slate-200 dark:border-slate-700
                                           bg-slate-50 dark:bg-slate-800/50"
                                >
                                    <span
                                        class="flex items-center justify-start text-sm font-medium
                                               text-slate-700 dark:text-slate-300 truncate"
                                    >
                                        {{ $page.props.auth.user.name }}
                                    </span>
                                </div>

                                <!-- Dropdown Links -->
                                <DropdownLink :href="route('profile.edit')">
                                    {{ lang().label.profile }}
                                </DropdownLink>
                                <DropdownLink
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                >
                                    {{ lang().label.logout }}
                                </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</template>

<style scoped>
/* Ensure smooth transitions */
nav {
    will-change: background-color, border-color;
}
</style>
