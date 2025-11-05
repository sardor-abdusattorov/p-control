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
                        <!-- Title -->
                        <div class="w-full md:w-2/3 px-4 mb-4">
                            <div class="form-group">
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
                        </div>

                        <!-- Sort and Image -->
                        <div class="w-full md:w-1/3 px-4 mb-4">
                            <!-- Sort -->
                            <div class="form-group mb-4">
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
                                <InputLabel for="image" :value="lang().label.image" />
                                <FileUpload
                                    mode="basic"
                                    @select="onFileSelect"
                                    :chooseLabel="lang().label.choose"
                                    customUpload
                                    auto
                                    severity="secondary"
                                    class="p-button-outlined"
                                />
                                <img
                                    v-if="src"
                                     :src="src"
                                     alt="Image"
                                     class="shadow-md rounded-xl w-full sm:w-64 mt-3"
                                />
                                <InputError class="mt-2" :message="form.errors.image" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-start">
                    <BackLink :href="route('product_brands.index')" />
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

<script setup>
import { ref, watchEffect } from "vue";
import { Head, useForm } from "@inertiajs/vue3";

import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import InputText from "primevue/inputtext";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import BackLink from "@/Components/BackLink.vue";
import FileUpload from "primevue/fileupload";

const props = defineProps({
    show: Boolean,
    title: String,
    breadcrumbs: Object,
});

const src = ref(null);

const form = useForm({
    title: "",
    sort: 10,
    image: "",
});

const create = () => {
    form.post(route("product_brands.store"), {
        forceFormData: true,
    });
};

function onFileSelect(event) {
    const file = event.files[0];
    if (!file) return;

    form.image = file;

    const reader = new FileReader();
    reader.onload = (e) => {
        src.value = e.target.result;
    };
    reader.readAsDataURL(file);
}

watchEffect(() => {
    if (props.show) {
        form.errors = {};
    }
});
</script>
<style>
.p-fileupload-basic{
    justify-content: flex-start !important;
}
</style>
