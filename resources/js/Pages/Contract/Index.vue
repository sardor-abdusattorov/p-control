<template>
    <Head :title="props.title"/>

    <AuthenticatedLayout>
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs"/>
        <div class="space-y-4">
            <div class="px-0">
                <div class="rounded-lg overflow-hidden w-fit">
                    <div>
                        <CreateLink :href="route('contract.create')" v-show="can(['create contract'])"/>
                    </div>
                    <Delete
                        :show="data.deleteOpen"
                        @close="data.deleteOpen = false"
                        :contract="data.contract"
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
                            v-show="can(['delete contract']) && data.selectedId.length !== 0"
                            @click="data.deleteBulkOpen = true"
                            class="px-3 py-1.5"
                            v-tooltip="lang().tooltip.delete_selected"
                        >
                            <TrashIcon class="w-5 h-5" />
                        </DangerButton>
                    </div>
<!--                    <InputText-->
<!--                        v-model="data.params.search"-->
<!--                        type="text"-->
<!--                        class="block w-3/6 md:w-2/6 lg:w-1/6 rounded-lg"-->
<!--                        :placeholder="lang().placeholder.search"-->
<!--                    />-->
                </div>
                <div class="overflow-x-auto scrollbar-table">
                    <table class="w-full select-width">
                        <thead class="text-sm border-t border-slate-200 dark:border-slate-700">
                        <tr class="dark:bg-slate-900/50 text-left border-b border-slate-300 dark:border-slate-600">
                            <th class="px-2 py-4 text-center">
                                <Checkbox v-show="can(['delete contract'])"
                                          v-model:checked="data.multipleSelect"
                                          @change="selectAll"
                                />
                            </th>
                            <th class="px-2 py-4 cursor-pointer w-[12%]" @click="order('contract_number')">
                                <div class="flex justify-between items-center">
                                    <span>{{ lang().label.contract_number }}</span>
                                    <ChevronUpDownIcon class="w-4 h-4"/>
                                </div>
                            </th>

                            <th class="px-2 py-4 cursor-pointer " @click="order('title')">
                                <div class="flex justify-between items-center">
                                    <span>{{ lang().label.title }}</span>
                                    <ChevronUpDownIcon class="w-4 h-4"/>
                                </div>
                            </th>

                            <th class="px-2 py-4 cursor-pointer " @click="order('currency_id')">
                                <div class="flex justify-between items-center">
                                    <span>{{ lang().label.contract_sum }}</span>
                                    <ChevronUpDownIcon class="w-4 h-4"/>
                                </div>
                            </th>

                            <th class="px-2 py-4 cursor-pointer " v-on:click="order('user_id')">
                                <div class="flex justify-between items-center">
                                    <span>{{ lang().label.user_id }}</span>
                                    <ChevronUpDownIcon class="w-4 h-4" />
                                </div>
                            </th>
                            <th class="px-2 py-4 cursor-pointer" @click="order('status')">
                                <div class="flex justify-between items-center">
                                    <span>{{ lang().label.status }}</span>
                                    <ChevronUpDownIcon class="w-4 h-4" />
                                </div>
                            </th>
                            <th class="px-2 py-4 cursor-pointer" @click="order('status')">
                                <div class="flex justify-between items-center">
                                    <span>Статус согласования</span>

                                </div>
                            </th>
                            <th class="px-2 py-4 text-center ">{{ lang().label.actions }}</th>
                        </tr>

                            <tr class="dark:bg-slate-900/50 text-left">
                            <th class="px-2 py-4 text-center">
                                <Checkbox v-model:checked="data.multipleSelect" @change="selectAll" v-show="can(['delete application'])" />
                            </th>
                            <th class="px-2 py-4 cursor-pointer">
                                <InputText
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="data.params.contract_number"
                                    :placeholder="lang().label.contract_number"
                                />
                            </th>
                            <th class="px-2 py-4 cursor-pointer">
                                <InputText
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="data.params.title"
                                    :placeholder="lang().label.title"
                                />
                            </th>
                            <th class="px-2 py-4 cursor-pointer">
                                <Select
                                    showClear
                                    v-model="data.params.currency_id"
                                    :options="props.currency"
                                    optionLabel="name"
                                    optionValue="id"
                                    filter
                                    checkmark
                                    :highlightOnSelect="false"
                                    :placeholder="lang().placeholder.select_currency"
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
                            <th class="px-2 py-4 text-center"></th>
                        </tr>

                        </thead>
                        <tbody>
                        <tr
                            v-for="(contract, index) in props.contracts.data"
                            :key="index"
                            class="border-t border-slate-200 dark:border-slate-700 hover:bg-slate-200/30 hover:dark:bg-slate-900/20"
                        >
                            <td class="whitespace-nowrap py-4 px-2 text-center">
                                <input v-show="can(['delete contract'])"
                                    class="rounded dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-primary dark:text-primary shadow-sm focus:ring-primary/80 dark:focus:ring-primary dark:focus:ring-offset-slate-800 dark:checked:bg-primary dark:checked:border-primary"
                                    type="checkbox"
                                    @change="select"
                                    :value="contract.id"
                                    v-model="data.selectedId"
                                />
                            </td>
                            <td class="whitespace-pre-wrap py-4 px-2 sm:py-3">
                                {{ contract.contract_number || lang().label.undefined }}
                            </td>
                            <td class="whitespace-pre-wrap py-4 px-2 sm:py-3">
                                <Link :href="route('contract.show', { contract: contract.id })" class="text-blue-500 hover:underline hover:underline dark:text-white">
                                    {{ contract.title ?? lang().label.undefined }}
                                </Link>
                            </td>
                            <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                {{ formatNumber(contract.budget_sum) }} {{ contract.currency?.short_name || '' }}
                            </td>
                            <td class="whitespace-pre-wrap py-4 px-2 sm:py-3">
                                {{ contract.user?.name || lang().label.undefined }}
                            </td>
                            <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                <Badge :value="getStatusLabel(contract.status)" :severity="getStatusSeverity(contract.status)" />
                            </td>
                            <td class="whitespace-pre-wrap py-4 px-2 sm:py-3">
                                <div v-if="approvals[contract.id] && approvals[contract.id].length">
                                    <ul class="space-y-2 text-sm">
                                        <li
                                            v-for="item in approvals[contract.id]"
                                            :key="item.user_id"
                                            class="flex items-center gap-2"
                                            :class="item.approved ? 'text-green-600' : 'text-orange-500'"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    :d="item.approved ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12'"
                                                />
                                            </svg>
                                            <span>{{ item.user_name }}</span>
                                        </li>
                                    </ul>
                                </div>
                                <div v-else class="text-gray-400 italic text-sm">
                                    {{ lang().label.no_approvers }}
                                </div>
                            </td>
                            <td class="whitespace-nowrap py-4 px-2">
                                <div class="rounded-md gap-1 justify-center overflow-hidden flex items-center">
                                    <ViewLink
                                        :href="route('contract.show', { contract: contract.id })"
                                        v-tooltip="lang().tooltip.show"
                                    />
                                    <EditLink
                                        v-if="(user.roles.some(role => role.name === 'superadmin')) || can(['update contract'])"
                                        :href="route('contract.edit', { contract: contract.id })"
                                        v-tooltip="lang().tooltip.edit"
                                    >
                                    </EditLink>

                                    <DangerButton v-show="can(['delete contract'])"
                                        type="button"
                                        @click="(data.deleteOpen = true), (data.contract = contract)"
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
                    <Pagination :links="props.contracts" :filters="data.params"/>
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
import {ChevronUpDownIcon, TrashIcon} from "@heroicons/vue/24/solid";
import Delete from "@/Pages/Contract/Delete.vue";
import DeleteBulk from "@/Pages/Contract/DeleteBulk.vue";
import Checkbox from "@/Components/Checkbox.vue";
import { usePage } from "@inertiajs/vue3";
import ViewLink from "@/Components/ViewLink.vue";
import EditLink from "@/Components/EditLink.vue";
import CreateLink from "@/Components/CreateLink.vue";
import Badge from "primevue/badge";
import InputText from "primevue/inputtext";
import Select from "primevue/select";
import InputNumber from 'primevue/inputnumber';

