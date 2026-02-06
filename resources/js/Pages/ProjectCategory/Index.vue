<template>
    <AuthenticatedLayout :title="props.title">
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs" />
        <div class="space-y-4">
            <div class="px-0">
                <div class="rounded-lg overflow-hidden w-fit">
                    <div>
                        <CreateLink :href="route('project-categories.create')" v-show="can(['manage project categories'])"/>
                    </div>
                    <Delete
                        :show="data.deleteOpen"
                        @close="data.deleteOpen = false"
                        :category="data.category"
                        :title="props.title"
                        v-show="can(['manage project categories'])"
                    />
                    <DeleteBulk
                        v-show="can(['manage project categories'])"
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
                            @click="data.deleteBulkOpen = true"
                            v-show="data.selectedId.length !== 0 && can(['manage project categories'])"
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
                            <th class="px-2 py-4 text-center w-10">
                                <Checkbox v-if="props.categories?.data?.length > 0" v-model:checked="data.multipleSelect" @change="selectAll" v-show="can(['manage project categories'])" />
                            </th>
                            <th class="px-2 py-4 w-10">#</th>
                            <th class="px-2 py-4 cursor-pointer" v-on:click="order('title')">
                                <div class="flex justify-between items-center">
                                    <span>{{ lang().label.title }}</span>
                                    <ChevronUpDownIcon class="w-4 h-4" />
                                </div>
                            </th>
                            <th class="px-2 py-4 cursor-pointer w-28" v-on:click="order('year')">
                                <div class="flex justify-between items-center">
                                    <span>{{ lang().label.year }}</span>
                                    <ChevronUpDownIcon class="w-4 h-4" />
                                </div>
                            </th>
                            <th class="px-2 py-4 cursor-pointer w-28" v-on:click="order('sort')">
                                <div class="flex justify-between items-center">
                                    <span>{{ lang().label.sort }}</span>
                                    <ChevronUpDownIcon class="w-4 h-4" />
                                </div>
                            </th>
                            <th class="px-2 py-4 cursor-pointer w-28" v-on:click="order('status')">
                                <div class="flex justify-between items-center">
                                    <span>{{ lang().label.status }}</span>
                                    <ChevronUpDownIcon class="w-4 h-4" />
                                </div>
                            </th>
                            <th class="px-2 py-4 text-center w-40">{{ lang().label.actions }}</th>
                        </tr>

                        <tr class="dark:bg-slate-900/50 text-left">
                            <th class="px-2 py-4"></th>
                            <th class="px-2 py-4"></th>
                            <th class="px-2 py-4">
                                <InputText
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="data.params.title"
                                    :placeholder="lang().label.title"
                                />
                            </th>
                            <th class="px-2 py-4">
                                <InputText
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="data.params.year"
                                    :placeholder="lang().label.year"
                                />
                            </th>
                            <th class="px-2 py-4"></th>
                            <th class="px-2 py-4">
                                <Select
                                    showClear
                                    v-model="data.params.status"
                                    :options="props.statuses"
                                    optionLabel="label"
                                    optionValue="id"
                                    :placeholder="lang().label.select_status"
                                    class="w-full"
                                    :pt="{
                                        option: { class: 'custom-option' },
                                        overlay: { class: 'parent-wrapper-class' }
                                    }"
                                />
                            </th>
                            <th class="px-2 py-4 text-center"></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(category, index) in categories.data" :key="index" class="border-t border-slate-200 dark:border-slate-700 hover:bg-slate-200/30 hover:dark:bg-slate-900/20">
                            <td class="whitespace-pre-wrap py-4 px-2 text-center w-10">
                                <input v-show="can(['manage project categories'])" type="checkbox" @change="select" :value="category.id" v-model="data.selectedId" class="rounded border-slate-300 dark:border-slate-700" />
                            </td>
                            <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                {{ (props.categories.current_page - 1) * props.categories.per_page + index + 1 }}
                            </td>
                            <td class="whitespace-pre-wrap py-4 px-2">
                                <Link :href="route('project-categories.show', { project_category: category.id })" class="text-blue-500 hover:underline dark:text-white">
                                    {{ category?.title || lang().label.no_available }}
                                </Link>
                            </td>
                            <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                {{ category.year }}
                            </td>
                            <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                {{ category.sort }}
                            </td>
                            <td class="whitespace-pre-wrap py-4 px-2 w-32">
                                <Badge :value="getStatusLabel(category.status)" :severity="getStatusSeverity(category.status)" />
                            </td>
                            <td class="whitespace-pre-wrap py-4 px-2 text-center w-24">
                                <div class="gap-1 flex justify-center">
                                    <Button
                                        type="button"
                                        icon="pi pi-ellipsis-v"
                                        @click="toggleMenu($event, category)"
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
                <div class="flex justify-between items-center p-2 border-t border-slate-200 dark:border-slate-700">
                    <Pagination :links="props.categories" :filters="data.params" />
                </div>
            </div>
        </div>
        <Menu ref="menu" :model="items" :popup="true" />
    </AuthenticatedLayout>
