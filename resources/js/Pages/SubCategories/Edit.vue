<template>
    <Head :title="props.title" />
    <AuthenticatedLayout>
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
                        :error="form.errors.title"
                    />
                    <InputError class="mt-2" :message="form.errors.title" />
                </div>

                <div class="form-group mb-3">
                    <InputLabel for="info" :value="lang().label.info" />
                    <Textarea
                        id="info"
                        type="text"
                        class="mt-1 block w-full"
                        autoResize
                        rows="5"
                        v-model="form.info"
                        :placeholder="lang().label.info"
                        :error="form.errors.info"
                    />
                    <InputError class="mt-2" :message="form.errors.info" />
                </div>

                <div class="form-group mb-3">
                    <InputLabel for="category_id" :value="lang().label.contact_categories" />
                    <Select
                        v-model="form.category_id"
                        :options="categories"
                        optionLabel="title"
                        optionValue="id"
                        :placeholder="lang().label.select_category"
                        class="w-full"
                        checkmark
                        :highlightOnSelect="false"
                        :pt="{
                                option: { class: 'custom-option' },
                                dropdown: { style: { maxWidth: '300px' } },
                                overlay: { class: 'parent-wrapper-class' }
                            }"
                    />
                    <InputError class="mt-2" :message="form.errors.category_id" />
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
                    <BackLink :href="route('contact-subcategories.index')" />
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
import Select from 'primevue/select';
import { Head, useForm } from "@inertiajs/vue3";
import {watchEffect} from "vue";
import InputText from "primevue/inputtext";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import BackLink from "@/Components/BackLink.vue";
import Textarea from "primevue/textarea";

const props = defineProps({
    show: Boolean,
    title: String,
    breadcrumbs: Object,
    statuses: Array,
    categories: Object,
    subCategory: Object,
});

const emit = defineEmits(["close"]);

const form = useForm({
    title: "",
    info: "",
    category_id: '',
    status: '',
});

const update = () => {
    form.put(route("contact-subcategories.update", props.subCategory?.id), {
    });
};

watchEffect(() => {
    form.errors = {};
    form.title = props.subCategory?.title;
    form.info = props.subCategory?.info;
    form.category_id = props.subCategory?.category_id;
    form.status = props.subCategory?.status;
});
</script>
