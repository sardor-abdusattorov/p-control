<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { useForm } from "@inertiajs/vue3";
import { watchEffect } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import Select from "primevue/select";
import InputText from "primevue/inputtext";
import InputNumber from "primevue/inputnumber";
import BackLink from "@/Components/BackLink.vue";
import Textarea from 'primevue/textarea';

const props = defineProps({
    show: Boolean,
    title: String,
    breadcrumbs: Object,
    categories: Array,
    statuses: Array,
});

const form = useForm({
    project_number: "",
    title: "",
    category_id: null,
    sort: 0,
    status_id: 1,
});

const create = () => {
    form.post(route("projects.store"), {});
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
                <div class="my-6 flex flex-wrap ">

                    <div class="w-full xl:w-2/3 pr-2">

                        <div class="form-group mb-3">
                            <InputLabel for="project_number" :value="lang().label.project_number" />
                            <InputText
                                id="project_number"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.project_number"
                                :placeholder="lang().label.project_number"
                                :error="form.errors.project_number"
                            />
                            <InputError class="mt-2" :message="form.errors.project_number" />
                        </div>

                        <div class="form-group mb-3">
                            <InputLabel for="title" :value="lang().label.title" />
                            <Textarea
                                id="title"
                                type="text"
                                class="mt-1 block w-full"
                                autoResize
                                rows="5"
                                v-model="form.title"
                                :placeholder="lang().label.title"
                                :error="form.errors.title"
                            />
                            <InputError class="mt-2" :message="form.errors.title" />
                        </div>

                        <div class="form-group mb-3">
                            <InputLabel for="category_id" :value="lang().label.category_id" />
                            <Select
                                v-model="form.category_id"
                                :options="categories"
                                optionLabel="title"
                                optionValue="id"
                                filter
                                showClear
                                checkmark
                                :highlightOnSelect="false"
                                :placeholder="lang().placeholder.category_id"
                                class="w-full"
                                :pt="{
                                    option: { class: 'custom-option' },
                                    dropdown: { style: { maxWidth: '300px' } },
                                    overlay: { class: 'parent-wrapper-class' }
                                }"
                            />
                            <InputError class="mt-2" :message="form.errors.category_id" />
                        </div>
                    </div>

                    <div class="w-full xl:w-1/3">
                        <div class="form-group mb-3">
                            <InputLabel for="sort" :value="lang().label.sort" />
                            <InputNumber
                                id="sort"
                                v-model="form.sort"
                                class="mt-1 block w-full"
                                :placeholder="lang().label.sort"
                            />
                            <InputError class="mt-2" :message="form.errors.sort" />
                        </div>

                        <div class="form-group mb-3">
                            <InputLabel for="status_id" :value="lang().label.status" />
                            <Select
                                v-model="form.status_id"
                                :options="statuses"
                                optionLabel="label"
                                optionValue="id"
                                :placeholder="lang().placeholder.select_status"
                                class="w-full"
                                checkmark
                                :highlightOnSelect="false"
                                :pt="{
                                    option: { class: 'custom-option' },
                                    overlay: { class: 'parent-wrapper-class' }
                                }"
                            />
                            <InputError class="mt-2" :message="form.errors.status_id" />
                        </div>
                    </div>

                </div>
                <div class="flex justify-start">
                    <BackLink :href="route('projects.index')"/>
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
