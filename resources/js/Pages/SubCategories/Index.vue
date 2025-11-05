<template>

    <AuthenticatedLayout :title="props.title">
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs" />
        <div class="space-y-4">
            <div class="px-0">
                <div class="rounded-lg overflow-hidden w-fit">
                    <div>
                        <CreateLink :href="route('contact-subcategories.create')" v-show="can(['manage contacts'])"/>
                    </div>
                    <Delete
                        :show="data.deleteOpen"
                        @close="data.deleteOpen = false"
                        :subCategory="data.subCategory"
                        :title="props.title"
                        v-show="can(['manage contacts'])"
                    />
                    <DeleteBulk
                        v-show="can(['manage contacts'])"
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
                            v-show="data.selectedId.length !== 0 && can(['manage contacts'])"
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
                                <Checkbox v-model:checked="data.multipleSelect" @change="selectAll" v-show="can(['manage contacts'])"
                                />
                            </th>
                            <th class="px-2 py-4 w-10">#</th>
                            <th class="px-2 py-4 cursor-pointer w-40" v-on:click="order('title')">
                                <div class="flex justify-between items-center">
                                    <span>{{ lang().label.title }}</span>
                                    <ChevronUpDownIcon class="w-4 h-4" />
                                </div>
                            </th>
                            <th class="px-2 py-4 cursor-pointer w-40" v-on:click="order('info')">
                                <div class="flex justify-between items-center">
                                    <span>{{ lang().label.info }}</span>
                                    <ChevronUpDownIcon class="w-4 h-4" />
                                </div>
                            </th>
                            <th class="px-2 py-4 cursor-pointer w-40" v-on:click="order('category_id')">
                                <div class="flex justify-between items-center">
                                    <span>{{ lang().label.contact_categories }}</span>
                                    <ChevronUpDownIcon class="w-4 h-4" />
                                </div>
                            </th>
                            <th class="px-2 py-4 cursor-pointer w-28" v-on:click="order('status')">
                                <div class="flex justify-between items-center">
                                    <span>{{ lang().label.status }}</span>
                                    <ChevronUpDownIcon class="w-4 h-4" />
                                </div>
                            </th>
                            <th class="px-2 py-4 cursor-pointer w-28" v-on:click="order('created_at')">
                                <div class="flex justify-between items-center">
                                    <span>{{ lang().label.created }}</span>
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
                                    id="title"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="data.params.title"
                                    :placeholder="lang().label.title"
                                />
                            </th>
                            <th class="px-2 py-4">
                                <InputText
                                    id="title"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="data.params.info"
                                    :placeholder="lang().label.info"
                                />
                            </th>
                            <th class="px-2 py-4">
                                <Select
                                    showClear
                                    v-model="data.params.category_id"
                                    :options="props.categories"
                                    optionLabel="title"
                                    optionValue="id"
                                    filter
                                    checkmark
                                    :highlightOnSelect="false"
                                    :placeholder="lang().label.select_category"
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
                                    v-model="data.params.status"
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
                            <th class="px-2 py-4"></th>
                            <th class="px-2 py-4 text-center"></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(subCategory, index) in subCategories.data" :key="index" class="border-t border-slate-200 dark:border-slate-700 hover:bg-slate-200/30 hover:dark:bg-slate-900/20">
                            <td class="whitespace-pre-wrap py-4 px-2 text-center w-10">
                                <input v-show="can(['manage contacts'])" type="checkbox" @change="select" :value="subCategory.id" v-model="data.selectedId" class="rounded border-slate-300 dark:border-slate-700" />
                            </td>
                            <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                {{ (props.subCategories.current_page - 1) * props.subCategories.per_page + index + 1 }}
                            </td>
                            <td class="whitespace-pre-wrap py-4 px-2 w-40">
                                <Link :href="route('contact-subcategories.show', { contact_subcategory: subCategory.id })" class="text-blue-500 hover:underline dark:text-white">
                                    {{ subCategory?.title || lang().label.no_available }}
                                </Link>
                            </td>
                            <td class="whitespace-pre-wrap py-4 px-2 w-40">
                                {{ subCategory?.info || lang().label.no_available }}
                            </td>
                            <td class="whitespace-pre-wrap py-4 px-2 w-40">
                                {{ subCategory.category?.title || lang().label.no_available }}
                            </td>

                            <td class="whitespace-pre-wrap py-4 px-2 w-32">
                                <Badge :value="getStatusLabel(subCategory.status)" :severity="getStatusSeverity(subCategory.status)" />
                            </td>
                            <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                {{ subCategory.created_at }}
                            </td>
                            <td class="whitespace-pre-wrap py-4 px-2 text-center w-24">
                                <div class="gap-1 flex justify-center">
                                    <Button
                                        type="button"
                                        icon="pi pi-ellipsis-v"
                                        @click="toggleMenu($event, subCategory)"
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
                    <Pagination :links="props.subCategories" :filters="data.params" />
                </div>
            </div>
        </div>
        <Menu ref="menu" :model="items" :popup="true" />
    </AuthenticatedLayout>
</template>

<script setup>
import {Head, usePage, router, Link} from "@inertiajs/vue3";
import { ref, reactive, watch, computed } from "vue";
import { debounce } from "lodash";

import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import CreateLink from "@/Components/CreateLink.vue";
import Pagination from "@/Components/Pagination.vue";
import DangerButton from "@/Components/DangerButton.vue";
import Delete from "@/Pages/SubCategories/Delete.vue";
import DeleteBulk from "@/Pages/SubCategories/DeleteBulk.vue";
import Checkbox from "@/Components/Checkbox.vue";
import InputText from "primevue/inputtext";
import Select from "primevue/select";
import Badge from "primevue/badge";
import Menu from "primevue/menu";
import Button from "primevue/button";
import { TrashIcon, ChevronUpDownIcon  } from "@heroicons/vue/24/solid";

const props = defineProps({
    title: String,
    filters: Object,
    categories: Object,
    subCategories: Object,
    breadcrumbs: Object,
    perPage: Number,
    statuses: Object,
});

const lang = () => usePage().props.language;

const data = reactive({
    params: {
        title: props.filters.title ?? "",
        info: props.filters.info ?? "",
        category_id: props.filters.category_id !== undefined ? Number(props.filters.category_id) : null,
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
            label: lang().label.actions || "Действия",
            items: [
                {
                    label: lang().tooltip.show,
                    icon: "pi pi-eye",
                    command: () => router.visit(route("contact-subcategories.show", selectedCategory.value.id)),
                },
                {
                    label: lang().tooltip.edit,
                    icon: "pi pi-pencil",
                    command: () => router.visit(route("contact-subcategories.edit", selectedCategory.value.id)),
                },
                {
                    label: lang().tooltip.delete,
                    icon: "pi pi-trash",
                    command: () => {
                        data.subCategory = selectedCategory.value;
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
        router.get(route('contact-subcategories.index'), query, {
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
        props.subCategories.data.forEach((category) => {
            data.selectedId.push(category.id);
        });
    }
};

const select = () => {
    data.multipleSelect = props.subCategories.data.length === data.selectedId.length;
};

const getStatusLabel = (statusId) => {
    const status = props.statuses.find(s => s.id === statusId);
    return status ? status.label : "Неизвестно";
};

const getStatusSeverity = (statusId) => {
    const severities = {
        1: "success",
        0: "warning",
    };
    return severities[statusId] || "info";
};
</script>
