<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { watchEffect, ref } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import Select from "primevue/select";
import DatePicker from "primevue/datepicker";
import InputText from "primevue/inputtext";
import InputNumber from "primevue/inputnumber";
import BackLink from "@/Components/BackLink.vue";
import Textarea from 'primevue/textarea';

const props = defineProps({
    show: Boolean,
    title: String,
    breadcrumbs: Object,
    users: Array,
});

const currentYear = new Date().getFullYear();

const endOfYear = new Date(currentYear, 11, 31);

const form = useForm({
    project_number: "",
    title: "",
    budget_sum: "",
    user_id: "",
    project_year: endOfYear,
    deadline: endOfYear,
});

const filteredUsers = ref(props.users);
const loading = ref(false);

const onFilter = async (event) => {
    loading.value = true;
    const query = event.query;
    filteredUsers.value = props.users.filter((user) =>
        user.name.toLowerCase().includes(query.toLowerCase())
    );
    loading.value = false;
};

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
    <Head :title="props.title"/>
    <AuthenticatedLayout>
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
                            <InputLabel for="project_year" :value="lang().label.project_year" />
                            <DatePicker
                                v-model="form.project_year"
                                view="year"
                                dateFormat="yy"
                                showIcon
                                showButtonBar
                                class="w-full"
                                :manualInput="false"
                                :minDate="new Date()"
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
                                :minDate="new Date()"
                            />
                            <InputError class="mt-2" :message="form.errors.deadline" />
                        </div>
                    </div>

                    <div class="w-full xl:w-1/3">
                        <div class="form-group mb-3">
                            <InputLabel for="user_id" :value="lang().label.responsible_user" />
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
                                :pt="{
                                option: { class: 'custom-option' },
                                dropdown: { style: { maxWidth: '300px' } },
                                overlay: { class: 'parent-wrapper-class' }
                            }"
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

<style scoped>
.p-inputnumber-input {
    flex: none !important;
    width: 100%;
}
</style>