const { _, debounce, pickBy } = pkg;
const user = usePage().props.auth.user;

const props = defineProps({
    title: String,
    statuses: Object,
    currency: Object,
    users: Object,
    filters: Object,
    contracts: Object,
    approvals: Object,
    breadcrumbs: Object,
    perPage: Number,
});
const data = reactive({
    params: {
        search: props.filters.search ?? "",
        field: props.filters.field ?? "",
        order: props.filters.order ?? "asc",
        perPage: props.perPage ?? 10,
        user_id: props.filters.user_id ?? "",
        status_id: props.filters.status_id ?? "",
        title: props.filters.title ?? "",
        contract_number: props.filters.contract_number ?? "",
        currency_id: props.filters.currency_id ?? "",
    },
    selectedId: [],
    multipleSelect: false,
    deleteOpen: false,
    deleteBulkOpen: false,
    contract: null,
    dataSet: usePage().props.app.perpage,
});

const order = (field) => {
    if (data.params.field === field) {
        data.params.order = data.params.order === "asc" ? "desc" : "asc";
    } else {
        data.params.field = field;
        data.params.order = "asc";
    }
};

watch(
    () => data.params,
    debounce(() => {
        let params = pickBy(data.params);
        router.get(route("contract.index"), params, {
            replace: true,
            preserveState: true,
            preserveScroll: true,
        });
    }, 150),
    { deep: true }
);

const selectAll = (event) => {
    if (event.target.checked === false) {
        data.selectedId = [];
    } else {
        props.contracts?.data.forEach((contract) => {
            data.selectedId.push(contract.id);
        });
    }
};
const select = () => {
    data.multipleSelect = props.contracts.data.length === data.selectedId.length;
};

const getStatusLabel = (statusId) => {
    const status = props.statuses.find(s => s.id === statusId);
    return status ? status.label : '';
};

const getStatusSeverity = (statusId) => {
    switch (statusId) {
        case 1:
            return 'secondary';
        case 2:
            return 'warn';
        case 3:
            return 'success';
        case -1:
            return 'danger';
        default:
            return 'info';
    }
};

const formatNumber = (amount) => {
    if (!amount) return '-';
    const formattedAmount = new Intl.NumberFormat('ru-RU', {
        style: 'decimal',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount);

    return formattedAmount;
};

</script>

