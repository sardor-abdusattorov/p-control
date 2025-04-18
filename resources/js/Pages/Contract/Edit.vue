<template>
    <Head :title="props.title"/>
    <AuthenticatedLayout>
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs"/>

        <section class="space-y-4 bg-white dark:bg-slate-800 shadow sm:rounded-lg">
            <form class="p-3 sm:p-6" @submit.prevent="update">
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
                        <Textarea
                            id="title"
                            v-model="form.title"
                            :placeholder="lang().label.contract_description"
                            autoResize
                            rows="4"
                            class="mt-1 w-full"
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

                    <div class="form-group mb-3">
                        <InputLabel for="type" :value="lang().label.type" />
                        <Select
                            id="type"
                            v-model="form.application_type"
                            :options="application_types"
                            optionLabel="label"
                            optionValue="id"
                            class="w-full"
                            filter
                            checkmark
                            :placeholder="lang().placeholder.select_type"
                            :highlightOnSelect="false"
                            :pt="{
            option: { class: 'custom-option' },
            dropdown: { style: { maxWidth: '300px' } },
            overlay: { class: 'parent-wrapper-class' }
        }"
                        />
                        <InputError class="mt-2" :message="form.errors.type" />
                    </div>

                    <div class="form-group mb-5">
                        <InputLabel for="application_id" :value="lang().label.application_id" />
                        <Select
                            id="application_id"
                            v-model="form.application_id"
                            optionLabel="title"
                            optionValue="id"
                            :options="filteredApplications"
                            filter
                            checkmark
                            :highlightOnSelect="false"
                            class="w-full"
                            :placeholder="lang().label.application_id"
                            :filterPlaceholder="lang().placeholder.select_application"
                            :pt="{
                                option: { class: 'custom-option' },
                                dropdown: { style: { maxWidth: '300px' } },
                                overlay: { class: 'parent-wrapper-class' }
                            }"
                        />
                        <InputError class="mt-2" :message="form.errors.application_id" />
                    </div>

                    <div class="form-group mb-3">
                        <InputLabel for="recipients" :value="lang().label.approval_users" />
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
                            ref="fileUploadRef"
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
                            <template #content="{ files, uploadedFiles, messages }">

                                <div class="flex flex-col gap-8 pt-4">
                                    <Message v-for="message of messages" :key="message"
                                             :class="{ 'mb-8': !files.length && !uploadedFiles.length}"
                                             severity="error">
                                        {{ message }}
                                    </Message>

                                    <div v-if="form.files.length > 0 || form.old_files.length > 0">
                                        <div class="flex flex-wrap gap-4">
                                            <div
                                                v-for="(file, index) in form.files"
                                                :key="'new-' + file.name + file.type + file.size"
                                                class="min-w-[160px] p-4 rounded-border flex flex-col border justify-between border-surface items-center gap-4"
                                                style="width: calc(20% - 0.8rem)"
                                            >
                                                <div class="header flex items-center flex-col w-full">
                                                    <div>
                                                        <i :class="getFileIcon(file.type)" style="font-size: 48px;" class="mb-6"></i>
                                                    </div>
                                                    <span class="font-semibold text-ellipsis max-w-full whitespace-nowrap overflow-hidden text-center">
                                                {{ file.name }}
                                            </span>
                                                </div>
                                                <Button
                                                    icon="pi pi-trash"
                                                    @click="removeUploadedFile(index)"
                                                    outlined
                                                    rounded
                                                    severity="danger"
                                                    :aria-label="lang().label.delete"
                                                />
                                            </div>

                                            <div
                                                v-for="(file, index) in form.old_files"
                                                :key="'old-' + file.name + file.type + file.size"
                                                class="min-w-[160px] p-4 rounded-border flex flex-col border border-surface items-center gap-4"
                                                style="width: calc(20% - 0.8rem)"
                                            >
                                                <div class="header flex items-center flex-col">
                                                    <div>
                                                        <i :class="getFileIcon(file.type)" style="font-size: 48px;" class="mb-6"></i>
                                                    </div>
                                                    <span class="font-semibold text-ellipsis max-w-full whitespace-nowrap overflow-hidden text-center mb-4">
                                                {{ file.name }}
                                            </span>
                                                    <span class="text-xs text-gray-500 mb-4">{{ formatDate(file.created_at) }}</span>
                                                    <a
                                                        :href="file.original_url"
                                                        target="_blank"
                                                        class="p-button p-component p-button-outlined p-button-success"
                                                    >
                                                        <span class="p-button-label">{{ lang().label.download }}</span>
                                                    </a>
                                                </div>

                                                <Button
                                                    icon="pi pi-trash"
                                                    @click="removeOldFile(index)"
                                                    outlined
                                                    rounded
                                                    severity="danger"
                                                    :aria-label="lang().label.delete"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <template #empty>
                                <div v-if="(form.old_files?.length ?? 0) === 0 && (form.files?.length ?? 0) === 0" class="flex items-center justify-center flex-col">
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
                        type="submit"
                        class="ml-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? lang().button.save + "..." : lang().button.save }}
                    </PrimaryButton>
                </div>
            </form>
        </section>

    </AuthenticatedLayout>
