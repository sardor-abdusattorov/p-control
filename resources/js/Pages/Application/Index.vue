<template>
    <Head :title="props.title" />

    <AuthenticatedLayout>
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs" />
        <div class="space-y-4">
            <div class="px-4 sm:px-0">
                <div class="rounded-lg overflow-hidden w-fit">
                    <div>
                        <CreateLink :href="route('application.create')" v-show="can(['create application'])"/>
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
                </div>
                <div class="overflow-x-auto scrollbar-table">
                    <table class="w-full">
                        <thead class="text-sm border-t border-slate-200 dark:border-slate-700">
                        <tr class="dark:bg-slate-900/50 text-left border-b border-slate-300 dark:border-slate-600">
                            <th class="px-2 py-4 text-center w-5">
                                <Checkbox v-model:checked="data.multipleSelect" @change="selectAll" v-show="can(['delete application'])" />
                            </th>
                            <th class="px-2 py-4 w-5">#</th>
                            <th class="px-2 py-4 cursor-pointer w-40" v-on:click="order('title')">
                                <div class="flex justify-between items-center">
                                    <span>{{ lang().label.title }}</span>
                                    <ChevronUpDownIcon class="w-4 h-4" />
                                </div>
                            </th>
                            <th class="px-2 py-4 cursor-pointer w-40" v-on:click="order('project_id')">
                                <div class="flex justify-between items-center">
                                    <span>{{ lang().label.project_id }}</span>
                                    <ChevronUpDownIcon class="w-4 h-4" />
                                </div>
                            </th>
                            <th class="px-2 py-4 cursor-pointer w-40" v-on:click="order('user_id')">
                                <div class="flex justify-between items-center">
                                    <span>{{ lang().label.user_id }}</span>
                                    <ChevronUpDownIcon class="w-4 h-4" />
                                </div>
                            </th>
                            <th class="px-2 py-4 cursor-pointer w-28" v-on:click="order('status_id')">
                                <div class="flex justify-between items-center">
                                    <span>{{ lang().label.status }}</span>
                                    <ChevronUpDownIcon class="w-4 h-4" />
                                </div>
                            </th>
                            <th class="px-2 py-4 cursor-pointer w-28" v-on:click="order('type')">
                                <div class="flex justify-between items-center">
                                    <span>{{ lang().label.type_shorten }}</span>
                                    <ChevronUpDownIcon class="w-4 h-4" />
                                </div>
                            </th>
                            <th class="px-2 py-4 text-center w-28">{{ lang().label.actions }}</th>
                        </tr>

                        <tr class="dark:bg-slate-900/50 text-left">
                            <th class="px-2 py-4 text-center">
                                <Checkbox v-model:checked="data.multipleSelect" @change="selectAll" v-show="can(['delete application'])" />
                            </th>
                            <th class="px-2 py-4">#</th>
                            <th class="px-2 py-4 cursor-pointer">
                                <InputText
                                    id="title"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="data.params.title"
                                    :placeholder="lang().label.title"
                                />
                            </th>
                            <th class="px-2 py-4 cursor-pointer">
                                <Select
                                    id="project_id"
                                    v-model="data.params.project_id"
                                    :options="formattedProjects"
                                    optionLabel="display"
                                    optionValue="id"
                                    filter
                                    showClear
                                    checkmark
                                    :highlightOnSelect="false"
                                    :filterBy="['project_number', 'title']"
                                    :filterPlaceholder="lang().placeholder.select_project"
                                    class="w-full"
                                    :placeholder="lang().label.project_name"
                                    :pt="{
                                option: { class: 'custom-option white-space-pre-wrap' },
                                dropdown: { style: { maxWidth: '300px' } },
                                overlay: { class: 'parent-wrapper-class' }
                            }"
                                />
                            </th>
                            <th class="px-2 py-4 cursor-pointer">
                                <Select
                                    showClear
                                    v-model="data.params.user_id"
                                    :options="props.users"
                                    optionLabel="name"
                                    optionValue="id"
                                    filter
                                    checkmark
                                    :highlightOnSelect="false"
                                    :placeholder="lang().label.select_user"
                                    class="w-full"
                                    :pt="{
                                option: { class: 'custom-option' },
                                dropdown: { style: { maxWidth: '300px' } },
                                overlay: { class: 'parent-wrapper-class' }
                            }"
                                />
                            </th>
                            <th class="px-2 py-4 cursor-pointer">
                                <Select
                                    showClear
                                    v-model="data.params.status_id"
                                    :options="props.statuses"
                                    optionLabel="label"
                                    optionValue="id"
                                    filter
                                    checkmark
                                    :highlightOnSelect="false"
                                    :placeholder="lang().label.select_user"
                                    class="w-full"
                                    :pt="{
                                option: { class: 'custom-option' },
                                dropdown: { style: { maxWidth: '300px' } },
                                overlay: { class: 'parent-wrapper-class' }
                            }"
                                />
                            </th>
                            <th class="px-2 py-4 cursor-pointer">
                                <Select
                                    showClear
                                    v-model="data.params.type"
                                    :options="props.types"
                                    optionLabel="label"
                                    optionValue="id"
                                    filter
                                    checkmark
                                    :highlightOnSelect="false"
                                    :placeholder="lang().label.select_user"
                                    class="w-full"
                                    :pt="{
                                option: { class: 'custom-option' },
                                dropdown: { style: { maxWidth: '300px' } },
                                overlay: { class: 'parent-wrapper-class' }
                            }"
                                />
                            </th>
                            <th class="px-2 py-4 text-center"></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(application, index) in applications.data" :key="index" class="border-t border-slate-200 dark:border-slate-700 hover:bg-slate-200/30 hover:dark:bg-slate-900/20">
                            <td class="whitespace-pre-wrap py-4 px-2 text-center w-10">
                                <input v-show="can(['delete application'])" type="checkbox" @change="select" :value="application.id" v-model="data.selectedId" class="rounded border-slate-300 dark:border-slate-700" />
                            </td>
                            <td class="whitespace-pre-wrap py-4 px-2 w-10">
                                {{ (props.applications.current_page - 1) * props.applications.per_page + index + 1 }}
                            </td>
                            <td class="whitespace-pre-wrap py-4 px-2 w-40">
                                <Link :href="route('application.show', { application: application.id })" class="text-blue-500 hover:underline">
                                    {{ application?.title || lang().label.no_available }}
                                </Link>
                            </td>
                            <td class="whitespace-pre-wrap py-4 px-2 w-40">
                                <Link :href="route('projects.show', { project: application.project_id })" class="text-blue-500 hover:underline">
                                    {{ application?.project?.title || lang().label.no_available }}
                                </Link>
                            </td>
                            <td class="whitespace-pre-wrap py-4 px-2 w-40">
                                {{ application.user.name }}
                            </td>
                            <td class="whitespace-pre-wrap py-4 px-2 w-32">
                                <Badge :value="getStatusLabel(application.status_id)" :severity="getStatusSeverity(application.status_id)" />
                            </td>
                            <td class="whitespace-pre-wrap py-4 px-2 w-32">
                                <Badge :value="getTypeLabel(application.type)" :severity="getTypeSeverity(application.type)" />
                            </td>
                            <td class="whitespace-pre-wrap py-4 px-2 text-center w-24">
                                <div class="gap-1 flex justify-center">
                                    <ViewLink :href="route('application.show', { application: application.id })" v-tooltip="lang().tooltip.show" />
                                    <EditLink v-if="user.roles.some(role => role.name === 'superadmin') || (can(['update application']) && application.user_id === user.id)" :href="route('application.edit', { application: application.id })" v-tooltip="lang().tooltip.edit" />
                                    <DangerButton v-show="can(['delete application'])" type="button" @click="(data.deleteOpen = true), (data.application = application)" v-tooltip="lang().tooltip.delete">
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
<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import { computed, reactive, watch } from "vue";
import DangerButton from "@/Components/DangerButton.vue";
import pkg from "lodash";
import { router } from "@inertiajs/vue3";
import Pagination from "@/Components/Pagination.vue";
import { ChevronUpDownIcon, TrashIcon } from "@heroicons/vue/24/solid";
import Delete from "@/Pages/Application/Delete.vue";
import DeleteBulk from "@/Pages/Application/DeleteBulk.vue";
import Checkbox from "@/Components/Checkbox.vue";
import { usePage } from "@inertiajs/vue3";
import CreateLink from "@/Components/CreateLink.vue";
import InputText from "primevue/inputtext";
import Select from "primevue/select";
import EditLink from "@/Components/EditLink.vue";
import ViewLink from "@/Components/ViewLink.vue";
import Badge from "primevue/badge";

