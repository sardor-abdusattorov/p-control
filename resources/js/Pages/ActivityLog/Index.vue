<template>
    <Head :title="props.title" />

    <AuthenticatedLayout>
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs" />
        <div class="space-y-4">
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
                                </th>
                                <th class="px-2 py-4">#</th>
                                <th
                                    class="px-2 py-4 cursor-pointer"
                                    v-on:click="order('log_name')"
                                >
                                    <div
                                        class="flex justify-between items-center"
                                    >
                                        <span>{{ lang().label.log_name }}</span>
                                        <ChevronUpDownIcon class="w-4 h-4" />
                                    </div>
                                </th>
                                <th
                                    class="px-2 py-4 cursor-pointer"
                                >
                                    <div
                                        class="flex justify-between items-center"
                                    >
                                        <span>{{ lang().label.description }}</span>
                                        <ChevronUpDownIcon class="w-4 h-4" />
                                    </div>
                                </th>
                                <th
                                    class="px-2 py-4 cursor-pointer"
                                >
                                    <div
                                        class="flex justify-between items-center"
                                    >
                                        <span>{{ lang().label.causer_id }}</span>
                                        <ChevronUpDownIcon class="w-4 h-4" />
                                    </div>
                                </th>
                                <th class="px-2 py-4 text-center">{{ lang().label.actions }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(log, index) in logs.data"
                                :key="index"
                                class="border-t border-slate-200 dark:border-slate-700 hover:bg-slate-200/30 hover:dark:bg-slate-900/20"
                            >
                                <td
                                    class="whitespace-nowrap py-4 px-2 sm:py-3 text-center"
                                >
                                </td>
                                <td
                                    class="whitespace-nowrap py-4 px-2 sm:py-3"
                                >
                                    {{ ++index }}
                                </td>
                                    <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                    {{ log.log_name || lang().label.no_available }}
                                </td>
                                <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                    {{ log.description }}
                                </td>

                                <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                    {{ log.user?.name }}
                                </td>
                                <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                    <div class="gap-1 justify-center overflow-hidden flex items-center">
                                        <ViewLink
                                            :href="route('logs.show', { log: log.id })"
                                            v-tooltip="lang().tooltip.show"
                                        />
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div
                    class="flex justify-between items-center p-2 border-t border-slate-200 dark:border-slate-700"
                >
                    <Pagination :links="props.logs" :filters="data.params" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {Head, Link} from "@inertiajs/vue3";
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
    logs: Object,
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
    log: null,
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
        router.get(route("logs.index"), params, {
            replace: true,
            preserveState: true,
            preserveScroll: true,
        });
    }, 150)
);

</script>

