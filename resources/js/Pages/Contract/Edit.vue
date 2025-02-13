<template>
    <Head :title="props.title"/>
    <AuthenticatedLayout>
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs"/>

        <section class="space-y-4 bg-white dark:bg-slate-800 shadow sm:rounded-lg">
            <form class="p-6" @submit.prevent="update">
                <h2
                    class="text-lg font-medium text-slate-900 dark:text-slate-100"
                >
                    {{ lang().label.edit }} {{ props.title }}
                </h2>
                <div class="my-6 w-full">
                    <div class="form-group mb-5">
                        <InputLabel for="contract_number" :value="lang().label.contract_number"/>
                        <InputText
                            id="contract_number"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.contract_number"
                            :placeholder="lang().label.contract_number"
                            :error="form.errors.contract_number"
                        />
                        <InputError class="mt-2" :message="form.errors.contract_number"/>
                    </div>

                    <div class="form-group mb-5">
                        <InputLabel for="title" :value="lang().label.title"/>
                        <InputText
                            id="title"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.title"
                            :placeholder="lang().label.title"
                            :error="form.errors.title"
                        />
                        <InputError class="mt-2" :message="form.errors.title"/>
                    </div>

                    <div class="form-group mb-5">
                        <InputLabel for="project_id" :value="lang().label.project_id"/>
                        <Select
                            id="project_id"
                            v-model="form.project_id"
                            :options="formattedProjects"
                            optionLabel="display"
                            optionValue="id"
                            filter
                            checkmark
                            :highlightOnSelect="false"
                            :filterBy="['project_number', 'title']"
                            :filterPlaceholder="lang().placeholder.select_project"
                            class="w-full"
                            :placeholder="lang().label.project_name"
                            :pt="{
                                option: { class: 'custom-option' },
                                dropdown: { style: { maxWidth: '300px' } },
                                overlay: { class: 'parent-wrapper-class' }
                            }"
                        />
                        <InputError class="mt-2" :message="form.errors.project_id"/>
                    </div>

                    <div class="form-group mb-5">
                        <InputLabel for="application_id" :value="lang().label.application_id"/>
                        <Select
                            id="application_id"
                            v-model="form.application_id"
                            optionLabel="title"
                            optionValue="id"
                            :options="props.applications"
                            filter
                            checkmark
                            :highlightOnSelect="false"
                            class="w-full"
                            :placeholder="lang().placeholder.select_application"
                            :pt="{
                                option: { class: 'custom-option' },
                                dropdown: { style: { maxWidth: '300px' } },
                                overlay: { class: 'parent-wrapper-class' }
                            }"
                        />
                        <InputError class="mt-2" :message="form.errors.application_id"/>
                    </div>


                    <div class="form-group mb-5" v-if="isAdmin">
                        <InputLabel for="user_id" :value="lang().label.user_id"/>
                        <Select
                            v-model="form.user_id"
                            :options="users"
                            optionLabel="name"
                            optionValue="id"
                            filter
                            checkmark
                            :highlightOnSelect="false"
                            :placeholder="lang().placeholder.select_user"
                            class="w-full"
                            :pt="{
                                option: { class: 'custom-option' },
                                dropdown: { style: { maxWidth: '300px' } },
                                overlay: { class: 'parent-wrapper-class' }
                            }"
                        />
                        <InputError class="mt-2" :message="form.errors.user_id"/>
                    </div>

                    <div class="form-group mb-5">
                        <InputLabel for="currency_id" :value="lang().label.currency_id"/>
                        <Select
                            v-model="form.currency_id"
                            :options="currency"
                            optionLabel="name"
                            optionValue="id"
                            filter
                            checkmark
                            :highlightOnSelect="false"
                            :placeholder="lang().placeholder.select_currency"
                            class="w-full"
                            :pt="{
                                option: { class: 'custom-option' },
                                dropdown: { style: { maxWidth: '300px' } },
                                overlay: { class: 'parent-wrapper-class' }
                            }"
                        />
                        <InputError class="mt-2" :message="form.errors.currency_id"/>
                    </div>

                    <div class="form-group mb-3">
                        <InputLabel for="budget_sum" :value="lang().label.contract_sum"/>
                        <InputNumber
                            id="budget_sum"
                            v-model="form.budget_sum"
                            class="mt-1 block w-full"
                            :minFractionDigits="2"
                            fluid
                            :placeholder="lang().label.contract_sum"
                            :error="form.errors.budget_sum"
                        />
                        <InputError class="mt-2" :message="form.errors.budget_sum"/>
                    </div>

                    <div class="form-group mb-3">
                        <InputLabel for="deadline" :value="lang().label.deadline"/>
                        <DatePicker
                            id="deadline"
                            v-model="form.deadline"
                            class="mt-1 block w-full"
                            :placeholder="lang().label.deadline"
                            showIcon
                            showButtonBar
                            :monthNavigator="true"
                            :yearNavigator="true"
                            yearRange="2020:2030"
                            dateFormat="dd/mm/yy"
                            :manualInput="false"
                            :minDate="new Date()"
                        />
                        <InputError class="mt-2" :message="form.errors.deadline"/>
                    </div>

                    <div class="form-group mb-5">
                        <InputLabel for="files" :value="lang().label.files"/>
                        <FileUpload
                            name="files[]"
                            :auto="false"
                            :accept="'application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'"
                            :multiple="true"
                            v-model="form.files"
                            @select="onFileChange"
                            :file-limit="6"
                            :custom-upload="true"
                            :show-upload-button="false"
                            @clear="onClearFiles"
                            :error="form.errors.files"
                            :chooseLabel="lang().label.choose"
                            :uploadLabel="lang().label.upload"
                            :cancelLabel="lang().label.cancel"
                        >
                            <template #content="{ files, uploadedFiles, removeUploadedFileCallback, messages }">

                                <div class="flex flex-col gap-8 pt-4">
                                    <Message v-for="message of messages" :key="message"
                                             :class="{ 'mb-8': !files.length && !uploadedFiles.length}"
                                             severity="error">
                                        {{ message }}
                                    </Message>

                                    <div v-if="form.files.length > 0">
                                        <div class="flex flex-wrap gap-4">
                                            <div v-for="(file, index) in files.slice(0, 4)" :key="file.name + file.type + file.size" class="p-8 rounded-border flex flex-col border border-surface items-center gap-4">
                                                <div>
                                                    <i :class="getFileIcon(file.type)" style="font-size: 32px;"></i>
                                                </div>
                                                <span class="font-semibold text-ellipsis max-w-60 whitespace-nowrap overflow-hidden">{{ file.name }}</span>
                                                <Button icon="pi pi-times" @click="removeUploadedFile(index, file.id)" outlined rounded severity="danger" />
                                            </div>
                                        </div>
                                    </div>

                                    <div v-if="props.files.length > 0">
                                        <div class="flex flex-wrap gap-4">
                                            <div v-for="(file, index) in props.files" :key="file.name + file.type + file.size" class="p-8 rounded-border flex flex-col border border-surface items-center gap-4">
                                                <div>
                                                    <i :class="getFileIcon(file.type)" style="font-size: 32px;"></i>
                                                </div>
                                                <span class="font-semibold text-ellipsis max-w-60 whitespace-nowrap overflow-hidden">{{ file.name }}</span>
                                                <span class="text-xs text-gray-500">
                                                    {{ formatDate((file.created_at)) }}
                                                </span>
                                                <a
                                                    :href="file.original_url"
                                                    target="_blank"
                                                    class="p-button p-component p-button-outlined p-button-success">
                                                    <span class="p-button-label">{{ lang().label.download }}</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </template>

                            <template #empty>
                                <div v-if="form.files.length !== 0" class="flex items-center justify-center flex-col">
                                    <i class="pi pi-cloud-upload !border-2 !rounded-full !p-8 !text-4xl !text-muted-color" />
                                    <p class="mt-6 mb-0">{{ lang().label.drag_and_drop }}</p>
                                </div>
                            </template>

                        </FileUpload>

                        <InputError class="mt-2" :message="form.errors.files"/>
                    </div>
                </div>
                <div class="flex justify-start">
                    <BackLink :href="route('contract.index')" />
                    <PrimaryButton
                        class="ml-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="update"
                    >
                        {{ form.processing ? lang().button.save + "..." : lang().button.save }}
                    </PrimaryButton>
                </div>
            </form>
        </section>

    </AuthenticatedLayout>
