<template>
    <AuthenticatedLayout :title="props.title">
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs" />
        <section class="space-y-4 bg-white dark:bg-slate-800 shadow sm:rounded-lg">
            <form class="p-3 sm:p-6" @submit.prevent="update">

                <div class="form-group mb-3">
                    <InputLabel for="title" :value="lang().label.title" />
                    <InputText
                        id="title"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.title"
                        :placeholder="lang().label.title"
                    />
                    <InputError class="mt-2" :message="form.errors.title" />
                </div>

                <div class="form-group mb-3">
                    <InputLabel for="year" :value="lang().label.year" />
                    <InputNumber
                        id="year"
                        class="mt-1 block w-full"
                        v-model="form.year"
                        :useGrouping="false"
                        :placeholder="lang().label.year"
                    />
                    <InputError class="mt-2" :message="form.errors.year" />
                </div>

                <div class="form-group mb-3">
                    <InputLabel for="sort" :value="lang().label.sort" />
                    <InputNumber
                        id="sort"
                        class="mt-1 block w-full"
                        v-model="form.sort"
                        :placeholder="lang().label.sort"
                    />
                    <InputError class="mt-2" :message="form.errors.sort" />
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

                <div class="flex justify-start">
                    <BackLink :href="route('project-categories.index')" />
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
import InputNumber from 'primevue/inputnumber';
import { useForm } from "@inertiajs/vue3";
import { watchEffect } from "vue";
import InputText from "primevue/inputtext";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import BackLink from "@/Components/BackLink.vue";

const props = defineProps({
    show: Boolean,
    title: String,
    breadcrumbs: Object,
    category: Object,
    statuses: Array,
});

const form = useForm({
    title: "",
    year: null,
    sort: 0,
    status: "",
});

const update = () => {
    form.put(route("project-categories.update", props.category?.id));
};

watchEffect(() => {
    form.errors = {};
    form.title = props.category?.title;
    form.year = props.category?.year;
    form.sort = props.category?.sort;
    form.status = props.category?.status;
});
</script>
