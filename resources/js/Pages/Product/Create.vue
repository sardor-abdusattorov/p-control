<script setup>
import { watchEffect } from "vue";
import { Head, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import InputText from "primevue/inputtext";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import BackLink from "@/Components/BackLink.vue";
import {Textarea} from "primevue";
import ToggleButton from 'primevue/togglebutton';
import Select from "primevue/select";

const props = defineProps({
    show: Boolean,
    title: String,
    brands: Object,
    users: Object,
    categories: Object,
    breadcrumbs: Object,
});

const form = useForm({
    title: '',
    description: '',
    serial_number: '',
    inventory_number: '',
    parameters: '',
    category_id: '',
    brand_id: '',
    user_id: '',
    sort: 10,
    status: true,
});

const create = () => {
    form.post(route("products.store"), {
        forceFormData: true,
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
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs" />

        <section class="space-y-4 bg-white dark:bg-slate-800 shadow sm:rounded-lg">
            <form class="p-6" @submit.prevent="create" enctype="multipart/form-data">
                <h2 class="text-lg font-medium text-slate-900 dark:text-slate-100">
                    {{ lang().label.create }} {{ props.title }}
                </h2>

                <div class="my-6 space-y-4">
                    <div class="flex flex-wrap -mx-4">

                        <div class="w-full md:w-2/3 px-4 mb-4">
                            <div class="form-group mb-5">
                                <InputLabel for="title" :value="lang().label.title" />
                                <InputText
                                    id="title"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.title"
                                    required
                                    :placeholder="lang().placeholder.name"
                                    :error="form.errors.title"
                                />
                                <InputError class="mt-2" :message="form.errors.title" />
                            </div>

                            <div class="form-group mb-5">
                                <InputLabel for="serial_number" :value="lang().label.serial_number" />
                                <InputText
                                    id="serial_number"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.serial_number"
                                    required
                                    :placeholder="lang().placeholder.serial_number"
                                    :error="form.errors.serial_number"
                                />
                                <InputError class="mt-2" :message="form.errors.serial_number" />
                            </div>

                            <div class="form-group mb-5">
                                <InputLabel for="inventory_number" :value="lang().label.inventory_number" />
                                <InputText
                                    id="inventory_number"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.inventory_number"
                                    :placeholder="lang().placeholder.inventory_number"
                                    :error="form.errors.inventory_number"
                                />
                                <InputError class="mt-2" :message="form.errors.inventory_number" />
                            </div>

                            <div class="form-group mb-5">
                                <InputLabel for="parameters" :value="lang().label.parameters" />
                                <Textarea
                                    id="parameters"
                                    v-model="form.parameters"
                                    :placeholder="lang().label.parameters"
                                    autoResize
                                    rows="4"
                                    class="mt-1 w-full"
                                />
                                <InputError class="mt-2" :message="form.errors.parameters" />
                            </div>

                            <div class="form-group mb-5">
                                <InputLabel for="description" :value="lang().label.description" />
                                <Textarea
                                    id="description"
                                    v-model="form.description"
                                    :placeholder="lang().label.description"
                                    autoResize
                                    rows="6"
                                    class="mt-1 w-full"
                                />
                                <InputError class="mt-2" :message="form.errors.description" />
                            </div>

                        </div>

                        <div class="w-full md:w-1/3 px-4 mb-4">

                            <div class="form-group mb-5">
                                <InputLabel for="category_id" :value="lang().label.category_id" />
                                <Select
                                    v-model="form.category_id"
                                    :options="categories"
                                    optionLabel="title"
                                    optionValue="id"
                                    filter
                                    checkmark
                                    :highlightOnSelect="false"
                                    :placeholder="lang().label.category_id"
                                    class="w-full"
                                >
                                    <template #option="slotProps">
                                        <div class="flex items-center space-x-2">
                                            <img
                                                v-if="slotProps.option.image_url"
                                                :src="slotProps.option.image_url"
                                                :alt="slotProps.option.title"
                                                class="w-10 h-10 rounded-full object-contain"
                                            />
                                            <span>{{ slotProps.option.title }}</span>
                                        </div>
                                    </template>
                                </Select>

                                <InputError class="mt-2" :message="form.errors.category_id" />
                            </div>

                            <div class="form-group mb-5">
                                <InputLabel for="brand_id" :value="lang().label.brand_id" />
                                <Select
                                    v-model="form.brand_id"
                                    :options="brands"
                                    optionLabel="title"
                                    optionValue="id"
                                    filter
                                    checkmark
                                    :highlightOnSelect="false"
                                    :placeholder="lang().label.brand_id"
                                    class="w-full"
                                >
                                    <template #option="slotProps">
                                        <div class="flex items-center space-x-2">
                                            <img
                                                v-if="slotProps.option.image_url"
                                                :src="slotProps.option.image_url"
                                                :alt="slotProps.option.title"
                                                class="w-10 h-10 rounded-full object-contain"
                                            />
                                            <span>{{ slotProps.option.title }}</span>
                                        </div>
                                    </template>
                                </Select>

                                <InputError class="mt-2" :message="form.errors.brand_id" />
                            </div>

                            <div class="form-group mb-5">
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
                                    :pt="{
                                option: { class: 'custom-option' },
                                dropdown: { style: { maxWidth: '300px' } },
                                overlay: { class: 'parent-wrapper-class' }
                            }"
                                />
                                <InputError class="mt-2" :message="form.errors.user_id" />
                            </div>

                            <!-- Sort -->
                            <div class="form-group mb-5">
                                <InputLabel for="sort" :value="lang().label.sort" />
                                <InputText
                                    id="sort"
                                    type="number"
                                    v-model="form.sort"
                                    class="mt-1 block w-full"
                                    :placeholder="lang().placeholder.sort"
                                    :error="form.errors.sort"
                                />
                                <InputError class="mt-2" :message="form.errors.sort" />
                            </div>

                            <!-- Image -->
                            <div class="form-group">
                                <InputLabel for="status" :value="lang().label.status" />
                                <ToggleButton
                                    v-model="form.status"
                                    class="w-auto"
                                    :onLabel="lang().status.active"
                                    :offLabel="lang().status.disable"
                                />
                                <InputError class="mt-2" :message="form.errors.status" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-start">
                    <BackLink :href="route('products.index')" />
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
