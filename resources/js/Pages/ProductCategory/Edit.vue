<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import BackLink from "@/Components/BackLink.vue";
import { Head, useForm } from "@inertiajs/vue3";
import {ref, watchEffect} from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import InputText from "primevue/inputtext";
import FileUpload from "primevue/fileupload";

const props = defineProps({
    title: String,
    category: Object,
    breadcrumbs: Object,
});

const form = useForm({
    title: '',
    image: null,
    sort: ''
});

const src = ref(null);

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

const update = () => {
    form.post(route("product_categories.update", props.category.id), {
        forceFormData: true,
    });
};

watchEffect(() => {
    form.errors = {};
    form.title = props.category?.title ?? '';
    form.sort = props.category?.sort ?? '';
    form.image = null;

    if (props.category?.image_url) {
        src.value = props.category.image_url;
    }
});

</script>

<template>

    <AuthenticatedLayout :title="props.title">
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs"/>

        <section class="space-y-4 bg-white dark:bg-slate-800 shadow sm:rounded-lg">
            <form class="p-6" @submit.prevent="update" enctype="multipart/form-data">
                <h2 class="text-lg font-medium text-slate-900 dark:text-slate-100">
                    {{ lang().label.edit }} {{ props.title }}
                </h2>

                <div class="my-6 space-y-4">
                    <div class="flex flex-wrap -mx-4">
                        <!-- Title -->
                        <div class="w-full md:w-2/3 px-4 mb-4">
                            <InputLabel for="title" :value="lang().label.title"/>
                            <InputText
                                id="title"
                                type="text"
                                v-model="form.title"
                                class="mt-1 block w-full"
                                :placeholder="lang().placeholder.name"
                            />
                            <InputError class="mt-2" :message="form.errors.title"/>
                        </div>

                        <!-- Sort and Image -->
                        <div class="w-full md:w-1/3 px-4 mb-4">
                            <div class="mb-4">
                                <InputLabel for="sort" :value="lang().label.sort"/>
                                <InputText
                                    id="sort"
                                    type="number"
                                    v-model="form.sort"
                                    class="mt-1 block w-full"
                                    :placeholder="lang().placeholder.sort"
                                />
                                <InputError class="mt-2" :message="form.errors.sort"/>
                            </div>

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

                <div class="flex justify-start">
                    <BackLink :href="route('product_categories.index')"/>
                    <PrimaryButton
                        class="ml-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? lang().button.save + "..." : lang().button.save }}
                    </PrimaryButton>
                </div>
            </form>
        </section>
    </AuthenticatedLayout>
</template>

<style>
.p-fileupload-basic{
    justify-content: flex-start !important;
}
</style>

