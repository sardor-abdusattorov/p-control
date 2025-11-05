<template>
    <Head :title="props.title" />

    <AuthenticatedLayout>
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs" />
        <div class="space-y-4">
            <div class="px-0">
                <div class="rounded-lg overflow-hidden w-fit">
                    <div v-if="can(['create products', 'manage products'])">
                        <CreateLink :href="route('products.create')"/>
                    </div>
                    <Delete
                        :show="data.deleteOpen"
                        @close="data.deleteOpen = false"
                        :product="data.product"
                        :title="props.title"
                    />
                    <DeleteBulk
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
                            v-if="can(['delete products', 'manage products'])"
                            @click="data.deleteBulkOpen = true"
                            v-show="data.selectedId.length !== 0"
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
                                <Checkbox v-model:checked="data.multipleSelect" @change="selectAll"/>
                            </th>
                            <th class="px-2 py-4 w-5">#</th>
                            <th class="px-2 py-4 cursor-pointer w-40" v-on:click="order('title')">
                                <div class="flex justify-between items-center">
                                    <span>{{ lang().label.title }}</span>
                                    <ChevronUpDownIcon class="w-4 h-4" />
                                </div>
                            </th>
                            <th class="px-2 py-4 cursor-pointer w-40" v-on:click="order('inventory_number')">
                                <div class="flex justify-between items-center">
                                    <span>{{ lang().label.inventory_number }}</span>
                                    <ChevronUpDownIcon class="w-4 h-4" />
                                </div>
                            </th>
                            <th class="px-2 py-4 cursor-pointer w-40" v-on:click="order('category_id')">
                                <div class="flex justify-between items-center">
                                    <span>{{ lang().label.category_id }}</span>
                                    <ChevronUpDownIcon class="w-4 h-4" />
                                </div>
                            </th>
                            <th class="px-2 py-4 cursor-pointer w-40" v-on:click="order('user_id')">
                                <div class="flex justify-between items-center">
                                    <span>{{ lang().label.user_id }}</span>
                                    <ChevronUpDownIcon class="w-4 h-4" />
                                </div>
                            </th>
                            <th class="px-2 py-4 cursor-pointer w-28" v-on:click="order('status')">
                                <div class="flex justify-between items-center">
                                    <span>{{ lang().label.status }}</span>
                                    <ChevronUpDownIcon class="w-4 h-4" />
                                </div>
                            </th>
                            <th class="px-2 py-4 text-center w-28">{{ lang().label.actions }}</th>
                        </tr>

                        <tr class="dark:bg-slate-900/50 text-left">
                            <th class="px-2 py-4 text-center">

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
                                <InputText
                                    id="inventory_number"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="data.params.inventory_number"
                                    :placeholder="lang().label.inventory_number"
                                />
                            </th>
                            <th class="px-2 py-4">
                                <Select
                                    id="project_id"
                                    v-model="data.params.category_id"
                                    :options="categories"
                                    optionLabel="title"
                                    optionValue="id"
                                    filter
                                    showClear
                                    checkmark
                                    :highlightOnSelect="false"
                                    :filterBy="['category_id', 'title']"
                                    :filterPlaceholder="lang().placeholder.select_project"
                                    class="w-full"
                                    :placeholder="lang().label.category_id"
                                    :pt="{
                                option: { class: 'custom-option white-space-pre-wrap' },
                                dropdown: { style: { maxWidth: '300px' } },
                                overlay: { class: 'parent-wrapper-class' }
                            }"
                                />
                            </th>
                            <th
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
                            <th class="px-2 py-4 text-center"></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(product, index) in products.data" :key="index" class="border-t border-slate-200 dark:border-slate-700 hover:bg-slate-200/30 hover:dark:bg-slate-900/20">
                            <td class="whitespace-pre-wrap py-4 px-2 text-center w-10">
                                <input
                                    class="rounded dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-primary dark:text-primary shadow-sm focus:ring-primary/80 dark:focus:ring-primary dark:focus:ring-offset-slate-800 dark:checked:bg-primary dark:checked:border-primary"
                                    type="checkbox"
                                    @change="select"
                                    :value="product.id"
                                    v-model="data.selectedId"
                                />
                            </td>
                            <td class="whitespace-pre-wrap py-4 px-2 w-10">
                                {{ (props.products.current_page - 1) * props.products.per_page + index + 1 }}
                            </td>
                            <td class="whitespace-pre-wrap py-4 px-2 w-40">
                                <Link :href="route('products.show', { product: product.id })" class="text-blue-500 hover:underline dark:text-white">
                                    {{ product?.title || lang().label.no_available }}
                                </Link>
                            </td>
                            <td class="whitespace-pre-wrap py-4 px-2 w-40">
                                {{ product?.inventory_number || lang().label.no_available }}
                            </td>
                            <td class="whitespace-pre-wrap py-4 px-2 w-40">
                                <Link :href="route('product_categories.show', { product_category: product.category_id })" class="text-blue-500 hover:underline dark:text-white">
                                    {{ product?.category?.title || lang().label.no_available }}
                                </Link>
                            </td>
                            <td class="whitespace-pre-wrap py-4 px-2 w-40">
                                {{ product.user?.name || lang().label.no_available }}
                            </td>
                            <td class="whitespace-pre-wrap py-4 px-2 w-32">
                                <Badge :value="getStatusLabel(product.status)" :severity="getStatusSeverity(product.status)" />
                            </td>
                            <td class="whitespace-pre-wrap py-4 px-2 text-center w-24">
                                <div class="gap-1 flex justify-center">
                                    <Button
                                        type="button"
                                        icon="pi pi-ellipsis-v"
                                        @click="toggleMenu($event, product)"
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
                    <Pagination :links="props.products" :filters="data.params" />
                </div>
            </div>
        </div>
        <Menu ref="menu" :model="items" :popup="true" />

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
import Delete from "@/Pages/Product/Delete.vue";
import DeleteBulk from "@/Pages/Product/DeleteBulk.vue";
import Checkbox from "@/Components/Checkbox.vue";
import { usePage } from "@inertiajs/vue3";
import CreateLink from "@/Components/CreateLink.vue";
import InputText from "primevue/inputtext";
import Select from "primevue/select";
import Badge from "primevue/badge";
import Menu from 'primevue/menu';
import Button from "primevue/button";
const menu = ref();
const selectedProduct = ref(null);
const lang = () => usePage().props.language;

