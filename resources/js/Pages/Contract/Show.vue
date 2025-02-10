<template>
    <Head :title="props.title" />
    <AuthenticatedLayout>
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs" />
        <section class="space-y-4 bg-white dark:bg-slate-800 shadow rounded-t-lg">
            <div class="border-b border-gray-300 dark:border-neutral-600 card-header flex flex-col md:flex-row justify-between items-start md:items-center p-4 bg-gray-100 dark:bg-slate-900 rounded-t-md gap-4">
                <div class="flex justify-start gap-4">
                    <Link
                        :href="route('contract.show', { contract: contract.id })"
                        class="px-6 py-3 rounded-md bg-blue-600 text-white hover:bg-blue-700 shadow-lg transition-all duration-300"
                    >
                        {{ lang().label.information }}
                    </Link>
                    <Link
                        :href="route('contract.chat', { id: contract.id })"
                        class="px-6 py-3 rounded-md bg-green-600 text-white hover:bg-green-700 shadow-lg transition-all duration-300"
                    >
                        {{ lang().label.chat }}
                    </Link>
                </div>
            </div>
            <div class="mt-0 p-4">
                <div class="block-header mb-5 flex flex-col md:flex-row justify-between items-start md:items-center pb-3 border-b border-gray-300 dark:border-neutral-600 gap-4">
                    <h1 class="text-xl md:text-2xl font-bold">{{ contract.title }}</h1>
                    <div class="actions flex flex-wrap gap-2">
                        <Button
                            v-show="can_approve"
                            type="button"
                            icon="pi pi-check-circle"
                            :label="lang().button.approve"
                            severity="success"
                            class="p-button-sm dark:text-white"
                            @click="(data.approveOpen = true), (data.contract = contract)"
                        />

                        <EditLink
                            :href="route('contract.edit', { contract: contract.id })"
                            class="px-4 py-2 rounded-md uppercase"
                            v-tooltip="lang().tooltip.edit"
                            v-show="can(['update contract'])"
                        >
                            {{ lang().tooltip.edit }}
                        </EditLink>

                        <DangerButton
                            type="button"
                            @click="(data.deleteOpen = true), (data.contract = contract)"
                            class="px-4 py-2 rounded-md"
                            v-tooltip="lang().tooltip.delete"
                            v-show="can(['delete contract'])"
                        >
                            {{ lang().tooltip.delete }}
                            <TrashIcon class="w-5 h-5" />
                        </DangerButton>

                        <Delete
                            :show="data.deleteOpen"
                            @close="data.deleteOpen = false"
                            :contract="data.contract"
                            :title="props.title"
                        />
                        <DeleteUser
                            :show="data.deleteUserOpen"
                            @close="data.deleteUserOpen = false"
                            :contract="data.contract"
                            :user="data.selectedUser"
                            :title="props.title"
                        />
                        <EditUser
                            :show="data.editUserOpen"
                            @close="data.editUserOpen = false"
                            :contract="props.contract"
                            :users="props.users"
                            :approvals="props.approvals"
                            :title="props.title"
                        />

                        <Approve
                            v-show="can(['approve contract']) && (contract.status === 1 || contract.status === 2)"
                            :show="data.approveOpen"
                            @close="data.approveOpen = false"
                            :contract="data.contract"
                            :title="props.title"
                        />
                    </div>
                </div>

                <div class="p-6 bg-gray-100 dark:bg-neutral-800 rounded-lg shadow-md mb-4">
                    <div class="flex items-center mb-3 justify-between">
                        <h2 class="text-lg font-bold">{{ lang().label.approval_status }}</h2>
                        <Button
                            type="button"
                            icon="pi pi-user-plus"
                            :label="lang().button.edit"
                            severity="info"
                            class="p-button-sm dark:text-white"
                            @click="data.editUserOpen = true"
                            v-show="contract.user_id === authUser.id"
                        />
                    </div>

                    <div class="space-y-2">
                        <div v-for="approval in approvals" :key="approval.user_id"
                             class="p-4 border border-gray-300 dark:border-neutral-700 rounded-md bg-white dark:bg-gray-900 shadow-md flex justify-between items-center">
                            <div class="details">
                                <h3 class="text-md font-semibold mb-2">👤 {{ approval.user_name }}</h3>
                                <p v-if="approval.approved" class="text-green-600 font-semibold">
                                    ✔ {{ lang().label.approved }} ({{ approval.approved_at }})
                                </p>
                                <p v-else class="text-red-600 font-semibold">✖ {{ lang().label.not_approved }}</p>
                            </div>
                            <div class="flex gap-2 items-center" v-show="contract.user_id === authUser.id">
                                <form class="p-6">
                                    <input type="hidden" :value="approval.user_id" />
                                    <Button
                                        type="button"
                                        icon="pi pi-trash"
                                        severity="danger"
                                        class="p-button-sm dark:text-white"
                                        @click="() => confirmRemoveApprover(approval)"
                                        :disabled="approval.approved"
                                    />
                                </form>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-300 dark:border-neutral-700 divide-y divide-gray-200 dark:divide-neutral-700">
                        <tbody>
                        <tr
                            class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                        >
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">ID</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ contract.id }}</td>
                        </tr>
                        <tr
                            class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                        >
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">ID</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ contract.contract_number }}</td>
                        </tr>
                        <tr
                            class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                        >
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.title }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ contract.title }}</td>
                        </tr>
                        <tr
                            class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                        >
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.project_id }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">
                                <span v-if="props.project">
                                    {{ props.project.project_number ?? lang().label.undefined }}
                                </span>
                                <span v-else>
                                    {{ lang().label.undefined }}
                                </span>
                            </td>
                        </tr>
                        <tr
                            class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                        >
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.application_id }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">
                                <span v-if="props.application">
                                    {{ props.application.title ?? lang().label.undefined }}
                                </span>
                                <span v-else>
                                    {{ lang().label.undefined }}
                                </span>
                            </td>
                        </tr>
                        <tr
                            class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                        >
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.user_id }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ contract.user.name }}</td>
                        </tr>
                        <tr
                            class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                        >
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.status }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">
                                <Badge :value="getStatusLabel(contract.status)" :severity="getStatusSeverity(contract.status)" />
                            </td>
                        </tr>
                        <tr
                            class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                        >
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.currency_id }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">
                                {{ contract.currency.name }}
                            </td>
                        </tr>
                        <tr
                            class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                        >
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.deadline }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">
                                {{ contract.deadline }}
                            </td>
                        </tr>
                        <tr
                            class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                        >
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.contract_sum }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ formatNumber(contract.budget_sum) }} {{ contract.currency?.short_name || '' }}</td>
                        </tr>
                        <tr class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800">
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.files }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">
                                <div v-if="props.files.length > 0">
                                    <ul class="list-none p-0 flex flex-col gap-1.5">
                                        <li v-for="(file, index) in props.files" :key="index" class="flex items-center space-x-2">
                                            <a v-tooltip="lang().tooltip.download" :href="file.original_url" target="_blank" class="text-blue-600 hover:text-blue-800">
                                                {{ file.name }}
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div v-else>
                                    {{ lang().label.no_files }}
                                </div>
                            </td>
                        </tr>
                        <tr
                            class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                        >
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.created }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ contract.created_at }}</td>
                        </tr>
                        <tr
                            class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                        >
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.updated }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ contract.updated_at }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

    </AuthenticatedLayout>
