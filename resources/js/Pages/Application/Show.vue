<template>
    <Head :title="props.title" />
    <AuthenticatedLayout>
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs" />
        <section class="space-y-4 bg-white dark:bg-slate-800 shadow sm:rounded-lg">
            <form aria-disabled="true">
                <Tabs value="chat">
                    <TabList>
                        <Tab value="details">
                            Data
                        </Tab>
                        <Tab value="chat">
                            Chat
                        </Tab>
                    </TabList>
                    <TabPanels>
                        <TabPanel value="details">
                            <div class="my-6 space-y-4">
                                <div class="form-group mb-3">
                                    <InputLabel for="title" :value="lang().label.title" />
                                    <InputText
                                        id="title"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.title"
                                        required
                                        :placeholder="lang().label.title"
                                        :error="form.errors.title"
                                        readonly
                                    />
                                    <InputError class="mt-2" :message="form.errors.title" />
                                </div>
                            </div>
                        </Tabpanel>
                        <TabPanel value="chat">
                            <div class="flex-1 justify-between flex flex-col">
                                <div class="content flex gap-4">
                                    <div class="w-1/3 relative bg-white text-gray-900 rounded-md shadow-lg">
                                        <Tabs value="dialogs">
                                            <TabList class="space-x-4 border-b-2 border-gray-200">
                                                <Tab value="dialogs">All Chats</Tab>
                                                <Tab value="all_users">All Users</Tab>
                                            </TabList>
                                            <TabPanels>
                                                <TabPanel value="dialogs">
                                                    <div class="flex flex-col gap-2 h-full max-h-[350px] overflow-y-auto">
                                                        <div class="py-3 px-2 flex items-center gap-2 justify-between rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 cursor-pointer select-none">
                                                            <div class="flex items-center gap-2 min-w-0 flex-1">
                                                                <span class="avatar avatar-circle avatar-md">
                                                                    <img class="avatar-img avatar-circle" loading="lazy" src="/img/avatars/thumb-4.jpg">
                                                                </span>
                                                                <div class="min-w-0 flex-1">
                                                                    <div class="flex justify-between">
                                                                        <div class="font-semibold text-sm text-gray-800 truncate flex gap-2 items-center">
                                                                            <span>Shannon Baker</span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="text-gray-600 truncate">Will do. Appreciate it!</div>
                                                                </div>
                                                            </div>
                                                            <div class="flex gap-1 items-center">
                                                                <small class="font-semibold text-gray-500">09:39 AM</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </TabPanel>
                                                <TabPanel value="all_users">
                                                    <div class="flex flex-col gap-2">
                                                        <div class="py-3 px-2 flex items-center gap-2 justify-between rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 cursor-pointer select-none">
                                                            <div class="flex items-center gap-2 min-w-0 flex-1">
                                                                    <span class="avatar avatar-circle avatar-md">
                                                                        <img class="avatar-img avatar-circle" loading="lazy" src="/img/avatars/thumb-4.jpg">
                                                                    </span>
                                                                <div class="min-w-0 flex-1">
                                                                    <div class="flex justify-between">
                                                                        <div class="font-semibold text-sm text-gray-800 truncate flex gap-2 items-center">
                                                                            <span>Shannon Baker</span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="text-gray-600 truncate">Will do. Appreciate it!</div>
                                                                </div>
                                                            </div>
                                                            <div class="flex gap-1 items-center">
                                                                <small class="font-semibold text-gray-500">09:39 AM</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </TabPanel>
                                            </TabPanels>
                                        </Tabs>
                                    </div>
                                    <div class="w-2/3 h-full relative bg-gray-100 text-gray-800  rounded-md shadow-md">
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

                                        <div class="absolute bottom-0 left-0 right-0 p-4 bg-gray-100 border-t-2 border-gray-200 rounded-md rounded-tl-none rounded-tr-none">
                                            <div class="flex items-stretch">
                                                <div class="input-group relative flex-1">
                                                    <input
                                                        placeholder="Write your message!"
                                                        class="rounded-l-md rounded-r-none shadow-sm placeholder:text-slate-400 placeholder:dark:text-slate-400/50 w-full border-1 border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 transition duration-200 ease-in-out px-4 py-2"
                                                    />
                                                    <input type="file" ref="fileInput" class="hidden"/>
                                                    <button
                                                        class="absolute right-0 p-2 top-0 h-full focus:outline-none focus:ring-0"
                                                    >
                                                        <i class="pi pi-paperclip"></i>
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
import { watchEffect } from 'vue';
import { defineProps, defineEmits } from 'vue';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import TextInput from "@/Components/TextInput.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";

const props = defineProps({
    show: Boolean,
    application: Object,
    title: String,
    breadcrumbs: Object,
    recipients: Array,
    projects: Array,
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

watchEffect(() => {
    form.errors = {};
    form.recipients = props.recipients.map(recipient => recipient.recipient_id)
    form.title = props.application.title
    form.project_id = props.application.project_id
    form.files = props.files;
});

const chatMessages = [
    { user: 'John', message: 'Hello, how are you?', timestamp: '2025-01-08 10:00' },
    { user: 'Alice', message: 'I am fine, thanks!', timestamp: '2025-01-08 10:02' },
];
</script>
