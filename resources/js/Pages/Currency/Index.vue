<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import { reactive, watch } from "vue";
import DangerButton from "@/Components/DangerButton.vue";
import pkg from "lodash";
import { router } from "@inertiajs/vue3";
import Pagination from "@/Components/Pagination.vue";
import {ChevronUpDownIcon, TrashIcon,} from "@heroicons/vue/24/solid";
import Delete from "@/Pages/Currency/Delete.vue";
import DeleteBulk from "@/Pages/Currency/DeleteBulk.vue";
import Checkbox from "@/Components/Checkbox.vue";
import { usePage } from "@inertiajs/vue3";
import CreateLink from "@/Components/CreateLink.vue";
import ViewLink from "@/Components/ViewLink.vue";
import EditLink from "@/Components/EditLink.vue";
import Select from "primevue/select";
import InputText from "primevue/inputtext";

const { _, debounce, pickBy } = pkg;

const props = defineProps({
    title: String,
    filters: Object,
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
    createOpen: false,
    editOpen: false,
    deleteOpen: false,
    deleteBulkOpen: false,
    currency: null,
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
        router.get(route("currency.index"), params, {
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
        props.currencies.data.forEach((currency) => {
            data.selectedId.push(currency.id);
        });
    }
};

const select = () => {
    data.multipleSelect = props.currency.data.length === data.selectedId.length;
};

</script>

<template>

    <AuthenticatedLayout :title="props.title">
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs" />
        <div class="space-y-4">
            <div class="px-4 sm:px-0">
                <div class="rounded-lg overflow-hidden w-fit">
                    <div>
                        <CreateLink :href="route('currency.create')"/>
                    </div>
                    <Delete
                        :show="data.deleteOpen"
                        @close="data.deleteOpen = false"
                        :currency="data.currency"
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
                                <Checkbox
                                    v-model:checked="data.multipleSelect"
                                    @change="selectAll"
                                />
                            </th>
                            <th class="px-2 py-4">#</th>

                            <th class="px-2 py-4 cursor-pointer" @click="order('name')">
                                <div class="flex justify-between items-center">
                                    <span>{{ lang().label.title }}</span>
                                    <ChevronUpDownIcon class="w-4 h-4"/>
                                </div>
                            </th>
                            <th class="px-2 py-4 cursor-pointer" @click="order('short_name')">
                                <div class="flex justify-between items-center">
                                    <span>{{ lang().label.short_name }}</span>
                                    <ChevronUpDownIcon class="w-4 h-4"/>
                                </div>
                            </th>
                            <th class="px-2 py-4 cursor-pointer" @click="order('value')">
                                <div class="flex justify-between items-center">
                                    <span>{{ lang().label.value }}</span>
                                    <ChevronUpDownIcon class="w-4 h-4"/>
                                </div>
                            </th>
                            <th class="px-2 py-4 cursor-pointer" @click="order('status')">
                                <div class="flex justify-between items-center">
                                    <span>{{ lang().label.status }}</span>
                                    <ChevronUpDownIcon class="w-4 h-4"/>
                                </div>
                            </th>
                            <th class="px-2 py-4 cursor-pointer" v-on:click="order('created_at')">
                                <div class="flex justify-between items-center">
                                    <span>{{ lang().label.created }}</span>
                                    <ChevronUpDownIcon class="w-4 h-4" />
                                </div>
                            </th>
                            <th class="px-2 py-4 text-center">{{ lang().label.actions }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr
                            v-for="(currency, index) in props.currencies.data"
                            :key="index"
                            class="border-t border-slate-200 dark:border-slate-700 hover:bg-slate-200/30 hover:dark:bg-slate-900/20"
                        >
                            <td class="whitespace-nowrap py-4 px-2 text-center">
                                <input
                                    class="rounded dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-primary dark:text-primary shadow-sm focus:ring-primary/80 dark:focus:ring-primary dark:focus:ring-offset-slate-800 dark:checked:bg-primary dark:checked:border-primary"
                                    type="checkbox"
                                    @change="select"
                                    :value="currency.id"
                                    v-model="data.selectedId"
                                />
                            </td>
                            <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                {{ (props.currencies.current_page - 1) * props.currencies.per_page + index + 1 }}
                            </td>
                            <td class="whitespace-nowrap py-4 px-2">
                                {{ currency.name }}
                            </td>
                            <td class="whitespace-nowrap py-4 px-2">
                                {{ currency.short_name }}
                            </td>
                            <td class="whitespace-nowrap py-4 px-2">
                                {{ currency.value }}
                            </td>
                            <td class="whitespace-nowrap py-4 px-2">
                               <span
                                   :class="[
                                            '.inline-flex items-center rounded-md px-2 py-1 text-sm font-medium ring-1 ring-inset',
                                            currency.status === 1 ? 'bg-green-50 text-green-700 ring-green-600/20' : 'bg-red-50 text-red-700 ring-red-600/20'
                                        ]"
                               >
                                        {{ currency.status === 1 ? lang().label.active : lang().label.inactive }}
                                    </span>
                            </td>
                            <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                {{ currency.created_at }}
                            </td>
                            <td class="whitespace-nowrap py-4 px-2">
                                <div class="gap-1 justify-center overflow-hidden flex items-center">
                                    <ViewLink
                                        :href="route('currency.show', { currency: currency.id })"
                                        v-tooltip="lang().tooltip.show"
                                    />
                                    <EditLink
                                        :href="route('currency.edit', { currency: currency.id })"
                                        v-tooltip="lang().tooltip.edit"
                                    />
                                    <DangerButton
                                        type="button"
                                        @click="
                                                (data.deleteOpen = true),
                                                    (data.currency = currency)
                                            "
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
                    <Pagination :links="props.currencies" :filters="data.params"/>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

