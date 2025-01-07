<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {Head, useForm} from "@inertiajs/vue3";
import {watchEffect} from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import Select from "primevue/select";
import BackLink from "@/Components/BackLink.vue";
import InputText from "primevue/inputtext";
import InputNumber from "primevue/inputnumber";

const props = defineProps({
    title: String,
    users: Array,
    currency: Object,
    statusOptions: Object,
    breadcrumbs: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    name: "",
    short_name: "",
    value: "",
    status: ""
});

const update = () => {
    form.put(route("currency.update", props.currency?.id), {
    });
};


watchEffect(() => {
    form.errors = {};
    form.name = props.currency?.name;
    form.short_name = props.currency?.short_name;
    form.value = props.currency?.value;
    form.status = props.currency?.status;
});

</script>

<template>
    <Head :title="props.title"/>
    <AuthenticatedLayout>
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs"/>

        <section class="space-y-4 bg-white dark:bg-slate-800 shadow sm:rounded-lg">
            <form class="p-6" @submit.prevent="update">
                <h2
                    class="text-lg font-medium text-slate-900 dark:text-slate-100"
                >
                    {{ lang().label.edit }} {{ props.title }}
                </h2>
                <div class="my-6 space-y-4">
                  <div class="flex flex-wrap -mx-4">
                      <div class="w-full md:w-2/3 px-4 mb-4">
                          <div class="form-group mb-3">
                              <InputLabel for="name" :value="lang().label.name" />
                              <InputText
                                  id="name"
                                  type="text"
                                  class="mt-1 block w-full"
                                  v-model="form.name"
                                  :error="form.errors.name"
                              />
                              <InputError class="mt-2" :message="form.errors.name" />
                          </div>

                          <div class="form-group mb-3">
                              <InputLabel for="short_name" :value="lang().label.short_name" />
                              <InputText
                                  id="short_name"
                                  type="text"
                                  class="mt-1 block w-full"
                                  v-model="form.short_name"
                                  :error="form.errors.short_name"
                              />
                              <InputError class="mt-2" :message="form.errors.short_name" />
                          </div>

                          <div class="form-group mb-3">
                              <InputLabel for="value" :value="lang().label.value" />
                              <InputNumber
                                  id="short_name"
                                  class="mt-1 block w-full"
                                  v-model="form.value"
                                  :placeholder="lang().label.value"
                                  :minFractionDigits="2" fluid
                                  :error="form.errors.value"
                              />
                              <InputError class="mt-2" :message="form.errors.value" />
                          </div>

                      </div>
                      <div class="w-full md:w-1/3 px-4 mb-4">
                          <div class="form-group">
                              <InputLabel for="status" :value="lang().label.status" />
                              <Select
                                  v-model="form.status"
                                  :options="statusOptions"
                                  optionLabel="label"
                                  optionValue="value"
                                  class="w-full"
                                  checkmark
                                  :highlightOnSelect="false"
                              />
                              <InputError class="mt-2" :message="form.errors.status" />
                          </div>
                      </div>
                  </div>
                </div>
                <div class="flex justify-start px-4">
                    <BackLink :href="route('currency.index')"/>
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
