
<template>
    <AuthenticatedLayout :title="props.title">
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs" />
        <section class="space-y-4 bg-white dark:bg-slate-800 shadow sm:rounded-lg">
            <form class="p-3 sm:p-6" @submit.prevent="update" enctype="multipart/form-data">
                <h2 class="text-lg font-medium text-slate-900 dark:text-slate-100 mb-6">
                    {{ lang().label.upload_scan }} {{ props.title }}
                </h2>
                <div class="form-group mb-3">
                    <InputLabel for="files" :value="lang().label.files" />
                    <FileUpload
                        name="files[]"
                        :auto="false"
                        accept="application/pdf"
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

                                <div v-if="files.length > 0 || props.scans?.length > 0">
                                    <div class="flex flex-wrap gap-4">
                                        <!-- Новые загружаемые файлы -->
                                        <div
                                            v-for="(file, index) in files"
                                            :key="'new-' + file.name + file.type + file.size"
                                            class="p-8 rounded-border flex flex-col border border-surface items-center gap-4"
                                            style="width: calc(20% - 0.8rem)"
                                        >
                                            <div>
                                                <i :class="getFileIcon(file.type)" style="font-size: 32px;"></i>
                                            </div>
                                            <span class="font-semibold text-ellipsis max-w-60 whitespace-nowrap overflow-hidden text-center">
                        {{ file.name }}
                    </span>
                                            <Button
                                                icon="pi pi-times"
                                                @click="removeUploadedFile(index)"
                                                outlined
                                                rounded
                                                severity="danger"
                                            />
                                        </div>

                                        <!-- Ранее загруженные сканы -->
                                        <div
                                            v-for="(file, index) in props.scans"
                                            :key="'scan-' + file.id"
                                            class="p-8 rounded-border flex flex-col border border-surface items-center gap-4"
                                            style="width: calc(20% - 0.8rem)"
                                        >
                                            <div>
                                                <i class="pi pi-file-pdf" style="font-size: 32px;"></i>
                                            </div>
                                            <span class="font-semibold text-ellipsis max-w-60 whitespace-nowrap overflow-hidden text-center">
                        {{ file.name }}
                    </span>
                                            <a
                                                :href="file.original_url"
                                                target="_blank"
                                                class="p-button p-component p-button-outlined p-button-success"
                                            >
                                                {{ lang().label.download }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>


                        <template #empty>
                            <div v-if="form.files.length === 0 && props.scans.length === 0" class="flex items-center justify-center flex-col">
                                <i class="pi pi-cloud-upload !border-2 !rounded-full !p-8 !text-4xl !text-muted-color" />
                                <p class="mt-6 mb-0">{{ lang().label.drag_and_drop }}</p>
                            </div>
                        </template>

                    </FileUpload>

                    <InputError class="mt-2" :message="form.errors.files" />
                </div>

                <div class="flex justify-start">
                    <BackLink :href="route('contract.show', { contract: contract.id })" />
                    <PrimaryButton
                        class="ml-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="uploadScan"
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
import { Head, useForm } from "@inertiajs/vue3";
import {watchEffect} from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import BackLink from "@/Components/BackLink.vue";
import FileUpload from 'primevue/fileupload';
import Button from "primevue/button";
import {Message} from "primevue";

const props = defineProps({
    show: Boolean,
    title: String,
    contract: Object,
    breadcrumbs: Object,
    scans: Array,
});

const emit = defineEmits(["close"]);

const form = useForm({
    files: [],
});


watchEffect(() => {
    form.errors = {};
    form.files = [];
});

const uploadScan = () => {
    form.post(route("contract.upload-scan", props.contract?.id), {
    });
};

const formatDate = (dateString) => {
    if (!dateString) return "-";
    const date = new Date(dateString);
    return new Intl.DateTimeFormat("ru-RU", { dateStyle: "short", timeStyle: "short" }).format(date);
};


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
    return 'pi pi-file-pdf';
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