</template>

<script setup>
import {Head, Link, usePage} from '@inertiajs/vue3';
import {defineProps, defineEmits, reactive, computed} from 'vue';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import Badge from "primevue/badge";
import {TrashIcon} from "@heroicons/vue/24/solid";
import EditLink from "@/Components/EditLink.vue";
import Approve from "@/Pages/Contract/Approve.vue";
import Delete from "@/Pages/Contract/Delete.vue";
import DangerButton from "@/Components/DangerButton.vue";
import DeleteUser from "@/Pages/Contract/DeleteUser.vue";
import Button from "primevue/button";
import { useForm } from "@inertiajs/vue3";
import EditUser from "@/Pages/Contract/EditUser.vue";

const form = useForm({});

const authUser = usePage().props.auth.user;

const props = defineProps({
    show: Boolean,
    contract: Object,
    can_approve: Boolean,
    application: Object,
    title: String,
    breadcrumbs: Object,
    statuses: Array,
    project: Object,
    users: Array,
    approvals: Object,
    files: Array,
});

const data = reactive({
    deleteOpen: false,
    editUserOpen: false,
    approveOpen: false,
    deleteUserOpen: false,
    project: null,
    selectedApprovers: computed(() => props.approvals.map(a => a.user_id)),
});

const emit = defineEmits(["close"]);

const confirmRemoveApprover = (user) => {
    console.log("Запрос на удаление пользователя:", user);

    data.selectedUser = user;
    data.contract = props.contract;
    data.deleteUserOpen = true;
};


const getStatusLabel = (statusId) => {
    const status = props.statuses.find(s => s.id === statusId);
    return status ? status.label : '';
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

const getStatusSeverity = (statusId) => {
    switch (statusId) {
        case 1:
            return 'info';
        case 2:
            return 'info';
        case 3:
            return 'success';
        default:
            return 'info';
    }
};
</script>
