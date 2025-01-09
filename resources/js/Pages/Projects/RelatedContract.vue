<template>
    <Head :title="props.title"/>

    <AuthenticatedLayout>
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs"/>
        <div class="space-y-4">
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
                            v-show="data.selectedId.length !== 0"
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
                            </th>
                            <th class="px-2 py-4">#</th>

                            <th class="px-2 py-4 cursor-pointer" @click="order('title')">
                                <div class="flex justify-between items-center">
                                    <span>{{ lang().label.title }}</span>
                                    <ChevronUpDownIcon class="w-4 h-4"/>
                                </div>
                            </th>

                            <th class="px-2 py-4 cursor-pointer" @click="order('contract_number')">
                                <div class="flex justify-between items-center">
                                    <span>{{ lang().label.contract_number }}</span>
                                    <ChevronUpDownIcon class="w-4 h-4"/>
                                </div>
                            </th>

                            <th class="px-2 py-4 cursor-pointer" @click="order('budget_sum')">
                                <div class="flex justify-between items-center">
                                    <span>{{ lang().label.contract_sum }}</span>
                                    <ChevronUpDownIcon class="w-4 h-4"/>
                                </div>
                            </th>

                            <th class="px-2 py-4 cursor-pointer" v-on:click="order('currency_id')">
                                <div class="flex justify-between items-center">
                                    <span>{{ lang().label.currency }}</span>
                                    <ChevronUpDownIcon class="w-4 h-4" />
                                </div>
                            </th>
                            <th class="px-2 py-4 cursor-pointer" v-on:click="order('status')">
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
                            v-for="(contract, index) in props.contracts.data"
                            :key="index"
                            class="border-t border-slate-200 dark:border-slate-700 hover:bg-slate-200/30 hover:dark:bg-slate-900/20"
                        >
                            <td class="whitespace-nowrap py-4 px-2 text-center">
                            </td>
                            <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                {{ ++index }}
                            </td>
                            <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                <Link :href="route('contract.show', { contract: contract.id })" class="text-blue-500 hover:underline">
                                    {{ contract.title ?? lang().label.undefined }}
                                </Link>
                            </td>
                            <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                {{ contract.contract_number }}
                            </td>
                            <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                {{ formatNumber(contract.budget_sum) }} {{ contract.currency?.short_name || '' }}
                            </td>
                            <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                {{ contract.currency?.name || lang().label.undefined }}
                            </td>
                            <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                <Badge :value="getStatusLabel(contract.status)" :severity="getStatusSeverity(contract.status)" />
                            </td>
                            <td class="whitespace-nowrap py-4 px-2">
                                <div class="rounded-md gap-1 justify-center overflow-hidden flex items-center">
                                    <ViewLink
                                        :href="route('contract.show', { contract: contract.id })"
                                        v-tooltip="lang().tooltip.show"
                                    />
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
import {ChevronUpDownIcon,TrashIcon,} from "@heroicons/vue/24/solid";
import { usePage } from "@inertiajs/vue3";
import ViewLink from "@/Components/ViewLink.vue";
import Select from "primevue/select";
import InputText from "primevue/inputtext";
import Badge from "primevue/badge";

const { _, debounce, pickBy } = pkg;

const props = defineProps({
    title: String,
    filters: Object,
    project: Object,
    contracts: Object,
    statuses: Object,
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
    contract: null,
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
        router.get(route("projects.related-contracts", props.project.id), params, {
            replace: true,
            preserveState: true,
            preserveScroll: true,
        });
    }, 150)
);

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
