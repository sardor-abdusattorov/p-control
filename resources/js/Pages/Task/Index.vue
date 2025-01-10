<template>
    <Head :title="props.title"/>

    <AuthenticatedLayout>
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs"/>
        <div class="space-y-4">
            <div class="px-4 sm:px-0">

                <div class="rounded-lg overflow-hidden w-fit">
                    <div>
                        <CreateLink :href="route('task.create') " v-show="can(['create task'])"/>
                    </div>
                    <Delete v-show="can(['delete task'])"
                        :show="data.deleteOpen"
                        @close="data.deleteOpen = false"
                        :task="data.task"
                        :title="props.title"
                    />
                    <DeleteBulk v-show="can(['delete task'])"
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
                            v-show="can(['delete task']) && data.selectedId.length !== 0"
                            @click="data.deleteBulkOpen = true"
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
                        <tr class="dark:bg-slate-900/50 text-left" >
                            <th class="px-2 py-4 text-center" >
                                <Checkbox v-show="can(['delete task'])"
                                    v-model:checked="data.multipleSelect"
                                    @change="selectAll"
                                />
                            </th>
                            <th class="px-2 py-4">#</th>

                            <th class="px-2 py-4 cursor-pointer" @click="order('name')">
                                <div class="flex justify-between items-center">
                                    <span>{{ lang().label.name }}</span>
                                    <ChevronUpDownIcon class="w-4 h-4"/>
                                </div>
                            </th>

                            <th class="px-2 py-4 cursor-pointer" @click="order('due_date')">
                                <div class="flex justify-between items-center">
                                    <span>{{ lang().label.due_date }}</span>
                                    <ChevronUpDownIcon class="w-4 h-4"/>
                                </div>
                            </th>

                            <th class="px-2 py-4 cursor-pointer" v-on:click="order('assigned_user')">
                                <div class="flex justify-between items-center">
                                    <span>{{ lang().label.assigned_user }}</span>
                                    <ChevronUpDownIcon class="w-4 h-4" />
                                </div>
                            </th>

                            <th class="px-2 py-4 cursor-pointer" @click="order('priority')">
                                <div class="flex justify-between items-center">
                                    <span>{{ lang().label.priority }}</span>
                                    <ChevronUpDownIcon class="w-4 h-4"/>
                                </div>
                            </th>

                            <th class="px-2 py-4 cursor-pointer" v-on:click="order('status')">
                                <div class="flex justify-between items-center">
                                    <span>{{ lang().label.status }}</span>
                                    <ChevronUpDownIcon class="w-4 h-4" />
                                </div>
                            </th>
                            <th class="px-2 py-4 text-center">{{ lang().label.action }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr
                            v-for="(task, index) in props.tasks.data"
                            :key="index"
                            class="border-t border-slate-200 dark:border-slate-700 hover:bg-slate-200/30 hover:dark:bg-slate-900/20"
                        >
                            <td class="whitespace-nowrap py-4 px-2 text-center">
                                <input v-show="can(['delete task'])"
                                    class="rounded dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-primary dark:text-primary shadow-sm focus:ring-primary/80 dark:focus:ring-primary dark:focus:ring-offset-slate-800 dark:checked:bg-primary dark:checked:border-primary"
                                    type="checkbox"
                                    @change="select"
                                    :value="task.id"
                                    v-model="data.selectedId"
                                />
                            </td>
                            <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                {{ ++index }}
                            </td>
                            <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                <Link :href="route('task.show', { task: task.id })" class="text-blue-500 hover:underline">
                                    {{ task.name }}
                                </Link>
                            </td>
                            <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                {{ task.due_date }}
                            </td>
                            <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                {{ props.users[task.assigned_user] ?? lang().label.undefined }}
                            </td>
                            <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                <Badge :value="getPriorityLabel(task.priority)" :severity="getPrioritySeverity(task.priority)" />
                            </td>
                            <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                <Badge :value="getStatusLabel(task.status)" :severity="getStatusSeverity(task.status)" />
                            </td>
                            <td class="whitespace-nowrap py-4 px-2">
                                <div class="gap-1 justify-center overflow-hidden flex items-center">
                                    <ViewLink
                                        :href="route('task.show', { task: task.id })"
                                        v-tooltip="lang().tooltip.show"
                                    />
                                    <EditLink v-show="can(['update task'])"
                                        :href="route('task.edit', { task: task.id })"
                                        v-tooltip="lang().tooltip.edit"
                                    />
                                    <DangerButton v-show="can(['delete task'])"
                                        type="button"
                                        @click="(data.deleteOpen = true), (data.task = task)"
                                        v-tooltip="lang().tooltip.delete"
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
                    <Pagination :links="props.tasks" :filters="data.params"/>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import { reactive, watch } from "vue";
import DangerButton from "@/Components/DangerButton.vue";
import pkg from "lodash";
import { router } from "@inertiajs/vue3";
import Pagination from "@/Components/Pagination.vue";
import {ChevronUpDownIcon, TrashIcon} from "@heroicons/vue/24/solid";
import Delete from "@/Pages/Task/Delete.vue";
import DeleteBulk from "@/Pages/Task/DeleteBulk.vue";
import Checkbox from "@/Components/Checkbox.vue";
import { usePage } from "@inertiajs/vue3";
import ViewLink from "@/Components/ViewLink.vue";
import EditLink from "@/Components/EditLink.vue";
import CreateLink from "@/Components/CreateLink.vue";
import InputText from "primevue/inputtext";
import Select from "primevue/select";
import Badge from 'primevue/badge';
import {Link} from "@inertiajs/vue3";

const { _, debounce, pickBy } = pkg;

const props = defineProps({
    title: String,
    filters: Object,
    tasks: Object,
    statuses: Object,
    priorities: Object,
    breadcrumbs: Object,
    users: Object,
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
    task: null,
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
        router.get(route("task.index"), params, {
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
        props.tasks?.data.forEach((task) => {
            data.selectedId.push(task.id);
        });
    }
};
const select = () => {
    data.multipleSelect = props.tasks.data.length === data.selectedId.length;
};

const getPriorityLabel = (priorityId) => {
    const priority = props.priorities.find(p => p.id === priorityId);
    return priority ? priority.label : '';
};

const getStatusLabel = (statusId) => {
    const status = props.statuses.find(s => s.id === statusId);
    return status ? status.label : '';
};

const getPrioritySeverity = (priorityId) => {
    switch (priorityId) {
        case 1:
            return 'contrast';
        case 2:
            return 'warn';
        case 3:
            return 'danger';
        default:
            return 'contrast';
    }
};

const getStatusSeverity = (statusId) => {
    switch (statusId) {
        case 1:
            return 'info';
        case 2:
            return 'info';
        case 3:
            return 'success';
        case 4:
            return 'danger';
        default:
            return 'contrast';
    }
};

</script>

