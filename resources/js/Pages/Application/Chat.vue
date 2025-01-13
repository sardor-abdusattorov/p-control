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
                                    Chats
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
                                            :key="chat.id"
                                            @click="loadChat(chat)"
                                            class="py-2 px-2 flex items-center gap-2 justify-between rounded-md hover:bg-gray-200 dark:hover:bg-gray-700 cursor-pointer select-none"
                                        >
                                            <div class="flex items-center gap-2 min-w-0 flex-1">
                                                <span class="avatar avatar-circle avatar-md">
                                                    <img
                                                        class="avatar-img avatar-circle rounded-full max-w-8"
                                                        loading="lazy"
                                                        :src="getUserImage(chat.user_id === currentUserId ? chat.receiver_id : chat.user_id)"
                                                        alt="Avatar"
                                                    >
                                                </span>
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex justify-between">
                                                        <div class="font-semibold text-sm dark:text-gray-100 text-gray-800 truncate flex gap-2 items-center">
                                                            {{ getReceiverName(chat.user_id === currentUserId ? chat.receiver_id : chat.user_id) || `Чат #${chat.id}` }}
                                                        </div>
                                                    </div>
                                                    <div class="text-gray-600 truncate dark:text-gray-400">
                                                        {{ chat.messages.length ? chat.messages[chat.messages.length - 1].text : 'Нет сообщений' }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex gap-1 items-center">
                                                <small class="font-semibold text-gray-500 dark:text-gray-300">
                                                    {{ chat.messages.length ? chat.messages[chat.messages.length - 1].created_date : '' }}
                                                </small>
                                            </div>
                                        </div>

                                    </div>
                                </ScrollPanel>
                            </div>
                            <div
                                v-show="activeTab === 'all_users'"
                                class="tab-panel p-4 transition-opacity duration-300 opacity-100"
                                :class="{ 'opacity-0': activeTab !== 'all_users' }"
                            >
                                <ScrollPanel style="height: 420px;">
                                    <div class="flex flex-col gap-2 h-full">
                                        <div
                                            v-for="(user, index) in props.users.filter(user => user.id !== currentUserId)"
                                            :key="user.id"
                                            @click="openChatWithUser(user)"
                                            class="py-2 px-2 flex items-center gap-2 justify-between rounded-md hover:bg-gray-200 dark:hover:bg-gray-700 cursor-pointer select-none"
                                        >
                                            <div class="flex items-center gap-2 min-w-0 flex-1">
                                            <span class="avatar avatar-circle avatar-md">
                                                <img
                                                    class="avatar-img avatar-circle rounded-full max-w-8"
                                                    loading="lazy"
                                                    :src="user.profile_image || '/default-avatar.jpg'"
                                                    alt="Avatar"
                                                />
                                            </span>
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex justify-between">
                                                        <div class="font-semibold text-sm dark:text-gray-100 text-gray-800 truncate flex gap-2 items-center">
                                                            {{ user.name }}
                                                        </div>
                                                    </div>
                                                    <div class="text-gray-600 truncate dark:text-gray-400">
                                                        <span v-if="getLastMessage(user.id)">
                                                            {{ getLastMessage(user.id).text }}
                                                        </span>
                                                        <span v-else>Нет сообщений</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex gap-1 items-center">
                                                <small class="font-semibold text-gray-500 dark:text-gray-300">
                                                    <span v-if="getLastMessage(user.id)">
                                                        {{ getLastMessage(user.id).created_date }}
                                                    </span>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </ScrollPanel>
                            </div>


                        </div>

                        <div class="w-2/3 h-full relative bg-white dark:bg-slate-900 text-gray-800 rounded-md shadow-md border border-slate-200 dark:border-neutral-800 overflow-hidden">
                            <ScrollPanel style="width: 100%; height: 450px;">
                                <div id="messages" class="flex h-full flex-col space-y-4 p-4 scrollbar-thumb-rounded-lg scrollbar-thumb-gray-600 scrollbar-track-gray-200 scrollbar-w-2 scrollbar-thumb-transition-all scrollbar-thumb-opacity-70 scrollbar-thumb-hover:bg-blue-400">
                                    <div v-for="(message, index) in messages" :key="index"
                                        :class="message.user_id === currentUserId ? 'chat-message right' : 'chat-message left'"
                                    >
                                        <div class="message-content flex items-end mb-3" :class="message.user_id === currentUserId ? 'justify-end' : ''">
                                            <div class="flex flex-col space-y-2 text-sm max-w-xs mx-2" :class="message.user_id === currentUserId ? 'order-1 items-end' : 'order-2 items-start'">
                                                <div
                                                    class="px-4 py-2 rounded-lg inline-block"
                                                    :class="message.user_id === currentUserId ? 'rounded-br-none bg-blue-600 text-white' : 'rounded-bl-none bg-gray-300 text-gray-600'"
                                                >
                                                    {{ message.text }}
                                                </div>

                                                <!-- Вывод прикрепленных файлов -->
                                                <div v-if="message.media && message.media.length > 0" class="mt-2">
                                                    <ul>
                                                        <li
                                                            v-for="file in message.media"
                                                            :key="file.id"
                                                            class="text-sm text-blue-600 hover:underline"
                                                        >
                                                            <a :href="file.original_url" target="_blank" download>{{ file.file_name }}</a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <img
                                                :src="usePage().props.auth.user.profile_image"
                                                alt="User Avatar"
                                                class="w-6 h-6 rounded-full"
                                                :class="message.user_id === currentUserId ? 'order-2' : 'order-1'"
                                            />
                                        </div>
                                        <div :class="message.user_id === currentUserId ? 'text-end' : 'text-start'" class="message-time text-sm">
                                            <small class="text-xs text-gray-400 mt-1">{{ message.created_date }}</small>
                                        </div>
                                    </div>
                                </div>
                            </ScrollPanel>
                            <div class="p-4 bg-gray-100 dark:bg-slate-800 border-t border-gray-200 dark:border-slate-700 rounded-md rounded-tl-none rounded-tr-none">
                                <form class="flex items-stretch" @submit.prevent="send" enctype="multipart/form-data">
                                    <!-- Скрытое поле для передачи chat_id -->
                                    <input type="hidden" v-model="form.receiver_id" name="receiver_id" />
                                    <input type="hidden" v-model="form.chat_id" name="chat_id" />

                                    <div class="input-group relative flex-1">
                                        <div>
                                            <input
                                                required
                                                v-model="form.message"
                                                placeholder="Write your message!"
                                                :disabled="!activeChat"
                                                class="dark:bg-slate-900 dark:focus:border-slate-400 dark:text-slate-300 border-slate-300 dark:border-slate-700 rounded-l-md rounded-r-none shadow-sm placeholder:text-slate-400 placeholder:dark:text-slate-400/50 w-full border-1 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 transition duration-200 ease-in-out px-4 py-2"
                                            />
                                        </div>
                                        <div>
                                            <input type="file" ref="fileInput" class="hidden" multiple @change="handleFileChange" />
                                            <button
                                                type="button"
                                                @click="triggerFileInput"
                                                :disabled="!activeChat"
                                                class="flex items-center absolute right-0 px-4 top-0 h-full focus:outline-none focus:ring-0"
                                            >
                                                <i class="pi pi-paperclip dark:text-white"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="flex gap-2">
                                        <button
                                            :disabled="!activeChat"
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
import {nextTick, onMounted, onUnmounted, ref} from 'vue';
    import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
    import Breadcrumb from '@/Components/Breadcrumb.vue';
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
    import ScrollPanel from 'primevue/scrollpanel';

    const activeTab = ref('dialogs');
    const props = defineProps({
    application: Object,
    title: String,
    breadcrumbs: Object,
    chats: Array,
    users: Array,
});

    const form = useForm({
    message: '',
    files: [],
    receiver_id: '',
    chat_id: '',
});

    const activeChat = ref(null); // Активный чат
    const messages = ref([]); // Сообщения активного чата
    const selectedFiles = ref([]); // Выбранные файлы

    const currentUserId = usePage().props.auth.user.id;

    // Очистка формы
    const clearForm = () => {
    form.message = '';
    form.files = [];
    selectedFiles.value = [];
};

    // Обновление сообщений чата
    const updateChatMessages = (chatId, newMessages) => {
    const chatIndex = props.chats.findIndex((chat) => chat.id === chatId);
    if (chatIndex !== -1) {
    props.chats[chatIndex].messages = newMessages;
}
};

    // Загрузка сообщений активного чата
    const fetchMessages = async () => {
    if (!activeChat.value?.id) return;

    try {
    const response = await axios.get(route('application.get-messages', { chat_id: activeChat.value.id }));
    if (response.data?.messages) {
    messages.value = response.data.messages;
    updateChatMessages(activeChat.value.id, response.data.messages);
}
} catch (error) {
    console.error('Ошибка при загрузке сообщений:', error);
}
};

    // Загрузка всех чатов
    const fetchAllChats = async () => {
    try {
    const response = await axios.get(route('application.get-all-chats', { application: props.application.id }));
    if (response.data?.chats) {
    props.chats.splice(0, props.chats.length, ...response.data.chats);
}
} catch (error) {
    console.error('Ошибка при загрузке чатов:', error);
}
};

    // Загрузка активного чата
    const loadChat = (chat) => {
    activeChat.value = chat;
    messages.value = chat.messages || [];
    form.receiver_id = chat.receiver_id;
    form.chat_id = chat.id;
    clearForm();
};

const send = async () => {
    try {
        await form.post(route('application.send-message', props.application?.id), {
            preserveScroll: true,
            onSuccess: (response) => {
                clearForm();
                if (response?.data?.message) {
                    // Добавляем новое сообщение в список сообщений
                    messages.value.push(response.data.message);

                    // Обновляем список чатов
                    fetchAllChats();

                    nextTick(() => {
                        const messagesContainer = document.getElementById('messages');
                        if (messagesContainer) {
                            messagesContainer.scrollTop = messagesContainer.scrollHeight;
                        }
                    });
                }
                fetchMessages();
            },
        });
    } catch (error) {
        console.error('Ошибка при отправке сообщения:', error);
    }
};



const triggerFileInput = () => {
        const fileInput = document.querySelector('input[type="file"]');
        fileInput.click();
    };

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


    // Получение имени пользователя
    const getReceiverName = (receiverId) => {
    const user = props.users.find((u) => u.id === receiverId);
    return user?.name || null;
};

    // Получение изображения пользователя
    const getUserImage = (userId) => {
    const user = props.users.find((u) => u.id === userId);
    return user?.profile_image || '/default-avatar.jpg';
};

    // Получение последнего сообщения
    const getLastMessage = (userId) => {
    const chat = props.chats.find(
    (c) =>
    (c.user_id === currentUserId && c.receiver_id === userId) ||
    (c.receiver_id === currentUserId && c.user_id === userId)
    );
    return chat?.messages?.[chat.messages.length - 1] || null;
};

const openChatWithUser = async (user) => {
    const existingChat = props.chats.find(
        (c) =>
            (c.user_id === currentUserId && c.receiver_id === user.id) ||
            (c.receiver_id === currentUserId && c.user_id === user.id)
    );

    if (existingChat) {
        console.log('Существующий чат найден:', existingChat);
        loadChat(existingChat);
    } else {
        try {
            console.log('Создаем новый чат с пользователем:', user);
            const response = await axios.post(route('application.create-chat'), {
                user_id: currentUserId,
                receiver_id: user.id,
            });

            console.log('Ответ сервера после создания чата:', response.data);

            if (response.data?.chat) {
                const newChat = response.data.chat;
                activeChat.value = newChat;
                messages.value = [];
                form.receiver_id = user.id;
                form.chat_id = newChat.id;

                console.log('Новый чат добавлен:', newChat);

                // Обновляем список чатов
                fetchAllChats();
            }
        } catch (error) {
            console.error('Ошибка при создании нового чата:', error);
        }
    }
};

// Интервалы для обновления данных
    let chatUpdateInterval, messageUpdateInterval;

    onMounted(() => {
    chatUpdateInterval = setInterval(fetchAllChats, 10000);
    messageUpdateInterval = setInterval(fetchMessages, 10000);
});

    onUnmounted(() => {
    clearInterval(chatUpdateInterval);
    clearInterval(messageUpdateInterval);
});
</script>

