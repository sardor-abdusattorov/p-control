<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import Select from 'primevue/select';
import { Head, useForm } from "@inertiajs/vue3";
import { watchEffect } from "vue";
import InputText from "primevue/inputtext";
import MultiSelect from "primevue/multiselect";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import BackLink from "@/Components/BackLink.vue";
import FileUpload from 'primevue/fileupload';
import {Message} from "primevue";

const props = defineProps({
    show: Boolean,
    application: Object,
    title: String,
    breadcrumbs: Object,
    recipients: Array,
    projects: Array,
    files: Array,
    users: Array
});

const emit = defineEmits(["close"]);

const form = useForm({
    title: "",
    project_id: "",
    recipients: [],
    files: [],
});

watchEffect(() => {
    form.errors = {};
    form.recipients = props.recipients.map(recipient => recipient.recipient_id)
    form.title = props.application.title
    form.project_id = props.application.project_id
    form.files = props.files;
});

</script>

<template>
    <Head :title="props.title" />
    <AuthenticatedLayout>
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs" />
        <section class="space-y-4 bg-white dark:bg-slate-800 shadow sm:rounded-lg">
            <form class="p-6" aria-disabled="true">
                <h2 class="text-lg font-medium text-slate-900 dark:text-slate-100">
                    {{ lang().label.preview }} {{ props.title }}
                </h2>
                <div class="my-6 space-y-4">
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
                            readonly
                        />
                        <InputError class="mt-2" :message="form.errors.title" />
                    </div>

                    <div class="form-group mb-3">
                        <InputLabel for="project_id" :value="lang().label.project_id" />
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
                        <InputError class="mt-2" :message="form.errors.project_id" />
                    </div>

                    <div class="form-group mb-3">
                        <InputLabel for="recipients" :value="lang().label.recipients" />
                        <MultiSelect
                            v-model="form.recipients"
                            display="chip"
                            optionValue="id"
                            :options="props.users"
                            optionLabel="name"
                            filter
                            :placeholder="lang().placeholder.select_recipients"
                            :maxSelectedLabels="8"
                            class="w-full"
                            :disabled="true"
                        />
                        <InputError class="mt-2" :message="form.errors.recipients" />
                    </div>

                    <div class="form-group mb-3">
                        <InputLabel for="files" :value="lang().label.files" />
                        <FileUpload
                            name="files[]"
                            :auto="false"
                            :multiple="true"
                            :disabled="true"
                            v-model="form.files"
                            :file-limit="4"
                            :custom-upload="true"
                            :show-upload-button="false"
                            :error="form.errors.files"
                            :chooseLabel="lang().label.choose"
                            :uploadLabel="lang().label.upload"
                            :cancelLabel="lang().label.cancel"
                        >
                        <template #content="{ files, uploadedFiles, removeUploadedFileCallback, messages }">
                            <div class="flex flex-col gap-8 pt-4">
                                <Message v-for="message of messages" :key="message" :class="{ 'mb-8': !files.length && !uploadedFiles.length}" severity="error">
                                    {{ message }}
                                </Message>
                                <div v-if="form.files.length > 0">
                                    <div class="flex flex-wrap gap-4">
                                        <div v-for="(file, index) in form.files" :key="file.name + file.type + file.size" class="p-8 rounded-border flex flex-col border border-surface items-center gap-4">
                                            <div>
                                                <i class="pi pi-file" style="font-size: 32px;"></i>
                                            </div>
                                            <span class="font-semibold text-ellipsis max-w-60 whitespace-nowrap overflow-hidden">{{ file.name }}</span>
                                            <a
                                                :href="file.original_url"
                                                target="_blank"
                                                class="p-button p-component p-button-outlined p-button-success">
                                                <span class="p-button-label">{{lang().label.download}}</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                        </FileUpload>
                        <InputError class="mt-2" :message="form.errors.files" />
                    </div>
                </div>
                <div class="flex justify-start">
                    <BackLink :href="route('application.index')" />
                </div>
            </form>
        </section>
    </AuthenticatedLayout>
</template>