</template>

<script setup>
import { usePage, router, Link } from "@inertiajs/vue3";
import { ref, reactive, watch, computed } from "vue";
import { debounce } from "lodash";

import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import CreateLink from "@/Components/CreateLink.vue";
import Pagination from "@/Components/Pagination.vue";
import DangerButton from "@/Components/DangerButton.vue";
import Delete from "@/Pages/ProjectCategory/Delete.vue";
import DeleteBulk from "@/Pages/ProjectCategory/DeleteBulk.vue";
import Checkbox from "@/Components/Checkbox.vue";
import InputText from "primevue/inputtext";
import Select from "primevue/select";
import Badge from "primevue/badge";
import Menu from "primevue/menu";
import Button from "primevue/button";
import { TrashIcon, ChevronUpDownIcon } from "@heroicons/vue/24/solid";

const props = defineProps({
    title: String,
    filters: Object,
    categories: Object,
    breadcrumbs: Object,
    perPage: Number,
    statuses: Object,
});

const lang = () => usePage().props.language;

const data = reactive({
    params: {
        title: props.filters.title ?? "",
        year: props.filters.year ?? "",
        status: props.filters.status !== undefined ? Number(props.filters.status) : null,
        field: props.filters.field ?? "",
        order: props.filters.order ?? "asc",
        perPage: props.perPage ?? 10,
    },
    dataSet: usePage().props.app.perpage,
    selectedId: [],
    multipleSelect: false,
    deleteOpen: false,
    deleteBulkOpen: false,
    category: null,
});

const selectedCategory = ref(null);
const menu = ref();

const items = computed(() => {
    if (!selectedCategory.value) return [];
    return [
        {
            label: lang().label.actions || "Actions",
            items: [
                {
                    label: lang().tooltip.show,
                    icon: "pi pi-eye",
                    command: () => router.visit(route("project-categories.show", selectedCategory.value.id)),
                },
                {
                    label: lang().tooltip.edit,
                    icon: "pi pi-pencil",
                    command: () => router.visit(route("project-categories.edit", selectedCategory.value.id)),
                },
                {
                    label: lang().tooltip.delete,
                    icon: "pi pi-trash",
                    command: () => {
                        data.category = selectedCategory.value;
                        data.deleteOpen = true;
                    },
                },
            ],
        },
    ];
});

const toggleMenu = (event, category) => {
    selectedCategory.value = category;
    menu.value.toggle(event);
};

watch(
    () => ({ ...data.params }),
    debounce(() => {
        const query = Object.fromEntries(
            Object.entries(data.params).filter(
                ([, value]) => value !== null && value !== undefined && value !== ''
            )
        );
        router.get(route('project-categories.index'), query, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    }, 150),
    { deep: true }
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
        props.categories.data.forEach((category) => {
            data.selectedId.push(category.id);
        });
    }
};

const select = () => {
    data.multipleSelect = props.categories.data.length === data.selectedId.length;
};

const getStatusLabel = (statusId) => {
    const status = props.statuses.find(s => s.id === statusId);
    return status ? status.label : "";
};

const getStatusSeverity = (statusId) => {
    const severities = { 1: "success", 0: "warning" };
    return severities[statusId] || "info";
};
</script>
