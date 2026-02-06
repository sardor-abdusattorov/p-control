<template>

    <AuthenticatedLayout :title="props.title">
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs"/>
        <div class="space-y-4">
            <div class="px-0">

                <div class="rounded-lg overflow-hidden w-fit">
                    <div>
                        <CreateLink :href="route('projects.create')" v-show="can(['create project'])"/>
                    </div>
                    <Delete v-show="can(['delete project'])"
                        :show="data.deleteOpen"
                        @close="data.deleteOpen = false"
                        :project="data.project"
                        :title="props.title"
                    />
                    <DeleteBulk v-show="can(['delete project'])"
                        :show="data.deleteBulkOpen"
                        @close="
                            (data.deleteBulkOpen = false),
                                (data.multipleSelect = false),
                                (data.selectedId = [])
                        "
                        :selectedId="data.selectedId"
                        :title="props.title"
                    />
                    <Contracts
                        :show="data.contractsOpen"
                        @close="data.contractsOpen = false"
                        :project="data.project || {}"
                        :currencies="props.currencies"
                        :title="data.project?.title || props.title"
                    />

                </div>
            </div>
            <div class="relative bg-white dark:bg-slate-800 shadow sm:rounded-lg">
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
                            v-show="data.selectedId.length !== 0 && can(['delete project'])"
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
                        <thead class="uppercase text-sm border-t border-slate-200 dark:border-slate-700">
                        <tr class="dark:bg-slate-900/50 text-left">
                            <th class="px-2 py-4 text-center">
                                <Checkbox
                                    v-show="can(['delete project'])"
                                    v-model:checked="data.multipleSelect"
                                    @change="selectAll"
                                />
                            </th>
                            <th class="px-2 py-4">#</th>

                            <th class="px-2 py-4 cursor-pointer" @click="order('title')">
                                <div class="flex justify-between items-center">
                                    <span>{{ lang().label.title }}</span>
                                    <ChevronUpDownIcon class="w-4 h-4"/>
                                </div>
                            </th>

                            <th class="px-2 py-4 cursor-pointer">
                                <div class="flex justify-between items-center">
                                    <span>{{ lang().label.contracts }}</span>
                                    <ChevronUpDownIcon class="w-4 h-4"/>
                                </div>
                            </th>


                            <th class="px-2 py-4 cursor-pointer" @click="order('project_number')">
                                <div class="flex justify-between items-center">
                                    <span>{{ lang().label.project_number }}</span>
                                    <ChevronUpDownIcon class="w-4 h-4"/>
                                </div>
                            </th>

                            <th class="px-2 py-4 cursor-pointer" v-on:click="order('category_id')">
                                <div class="flex justify-between items-center">
                                    <span>{{ lang().label.project_category }}</span>
                                    <ChevronUpDownIcon class="w-4 h-4" />
                                </div>
                            </th>
                            <th class="px-2 py-4 cursor-pointer" v-on:click="order('sort')">
                                <div class="flex justify-between items-center">
                                    <span>{{ lang().label.sort }}</span>
                                    <ChevronUpDownIcon class="w-4 h-4" />
                                </div>
                            </th>
                            <th class="px-2 py-4 cursor-pointer" v-on:click="order('status_id')">
                                <div class="flex justify-between items-center">
                                    <span>{{ lang().label.status }}</span>
                                    <ChevronUpDownIcon class="w-4 h-4" />
                                </div>
                            </th>

                            <th class="px-2 py-4 text-center">{{ lang().label.actions }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr
                            v-for="(project, index) in props.projects.data"
                            :key="index"
                            class="border-t border-slate-200 dark:border-slate-700 hover:bg-slate-200/30 hover:dark:bg-slate-900/20"
                        >
                            <td class="whitespace-nowrap py-4 px-2 text-center">
                                <input
                                    v-show="can(['delete project'])"
                                    class="rounded dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-primary dark:text-primary shadow-sm focus:ring-primary/80 dark:focus:ring-primary dark:focus:ring-offset-slate-800 dark:checked:bg-primary dark:checked:border-primary"
                                    type="checkbox"
                                    @change="select"
                                    :value="project.id"
                                    v-model="data.selectedId"
                                />
                            </td>
                            <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                {{ (props.projects.current_page - 1) * props.projects.per_page + index + 1 }}
                            </td>
                            <td class="whitespace-pre-wrap py-4 px-2 sm:py-3 max-w-xs">
                                <Link
                                    :href="route('projects.show', { project: project.id })"
                                    class="block text-blue-600 font-bold underline hover:underline dark:text-white"
                                >
                                    {{ project.title }}
                                </Link>
                            </td>
                            <td
                                v-tooltip="lang().tooltip.detail"
                                @click="project.contracts.length > 0 ? (data.contractsOpen = true, data.project = project) : null"
                                class="whitespace-nowrap py-4 px-2 sm:py-3 cursor-pointer text-blue-600 dark:text-white font-bold underline"
                                :class="{'cursor-not-allowed opacity-50': project.contracts.length === 0}"
                            >
                                {{ project.contracts.length > 0
                                ? `${lang().label.contracts_length}: ${project.contracts.length}`
                                : lang().label.no_contracts }}
                            </td>
                            <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                {{ project.project_number }}
                            </td>
                            <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                {{ project.category?.title || '-' }}
                            </td>
                            <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                {{ project.sort }}
                            </td>
                            <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                <Badge :value="getStatusLabel(project.status_id)" :severity="getStatusSeverity(project.status_id)" />
                            </td>
                            <td class="whitespace-nowrap py-4 px-2">
                                <div class="gap-1 justify-center overflow-hidden flex items-center">
                                    <ExcelButton
                                        :disabled="!project.contracts.length"
                                        aria-label="Экспорт контрактов в Excel"
                                        @click="exportContractsExcel(project.id)"
                                        v-tooltip="'Экспорт контрактов в Excel'"
                                    />
                                    <ViewLink
                                        :href="route('projects.show', { project: project.id })"
                                        v-tooltip="lang().tooltip.show"
                                    />
                                    <EditLink
                                        v-show="can(['update project'])"
                                        :href="route('projects.edit', { project: project.id })"
                                        v-tooltip="lang().tooltip.edit"
                                    />
                                    <DangerButton
                                        type="button"
                                        @click="(data.deleteOpen = true), (data.project = project)"
                                        v-tooltip="lang().tooltip.delete"
                                        v-show="can(['delete project'])"
                                    >
                                        <TrashIcon class="w-4 h-4" />
                                    </DangerButton>
                                </div>

                            </td>
                        </tr>
                        </tbody>

                    </table>
                </div>
                <div class="flex justify-between items-center p-2 border-t border-slate-200 dark:border-slate-700">
                    <Pagination :links="props.projects" :filters="data.params"/>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {Link} from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import { reactive, watch } from "vue";
import DangerButton from "@/Components/DangerButton.vue";
import pkg from "lodash";
import { router } from "@inertiajs/vue3";
import Pagination from "@/Components/Pagination.vue";
import {ChevronUpDownIcon,TrashIcon,} from "@heroicons/vue/24/solid";
import Delete from "@/Pages/Projects/Delete.vue";
import DeleteBulk from "@/Pages/Projects/DeleteBulk.vue";
import Checkbox from "@/Components/Checkbox.vue";
import { usePage } from "@inertiajs/vue3";
import ViewLink from "@/Components/ViewLink.vue";
import EditLink from "@/Components/EditLink.vue";
import CreateLink from "@/Components/CreateLink.vue";
import Select from "primevue/select";
import InputText from "primevue/inputtext";
import Badge from "primevue/badge";
import Contracts from "@/Pages/Projects/Contracts.vue";
import ExcelButton from "@/Components/ExcelButton.vue";

const { _, debounce, pickBy } = pkg;

const props = defineProps({
    title: String,
    filters: Object,
    projects: Object,
    statuses: Object,
    currencies: Object,
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
    deleteOpen: false,
    deleteBulkOpen: false,
    contractsOpen: false,
    project: null,
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
        router.get(route("projects.index"), params, {
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
        props.projects?.data.forEach((project) => {
            data.selectedId.push(project.id);
        });
    }
};
const select = () => {
    data.multipleSelect = props.projects.data.length === data.selectedId.length;
};
const getStatusLabel = (statusId) => {
    const status = props.statuses.find(s => s.id === statusId);
    return status ? status.label : '';
};

const getStatusSeverity = (statusId) => {
    switch (statusId) {
        case 1:
            return 'info';
        case 2:
            return 'success';
        case -1:
            return 'danger';
        default:
            return 'info';
    }
};

const exportContractsExcel = (projectId) => {
    window.open(route('projects.contracts.export', { project: projectId }), '_blank');
};


</script>
