<template>
    <AuthenticatedLayout :title="props.title">
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs"/>

        <section class="space-y-4 bg-white dark:bg-slate-800 shadow sm:rounded-lg">
            <form class="p-3 sm:p-6" @submit.prevent="create">
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
                        <Textarea
                            id="title"
                            v-model="form.title"
                            :placeholder="lang().label.contract_description"
                            autoResize
                            rows="4"
                            class="mt-1 w-full"
                        />
                        <InputError class="mt-2" :message="form.errors.title" />
                    </div>

                    <div class="form-group mb-5">
                        <InputLabel for="project_year" :value="lang().label.year" />
                        <InputNumber
                            id="project_year"
                            v-model="projectYear"
                            class="mt-1 block w-full"
                            :useGrouping="false"
                        />
                    </div>

                    <div class="form-group mb-5">
                        <InputLabel for="project_id" :value="lang().label.project" />
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
                            <InputLabel for="contact_id" :value="lang().section.contact" />
                            <div class="flex gap-2">
                                <Select
                                    id="contact_id"
                                    v-model="form.contact_id"
                                    :options="formattedContacts"
                                    optionLabel="display"
                                    optionValue="id"
                                    filter
                                    showClear
                                    checkmark
                                    class="w-full"
                                    :placeholder="lang().label.select_contact"
                                />
                                <Button outlined @click="showContactModal = true">
                                    <i class="pi pi-plus mr-2"></i>
                                    {{ lang().label.add }}
                                </Button>

                            </div>
                            <InputError class="mt-2" :message="form.errors.contact_id" />
                </div>

                    <div class="form-group mb-5">
                        <InputLabel for="transaction_type" :value="lang().label.transaction_type" />
                        <Select
                            id="transaction_type"
                            v-model="form.transaction_type"
                            :options="transaction_types"
                            optionLabel="label"
                            optionValue="id"
                            class="w-full"
                            checkmark
                            :placeholder="lang().placeholder.select_transaction_type"
                            :highlightOnSelect="false"
                        />
                        <InputError class="mt-2" :message="form.errors.transaction_type" />
                    </div>

                    <div class="form-group mb-3" v-if="form.transaction_type !== 2">
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

                    <div class="form-group mb-5" v-if="form.transaction_type !== 2">
                        <InputLabel for="application_id" :value="lang().label.application_id" />
                        <Select
                            id="application_id"
                            v-model="form.application_id"
                            :options="filteredApplications"
                            optionLabel="title"
                            optionValue="id"
                            optionGroupLabel="label"
                            optionGroupChildren="items"
                            filter
                            showClear
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
                                            <div v-for="(file, index) in files" :key="file.name + file.type + file.size" class="p-8 rounded-border flex flex-col border border-surface items-center gap-4">
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

        <ContactCreate
            :show="showContactModal"
            :categories="props.categories"
            :countries="props.countries"
            :statuses="props.statuses"
            @close="showContactModal = false"
        />

    </AuthenticatedLayout>
</template>

<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {Head, router, useForm, usePage} from "@inertiajs/vue3";
import {computed, reactive, ref, watch, watchEffect} from "vue";
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
import {Textarea} from "primevue";
import { onMounted } from 'vue';
import ContactCreate from "@/Pages/Contract/ContactCreate.vue";
import axios from "axios";

const props = defineProps({
    show: Boolean,
    title: String,
    breadcrumbs: Object,
    users: Array,
    projects: Array,
    applications: Array,
    currency: Array,
    contacts: Object,
    recipients: Array,
    application_types: Object,
    transaction_types: Array,
    categories: Object,
    countries: Object,
    statuses: Object,
});

const allowedFileTypes = [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
];

const showContactModal = ref(false);
const projectYear = ref(new Date().getFullYear());
const groupedProjects = ref([]);

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

const form = useForm({
    contract_number: "",
    files: [],
    recipients: [],
    title: "",
    project_id: "",
    application_id: "",
    application_type: 1,
    transaction_type: 1,
    currency_id: 1,
    budget_sum: 0,
    deadline: new Date(new Date().getFullYear(), 11, 31),
    contact_id: null,
});

const filteredApplications = computed(() => {
    if (!form.application_type || !props.applications) return [];

    return props.applications
        .map(group => {
            const filteredItems = group.items
                .filter(app => app.type === form.application_type);

            return {
                label: group.label,
                items: filteredItems,
            };
        })
        .filter(group => group.items.length > 0);
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

onMounted(() => {
    if (!form.recipients.length && props.recipients.length) {
        form.recipients = props.recipients.map(r => r.recipient_id);
    }
});

watchEffect(() => {
    form.errors = {};
});


const formattedContacts = computed(() => {
    return props.contacts.map(contact => ({
        id: contact.id,
        firstname: contact.firstname || '',
        lastname: contact.lastname || '',
        email: contact.email || '',
        display: `${contact.firstname} ${contact.lastname} – ${contact.email}`.trim()
    }))
})

</script>

<style>

.p-inputnumber-input{
    width: 100% !important;
}
</style>
