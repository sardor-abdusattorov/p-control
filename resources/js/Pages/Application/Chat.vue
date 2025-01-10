<template>
    <Head :title="props.title" />
    <AuthenticatedLayout>
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs" />
        <section class="space-y-4 bg-white dark:bg-slate-800 shadow rounded-t-lg">
            <div class="border-b border-gray-300 dark:border-neutral-600 card-header flex justify-between items-center p-4 bg-gray-100 dark:bg-slate-900 rounded-t-md">
                <div class="flex justify-start gap-4">
                    <Link
                        :href="route('application.show', { application: application.id })"
                        class="px-6 py-3 rounded-md bg-blue-600 text-white hover:bg-blue-700 shadow-lg transition-all duration-300"
                    >
                        {{ lang().label.information }}
                    </Link>
                    <Link
                        :href="route('application.chat', { id: application.id })"
                        class="px-6 py-3 rounded-md bg-green-600 text-white hover:bg-green-700 shadow-lg transition-all duration-300"
                    >
                        {{ lang().label.chat }}
                    </Link>
                </div>
            </div>
            <div class="p-4">
                <div class="flex-1 justify-between flex flex-col">
                    <div class="content flex gap-4">
                        <div class="w-1/3 relative bg-gray-100 dark:bg-slate-900 text-gray-900 rounded-md shadow-md border border-slate-200 dark:border-neutral-800 overflow-hidden">
                            <div class="tabs flex gap-4 border-b border-slate-300 dark:border-neutral-700">
                                <button
                                    @click="activeTab = 'dialogs'"
                                    :class="[
                                            'px-4 py-4 border-b font-semibold transition-all -mb-px',
                                            activeTab === 'dialogs'
                                                ? 'border-black text-black dark:border-white dark:text-white'
                                                : 'border-gray-300 text-gray-500 dark:border-neutral-700 dark:text-neutral-400'
                                        ]">
                                    All Chats
                                </button>
                                <button
                                    @click="activeTab = 'all_users'"
                                    :class="[
                                            'px-4 py-4 border-b font-semibold transition-all -mb-px',
                                            activeTab === 'all_users'
                                                ? 'border-black text-black dark:border-white dark:text-white'
                                                : 'border-gray-300 text-gray-500 dark:border-neutral-700 dark:text-neutral-400'
                                        ]">
                                    All Users
                                </button>
                            </div>
                            <div v-show="activeTab === 'dialogs'" class="tab-panel p-4 transition-opacity duration-300 opacity-100" :class="{ 'opacity-0': activeTab !== 'dialogs' }">
                                <ScrollPanel style="height: 420px;">
                                    <div class="flex flex-col gap-2 h-full">
                                        <div
                                            v-for="(chat, index) in props.chats"
                                            :key="index"
                                            @click="selectChat(chat)"
                                            class="py-2 px-2 flex items-center gap-2 justify-between rounded-md hover:bg-gray-200 dark:hover:bg-gray-700 cursor-pointer select-none"
                                        >
                                            <div class="flex items-center gap-2 min-w-0 flex-1">
                                                <!-- Аватарка собеседника -->
                                                <span class="avatar avatar-circle avatar-md">
                                                      <img
                                                          :src="getUser(chat.receiver_id)?.profile_image || '/default-avatar.png'"
                                                          alt="User Avatar"
                                                          class="avatar-img avatar-circle rounded-full max-w-8"
                                                      />
                                                    </span>

                                                <!-- Информация о чате -->
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex justify-between">
                                                        <div class="font-semibold text-sm dark:text-gray-100 text-gray-800 truncate flex gap-2 items-center">
                                                            <!-- Имя собеседника -->
                                                            <span>{{ getUser(chat.receiver_id)?.name || 'Unknown User' }}</span>
                                                        </div>
                                                    </div>
                                                    <!-- Последнее сообщение -->
                                                    <div class="text-gray-600 truncate dark:text-gray-400">
                                                        {{ chat.messages[0]?.text || 'No messages yet' }}
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Дата последнего сообщения -->
                                            <div class="flex gap-1 items-center">
                                                <small class="font-semibold text-gray-500 dark:text-gray-300">
                                                    {{ formatDate(chat.messages[0]?.created_date) }}
                                                </small>
                                            </div>
                                        </div>

                                    </div>
                                </ScrollPanel>
                            </div>

                            <div v-show="activeTab === 'all_users'" class="tab-panel p-4 transition-opacity duration-300 opacity-100" :class="{ 'opacity-0': activeTab !== 'all_users' }">
                                <ScrollPanel style="height: 420px;">
                                    <div class="flex flex-col gap-2 h-full">
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
                                </ScrollPanel>
                            </div>

                        </div>

                        <div class="w-2/3 h-full relative bg-gray-100 dark:bg-slate-800 text-gray-800 rounded-md shadow-md border border-slate-200 dark:border-neutral-800 overflow-hidden">
                            <ScrollPanel style="width: 100%; height: 450px;">
                                <div id="messages" class="flex h-full flex-col space-y-4 p-4 scrollbar-thumb-rounded-lg scrollbar-thumb-gray-600 scrollbar-track-gray-200 scrollbar-w-2 scrollbar-thumb-transition-all scrollbar-thumb-opacity-70 scrollbar-thumb-hover:bg-blue-400 bg-white dark:bg-slate-900 shadow-lg dark:shadow-lg rounded-md">
                                    <div
                                        v-for="(message, index) in chatMessages"
                                        :key="index"
                                        :class="message.user_id === props.application.id ? 'chat-message-right' : 'chat-message-left'"
                                    >
                                        <div class="message-content flex items-end mb-3">
                                            <div
                                                class="flex text-sm flex-col space-y-2 max-w-xs mx-2"
                                                :class="message.user_id === props.application.id ? 'order-1 items-end' : 'order-2 items-start'"
                                            >
                                                <div
                                                    class="px-4 py-2 rounded-lg inline-block"
                                                    :class="message.user_id === props.application.id
                                                    ? 'bg-blue-600 text-white rounded-br-none'
                                                    : 'bg-gray-300 text-gray-600 rounded-bl-none'"
                                                    >
                                                    {{ message.text }}
                                                </div>
                                            </div>
                                            <img
                                                :src="getUser(message.user_id)?.profile_image || '/default-avatar.png'"
                                                alt="User Profile"
                                                class="w-6 h-6 rounded-full"
                                                :class="message.user_id === props.application.id ? 'order-2' : 'order-1'"
                                            >
                                        </div>
                                        <div class="message-time text-sm" :class="message.user_id === props.application.id ? 'text-end' : 'text-start'">
                                            <small class="text-xs text-gray-400 mt-1">{{ formatDate(message.created_date) }}</small>
                                        </div>
                                    </div>
                                </div>
                            </ScrollPanel>
                            <div class="p-4 bg-gray-100 dark:bg-slate-800 border-t border-gray-200 dark:border-slate-700 rounded-md rounded-tl-none rounded-tr-none">
                                <form class="flex items-stretch" @submit.prevent="send" enctype="multipart/form-data">
                                    <div class="input-group relative flex-1">
                                        <div>
                                            <input
                                                v-model="form.message"
                                                placeholder="Write your message!"
                                                class="dark:bg-slate-900 dark:focus:border-slate-400 dark:text-slate-300 border-slate-300 dark:border-slate-700 rounded-l-md rounded-r-none shadow-sm placeholder:text-slate-400 placeholder:dark:text-slate-400/50 w-full border-1 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 transition duration-200 ease-in-out px-4 py-2"
                                            />
                                            <InputError class="mt-2" :message="form.errors.message" />
                                        </div>
                                        <div>
                                            <input type="file" ref="fileInput" class="hidden" multiple @change="handleFileChange" />
                                            <button type="button" @click="triggerFileInput" class="flex items-center absolute right-0 px-4 top-0 h-full focus:outline-none focus:ring-0">
                                                <i class="pi pi-paperclip dark:text-white"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="flex gap-2">
                                        <button
                                            class="bg-blue-500 text-white hover:bg-blue-600 focus:bg-blue-700 active:bg-blue-800 px-6 py-2 rounded-l-none rounded-md border-1 border-transparent hover:border-blue-400 focus:border-blue-500 shadow-md hover:shadow-lg focus:shadow-lg active:shadow-none transition duration-200 ease-in-out font-semibold uppercase focus:outline-none focus:ring-0"
                                        >
                                            Send
                                        </button>
                                    </div>
                                </form>
                                <div v-if="selectedFiles.length > 0" class="mt-4 p-2 bg-gray-200 dark:bg-slate-700 rounded-md">
                                    <h4 class="text-sm font-semibold dark:text-white">Selected Files:</h4>
                                    <ul>
                                        <li
                                            v-for="(file, index) in selectedFiles"
                                            :key="file.name"
                                            class="flex justify-between items-center text-sm text-gray-600 dark:text-slate-300"
                                        >
                                            {{ file.name }}
                                            <button
                                                @click="removeFile(index)"
                                                class="text-red-500 hover:text-red-700 focus:outline-none"
                                                title="Remove file"
                                            >
                                                <i class="pi pi-times"></i>
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>
    </AuthenticatedLayout>
