<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {Head, useForm} from "@inertiajs/vue3";
import {watchEffect} from "vue";
import Select from "primevue/select";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import DatePicker from "primevue/datepicker";
import InputNumber from "primevue/inputnumber";
import InputText from "primevue/inputtext";
import BackLink from "@/Components/BackLink.vue";

const props = defineProps({
    title: String,
    project: Object,
    users: Array,
    breadcrumbs: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    project_number: "",
    title: "",
    budget_sum: "",
    project_year: "",
    deadline: "",
    user_id: "",
});

const update = () => {
    form.put(route("projects.update", props.project?.id), {
    });
};


watchEffect(() => {
    form.errors = {};
    form.project_number = props.project?.project_number;
    form.title = props.project?.title;
    form.budget_sum = props.project?.budget_sum;
    form.deadline = props.project?.deadline ? new Date(props.project.deadline) : null;
    form.project_year = props.project?.project_year ? new Date(props.project.project_year) : null;
    form.user_id = props.project?.user_id;
});

</script>

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
                <div class="my-6 flex flex-wrap ">

                    <div class="w-full xl:w-2/3 px-4">

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
                            <InputText
                                id="title"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.title"
                                :placeholder="lang().label.title"
                                :error="form.errors.title"
                            />
                            <InputError class="mt-2" :message="form.errors.title" />
                        </div>

                        <div class="form-group mb-3">
                            <InputLabel for="project_year" :value="lang().label.project_year" />
                            <DatePicker
                                v-model="form.project_year"
                                view="year"
                                dateFormat="yy"
                                showIcon
                                showButtonBar
                                class="w-full"
                                :manualInput="false"
                            />
                            <InputError class="mt-2" :message="form.errors.project_year" />
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
                                yearRange="2020:2030"
                                dateFormat="dd/mm/yy"
                                :manualInput="false"
                            />
                            <InputError class="mt-2" :message="form.errors.deadline" />
                        </div>
                    </div>

                    <div class="w-full xl:w-1/3 px-4">
                        <div class="form-group mb-3">
                            <InputLabel for="user_id" :value="lang().label.user_id" />
                            <Select
                                v-model="form.user_id"
                                :options="users"
                                optionLabel="name"
                                optionValue="id"
                                filter
                                checkmark
                                :highlightOnSelect="false"
                                :placeholder="lang().label.select_user"
                                class="w-full"
                            />
                            <InputError class="mt-2" :message="form.errors.user_id" />
                        </div>

                        <div class="form-group mb-3">
                            <InputLabel for="budget_sum" :value="lang().label.budget_sum" />
                            <InputNumber
                                id="budget_sum"
                                v-model="form.budget_sum"
                                class="mt-1 block w-full"
                                mode="currency"
                                currency="UZS"
                                locale="uz-UZ"
                                :placeholder="lang().label.budget_sum"
                                :error="form.errors.budget_sum"
                            />

                            <InputError class="mt-2" :message="form.errors.budget_sum" />
                        </div>
                    </div>

                </div>
                <div class="flex justify-start px-4">
                    <BackLink :href="route('projects.index')"/>
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
