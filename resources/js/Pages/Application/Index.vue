<template>
    <Head :title="props.title" />

    <AuthenticatedLayout>
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs" />
        <div class="space-y-4">
            <div class="px-0">
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
                            v-if="isAdmin"
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
                    <table class="w-full select-width">
                        <thead class="text-sm border-t border-slate-200 dark:border-slate-700">
                        <tr class="dark:bg-slate-900/50 text-left border-b border-slate-300 dark:border-slate-600">
                            <th class="px-2 py-4 text-center w-5">
                                <Checkbox v-model:checked="data.multipleSelect" @change="selectAll" v-show="can(['delete application'])"
                                v-if="isAdmin"
                                />
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
                            <th class="px-2 py-4 cursor-pointer w-40" v-on:click="order('user_id')"  v-if="can(['approve application']) || isAdmin">
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
                                <Checkbox v-model:checked="data.multipleSelect" @change="selectAll" v-show="can(['delete application'])" v-if="isAdmin"/>
                            </th>
                            <th class="px-2 py-4"></th>
                            <th class="px-2 py-4">
                                <InputText
                                    id="title"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="data.params.title"
                                    :placeholder="lang().label.title"
                                />
                            </th>
                            <th class="px-2 py-4">
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
                            <th
                                v-if="can(['approve application']) || isAdmin"
                                class="px-2 py-4"
                            >
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

                            <th class="px-2 py-4">
                                <Select
                                    showClear
                                    v-model="data.params.status_id"
                                    :options="props.statuses"
                                    optionLabel="label"
                                    optionValue="id"
                                    filter
                                    checkmark
                                    :highlightOnSelect="false"
                                    :placeholder="lang().label.select_status"
                                    class="w-full"
                                    :pt="{
                                option: { class: 'custom-option' },
                                dropdown: { style: { maxWidth: '300px' } },
                                overlay: { class: 'parent-wrapper-class' }
                            }"
                                />
                            </th>
                            <th class="px-2 py-4">
                                <Select
                                    showClear
                                    v-model="data.params.type"
                                    :options="props.types"
                                    optionLabel="label"
                                    optionValue="id"
                                    filter
                                    checkmark
                                    :highlightOnSelect="false"
                                    :placeholder="lang().placeholder.select_type"
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
                                <input v-if="isAdmin" v-show="can(['delete application'])" type="checkbox" @change="select" :value="application.id" v-model="data.selectedId" class="rounded border-slate-300 dark:border-slate-700" />
                            </td>
                            <td class="whitespace-pre-wrap py-4 px-2 w-10">
                                {{ (props.applications.current_page - 1) * props.applications.per_page + index + 1 }}
                            </td>
                            <td class="whitespace-pre-wrap py-4 px-2 w-40">
                                <Link :href="route('application.show', { application: application.id })" class="text-blue-500 hover:underline dark:text-white">
                                    {{ application?.title || lang().label.no_available }}
                                </Link>
                            </td>
                            <td class="whitespace-pre-wrap py-4 px-2 w-40">
                                <Link :href="route('projects.show', { project: application.project_id })" class="text-blue-500 hover:underline dark:text-white">
                                    {{ application?.project?.title || lang().label.no_available }}
                                </Link>
                            </td>
                            <td class="whitespace-pre-wrap py-4 px-2 w-40"  v-if="can(['approve application']) || isAdmin">
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
                                    <Button
                                        type="button"
                                        icon="pi pi-ellipsis-v"
                                        @click="toggleMenu($event, application)"
                                        aria-haspopup="true"
                                        aria-controls="menu"
                                        severity="secondary"
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
                    <Pagination :links="props.applications" :filters="data.params" />
                </div>
            </div>
        </div>
        <Menu ref="menu" :model="items" :popup="true" />
        <SendApproval ref="confirmDialogRef" />

    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {computed, reactive, ref, watch} from "vue";
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
import Badge from "primevue/badge";
import Menu from 'primevue/menu';
import SendApproval from "@/Pages/Application/SendApproval.vue";
import Button from "primevue/button";
const menu = ref();
const selectedApplication = ref(null);
const lang = () => usePage().props.language;
const user = usePage().props.auth.user;

const items = computed(() => {
    const baseItems = [];

    if (!selectedApplication.value) return [];

    if (
        selectedApplication.value.status_id === 1 &&
        selectedApplication.value.type !== 2 &&
        selectedApplication.value.user_id === user.id
    ) {
        baseItems.push({
            label: lang().tooltip.send_for_approval || 'Отправить на согласование',
            icon: 'pi pi-send',
            command: () => {
                confirmDialogRef.value.open(selectedApplication.value);
            },
        });
    }

    baseItems.push({
        label: lang().tooltip.show,
        icon: 'pi pi-eye',
        command: () => {
            router.visit(route('application.show', { application: selectedApplication.value.id }));
        },
    });

    if (
        selectedApplication.value.status_id === 3 &&
        selectedApplication.value.type !== 2 &&
        selectedApplication.value.user_id === user.id
    ) {
        baseItems.push({
            label: lang().label.upload_scan,
            icon: 'pi pi-upload',
            command: () => {
                router.visit(route('application.upload-scan', { application: selectedApplication.value.id }));
            },
        });
    }

    if (
        selectedApplication.value.status_id !== 3 &&
        selectedApplication.value.user_id === user.id
    ) {
        baseItems.push({
            label: lang().tooltip.edit,
            icon: 'pi pi-pencil',
            command: () => {
                router.visit(route('application.edit', { application: selectedApplication.value.id }));
            },
        });
    }

    if (
        selectedApplication.value.status_id === 1 &&
        selectedApplication.value.user_id === user.id
    ) {
        baseItems.push({
            label: lang().tooltip.delete,
            icon: 'pi pi-trash',
            command: () => {
                data.deleteOpen = true;
                data.application = selectedApplication.value;
            },
        });
    }

    return [
        {
            label: lang().actions || 'Действия',
            items: baseItems,
        },
    ];
});

const toggleMenu = (event, application) => {
    selectedApplication.value = application;
    menu.value.toggle(event);
};

const confirmDialogRef = ref();
const { _, debounce, pickBy } = pkg;

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
        project_id: props.filters.project_id ?? null,
        user_id: props.filters.user_id ?? null,
        status_id: props.filters.status_id ?? null,
        type: props.filters.type ?? null,
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
        1: "secondary",
        2: "warn",
        3: "success",
        "-1": "danger",
    };
    return severities[statusId] || "info";
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

const isAdmin = usePage().props.auth.user.roles?.some(role => role.name === 'superadmin');
</script>