</template>

<script setup>
import {ref, watchEffect} from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ScrollPanel from 'primevue/scrollpanel';
import InputError from "@/Components/InputError.vue";

const props = defineProps({
    application: Object,
    chats: Object,
    title: String,
    breadcrumbs: Object,
    users: Object,
    files: Array,
});

const selectedChat = ref(null); // Хранит выбранный чат
const chatMessages = ref([]); // Сообщения выбранного чата

const selectChat = (chat) => {
    selectedChat.value = chat; // Устанавливаем выбранный чат
    chatMessages.value = chat.messages || []; // Обновляем сообщения
};

const form = useForm({
    message: '',
    files: [],
});
const activeTab = ref('dialogs');
const selectedFiles = ref([]);
const triggerFileInput = () => {
    const fileInput = document.querySelector('input[type="file"]');
    fileInput.click();
};

watchEffect(() => {
    form.errors = {};

});

const handleFileChange = (event) => {
    const files = event.target.files;
    if (files.length > 0) {
        selectedFiles.value = Array.from(files);
        form.files = selectedFiles.value;
    }
};

const removeFile = (index) => {
    selectedFiles.value.splice(index, 1);
    form.files = selectedFiles.value;
};

const send = () => {
    form.post(route('application.send-message', props.application?.id), {
        files: form.files,
    });
};

const formatDate = (date) => {
    if (!date) return '';
    const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return new Date(date).toLocaleString('ru-RU', options);
};


const getUser = (userId) => {
    return props.users.find(user => user.id === userId) || null;
};

</script>

