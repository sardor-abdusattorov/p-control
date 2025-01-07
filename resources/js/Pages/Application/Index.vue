<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import { reactive, watch } from "vue";
import DangerButton from "@/Components/DangerButton.vue";
import pkg from "lodash";
import { router } from "@inertiajs/vue3";
import Pagination from "@/Components/Pagination.vue";
import {ChevronUpDownIcon, TrashIcon,} from "@heroicons/vue/24/solid";
import Delete from "@/Pages/Application/Delete.vue";
import DeleteBulk from "@/Pages/Application/DeleteBulk.vue";
import Checkbox from "@/Components/Checkbox.vue";
import { usePage } from "@inertiajs/vue3";
import CreateLink from "@/Components/CreateLink.vue";
import InputText from "primevue/inputtext";
import Select from "primevue/select";
import EditLink from "@/Components/EditLink.vue";
import ViewLink from "@/Components/ViewLink.vue";

const { _, debounce, pickBy } = pkg;
const props = defineProps({
    title: String,
    filters: Object,
    applications: Object,
    breadcrumbs: Object,
    perPage: Number,
});
const data = reactive({
    params: {
        search: props.filters.search,
        field: props.filters.field,
        order: props.filters.order,
        perPage: props.perPage,
    },
    selectedId: [],
    multipleSelect: false,
    createOpen: false,
    editOpen: false,
    deleteOpen: false,
    deleteBulkOpen: false,
    application: null,
    dataSet: usePage().props.app.perpage,
});

const order = (field) => {
    data.params.field = field;
    data.params.order = data.params.order === "asc" ? "desc" : "asc";
};

watch(
    () => _.cloneDeep(data.params),
    debounce(() => {
        let params = pickBy(data.params);
        router.get(route("application.index"), params, {
            replace: true,
            preserveState: true,
            preserveScroll: true,
        });
    }, 150)
);

const selectAll = (event) => {
    if (event.target.checked === false) {
        data.selectedId = [];
    } else {
        props.applications?.data.forEach((application) => {
            data.selectedId.push(application.id);
        });
    }
};
const select = () => {
    data.multipleSelect = props.applications?.data.length === data.selectedId.length;
};
</script>

<template>
    <Head :title="props.title" />

    <AuthenticatedLayout>
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs" />
        <div class="space-y-4">
            <div class="px-4 sm:px-0">
                <div class="rounded-lg overflow-hidden w-fit">
                    <div>
                        <CreateLink :href="route('application.create')"/>
                    </div>
                    <Delete
                        :show="data.deleteOpen"
                        @close="data.deleteOpen = false"
                        :application="data.application"
                        :title="props.title"
                        v-show="can(['delete application'])"
                    />
                    <DeleteBulk
                        v-show="can(['delete application'])"
                        :show="data.deleteBulkOpen"
                        @close="
                            (data.deleteBulkOpen = false),
                                (data.multipleSelect = false),
                                (data.selectedId = [])
                        "
                        :selectedId="data.selectedId"
                        :title="props.title"
                    />
                </div>
            </div>
            <div
                class="relative bg-white dark:bg-slate-800 shadow sm:rounded-lg"
            >
                <div class="flex justify-between p-2">
                    <div class="flex space-x-2">
                        <Select
                            v-model="data.params.perPage"
                            :options="data.dataSet"
                            optionLabel="label"
                            optionValue="value"
                        />
                        <DangerButton
                            @click="data.deleteBulkOpen = true"
                            v-show="data.selectedId.length !== 0 && can(['delete application'])"
                            class="px-3 py-1.5"
                            v-tooltip="lang().tooltip.delete_selected"
                        >
                            <TrashIcon class="w-5 h-5" />
                        </DangerButton>
                    </div>
                    <InputText
                        v-model="data.params.search"
                        type="text"
                        class="block w-3/6 md:w-2/6 lg:w-1/6 rounded-lg"
                        :placeholder="lang().placeholder.search"
                    />
                </div>
                <div class="overflow-x-auto scrollbar-table">
                    <table class="w-full">
                        <thead
                            class="uppercase text-sm border-t border-slate-200 dark:border-slate-700"
                        >
                            <tr class="dark:bg-slate-900/50 text-left">
                                <th class="px-2 py-4 text-center" >
                                    <Checkbox
                                        v-model:checked="data.multipleSelect"
                                        @change="selectAll"
                                        v-show="can(['delete application'])"
                                    />
                                </th>
                                <th class="px-2 py-4">#</th>
                                <th
                                    class="px-2 py-4 cursor-pointer"
                                    v-on:click="order('title')"
                                >
                                    <div
                                        class="flex justify-between items-center"
                                    >
                                        <span>{{ lang().label.name }}</span>
                                        <ChevronUpDownIcon class="w-4 h-4" />
                                    </div>
                                </th>
                                <th
                                    class="px-2 py-4 cursor-pointer"
                                    v-on:click="order('project_id')"
                                >
                                    <div
                                        class="flex justify-between items-center"
                                    >
                                        <span>{{ lang().label.project_id }}</span>
                                        <ChevronUpDownIcon class="w-4 h-4" />
                                    </div>
                                </th>
                                <th
                                    class="px-2 py-4 cursor-pointer"
                                    v-on:click="order('user_id')"
                                >
                                    <div
                                        class="flex justify-between items-center"
                                    >
                                        <span>{{ lang().label.user_id }}</span>
                                        <ChevronUpDownIcon class="w-4 h-4" />
                                    </div>
                                </th>
                                <th class="px-2 py-4 text-center">{{ lang().label.action }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(application, index) in applications.data"
                                :key="index"
                                class="border-t border-slate-200 dark:border-slate-700 hover:bg-slate-200/30 hover:dark:bg-slate-900/20"
                            >
                                <td
                                    class="whitespace-nowrap py-4 px-2 sm:py-3 text-center"
                                >
                                    <input
                                        v-show="can(['delete application'])"
                                        class="rounded dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-primary dark:text-primary shadow-sm focus:ring-primary/80 dark:focus:ring-primary dark:focus:ring-offset-slate-800 dark:checked:bg-primary dark:checked:border-primary"
                                        type="checkbox"
                                        @change="select"
                                        :value="application.id"
                                        v-model="data.selectedId"
                                    />
                                </td>
                                <td
                                    class="whitespace-nowrap py-4 px-2 sm:py-3"
                                >
                                    {{ ++index }}
                                </td>
                                    <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                    <span class="flex justify-start items-center">
                                        {{ application?.title || 'No title available' }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                    {{ application?.project?.title || lang().label.no_available }}
                                </td>

                                <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                    {{ application.user.name }}
                                </td>
                                <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                    <div class="gap-1 justify-center overflow-hidden flex items-center">
                                        <ViewLink
                                            :href="route('application.show', { application: application.id })"
                                            v-tooltip="lang().tooltip.show"
                                        />
                                        <EditLink v-show="can(['update application'])"
                                                  :href="route('application.edit', { application: application.id })"
                                                  v-tooltip="lang().tooltip.edit"
                                        />
                                        <DangerButton v-show="can(['delete application'])"
                                                      type="button"
                                                      @click="
                                                    (data.deleteOpen = true),
                                                        (data.application = application)
                                                "
                                                      v-tooltip="
                                                    lang().tooltip.delete
                                                "
                                        >
                                            <TrashIcon class="w-4 h-4" />
                                        </DangerButton>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div
                    class="flex justify-between items-center p-2 border-t border-slate-200 dark:border-slate-700"
                >
                    <Pagination :links="props.applications" :filters="data.params" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
