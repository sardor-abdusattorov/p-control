<template>
    <AuthenticatedLayout :title="props.title">
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs" />
        <section class="space-y-4 bg-white dark:bg-slate-800 shadow sm:rounded-lg">

            <form class="p-3 sm:p-6" @submit.prevent="create" enctype="multipart/form-data">

                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 p-4 rounded mb-4" role="alert">
                    <p class="font-bold mb-1">📢 {{ lang().label.attention }}</p>
                    <p>
                        {{ lang().label.use_new_application_form }}
                        <a
                            href="/downloads/new_application_form.docx"
                            download
                            class="text-blue-600 underline hover:text-blue-800"
                        >
                            {{ lang().label.download_docx }}
                        </a>
                    </p>
                </div>

                <div class="form-group mb-3">
                    <InputLabel for="type" :value="lang().label.type" />
                    <Select
                        id="type"
                        v-model="form.type"
                        :options="types"
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
                    />
                    <InputError class="mt-2" :message="form.errors.title" />
                </div>

                <div class="form-group mb-3">
                    <InputLabel for="project_year" :value="lang().label.project_year" />
                    <Select
                        id="project_year"
                        v-model="projectYear"
                        :options="yearsList"
                        class="mt-1 block w-full"
                        :placeholder="lang().placeholder.select_year"
                    />
                </div>

                <div class="form-group mb-3">
                    <InputLabel for="project_id" :value="lang().label.project_id" />
                    <Select
                        id="project_id"
                        v-model="form.project_id"
                        :options="groupedProjects"
                        optionLabel="display"
                        optionValue="id"
                        optionGroupLabel="label"
                        optionGroupChildren="items"
                        filter
                        showClear
                        checkmark
                        :highlightOnSelect="false"
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

                <div class="form-group mb-3" v-if="form.type !== 2">
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

                <div class="form-group mb-3">
                    <InputLabel for="files" :value="lang().label.files" />
                    <FileUpload
                        name="files[]"
                        :auto="false"
                        :multiple="true"
                        v-model="form.files"
                        @select="onFileChange"
                        :file-limit="6"
                        :custom-upload="true"
                        :show-upload-button="false"
                        :chooseLabel="lang().label.choose"
                        :uploadLabel="lang().label.upload"
                        :cancelLabel="lang().label.cancel"
                        @clear="onClearFiles"
                        :error="form.errors.files"
                    >
                        <template #content="{ files, uploadedFiles, messages }">

                            <div class="flex flex-col gap-8 pt-4">
                                <Message v-for="message of messages" :key="message" :class="{ 'mb-8': !files.length && !uploadedFiles.length}" severity="error">
                                    {{ message }}
                                </Message>

                                <div v-if="files.length > 0">
                                    <div class="flex flex-wrap gap-4">
                                        <div v-for="(file, index) in files" :key="file.name + file.type + file.size" class="p-8 rounded-border flex flex-col border border-surface items-center gap-4">
                                            <div>
                                                <i :class="getFileIcon(file.type)" style="font-size: 32px;"></i>
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

                    <ul v-if="fileValidationErrors.length" class="text-sm text-red-600 mt-2 space-y-1">
                        <li v-for="(err, i) in fileValidationErrors" :key="i">
                            {{ err }}
                        </li>
                    </ul>

                </div>

                <div class="flex justify-start">
                    <BackLink :href="route('application.index')" />
                    <PrimaryButton
                        class="ml-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="create"
                    >
                        {{ form.processing ? lang().button.add + "..." : lang().button.add }}
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
import Select from 'primevue/select';
import { Head, useForm } from "@inertiajs/vue3";
import {computed, ref, watch, watchEffect} from "vue";
import InputText from "primevue/inputtext";
import InputNumber from "primevue/inputnumber";
import axios from "axios";
import MultiSelect from "primevue/multiselect";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import BackLink from "@/Components/BackLink.vue";
import FileUpload from 'primevue/fileupload';
import Button from "primevue/button";
import {Message} from "primevue";


const props = defineProps({
    show: Boolean,
    title: String,
    breadcrumbs: Object,
    recipients: Array,
    projects: Array,
    users: Array,
    types: Array,
    currency: Array,
    availableYears: Array,
});

const emit = defineEmits(["close"]);

const projectYear = ref(new Date().getFullYear());
const groupedProjects = ref([]);

const form = useForm({
    title: "",
    project_id: "",
    recipients: [],
    files: [],
    type: 1,
    currency_id: 1,
});

watch(
    () => projectYear.value,
    async (year) => {
        if (!year) { groupedProjects.value = []; return; }
        try {
            const response = await axios.get(route("projects.by-year", year));
            groupedProjects.value = response.data;
            form.project_id = "";
        } catch (e) { groupedProjects.value = []; }
    },
    { immediate: true }
);

const create = () => {
    form.post(route("application.store"), {
    });
};

watchEffect(() => {
    form.errors = {};
    if (form.type === 2) {
        form.recipients = [];
    } else {
        form.recipients = props.recipients.map(recipient => recipient.recipient_id);
    }
});

const fileValidationErrors = computed(() => {
    const errors = form.errors || {};
    return Object.entries(errors)
        .filter(([key, val]) => typeof key === 'string' && key.startsWith('files.') && typeof val === 'string')
        .map(([, msg]) => msg);
});

const onFileChange = (event) => {
    form.files = event.files || [];
};

const onClearFiles = () => {
    form.files = [];
};

const removeUploadedFile = (index) => {
    form.files.splice(index, 1);
};

const getFileIcon = (fileType) => {
    return 'pi pi-file';
};

const yearsList = props.availableYears ?? [];


</script>

