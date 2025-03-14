
<template>
    <Head :title="props.title" />
    <AuthenticatedLayout>
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs" />
        <section class="space-y-4 bg-white dark:bg-slate-800 shadow sm:rounded-lg">
            <form class="p-3 sm:p-6" @submit.prevent="update" enctype="multipart/form-data">
                <h2 class="text-lg font-medium text-slate-900 dark:text-slate-100">
                    {{ lang().label.update }} {{ props.title }}
                </h2>
                <div class="my-6 space-y-4">
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
                            :disabled=true
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
                        <InputLabel for="project_id" :value="lang().label.project_id" />
                        <Select
                            id="project_id"
                            v-model="form.project_id"
                            :options="formattedProjects"
                            optionLabel="display"
                            optionValue="id"
                            filter
                            checkmark
                            :disabled="[2, 3].includes(props.application.status_id)"
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
                        <InputLabel for="files" :value="lang().label.files" />
                        <FileUpload
                            name="files[]"
                            :auto="false"
                            :accept="'application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'"
                            :multiple="true"
                            v-model="form.files"
                            @select="onFileChange"
                            :file-limit="4"
                            :custom-upload="true"
                            :show-upload-button="false"
                            :chooseLabel="lang().label.choose"
                            :uploadLabel="lang().label.upload"
                            :cancelLabel="lang().label.cancel"
                            @clear="onClearFiles"
                            :error="form.errors.files"
                        >
                            <template #content="{ files, uploadedFiles, removeUploadedFileCallback, messages }">
                                <div class="flex flex-col gap-8 pt-4">
                                    <Message v-for="message of messages" :key="message" :class="{ 'mb-8': !files.length && !uploadedFiles.length}" severity="error">
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
                                    <div v-if="form.old_files.length > 0">
                                        <div class="flex flex-wrap gap-4">
                                            <div v-for="(file, index) in form.old_files" :key="file.name + file.type + file.size" class="p-8 rounded-border flex flex-col border border-surface items-center gap-4">
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
                                <div v-if="form.old_files.length === 0" class="flex items-center justify-center flex-col">
                                    <i class="pi pi-cloud-upload !border-2 !rounded-full !p-8 !text-4xl !text-muted-color" />
                                    <p class="mt-6 mb-0">{{ lang().label.drag_and_drop }}</p>
                                </div>
                            </template>
                        </FileUpload>


                        <InputError class="mt-2" :message="form.errors.files" />
                    </div>
                </div>
                <div class="flex justify-start">
                    <BackLink :href="route('application.index')" />
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
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import Select from 'primevue/select';
import { Head, useForm } from "@inertiajs/vue3";
import {computed, watchEffect} from "vue";
import InputText from "primevue/inputtext";
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
    application: Object,
    breadcrumbs: Object,
    projects: Array,
    users: Array,
    types: Array,
    files: Array
});

const emit = defineEmits(["close"]);

const form = useForm({
    title: "",
    project_id: "",
    type: "",
    files: [],
    old_files: []
});

watchEffect(() => {
    form.errors = {};
    form.title = props.application.title;
    form.project_id = props.application.project_id;
    form.files = [];
    form.old_files = props.files;
    form.type = props.application.type;
});

const allowedFileTypes = [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
];

const update = () => {
    form.post(route("application.update", props.application?.id), {
    });
};

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
            form.files = [...form.files, ...newFiles];
        }
    }
};

const onClearFiles = () => {
    form.files = [];
};
const removeUploadedFile = (index) => {
    form.old_files.splice(index, 1);
};

const getFileIcon = (fileType) => {
    if (fileType === 'application/pdf') {
        return 'pi pi-file-pdf';
    } else if (fileType === 'application/msword' || fileType === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
        return 'pi pi-file-word';
    } else if (fileType === 'application/vnd.ms-excel' || fileType === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
        return 'pi pi-file-excel';
    }
    return 'pi pi-file';
};

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
