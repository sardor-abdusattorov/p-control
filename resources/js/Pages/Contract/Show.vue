<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import {Head, useForm, usePage} from "@inertiajs/vue3";
import { watchEffect } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import Select from "primevue/select";
import DatePicker from "primevue/datepicker";
import InputText from "primevue/inputtext";
import InputNumber from "primevue/inputnumber";
import BackLink from "@/Components/BackLink.vue";
import FileUpload from 'primevue/fileupload';
import { Message } from "primevue";


const isAdmin = usePage().props.auth.user.roles?.some(role => role.name === 'superadmin');

const props = defineProps({
    show: Boolean,
    title: String,
    breadcrumbs: Object,
    contract: Object,
    users: Array,
    projects: Array,
    applications: Array,
    currency: Array,
    files: Array,
});

const form = useForm({
    contract_number: "",
    files: [],
    title: "",
    project_id: "",
    application_id: "",
    currency_id: "",
    user_id: "",
    budget_sum: "",
    deadline: new Date(new Date().getFullYear(), 11, 31),
});

watchEffect(() => {
    form.contract_number = props.contract.contract_number
    form.project_id = props.contract.project_id
    form.application_id = props.contract.application_id
    form.user_id = props.contract.user_id
    form.currency_id = props.contract.currency_id
    form.title = props.contract.title
    form.budget_sum = props.contract.budget_sum
    form.deadline = props.contract?.deadline ? new Date(props.contract.deadline) : null;
    form.files = props.files;
});
</script>

<template>
    <Head :title="props.title"/>
    <AuthenticatedLayout>
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs"/>

        <section class="space-y-4 bg-white dark:bg-slate-800 shadow sm:rounded-lg">
            <form class="p-6">
                <h2 class="text-lg font-medium text-slate-900 dark:text-slate-100">
                    {{ lang().label.preview }} {{ props.title }}
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
                            readonly
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
                            readonly
                        />
                        <InputError class="mt-2" :message="form.errors.title"/>
                    </div>

                    <div class="form-group mb-5">
                        <InputLabel for="project_id" :value="lang().label.project_id"/>
                        <Select
                            id="project_id"
                            v-model="form.project_id"
                            optionLabel="title"
                            optionValue="id"
                            :options="props.projects"
                            filter
                            :filterPlaceholder="lang().placeholder.select_project"
                            class="w-full"
                            :placeholder="lang().label.project_id"
                            :disabled="true"
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
                            class="w-full"
                            :placeholder="lang().placeholder.select_application"
                            :disabled="true"
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
                            :placeholder="lang().placeholder.select_user"
                            class="w-full"
                            :disabled="true"
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
                            :placeholder="lang().placeholder.select_currency"
                            class="w-full"
                            :disabled="true"
                        />
                        <InputError class="mt-2" :message="form.errors.currency_id"/>
                    </div>

                    <div class="form-group mb-3">
                        <InputLabel for="budget_sum" :value="lang().label.budget_sum"/>
                        <InputNumber
                            id="budget_sum"
                            v-model="form.budget_sum"
                            class="mt-1 block w-full"
                            mode="currency"
                            currency="UZS"
                            locale="uz-UZ"
                            :placeholder="lang().label.budget_sum"
                            :error="form.errors.budget_sum"
                            readonly
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
                            readonly
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
                            :file-limit="6"
                            :custom-upload="true"
                            :show-upload-button="false"
                            :disabled="true"
                            :error="form.errors.files"
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
                                            <div v-for="(file, index) in form.files"
                                                 :key="file.name + file.type + file.size"
                                                 class="p-8 rounded-border flex flex-col border border-surface items-center gap-4">
                                                <div>
                                                    <i class="pi pi-file" style="font-size: 32px;"></i>
                                                </div>
                                                <span
                                                    class="font-semibold text-ellipsis max-w-60 whitespace-nowrap overflow-hidden">{{
                                                        file.name
                                                    }}</span>
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
                        </FileUpload>

                        <InputError class="mt-2" :message="form.errors.files"/>
                    </div>
                </div>
                <div class="flex justify-start">
                    <BackLink :href="route('contract.index')"/>
                </div>
            </form>
        </section>
    </AuthenticatedLayout>
</template>
