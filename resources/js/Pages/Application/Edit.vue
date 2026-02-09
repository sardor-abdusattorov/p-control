
<template>
    <AuthenticatedLayout :title="props.title">
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs" />
        <section class="space-y-4 bg-white dark:bg-slate-800 shadow sm:rounded-lg">
            <form class="p-3 sm:p-6" @submit.prevent="update" enctype="multipart/form-data">
                <h2 class="text-lg font-medium text-slate-900 dark:text-slate-100 mb-6">
                    {{ lang().label.update }} {{ props.title }}
                </h2>
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
                    <InputLabel for="project_year" :value="lang().label.year" />
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
                        ref="fileUploadRef"
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
                                <Message
                                    v-for="message of messages"
                                    :key="message"
                                    :class="{ 'mb-8': !files.length && !uploadedFiles.length }"
                                    severity="error"
                                >
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
                                            <div class="header flex items-center flex-col w-full">
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


                    <InputError class="mt-2" :message="form.errors.files" />
                </div>

                <div class="flex justify-start">
                    <BackLink :href="route('application.index')" />
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
    title: String,
    application: Object,
    breadcrumbs: Object,
    projects: Array,
    users: Array,
    types: Array,
    currency: Array,
    files: Array,
    approval_user_ids: Array
});

const emit = defineEmits(["close"]);

const projectYear = ref(new Date().getFullYear());
const groupedProjects = ref([]);

const form = useForm({
    title: "",
    project_id: "",
    type: "",
    files: [],
    currency_id: '',
    old_files: [],
    recipients: [],
    deleted_old_file_ids: []
});

let initialized = false;
let skipProjectReset = false;

watchEffect(() => {
    if (initialized) return;

    form.errors = {};
    form.title = props.application.title;
    form.project_id = props.application.project_id;
    form.files = [];
    form.old_files = props.files;
    form.currency_id = props.application.currency_id ?? 1;
    form.type = props.application.type;
    form.recipients = props.approval_user_ids || [];

    // Determine year from existing project's category
    if (props.application.project_id && props.projects) {
        const existingProject = props.projects.find(p => p.id === props.application.project_id);
        if (existingProject && existingProject.category && existingProject.category.year) {
            projectYear.value = existingProject.category.year;
            skipProjectReset = true;
        }
    }

    initialized = true;
});

watch(
    () => projectYear.value,
    async (year) => {
        if (!year) { groupedProjects.value = []; return; }
        try {
            const response = await axios.get(route("projects.by-year", year));
            groupedProjects.value = response.data;
            if (!skipProjectReset) {
                form.project_id = "";
            }
            skipProjectReset = false;
        } catch (e) { groupedProjects.value = []; }
    },
    { immediate: true }
);

const fileUploadRef = ref(null);

const update = () => {
    form.post(route("application.update", props.application?.id), {
    });
};

const onFileChange = (event) => {
    form.files = event.files || [];
};

const removeUploadedFile = (index) => {
    form.files.splice(index, 1);
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

const removeOldFile = (index) => {
    const removed = form.old_files.splice(index, 1)[0];
    if (removed?.id) {
        form.deleted_old_file_ids.push(removed.id);
    }
};

const getFileIcon = (fileType) => {
    return 'pi pi-file';
};


const formatDate = (dateString) => {
    if (!dateString) return "-";
    const date = new Date(dateString);
    return new Intl.DateTimeFormat("ru-RU", { dateStyle: "short", timeStyle: "short" }).format(date);
};

const currentYear = new Date().getFullYear();
const yearsList = Array.from({ length: currentYear - 2025 + 1 }, (_, i) => 2025 + i);

</script>

<style>
.p-inputnumber-input {
    flex: none !important;
    width: 100%;
}
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