</template>

<script setup>
import { nextTick, onMounted, onUnmounted, ref, computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ScrollPanel from 'primevue/scrollpanel';
import axios from 'axios';

const activeTab = ref('dialogs');
const props = defineProps({
    contract: Object,
    title: String,
    breadcrumbs: Object,
    chats: Array,
    users: Array,
});

const chats = ref([...props.chats]);

const form = useForm({
    message: '',
    files: [],
    receiver_id: '',
    chat_id: '',
});

const activeChat = ref(null);
const messages = ref([]);
const selectedFiles = ref([]);

const currentUserId = usePage().props.auth.user.id;

const clearForm = () => {
    form.message = '';
    form.files = [];
    selectedFiles.value = [];
};

const updateChatMessages = (chatId, newMessages) => {
    const chatIndex = chats.value.findIndex((chat) => chat.id === chatId);
    if (chatIndex !== -1) {
        chats.value[chatIndex].messages = newMessages;
    }
};

const fetchMessages = async () => {
    if (!activeChat.value?.id) return;

    try {
        const response = await axios.get(route('contract.get-messages', { chat_id: activeChat.value.id }));
        if (response.data?.messages) {
            messages.value = response.data.messages;
            updateChatMessages(activeChat.value.id, response.data.messages);
        }
    } catch (error) {
        console.error('Ошибка при загрузке сообщений:', error);
    }
};

const fetchAllChats = async () => {
    try {
        const response = await axios.get(route('contract.get-all-chats', { contract: props.contract.id }));
        if (response.data?.chats) {
            chats.value = response.data.chats;
        }
    } catch (error) {
        console.error('Ошибка при загрузке чатов:', error);
    }
};

const loadChat = (chat) => {
    activeChat.value = chat;
    messages.value = chat.messages || [];
    form.receiver_id = chat.receiver_id;
    form.chat_id = chat.id;
    clearForm();
};

const send = async () => {
    try {
        await form.post(route('contract.send-message', props.contract?.id), {
            preserveScroll: true,
            onSuccess: (response) => {
                clearForm();
                if (response?.data?.message) {
                    messages.value.push(response.data.message);

                    if (!activeChat.value.id && response.data.chat) {
                        activeChat.value.id = response.data.chat.id;
                        form.chat_id = response.data.chat.id;
                    }

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
    if (fileInput) fileInput.click();
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

const getReceiverName = computed(() => (receiverId) => {
    const user = props.users.find((u) => u.id === receiverId);
    return user?.name || 'Неизвестный';
});

const getUserImage = (userId) => {
    const user = props.users.find((u) => u.id === userId);
    return user?.profile_image || '/default-avatar.jpg';
};

const getLastMessage = computed(() => (userId) => {
    const chat = chats.value.find(
        (c) =>
            (c.user_id === currentUserId && c.receiver_id === userId) ||
            (c.receiver_id === currentUserId && c.user_id === userId)
    );
    return chat?.messages?.length ? chat.messages[chat.messages.length - 1] : null;
});

const openChatWithUser = async (user) => {
    const existingChat = chats.value.find(
        (c) =>
            (c.user_id === currentUserId && c.receiver_id === user.id) ||
            (c.receiver_id === currentUserId && c.user_id === user.id)
    );

    if (existingChat) {
        loadChat(existingChat);
    } else {
        activeChat.value = {
            id: null,
            receiver_id: user.id,
            user_id: currentUserId,
            messages: [],
        };
        form.receiver_id = user.id;
        form.chat_id = null;
        messages.value = [];
    }
};

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

<style>
.custom-option{
    white-space: pre-wrap !important;
}
.custom-overlay-class {
    width: 100%;
    max-width: 300px;
}

.parent-wrapper-class{
    width: 1%;
    left: 0;
    right: auto;
}
</style>
