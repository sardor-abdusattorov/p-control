<template>
    <Head :title="props.title"/>
    <AuthenticatedLayout>
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs"/>
        <section class="space-y-4 bg-white dark:bg-slate-800 shadow sm:rounded-lg">
            <form class="p-3 sm:p-6" @submit.prevent="create" enctype="multipart/form-data">
                <h2
                    class="text-lg font-medium text-slate-900 dark:text-slate-100"
                >
                    {{ lang().label.add }} {{ props.title }}
                </h2>
                <div class="my-6 space-y-4">
                    <div class="form-group mb-3">
                        <InputLabel for="name" :value="lang().label.name" />
                        <InputText
                            id="name"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.name"
                            required
                            :placeholder="lang().placeholder.name"
                            :error="form.errors.name"
                        />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>
                    <div class="form-group mb-3">
                        <InputLabel for="email" :value="lang().label.email" />
                        <InputText
                            id="email"
                            type="email"
                            class="mt-1 block w-full"
                            v-model="form.email"
                            :placeholder="lang().placeholder.email"
                            :error="form.errors.email"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>
                    <div class="form-group mb-3">
                        <InputLabel
                            for="password"
                            :value="lang().label.password"
                        />
                        <InputText
                            id="password"
                            type="password"
                            class="mt-1 block w-full"
                            v-model="form.password"
                            :placeholder="lang().placeholder.password"
                            :error="form.errors.password"
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.password"
                        />
                    </div>
                    <div class="form-group mb-3">
                        <InputLabel
                            for="password_confirmation"
                            :value="lang().label.password_confirmation"
                        />
                        <InputText
                            id="password_confirmation"
                            type="password"
                            class="mt-1 block w-full"
                            v-model="form.password_confirmation"
                            :placeholder="
                                lang().placeholder.password_confirmation
                            "
                            :error="form.errors.password_confirmation"
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.password_confirmation"
                        />
                    </div>
                    <div class="form-group mb-3">
                        <InputLabel for="role" :value="lang().label.role" />
                        <Select
                            id="role"
                            class="mt-1 block w-full"
                            v-model="form.role"
                            filter
                            checkmark
                            :highlightOnSelect="false"
                            :options="roles"
                            optionLabel="name"
                            optionValue="id"
                            :placeholder="lang().placeholder.select_role"
                            :pt="{
                                option: { class: 'custom-option' },
                                dropdown: { style: { maxWidth: '300px' } },
                                overlay: { class: 'parent-wrapper-class' }
                            }"
                        >
                        </Select>
                        <InputError class="mt-2" :message="form.errors.role" />
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
                        <InputLabel for="department_id" :value="lang().label.department_id" />
                        <Select
                        v-model="form.department_id"
                        :options="departments"
                        optionLabel="name"
                        optionValue="id"
                        filter
                        showClear
                        checkmark
                        :highlightOnSelect="false"
                        :placeholder="lang().label.select_departments"
                        class="w-full"
                        @change="onDepartmentChange"
                        :pt="{
                                option: { class: 'custom-option' },
                                dropdown: { style: { maxWidth: '300px' } },
                                overlay: { class: 'parent-wrapper-class' }
                            }"
                        />
                        <InputError class="mt-2" :message="form.errors.department_id" />
                    </div>

                    <div class="form-group mb-3">
                        <InputLabel for="position_id" :value="lang().label.position_id" />
                        <Select
                        v-model="form.position_id"
                        :options="filteredPositions"
                        optionLabel="name"
                        optionValue="id"
                        placeholder="Select Position"
                        class="w-full"
                        :disabled="!form.department_id"
                        checkmark
                        :highlightOnSelect="false"
                        :pt="{
                                option: { class: 'custom-option' },
                                dropdown: { style: { maxWidth: '300px' } },
                                overlay: { class: 'parent-wrapper-class' }
                            }"
                        />
                        <InputError class="mt-2" :message="form.errors.position_id" />
                    </div>

                    <div class="form-group mb-3">
                        <InputLabel for="telegram_id" :value="lang().label.telegram_id" />
                        <InputText
                            id="telegram_id"
                            type="text"
                            class="w-full mt-1"
                            v-model="form.telegram_id"
                            :placeholder="lang().placeholder.telegram_id"
                        />
                        <InputError class="mt-2" :message="form.errors.telegram_id" />
                    </div>

                    <div class="form-group mb-3">
                        <InputLabel for="status" :value="lang().label.status" />
                        <Select
                            v-model="form.status"
                            :options="statuses"
                            optionLabel="label"
                            optionValue="id"
                            :placeholder="lang().placeholder.select_status"
                            class="w-full"
                            checkmark
                            :highlightOnSelect="false"
                            :pt="{
                                option: { class: 'custom-option' },
                                dropdown: { style: { maxWidth: '300px' } },
                                overlay: { class: 'parent-wrapper-class' }
                            }"
                        />
                        <InputError class="mt-2" :message="form.errors.status" />
                    </div>

                    <div class="form-group">
                        <InputLabel for="image" :value="lang().label.image" />
                        <div class="upload-container flex flex-col items-start">
                            <FileUpload
                                name="image"
                                v-model="form.image"
                                mode="basic"
                                :accept="'image/*'"
                                :maxFileSize="15728640"
                                @input="handleImageUpload"
                            >
                            </FileUpload>
                        </div>
                        <InputError class="mt-2" :message="form.errors.image" />
                    </div>

                </div>
                <div class="flex justify-start">
                    <BackLink :href="route('user.index')"/>
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
import Select from 'primevue/select';
import {Head, useForm} from "@inertiajs/vue3";
import {computed, watchEffect} from "vue";
import InputText from "primevue/inputtext";
import MultiSelect from "primevue/multiselect";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import BackLink from "@/Components/BackLink.vue";
import FileUpload from 'primevue/fileupload';

const props = defineProps({
    show: Boolean,
    title: String,
    roles: Object,
    breadcrumbs: Object,
    users: Array,
    departments: Array,
    positions: Array,
    statuses: Array,
});

const emit = defineEmits(["close"]);

const form = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
    role: "manager",
    department_id: "",
    position_id: "",
    telegram_id: "",
    recipients: "",
    image: null,
    status: 1,
});

const create = () => {
    form.post(route("user.store"), {
    });
};

watchEffect(() => {
    if (props.show) {
        form.errors = {};
    }
});

const filteredPositions = computed(() => {
    return form.department_id
        ? props.positions.filter(position => position.department_id === form.department_id)
        : [];
});

const onDepartmentChange = () => {
    form.position_id = null;
};

const handleImageUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.image = file;
    } else {
        form.image = null;
    }
};

</script>

