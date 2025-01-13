<template>
    <Dropdown align="right" width="100">
        <template #trigger>
            <button
                v-tooltip="lang && lang().tooltip.notifications ? lang().tooltip.notifications : ''"
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
                <div class="p-2 sm:p-3 md-p-3">
                    <h6 class="text-[14px] md:text-lg font-semibold mb-2 sm:mb-4 dark:text-white">{{ lang && lang().label.notifications ? lang().label.notifications : 'Notifications' }}</h6>

                    <div class="mb-4 flex justify-center space-x-4 border-b-2 border-slate-200 dark:border-slate-600 overflow-auto">
                        <button
                            :class="{'text-blue-500 border-b-2 border-blue-500': activeTab === 'tasks'}"
                            @click="activeTab = 'tasks'"
                            class="sm:px-4 px-2 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-blue-500 dark:hover:text-white"
                        >
                            {{ lang && lang().label.tasks ? lang().label.tasks : 'Tasks' }}
                        </button>
                        <button
                            :class="{'text-blue-500 border-b-2 border-blue-500': activeTab === 'applications'}"
                            @click="activeTab = 'applications'"
                            class="sm:px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-blue-500 dark:hover:text-white"
                        >
                            {{ lang && lang().label.applications ? lang().label.applications : 'Applications' }}
                        </button>
                        <button
                            :class="{'text-blue-500 border-b-2 border-blue-500': activeTab === 'contracts'}"
                            @click="activeTab = 'contracts'"
                            class="sm:px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-blue-500 dark:hover:text-white"
                        >
                            {{ lang && lang().label.contracts ? lang().label.contracts : 'Contracts' }}
                        </button>
                    </div>
                    <div class="max-h-[350px] overflow-y-auto">
                        <ul v-if="filteredNotifications.length > 0" class="divide-y divide-slate-200 dark:divide-zinc-600">
                            <li
                                v-for="notification in filteredNotifications"
                                :key="notification.id"
                                class="flex items-start p-2 sm:p-3 gap-2 sm:gap-3 hover:bg-slate-50 dark:hover:bg-zinc-700"
                            >

                                <div v-if="notification.model === 'task'">
                                    <Link @click.prevent="handleNotificationClick(notification)" :href="route('task.show', notification.model_id)">
                                        <div v-if="notification.action == 'create'">
                                            <div class="flex-grow">
                                                <h6 class="font-medium dark:text-white mb-2 sm:mb-4">
                                                    {{ lang && lang().notification.task_assigned ? lang().notification.task_assigned : 'Task assigned' }}
                                                </h6>
                                                <p class="text-[14px] sm:text-xs text-slate-400 dark:text-zinc-400">
                                                    {{ notification.created_at }}
                                                </p>
                                            </div>
                                        </div>

                                        <div v-else-if="notification.action == 'start'">
                                            <div class="flex-grow">
                                                <h6 class="font-medium dark:text-white mb-2 sm:mb-4">
                                                    {{ lang && lang().notification.task_started ? lang().notification.task_started : 'Task started' }}
                                                </h6>
                                                <p class="text-[14px] sm:text-xs text-slate-400 dark:text-zinc-400">
                                                    {{ notification.created_at }}
                                                </p>
                                            </div>
                                        </div>

                                        <div v-else-if="notification.action == 'complete'">
                                            <div class="flex-grow">
                                                <h6 class="font-medium dark:text-white mb-2 sm:mb-4">
                                                    {{ lang && lang().notification.task_completed ? lang().notification.task_completed : 'Task completed' }}
                                                </h6>
                                                <p class="text-[14px] sm:text-xs text-slate-400 dark:text-zinc-400">
                                                    {{ notification.created_at }}
                                                </p>
                                            </div>
                                        </div>
                                    </Link>
                                </div>

                                <div v-if="notification.model === 'application'">
                                    <Link @click.prevent="handleNotificationClick(notification)" :href="route('application.show', notification.model_id)">
                                        <div class="flex-grow">
                                            <h6 class="text-[14px] sm:font-medium dark:text-white mb-2 sm:mb-4">{{ lang && lang().notification.application_received ? lang().notification.application_received : 'Application received' }}</h6>
                                            <p class="text-[14px] sm:text-xs text-slate-400 dark:text-zinc-400">
                                                {{ notification.created_at }}
                                            </p>
                                        </div>
                                    </Link>
                                </div>

                                <div v-if="notification.model === 'contract'">
                                    <Link @click.prevent="handleNotificationClick(notification)" :href="route('contract.show', notification.model_id)">
                                        <div class="flex-grow">
                                            <h6 class="text-[14px] sm:font-medium dark:text-white mb-2 sm:mb-4">{{ lang && lang().notification.contract_created ? lang().notification.contract_created : 'Contract created' }}</h6>
                                            <p class="text-[14px] sm:text-xs text-slate-400 dark:text-zinc-400">
                                                {{ notification.created_at }}
                                            </p>
                                        </div>
                                    </Link>
                                </div>
                            </li>
                        </ul>
                        <p v-else class="text-[14px] sm:text-base text-center text-slate-500 dark:text-zinc-400 p-2 sm:p-3">
                            {{ lang && lang().label.no_notifications ? lang().label.no_notifications : 'No notifications' }}
                        </p>
                    </div>
                </div>
            </div>
        </template>
    </Dropdown>
</template>

<script setup>
import { BellIcon } from "@heroicons/vue/24/solid";
import Dropdown from "@/Components/Dropdown.vue";
import { Link, usePage } from "@inertiajs/vue3";
import axios from "axios";
import { ref, computed, watch } from "vue";

const notifications = ref(usePage().props.notifications);
const activeTab = ref('tasks');


watch(notifications, (newNotifications) => {

    console.log("Уведомления обновлены:", newNotifications);
}, { deep: true });

const filteredNotifications = computed(() => {
    if (activeTab.value === 'tasks') {
        return notifications.value.filter(notification => notification.model === 'task');
    } else if (activeTab.value === 'applications') {
        return notifications.value.filter(notification => notification.model === 'application');
    } else if (activeTab.value === 'contracts') {
        return notifications.value.filter(notification => notification.model === 'contract');
    }
    return notifications.value;
});

const handleNotificationClick = (notification) => {
    axios.post(`/notifications/${notification.id}/mark-as-read`)
        .then(() => {
            // Здесь мы убираем уведомление после того как пометили его как прочитанное
            notifications.value = notifications.value.filter(n => n.id !== notification.id);
        })
        .catch((error) => {
            console.error('Ошибка при помечании уведомления как прочитанное:', error);
        });
};
</script>