</template>

<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {Head, useForm, usePage} from "@inertiajs/vue3";
import {computed, watchEffect} from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import Select from "primevue/select";
import DatePicker from "primevue/datepicker";
import InputText from "primevue/inputtext";
import InputNumber from "primevue/inputnumber";
import BackLink from "@/Components/BackLink.vue";
import FileUpload from 'primevue/fileupload';
import {Message, Textarea} from "primevue";
import Button from "primevue/button";
import MultiSelect from "primevue/multiselect";
import { watch } from "vue";

const props = defineProps({
    title: String,
    breadcrumbs: Object,
    contract: Object,
    users: Array,
    projects: Array,
    applications: Array,
    currency: Array,
    files: Array,
    application_types: Object,
    approval_user_ids: Array
});

const form = useForm({
    contract_number: "",
    files: [],
    old_files: [],
    deleted_old_file_ids: [],
    recipients: [],
    title: "",
    project_id: "",
    application_id: "",
    currency_id: "",
    user_id: "",
    budget_sum: "",
    deadline: "",
    application_type: "",
});

const filteredApplications = computed(() => {
    if (!form.application_type) return [];
    return props.applications.filter(app => app.type === form.application_type);
});

const onFileChange = (event) => {
    form.files = event.files || [];
};

const onClearFiles = () => {
    form.files = [];
    form.old_files.forEach(file => {
        if (file.id) {
            form.deleted_old_file_ids.push(file.id);
        }
    });

    form.old_files = [];
};
const removeUploadedFile = (index) => {
    form.files.splice(index, 1);
};

const removeOldFile = (index) => {
    const removed = form.old_files.splice(index, 1)[0];
    if (removed?.id) {
        form.deleted_old_file_ids.push(removed.id);
    }
};
const update = () => {
    form.post(route("contract.update", props.contract?.id));
};

let initialized = false;

watchEffect(() => {
    if (initialized) return;

    form.contract_number = props.contract.contract_number;
    form.project_id = props.contract.project_id;
    form.application_id = props.contract.application_id;
    form.user_id = props.contract.user_id;
    form.currency_id = props.contract.currency_id;
    form.title = props.contract.title;
    form.budget_sum = props.contract.budget_sum;
    form.deadline = props.contract?.deadline ? new Date(props.contract.deadline) : null;
    form.files = [];
    form.errors = {};
    form.old_files = props.files;
    form.recipients = props.approval_user_ids || [];

    const application = props.applications.find(app => app.id === props.contract.application_id);
    form.application_type = application ? application.type : "";

    initialized = true;
});

const getFileIcon = (fileType) => {
    return 'pi pi-file';
};


watch(() => form.application_type, () => {
    form.application_id = "";
});


const formattedProjects = computed(() => {
    return props.projects.map(project => ({
        id: project.id,
        project_number: project.project_number || '',
        title: project.title,
        display: `${project.project_number ? project.project_number + '.' : ''} ${project.title}`.trim()
    }));
});

const formatDate = (dateString) => {
    if (!dateString) return "-";
    const date = new Date(dateString);
    return new Intl.DateTimeFormat("ru-RU", { dateStyle: "short", timeStyle: "short" }).format(date);
};

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
.p-inputnumber-fluid .p-inputnumber-input{
    width: 100% !important;
}
</style>