const props = defineProps({
    title: String,
    filters: Object,
    products: Object,
    breadcrumbs: Object,
    perPage: Number,
    statuses: Object,
    categories: Object,
    users: Array,
});

const can = (permissions) => {
    const allPermissions = usePage().props.auth.can;
    return permissions.some(permission => allPermissions[permission]);
};

const items = computed(() => {
    const baseItems = [];

    baseItems.push({
        label: lang().tooltip.show,
        icon: 'pi pi-eye',
        command: () => {
            router.visit(route('products.show', { product: selectedProduct.value.id }));
        },
    });

    if (can(['update products', 'manage products'])) {
        baseItems.push({
            label: lang().tooltip.edit,
            icon: 'pi pi-pencil',
            command: () => {
                router.visit(route('products.edit', { product: selectedProduct.value.id }));
            },
        });
    }

    if (can(['delete products', 'manage products'])) {
        baseItems.push({
            label: lang().tooltip.delete,
            icon: 'pi pi-trash',
            command: () => {
                data.deleteOpen = true;
                data.product = selectedProduct.value;
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

const toggleMenu = (event, product) => {
    selectedProduct.value = product;
    menu.value.toggle(event);
};

const { _, debounce, pickBy } = pkg;

const data = reactive({
    params: {
        search: props.filters.search ?? "",
        field: props.filters.field ?? "",
        order: props.filters.order ?? "asc",
        perPage: props.perPage ?? 10,
        category_id: props.filters.category_id ?? null,
        user_id: props.filters.user_id ?? null,
        status: props.filters.status ?? null
    },
    selectedId: [],
    multipleSelect: false,
    deleteOpen: false,
    deleteBulkOpen: false,
    product: null,
    dataSet: usePage().props.app.perpage,
});

watch(
    () => _.cloneDeep(data.params),
    debounce(() => {
        let params = pickBy(data.params, v => v !== null && v !== '')
        router.get(route("products.index"), params, {
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
        props.products?.data.forEach((product) => {
            data.selectedId.push(product.id);
        });
    }
};

const select = () => {
    data.multipleSelect = props.products?.data.length === data.selectedId.length;
};

const getStatusLabel = (statusId) => {
    const status = props.statuses.find(s => s.id === statusId);
    return status ? status.label : "Неизвестно";
};

const getStatusSeverity = (statusId) => {
    const severities = {
        0: "danger  ",
        1: "success",
    };
    return severities[statusId] || "info";
};

</script>
