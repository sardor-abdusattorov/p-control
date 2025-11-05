<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import {Head, Link, useForm} from "@inertiajs/vue3";
import { watchEffect } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import Select from "primevue/select";
import InputText from "primevue/inputtext";
import BackLink from "@/Components/BackLink.vue";

const props = defineProps({
    show: Boolean,
    title: String,
    users: Array,
    breadcrumbs: Object,
    statusOptions: Object,
});

const form = useForm({
    name: "",
    head_of_department: '',
    status: 1
});

const create = () => {
    form.post(route("departments.store"), {
    });
};

watchEffect(() => {
    if (props.show) {
        form.errors = {};
    }
});
</script>

<template>
    <AuthenticatedLayout :title="props.title">
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs"/>

        <section class="space-y-4 bg-white dark:bg-slate-800 shadow sm:rounded-lg">
            <form class="p-6" @submit.prevent="create">
                <h2
                    class="text-lg font-medium text-slate-900 dark:text-slate-100"
                >
                    {{ lang().label.create }} {{ props.title }}
                </h2>
                <div class="my-6 space-y-4">
                    <div class="flex flex-wrap -mx-4">
                        <div class="w-full md:w-2/3 px-4 mb-4">
                            <div class="form-group">
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
                        </div>
                        <div class="w-full md:w-1/3 px-4 mb-4">

                            <div class="form-group mb-3">
                                <InputLabel for="head_of_department" :value="lang().label.head_of_department" />
                                <Select
                                    v-model="form.head_of_department"
                                    :options="users"
                                    optionLabel="name"
                                    optionValue="id"
                                    filter
                                    checkmark
                                    :highlightOnSelect="false"
                                    :placeholder="lang().label.select_user"
                                    class="w-full"
                                    :pt="{
                                option: { class: 'custom-option' },
                                dropdown: { style: { maxWidth: '300px' } },
                                overlay: { class: 'parent-wrapper-class' }
                            }"
                                />
                                <InputError class="mt-2" :message="form.errors.head_of_department" />
                            </div>

                            <div class="input-group">
                                <InputLabel for="status" value="Status" />
                                <Select
                                    v-model="form.status"
                                    :options="statusOptions"
                                    optionLabel="label"
                                    optionValue="value"
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
                        </div>
                    </div>

                </div>
                <div class="flex justify-start">
                    <BackLink :href="route('departments.index')"/>
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

