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
                        <InputLabel for="name" :value="lang().label.name"/>
                        <InputText
                            id="name"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.name"
                            :placeholder="lang().label.name"
                            :error="form.errors.name"
                        />
                        <InputError class="mt-2" :message="form.errors.name"/>
                    </div>

                    <div class="form-group mb-5">
                        <InputLabel for="description" :value="lang().label.description"/>

                        <Editor v-model="form.description" editorStyle="height: 320px"/>

                        <InputError class="mt-2" :message="form.errors.name"/>
                    </div>

                    <div class="form-group mb-5">
                        <InputLabel for="due_date" :value="lang().label.due_date"/>
                        <DatePicker
                            id="due_date"
                            v-model="form.due_date"
                            class="mt-1 block w-full"
                            :placeholder="lang().label.due_date"
                            showIcon
                            showButtonBar
                            :monthNavigator="true"
                            :yearNavigator="true"
                            yearRange="2020:2030"
                            :manualInput="false"
                            :minDate="new Date()"
                            dateFormat="dd/mm/yy"
                        />
                        <InputError class="mt-2" :message="form.errors.due_date"/>
                    </div>

                    <div class="form-group mb-5">
                        <InputLabel for="assigned_user" :value="lang().label.assigned_user"/>
                        <Select
                            id="assigned_user"
                            v-model="form.assigned_user"
                            :options="users"
                            optionLabel="name"
                            optionValue="id"
                            filter
                            checkmark
                            :highlightOnSelect="false"
                            :placeholder="lang().label.assigned_user"
                            :filterPlaceholder="lang().placeholder.search"
                            :error="form.errors.assigned_user"
                            class="w-full"
                        />

                        <InputError class="mt-2" :message="form.errors.assigned_user"/>
                    </div>

                    <div class="form-group mb-5">
                        <InputLabel for="status" :value="lang().label.status"/>
                        <Select
                            id="status"
                            v-model="form.status"
                            :options="statuses"
                            optionLabel="label"
                            optionValue="id"
                            filter
                            checkmark
                            :highlightOnSelect="false"
                            :filterPlaceholder="lang().placeholder.search"
                            class="w-full"
                            :placeholder="lang().placeholder.select_status"
                            :error="form.errors.status"
                        />
                        <InputError class="mt-2" :message="form.errors.status"/>
                    </div>

                    <div class="form-group mb-5">
                        <InputLabel for="priority" :value="lang().label.priority"/>
                        <Select
                            id="priority"
                            v-model="form.priority"
                            :options="priorities"
                            optionLabel="label"
                            optionValue="id"
                            class="w-full"
                            :highlightOnSelect="false"
                            checkmark
                            :placeholder="lang().placeholder.priority"
                            :error="form.errors.priority"
                        />
                        <InputError class="mt-2" :message="form.errors.priority"/>
                    </div>

                    <div class="form-group mb-5">
                        <InputLabel for="files" :value="lang().label.files"/>
                        <FileUpload
                            name="files[]"
                            :auto="false"
                            :accept="allowedFileTypes.join(',')"
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
                                                    <i class="pi pi-file" style="font-size: 32px;"></i>
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
                                                    <i class="pi pi-file" style="font-size: 32px;"></i>
                                                </div>
                                                <span class="font-semibold text-ellipsis max-w-60 whitespace-nowrap overflow-hidden">{{ file.name }}</span>
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
                    <BackLink :href="route('task.index')"/>
                    <PrimaryButton
                        class="ml-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="update"
                    >
                        {{
                            form.processing
                                ? lang().button.save + "..."
                                : lang().button.save
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
import { Head, useForm } from "@inertiajs/vue3";
import {computed, watchEffect} from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import Select from "primevue/select";
import DatePicker from "primevue/datepicker";
import InputText from "primevue/inputtext";
import BackLink from "@/Components/BackLink.vue";
import Editor from 'primevue/editor';
import FileUpload from "primevue/fileupload";
import Button from "primevue/button";
import {Message} from "primevue";

const props = defineProps({
    show: Boolean,
    title: String,
    breadcrumbs: Object,
    task: Object,
    users: Array,
    projects: Array,
    statuses: Array,
    priorities: Array,
    files: Array
});

const allowedFileTypes = [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'text/plain',
    'application/zip',
    'application/x-rar-compressed',
];

const form = useForm({
    project_id: "",
    name: "",
    description: "",
    assigned_user: "",
    status: "",
    priority: "",
    files: [],
    due_date: "",
});

const update = () => {
    form.post(route("task.update", props.task?.id));
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

watchEffect(() => {
    form.project_id = props.task.project_id
    form.name = props.task.name
    form.description = props.task.description
    form.assigned_user = props.task.assigned_user
    form.status = props.task.status
    form.priority = props.task.priority
    form.files = [];
    form.due_date = props.task?.due_date
        ? new Date(props.task.due_date.split(' ')[0].split('-').reverse().join('-') + 'T' + props.task.due_date.split(' ')[1])
        : null;
});

const formattedProjects = computed(() => {
    return props.projects.map(project => ({
        id: project.id,
        project_number: project.project_number,
        title: project.title,
        display: `${project.project_number}. ${project.title}`
    }));
});

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
