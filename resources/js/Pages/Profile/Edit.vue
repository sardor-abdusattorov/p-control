<template>

        <AuthenticatedLayout :title="lang().label.profile">
            <Breadcrumb :title="title" :breadcrumbs="breadcrumbs"/>

            <div class="space-y-4">
                <div class="font-std mb-10 w-full rounded-2xl bg-white dark:bg-gray-900 font-normal leading-relaxed text-gray-900 shadow-xl">
                    <Tabs value="Profile">
                        <TabList>
                            <Tab value="Profile">{{ lang().profile.profile_information }}</Tab>
                            <Tab value="Change Password">{{ lang().profile.password }}</Tab>
                        </TabList>
                        <TabPanels>
                            <TabPanel value="Profile">
                                <form @submit.prevent="updateProfile" class="space-y-4" enctype="multipart/form-data">
                                    <div class="profile-texts">
                                        <h2 class="text-lg font-medium text-slate-900 dark:text-slate-100">
                                            {{ lang().profile.profile_information }}
                                        </h2>
                                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                                            {{ lang().profile.update_profile }}
                                        </p>
                                    </div>

                                    <div class="form-group mb-5">
                                        <InputLabel for="image" :value="lang().label.image" />
                                        <div class="user-image flex items-start relative w-fit">
                                            <img
                                                :src="previewImage"
                                                alt="User logo preview"
                                                class="rounded-full w-[160px] h-[160px] border-4 border-gray-300 dark:border-gray-700"
                                            >
                                            <div class="bg-white/90 dark:bg-gray-800/80 rounded-full flex items-center justify-center absolute top-0 right-0">
                                                <input type="file" @input="handleImageChange" name="image" id="image" hidden>
                                                <label for="image">
                                                    <svg
                                                        data-slot="icon"
                                                        class="w-9 h-9 text-blue-700 dark:text-blue-400 p-1"
                                                        fill="none"
                                                        stroke-width="1.5"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24"
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        aria-hidden="true"
                                                    >
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z"></path>
                                                    </svg>
                                                </label>
                                            </div>
                                        </div>
                                        <div v-if="form.errors.image" class="text-red-500 mt-2">
                                            {{ form.errors.image }}
                                        </div>
                                    </div>





                                    <div class="inputs-group">
                                        <div class="form-group mb-3">
                                            <InputLabel for="name" :value="lang().label.name" />
                                            <InputText
                                                id="name"
                                                type="text"
                                                class="w-full mt-1"
                                                v-model="form.name"
                                                required
                                                autofocus
                                                autocomplete="name"
                                                :placeholder="lang().placeholder.name"
                                            />
                                            <InputError class="mt-2" :message="form.errors.name" />
                                        </div>
                                        <div class="form-group mb-3">
                                            <InputLabel for="email" :value="lang().label.email" />
                                            <InputText
                                                id="email"
                                                type="email"
                                                class="w-full mt-1"
                                                v-model="form.email"
                                                required
                                                :disabled="!isAdmin"
                                                autocomplete="email"
                                                :placeholder="lang().placeholder.email"
                                            />
                                            <InputError class="mt-2" :message="form.errors.email" />
                                        </div>
                                        <div class="form-group mb-3" v-if="props.mustVerifyEmail && user.email_verified_at === null">
                                            <p class="text-sm mt-2 text-slate-800 dark:text-slate-200">
                                                {{ lang().profile.unverified_email }}
                                                <Link :href="route('verification.send')" method="post" as="button" class="underline text-sm text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary dark:focus:ring-offset-slate-800">
                                                    {{ lang().profile.resend_email_verification }}
                                                </Link>
                                            </p>
                                            <div v-show="props.status === 'verification-link-sent'" class="mt-2 font-medium text-sm text-green-600 dark:text-green-400">
                                                {{ lang().profile.sent_verification_email }}
                                            </div>
                                        </div>

                                        <div class="form-group mb-3">
                                            <InputLabel for="recipients" :value="lang().label.recipients" />
                                            <MultiSelect
                                                v-model="form.recipients"
                                                display="chip"
                                                :options="props.users"
                                                optionGroupLabel="label"
                                                optionGroupChildren="items"
                                                optionLabel="name"
                                                optionValue="id"
                                                filter
                                                checkmark
                                                :highlightOnSelect="false"
                                                :placeholder="lang().placeholder.select_recipients"
                                                :maxSelectedLabels="8"
                                                class="w-full"
                                                :pt="{
                                                    option: { class: 'custom-option' },
                                                    dropdown: { style: { maxWidth: '300px' } },
                                                    overlay: { class: 'parent-wrapper-class' }
                                                }"
                                            />
                                            <InputError class="mt-2" :message="form.errors.recipients" />
                                        </div>

                                        <div class="form-group mb-3">
                                            <InputLabel for="department_id" :value="lang().label.department_id" />
                                            <Select
                                                v-model="form.department_id"
                                                filter
                                                checkmark
                                                :highlightOnSelect="false"
                                                showClear
                                                :options="departments"
                                                optionLabel="name"
                                                optionValue="id"
                                                :placeholder="lang().label.select_departments"
                                                class="w-full"
                                                @change="onDepartmentChange"
                                                :disabled="!isAdmin"
                                                :pt="{
                                                    option: { class: 'custom-option' },
                                                    dropdown: { style: { maxWidth: '300px' } },
                                                    overlay: { class: 'parent-wrapper-class' }
                                                }"
                                            />
                                            <InputError class="mt-2" :message="form.errors.department_id" />
                                        </div>

                                        <div class="form-group mb-3">
                                            <InputLabel for="position_id" :value="lang().label.position_id" />
                                            <Select
                                                v-model="form.position_id"
                                                :options="filteredPositions"
                                                optionLabel="name"
                                                optionValue="id"
                                                placeholder="Select Position"
                                                class="w-full"
                                                filter
                                                checkmark
                                                :highlightOnSelect="false"
                                                :disabled="!form.department_id || !isAdmin"
                                                :pt="{
                                                    option: { class: 'custom-option' },
                                                    dropdown: { style: { maxWidth: '300px' } },
                                                    overlay: { class: 'parent-wrapper-class' }
                                                }"
                                            />

                                            <InputError class="mt-2" :message="form.errors.position_id" />
                                        </div>

                                        <div class="form-group mb-3">
                                            <InputLabel for="telegram_id" :value="lang().label.telegram_id" />
                                            <InputText
                                                id="telegram_id"
                                                type="text"
                                                class="w-full mt-1"
                                                v-model="form.telegram_id"
                                                :placeholder="lang().placeholder.telegram_id"
                                            />
                                            <InputError class="mt-2" :message="form.errors.telegram_id" />
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-4">
                                        <PrimaryButton :disabled="form.processing">{{lang().button.save}}</PrimaryButton>
                                        <Transition
                                            enter-active-class="transition ease-in-out"
                                            enter-from-class="opacity-0"
                                            leave-active-class="transition ease-in-out"
                                            leave-to-class="opacity-0"
                                        >
                                            <p v-if="form.recentlySuccessful" class="text-sm text-slate-600 dark:text-slate-400">
                                                {{ lang().profile.saved }}
                                            </p>
                                        </Transition>
                                    </div>
                                </form>
                            </TabPanel>
                            <TabPanel value="Change Password">
                                <UpdatePasswordForm/>
                            </TabPanel>
                        </TabPanels>

                    </Tabs>
                </div>
            </div>
        </AuthenticatedLayout>
    </template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import UpdatePasswordForm from "./Partials/UpdatePasswordForm.vue";
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import InputText from 'primevue/inputtext';
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputError from "@/Components/InputError.vue";
import Select from 'primevue/select';
import { ref, computed } from "vue";
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';
import MultiSelect from 'primevue/multiselect';


const props = defineProps({
    mustVerifyEmail: Boolean,
    status: String,
    title: String,
    departments: Array,
    breadcrumbs: Object,
    positions: Array,
    users: Array,
    recipients: Array,
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
    department_id: user.department_id,
    position_id: user.position_id,
    telegram_id: user.telegram_id,
    image: null,
    recipients: props.recipients.map(recipient => recipient.recipient_id)
});

const previewImage = ref(user.profile_image);

const updateProfile = () => {
    form.post(route("profile.update"), {
        preserveScroll: true,
    });
};

const handleImageChange = (event) => {
    const file = event.target.files[0];
    if (file) {

        const allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!allowedMimeTypes.includes(file.type)) {

            form.image = null;
            previewImage.value = user.profile_image;
            form.errors.image = 'Недопустимый тип файла. Загрузите изображение.';
            return;
        }

        form.image = file;
        previewImage.value = URL.createObjectURL(file);
        form.errors.image = null;
    }
};

const filteredPositions = computed(() => {
    return form.department_id
        ? props.positions.filter(position => position.department_id === form.department_id)
        : [];
});

const onDepartmentChange = () => {
    form.position_id = null;
};

const isAdmin = usePage().props.auth.user.roles?.some(role => role.name === 'superadmin');

</script>

