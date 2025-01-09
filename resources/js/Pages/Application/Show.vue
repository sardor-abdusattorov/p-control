<template>
    <Head :title="props.title" />
    <AuthenticatedLayout>
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs" />
        <section class="space-y-4 bg-white dark:bg-slate-800 shadow sm:rounded-lg overflow-hidden">
            <form aria-disabled="true">
                <Tabs value="details">
                    <TabList>
                        <Tab value="details">
                            {{lang().label.information}}
                        </Tab>
                        <Tab value="chat">
                            {{lang().label.chat}}
                        </Tab>
                    </TabList>
                    <TabPanels>
                        <TabPanel value="details">
                            <table class="min-w-full border border-gray-300 dark:border-neutral-700 divide-y divide-gray-200 dark:divide-neutral-700">
                                <tbody>
                                <tr
                                    class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                                >
                                    <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">ID</td>
                                    <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ application.id }}</td>
                                </tr>
                                <tr
                                    class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                                >
                                    <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.title }}</td>
                                    <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ application.title }}</td>
                                </tr>
                                <tr
                                    class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                                >
                                    <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.project_id }}</td>
                                    <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">
                                        <span v-if="props.project">
                                            {{ props.project.project_number ?? lang().label.undefined }}
                                        </span>
                                            <span v-else>
                                            {{ lang().label.undefined }}
                                        </span>
                                    </td>
                                </tr>
                                <tr
                                    class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                                >
                                    <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.user_id }}</td>
                                    <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ application.user.name }}</td>
                                </tr>
                                <tr class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800">
                                    <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.files }}</td>
                                    <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">
                                        <div v-if="props.files.length > 0">
                                            <ul class="list-none p-0 flex flex-col gap-1.5">
                                                <li v-for="(file, index) in props.files" :key="index" class="flex items-center space-x-2">
                                                    <a v-tooltip="lang().tooltip.download" :href="file.original_url" target="_blank" class="text-blue-600 hover:text-blue-800">
                                                        {{ file.name }}
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                        <div v-else>
                                            {{ lang().label.no_files }}
                                        </div>
                                    </td>
                                </tr>

                                <tr
                                    class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                                >
                                    <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.status }}</td>
                                    <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">
                                        <Badge :value="getStatusLabel(application.status_id)" :severity="getStatusSeverity(application.status_id)" />
                                    </td>
                                </tr>

                                <tr
                                    class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                                >
                                    <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.created }}</td>
                                    <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ application.created_at }}</td>
                                </tr>
                                <tr
                                    class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                                >
                                    <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.updated }}</td>
                                    <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ application.updated_at }}</td>
                                </tr>
                                </tbody>
                            </table>
                        </Tabpanel>
                        <TabPanel value="chat">
                            <div class="flex-1 justify-between flex flex-col">
                                <div class="content flex gap-4">
                                    <div class="w-1/3 relative bg-white dark:bg-neutral-900 text-gray-900 rounded-md shadow-md border border-slate-200 dark:border-neutral-800 overflow-hidden">
                                        <Tabs value="dialogs">
                                            <TabList>
                                                <Tab value="dialogs">All Chats</Tab>
                                                <Tab value="all_users">All Users</Tab>
                                            </TabList>
                                            <TabPanels>
                                                <TabPanel value="dialogs">
                                                    <div class="flex flex-col gap-2 h-full max-h-[350px] overflow-y-auto">
                                                        <div v-for="(user, index) in props.users" :key="index" class="py-2 px-2 flex items-center gap-2 justify-between rounded-md hover:bg-gray-200 dark:hover:bg-gray-700 cursor-pointer select-none">
                                                            <div class="flex items-center gap-2 min-w-0 flex-1">
                                                                      <span class="avatar avatar-circle avatar-md">
                                                                        <img class="avatar-img avatar-circle rounded-full max-w-8" loading="lazy" :src="user.profile_image" />
                                                                      </span>
                                                                <div class="min-w-0 flex-1">
                                                                    <div class="flex justify-between">
                                                                        <div class="font-semibold text-sm dark:text-gray-100 text-gray-800 truncate flex gap-2 items-center">
                                                                            <span>{{ user.name }}</span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="text-gray-600 truncate dark:text-gray-400">Will do. Appreciate it!</div>
                                                                </div>
                                                            </div>
                                                            <div class="flex gap-1 items-center">
                                                                <small class="font-semibold text-gray-500 dark:text-gray-300">09:39 AM</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </TabPanel>
                                                <TabPanel value="all_users">
                                                    <div class="flex flex-col gap-2 h-full max-h-[350px] overflow-y-auto">
                                                        <div v-for="(user, index) in props.users" :key="index" class="py-2 px-2 flex items-center gap-2 justify-between rounded-md hover:bg-gray-200 dark:hover:bg-gray-700 cursor-pointer select-none">
                                                            <div class="flex items-center gap-2 min-w-0 flex-1">
                                                                      <span class="avatar avatar-circle avatar-md">
                                                                        <img class="avatar-img avatar-circle rounded-full max-w-8" loading="lazy" :src="user.profile_image" />
                                                                      </span>
                                                                <div class="min-w-0 flex-1">
                                                                    <div class="flex justify-between">
                                                                        <div class="font-semibold text-sm dark:text-gray-100 text-gray-800 truncate flex gap-2 items-center">
                                                                            <span>{{ user.name }}</span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="text-gray-600 truncate dark:text-gray-400">Will do. Appreciate it!</div>
                                                                </div>
                                                            </div>
                                                            <div class="flex gap-1 items-center">
                                                                <small class="font-semibold text-gray-500 dark:text-gray-300">09:39 AM</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </TabPanel>
                                            </TabPanels>
                                        </Tabs>
                                    </div>
                                    <div class="w-2/3 h-full relative bg-gray-100 dark:bg-slate-800 text-gray-800  rounded-md shadow-md border- border-slate-200 dark:border-neutral-800 overflow-hidden">
                                        <div id="messages" class="min-h-[500px] overflow-y-auto flex flex-col space-y-4 p-3 scrollbar-thumb-rounded-lg scrollbar-thumb-gray-600 scrollbar-track-gray-200 scrollbar-w-2 scrollbar-thumb-transition-all scrollbar-thumb-opacity-70 scrollbar-thumb-hover:bg-blue-400">
                                        <div class="chat-message left">
                                                <div class="message-content flex items-end mb-3">
                                                    <div class="flex flex-col space-y-2 text-xs max-w-xs mx-2 order-2 items-start">
                                                        <div class="px-4 py-2 rounded-lg inline-block rounded-bl-none bg-gray-300 text-gray-600">
                                                            Can be verified on any platform using docker
                                                        </div>
                                                    </div>
                                                    <img src="https://images.unsplash.com/photo-1549078642-b2ba4bda0cdb?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=3&w=144&h=144" alt="My profile" class="w-6 h-6 rounded-full order-1">
                                                </div>
                                                <div class="message-time text-start">
                                                    <small class="text-xs text-gray-400 mt-1">09:40 AM</small>
                                                </div>
                                            </div>

                                            <!-- Правое сообщение -->
                                            <div class="chat-message right">
                                                <div class="message-content flex items-end mb-3 justify-end">
                                                    <div class="flex flex-col space-y-2 text-xs max-w-xs mx-2 order-1 items-end">
                                                        <div class="px-4 py-2 rounded-lg inline-block rounded-br-none bg-blue-600 text-white">
                                                            Can be verified on any platform using docker
                                                        </div>
                                                    </div>
                                                    <img src="https://images.unsplash.com/photo-1549078642-b2ba4bda0cdb?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=3&w=144&h=144" alt="My profile" class="w-6 h-6 rounded-full order-2">
                                                </div>
                                                <div class="message-time text-end">
                                                    <small class="text-xs text-gray-400 mt-1">09:40 AM</small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="absolute bottom-0 left-0 right-0 p-4 bg-gray-100 dark:bg-slate-800 border-t border-gray-200 dark:border-slate-700 rounded-md rounded-tl-none rounded-tr-none">
                                            <div class="flex items-stretch">
                                                <div class="input-group relative flex-1">
                                                    <input
                                                        placeholder="Write your message!"
                                                        class="dark:bg-slate-900 dark:focus:border-slate-400 dark:text-slate-300 border-slate-300 dark:border-slate-700 rounded-l-md rounded-r-none shadow-sm placeholder:text-slate-400 placeholder:dark:text-slate-400/50 w-full border-1 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 transition duration-200 ease-in-out px-4 py-2"
                                                    />
                                                    <input type="file" ref="fileInput" class="hidden"/>
                                                    <button
                                                        class="absolute right-0 p-2 top-0 h-full focus:outline-none focus:ring-0"
                                                    >
                                                        <i class="pi pi-paperclip dark:text-white"></i>
                                                    </button>
                                                </div>
                                                <div class="flex gap-2">
                                                    <button
                                                        class="bg-blue-500 text-white hover:bg-blue-600 focus:bg-blue-700 active:bg-blue-800 px-6 py-2 rounded-l-none rounded-md border-1 border-transparent hover:border-blue-400 focus:border-blue-500 shadow-md hover:shadow-lg focus:shadow-lg active:shadow-none transition duration-200 ease-in-out font-semibold uppercase focus:outline-none focus:ring-0"
                                                    >
                                                        Send
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </Tabpanel>
                    </TabPanels>
                </Tabs>
            </form>
        </section>
    </AuthenticatedLayout>
</template>

<script setup>
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';
import {Head, useForm} from '@inertiajs/vue3';
import { defineProps, defineEmits } from 'vue';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import Badge from "primevue/badge";

const props = defineProps({
    show: Boolean,
    application: Object,
    title: String,
    breadcrumbs: Object,
    statuses: Array,
    project: Object,
    files: Array,
    users: Array,
    messages: Array
});

const emit = defineEmits(["close"]);

const form = useForm({
    title: "",
    project_id: "",
    recipients: [],
    files: [],
});

const getStatusLabel = (statusId) => {
    const status = props.statuses.find(s => s.id === statusId);
    return status ? status.label : '';
};

const getStatusSeverity = (statusId) => {
    switch (statusId) {
        case 1:
            return 'info';
        case 2:
            return 'success';
        case 3:
            return 'danger';
        default:
            return 'info';
    }
};

</script>
