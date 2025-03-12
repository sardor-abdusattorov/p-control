<template>
    <Head :title="props.title"/>
    <AuthenticatedLayout>
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs"/>

        <section class="space-y-4 bg-white dark:bg-slate-800 shadow sm:rounded-lg">
            <form class="p-6" @submit.prevent="create">
                <h2
                    class="text-lg font-medium text-slate-900 dark:text-slate-100"
                >
                    {{ lang().label.create }} {{ props.title }}
                </h2>

                <div class="font-semibold text-base p-4 mt-4 bg-blue-100 border-l-4 border-blue-500 text-blue-700 dark:bg-blue-900 dark:border-blue-400 dark:text-blue-300 rounded-lg shadow-md">
                    ℹ️ {{ lang().label.application_contract_notice }}
                </div>

                <div class="my-6 w-full">
                    <div class="form-group mb-5">
                        <InputLabel for="contract_number" :value="lang().label.contract_number" />
                        <InputText
                            id="contract_number"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.contract_number"
                            :placeholder="lang().label.contract_number"
                            :error="form.errors.contract_number"
                        />
                        <InputError class="mt-2" :message="form.errors.contract_number" />
                    </div>

                    <div class="form-group mb-5">
                        <InputLabel for="title" :value="lang().label.contract_description" />
                        <InputText
                            id="title"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.title"
                            :placeholder="lang().label.contract_description"
                            :error="form.errors.title"
                        />
                        <InputError class="mt-2" :message="form.errors.title" />
                    </div>

                    <div class="form-group mb-5">
                        <InputLabel for="project_id" :value="lang().label.project" />
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
                        <InputError class="mt-2" :message="form.errors.project_id" />
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
                            :disabled="!form.application_type"
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
                            optionValue="id"
                            :options="props.users"
                            optionLabel="name"
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
                        <InputLabel for="currency_id" :value="lang().label.currency_id" />
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
                        <InputError class="mt-2" :message="form.errors.currency_id" />
                    </div>

                    <div class="form-group mb-3">
                        <InputLabel for="budget_sum" :value="lang().label.contract_sum" />
                        <InputNumber
                            v-if="form.budget_sum !== null"
                            id="budget_sum"
                            v-model="form.budget_sum"
                            class="mt-1 block w-full"
                            :minFractionDigits="2"
                            fluid
                            :placeholder="lang().label.contract_sum"
                            :error="form.errors.budget_sum"
                        />

                        <InputError class="mt-2" :message="form.errors.budget_sum" />
                    </div>

                    <div class="form-group mb-3">
                        <InputLabel for="deadline" :value="lang().label.deadline" />
                        <DatePicker
                            id="deadline"
                            v-model="form.deadline"
                            class="mt-1 block w-full"
                            :placeholder="lang().label.deadline"
                            showIcon
                            showButtonBar
                            :monthNavigator="true"
                            :yearNavigator="true"
                            dateFormat="dd/mm/yy"
                            yearRange="2020:2030"
                            :manualInput="false"
                            :minDate="new Date()"
                        />
                        <InputError class="mt-2" :message="form.errors.deadline" />
                    </div>

                    <div class="form-group mb-5">
                        <InputLabel for="files" :value="lang().label.files" />
                        <FileUpload
                            name="files[]"
                            :auto="false"
                            :accept="'application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'"
                            :multiple="true"
                            v-model="form.files"
                            @select="onFileChange"
                            :file-limit="6"
                            :custom-upload="true"
                            :chooseLabel="lang().label.choose"
                            :uploadLabel="lang().label.upload"
                            :cancelLabel="lang().label.cancel"
                            :show-upload-button="false"
                            @clear="onClearFiles"
                            :error="form.errors.files"
                        >
                            <template #content="{ files, uploadedFiles, removeUploadedFileCallback, messages }">

                                <div class="flex flex-col gap-8 pt-4">
                                    <Message v-for="message of messages" :key="message" :class="{ 'mb-8': !files.length && !uploadedFiles.length}" severity="error">
                                        {{ message }}
                                    </Message>

                                    <div v-if="files.length > 0">
                                        <div class="flex flex-wrap gap-4">
                                            <div v-for="(file, index) in files.slice(0, 6)" :key="file.name + file.type + file.size" class="p-8 rounded-border flex flex-col border border-surface items-center gap-4">
                                                <div>
                                                    <i class="pi pi-file" style="font-size: 32px;"></i>
                                                </div>
                                                <span class="font-semibold text-ellipsis max-w-60 whitespace-nowrap overflow-hidden">{{ file.name }}</span>
                                                <Button icon="pi pi-times" @click="removeUploadedFile(index)" outlined rounded severity="danger" />
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </template>

                            <template #empty>
                                <div class="flex items-center justify-center flex-col">
                                    <i class="pi pi-cloud-upload !border-2 !rounded-full !p-8 !text-4xl !text-muted-color" />
                                    <p class="mt-6 mb-0">{{ lang().label.drag_and_drop }}</p>
                                </div>
                            </template>

                        </FileUpload>

                        <InputError class="mt-2" :message="form.errors.files" />
                    </div>
                </div>
                <div class="flex justify-start">
                    <BackLink :href="route('contract.index')"/>
                    <PrimaryButton
                        class="ml-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="create"
                    >
                        {{
                            form.processing
                                ? lang().button.add + "..."
                                : lang().button.add
                        }}
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
import {computed, ref, watch, watchEffect} from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import Select from "primevue/select";
import DatePicker from "primevue/datepicker";
import InputText from "primevue/inputtext";
import InputNumber from "primevue/inputnumber";
import BackLink from "@/Components/BackLink.vue";
import FileUpload from 'primevue/fileupload';
import {Message} from "primevue";
import Button from "primevue/button";
import MultiSelect from "primevue/multiselect";

const props = defineProps({
    show: Boolean,
    title: String,
    breadcrumbs: Object,
    users: Array,
    projects: Array,
    applications: Array,
    currency: Array,
    recipients: Array,
    application_types: Object,
});

// const user = usePage().props.auth.user;
// const role = user.roles?.[0]?.name || null;

const allowedFileTypes = [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
];


const form = useForm({
    contract_number: "",
    files: [],
    recipients: [],
    title: "",
    project_id: "",
    application_id: "",
    application_type: 1,
    currency_id: 1,
    budget_sum: 0,
    deadline: new Date(new Date().getFullYear(), 11, 31),
});

const filteredApplications = computed(() => {
    if (!form.application_type) return [];
    return props.applications.filter(app => app.type === form.application_type);
});

watch(() => form.application_type, () => {
    form.application_id = "";
});

const onFileChange = (event) => {
    if (event.files && event.files.length > 0) {
        const newFiles = event.files;
        const invalidFiles = [];
        newFiles.forEach((file) => {
            if (!allowedFileTypes.includes(file.type)) {
                invalidFiles.push(file.name);
            }
        });
        if (invalidFiles.length > 0) {
        } else {
            form.files = newFiles;
        }
    }
};

const onClearFiles = () => {
    form.files = [];
};

const removeUploadedFile = (index) => {
    form.files.splice(index, 1);
};
const create = () => {
    form.post(route("contract.store"));
};

watchEffect(() => {
    form.errors = {};
    form.recipients = props.recipients.map(recipient => recipient.recipient_id);
});

const formattedProjects = computed(() => {
    return props.projects.map(project => ({
        id: project.id,
        project_number: project.project_number || '',
        title: project.title,
        display: `${project.project_number ? project.project_number + '.' : ''} ${project.title}`.trim()
    }));
});

</script>
