<template>
    <Dropdown align="right" width="100">
        <template #trigger>
            <button
                v-tooltip="lang().tooltip.notifications"
                type="button"
                class="dark-mode-button hover:text-slate-400 hover:bg-slate-900 focus:bg-slate-900 focus:text-slate-400 inline-flex items-center justify-center p-2 rounded-md lg:hover:text-slate-500 dark:hover:text-slate-400 lg:hover:bg-slate-100 dark:hover:bg-slate-900 focus:outline-none lg:focus:bg-slate-100 dark:focus:bg-slate-900 lg:focus:text-slate-500 dark:focus:text-slate-400 transition duration-150 ease-in-out"
            >
                <BellIcon
                    class="h-6 w-6 text-topbar-item hover:text-topbar-item-hover dark:text-zinc-300 dark:hover:text-white sm:h-5 sm:w-5"/>
                <span class="absolute top-0 right-0 flex w-1.5 h-1.5">
                    <span
                        class="absolute inline-flex w-full h-full bg-sky-400 rounded-full opacity-75 animate-ping"></span>
                    <span class="relative inline-flex w-1.5 h-1.5 bg-sky-500 rounded-full"></span>
                </span>
            </button>
        </template>

        <template #content>
            <div
                @click.stop
                class="absolute right-0 z-50 sm:w-[16rem] md:w-[20rem] lg:w-[26rem] mt-2 rounded-md bg-white dark:bg-slate-700"
            >
                <div class="p-2 sm:p-4">
                    <h6 class="text-[14px] md:text-lg font-semibold mb-2 sm:mb-4 dark:text-white">{{ lang().label.notifications }}</h6>
                    <div class="max-h-[350px] overflow-y-auto">
                        <ul v-if="usePage().props.notifications.length > 0" class="divide-y divide-slate-200 dark:divide-zinc-600">
                            <li
                                v-for="notification in usePage().props.notifications"
                                :key="notification.id"
                                class="flex items-start p-2 sm:p-4 gap-2 sm:gap-3 hover:bg-slate-50 dark:hover:bg-zinc-700"
                            >
                                <div v-if="notification.type == 1">
                                    <Link @click.prevent="handleNotificationClick(notification.id)" :href="route('tasks.show', notification.task_id)">
                                        <div class="flex-grow">
                                            <h6 class="font-medium dark:text-white mb-2 sm:mb-4">{{ lang().notification.task_assigned }}</h6>
                                            <p class="text-[14px] sm:text-xs text-slate-400 dark:text-zinc-400">
                                                {{ notification.created_at }}
                                            </p>
                                        </div>
                                    </Link>
                                </div>
                                <div v-if="notification.type == 2">
                                    <Link @click.prevent="handleNotificationClick(notification.id)" :href="route('tasks.show', notification.task_id)">
                                        <div class="flex-grow">
                                            <h6 class="text-[14px] sm:font-medium dark:text-white mb-2 sm:mb-4">{{ lang().notification.task_completed }}</h6>
                                            <p class="text-[14px] sm:text-xs text-slate-400 dark:text-zinc-400">
                                                {{ notification.created_at }}
                                            </p>
                                        </div>
                                    </Link>
                                </div>
                            </li>
                        </ul>
                        <p v-else class="text-[14px] sm:text-base text-center text-slate-500 dark:text-zinc-400 p-2 sm:p-4">
                            {{ lang().label.no_notifications }}
                        </p>
                    </div>
                </div>
            </div>
        </template>
    </Dropdown>
</template>

<script setup>
import {BellIcon} from "@heroicons/vue/24/solid";
import Dropdown from "@/Components/Dropdown.vue";
import {Link, usePage} from "@inertiajs/vue3";
import axios from "axios";
const handleNotificationClick = (notificationId) => {
    axios.post(`/notifications/${notificationId}/mark-as-read`)
        .then((response) => {
        })
        .catch((error) => {
        });
};

</script>

