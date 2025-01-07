<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputText from "primevue/inputtext";
import {Head, useForm} from "@inertiajs/vue3";
import { watchEffect } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import Select from 'primevue/select';
import BackLink from "@/Components/BackLink.vue";

const props = defineProps({
    show: Boolean,
    title: String,
    breadcrumbs: Object,
    statusOptions: Object,
});

const form = useForm({
    name: "",
    status: 1,
});

const create = () => {
    form.post(route("status.store"), {
    });
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
                <div class="my-6 space-y-4">
                    <div class="flex flex-wrap -mx-4">
                        <div class="w-full md:w-1/2 px-4 mb-4">
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

                        <div class="w-full md:w-1/2 px-4 mb-4">
                            <div class="form-group">
                                <InputLabel for="status" :value="lang().label.status" />
                                <Select
                                    checkmark
                                    :highlightOnSelect="false"
                                    v-model="form.status"
                                    :options="statusOptions"
                                    optionLabel="label"
                                    optionValue="value"
                                    class="w-full"
                                />
                                <InputError class="mt-2" :message="form.errors.status" />
                            </div>
                        </div>
                    </div>

                </div>
                <div class="flex justify-start">
                    <BackLink :href="route('status.index')"/>
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
