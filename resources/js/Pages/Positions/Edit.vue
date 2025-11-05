<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import MultiSelect from "primevue/multiselect";
import { watch } from 'vue';
import InputText from "primevue/inputtext";
import BackLink from "@/Components/BackLink.vue";

const props = defineProps({
    title: String,
    position: Object,
    departments: Array,
    breadcrumbs: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    name: "",
    departments: [],
});

watch(() => props.position, (newPosition) => {
    if (newPosition) {
        form.name = newPosition.name;
        form.departments = newPosition.departments.map(dep => dep.department_id);
    }
}, { immediate: true });

const update = () => {
    form.put(route("positions.update", props.position?.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AuthenticatedLayout :title="props.title">
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs"/>
        <div class="space-y-4 bg-white dark:bg-slate-800 shadow sm:rounded-lg">
            <form class="p-3 sm:p-6" @submit.prevent="update">
                <h2 class="text-lg font-medium text-slate-900 dark:text-slate-100">
                    {{ lang().label.edit }} {{ props.title }}
                </h2>
                <div class="my-6 space-y-4">
                    <div class="form-group mb-3">
                        <InputLabel for="name" :value="lang().label.name" />
                        <InputText
                            id="name"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.name"
                            :placeholder="lang().placeholder.name"
                            :error="form.errors.name"
                        />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div class="form-group mb-3">
                        <InputLabel for="departments" :value="lang().label.departments"/>
                        <MultiSelect
                            v-model="form.departments"
                            display="chip"
                            optionValue="id"
                            :options="props.departments"
                            optionLabel="name"
                            filter
                            :placeholder= "lang().label.select_departments"
                            :maxSelectedLabels="6"
                            class="w-full"
                            :pt="{
                                option: { class: 'custom-option' },
                                dropdown: { style: { maxWidth: '300px' } },
                                overlay: { class: 'parent-wrapper-class' }
                            }"
                        />
                        <InputError class="mt-2" :message="form.errors.departments"/>
                    </div>
                </div>
                <div class="flex justify-start">
                    <BackLink :href="route('positions.index')"/>
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
        </div>
    </AuthenticatedLayout>
</template>
