<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { useForm } from "@inertiajs/vue3";
import { Link } from '@inertiajs/vue3';
import { watchEffect } from "vue";
import { Head } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import MultiSelect from "primevue/multiselect";
import InputText from "primevue/inputtext";
import BackLink from "@/Components/BackLink.vue";

const props = defineProps({
    show: Boolean,
    departments: Array,
    title: String,
    breadcrumbs: Object,
});

const emit = defineEmits(["close"]);

const form = useForm({
    name: "",
    departments: [],
});

const create = () => {
    form.post(route("positions.store"), {
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
        <div class="space-y-4 bg-white dark:bg-slate-800 shadow sm:rounded-lg">
            <form class="p-6" @submit.prevent="create">
                <h2
                    class="text-lg font-medium text-slate-900 dark:text-slate-100"
                >
                    {{ lang().label.create }} {{ props.title }}
                </h2>
                <div class="my-6 space-y-4">
                    <div class="form-group mb-3">
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
                    <div class="form-group mb-3">
                        <InputLabel for="name" :value="lang().label.departments" />
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
                        />
                        <InputError class="mt-2" :message="form.errors.departments" />
                    </div>



                </div>
                <div class="flex justify-start">
                    <BackLink :href="route('positions.index')"/>
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
        </div>
    </AuthenticatedLayout>

</template>