const { _, debounce, pickBy } = pkg;
const user = usePage().props.auth.user;

const props = defineProps({
    title: String,
    filters: Object,
    applications: Object,
    breadcrumbs: Object,
    perPage: Number,
    statuses: Object,
    types: Object,
    users: Array,
    projects: Object,
});

const data = reactive({
    params: {
        search: props.filters.search ?? "",
        field: props.filters.field ?? "",
        order: props.filters.order ?? "asc",
        perPage: props.perPage ?? 10,
        project_id: props.filters.project_id ?? "",
        user_id: props.filters.user_id ?? "",
        status_id: props.filters.status_id ?? "",
        type: props.filters.type ?? "",
    },
    selectedId: [],
    multipleSelect: false,
    deleteOpen: false,
    deleteBulkOpen: false,
    application: null,
    dataSet: usePage().props.app.perpage,
});

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

const order = (field) => {
    if (data.params.field === field) {
        data.params.order = data.params.order === "asc" ? "desc" : "asc";
    } else {
        data.params.field = field;
        data.params.order = "asc";
    }
};

const selectAll = (event) => {
    if (!event.target.checked) {
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

const getStatusLabel = (statusId) => {
    const status = props.statuses.find(s => s.id === statusId);
    return status ? status.label : "Неизвестно";
};

const getStatusSeverity = (statusId) => {
    const severities = {
        1: "info",
        2: "info",
        3: "success",
        "-1": "danger",
    };
    return severities[statusId] || "contrast";
};

const getTypeLabel = (type) => {
    const typeData = props.types.find(t => t.id === type);
    return typeData ? typeData.label : "Неизвестно";
};

const getTypeSeverity = (type) => {
    const severities = {
        1: "success",
        2: "warning",
    };
    return severities[type] || "info";
};

const formattedProjects = computed(() => {
    return props.projects.map((project) => ({
        id: project.id,
        project_number: project.project_number || "",
        title: project.title,
        display: `${project.project_number ? project.project_number + "." : ""} ${project.title}`.trim(),
    }));
});
</script>


<style>
.p-inputtext{
    font-size: 14px;
    margin: 0;
    height: 36px;
}
.p-select{
    justify-content: space-between;
}
.p-select .p-select-label{
    max-width: 200px;
}

</style>
