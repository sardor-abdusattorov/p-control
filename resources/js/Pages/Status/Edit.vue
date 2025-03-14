<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import {Head, Link, useForm} from "@inertiajs/vue3";
import {watchEffect} from "vue";
import Select from "primevue/select";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import BackLink from "@/Components/BackLink.vue";

const props = defineProps({
    title: String,
    status: Object,
    statusOptions: Object,
    breadcrumbs: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    name: "",
    status: '',
});

const update = () => {
    form.put(route("status.update", props.status?.id), {
    });
};


watchEffect(() => {
    form.errors = {};
    form.name = props.status?.name;
    form.status = props.status?.status;
});

</script>

<template>
    <Head :title="props.title"/>
    <AuthenticatedLayout>
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs"/>

        <section class="space-y-4 bg-white dark:bg-slate-800 shadow sm:rounded-lg">
            <form class="p-3 sm:p-6" @submit.prevent="update">
                <h2
                    class="text-lg font-medium text-slate-900 dark:text-slate-100"
                >
                    {{ lang().label.edit }} {{ props.title }}
                </h2>
                <div class="my-6 space-y-4">

                    <div class="flex flex-wrap -mx-4">
                        <div class="w-full md:w-1/2 px-4 mb-4">
                            <InputLabel for="name" :value="lang().label.title" />
                            <TextInput
                                id="name"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.name"
                                required
                                :placeholder="lang().label.name"
                                :error="form.errors.name"
                            />
                            <InputError class="mt-2" :message="form.errors.name" />
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
                <div class="flex justify-start px-4">
                    <BackLink :href="route('status.index')"/>
                    <PrimaryButton
                        class="ml-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="update"
                    >
                        {{
                            form.processing ? lang().button.save + "..." : lang().button.save
                        }}
                    </PrimaryButton>
                </div>
            </form>
        </section>
    </AuthenticatedLayout>
</template>
