<template>
    <Head :title="props.title" />
    <AuthenticatedLayout>
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs" />
        <section class="space-y-4 bg-white dark:bg-slate-800 shadow rounded-t-lg">
            <div class="mt-3 p-4">
                <div class="block-header mb-5 flex flex-col md:flex-row justify-between items-start md:items-center pb-3 border-b border-gray-300 dark:border-neutral-600 gap-4">
                    <h1 class="text-xl md:text-2xl font-bold">{{ application.title }}</h1>

                    <div class="actions flex flex-wrap gap-2">

                        <Link
                            v-if="application.status_id === 3 && application.user_id === authUser.id && application.type !== 2"
                            :href="route('application.upload-scan', { application: application.id })"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md transition"
                            v-tooltip="lang().label.upload_scan"
                        >
                            <i class="pi pi-upload mr-2"></i>
                            {{ lang().label.upload_scan }}
                        </Link>

                        <Button v-if="userApproval && application.type !== 2"
                            type="button"
                            icon="pi pi-check-circle"
                            :label="lang().button.approve"
                            severity="success"
                            class="p-button-sm dark:text-white"
                            @click="(data.approveOpen = true), (data.application = application)"
                        />

                        <Button
                            v-if="application.status_id === 1 && (application.user_id === authUser.id || isAdmin) && application.type !== 2"
                            type="button"
                            icon="pi pi-send"
                            :label="lang().tooltip.send_for_approval"
                            severity="info"
                            class="p-button-sm dark:text-white"
                            @click="confirmDialogRef.open(application)"
                        />

                        <SendApproval ref="confirmDialogRef"
                            v-if="application.type !== 2"
                        />

                        <Button
                            v-if="userApproval"
                            type="button"
                            icon="pi pi-times"
                            severity="danger"
                            :label="lang().label.cancel_approval"
                            class="p-button-sm bg-yellow-500 text-white dark:text-white"
                            @click="(data.cancelApproval = true), (data.application = application)"
                        />

                        <DeleteUser
                            :show="data.deleteUserOpen"
                            @close="data.deleteUserOpen = false"
                            :application="data.application"
                            :user="data.selectedUser"
                            :title="props.title"
                            v-if="application.type !== 2"
                        />

                        <EditUser
                            :show="data.editUserOpen"
                            @close="data.editUserOpen = false"
                            :application="props.application"
                            :users="props.users"
                            :approvals="activeApprovals"
                            :title="props.title"
                            v-if="application.type !== 2"
                        />

                        <Approve
                            v-if="userApproval && application.type !== 2 && props.application.status_id === 2"
                            :show="can(['approve application']) && data.approveOpen"
                            @close="data.approveOpen = false"
                            :application="data.application"
                            :title="props.title"
                        />

                        <CancelApproval
                            v-if="userApproval && application.type !== 2 && props.application.status_id === 2"
                            :show="can(['approve application']) && data.cancelApproval"
                            @close="data.cancelApproval = false"
                            :application="data.application"
                            :title="props.title"
                        />

                        <EditLink
                            v-if="application.user_id === authUser.id || isAdmin && application.status_id !== 3"
                            :href="route('application.edit', { application: application.id })"
                            class="px-4 py-2 rounded-md"
                            v-tooltip="lang().tooltip.edit"
                        >
                            {{ lang().tooltip.edit }}
                        </EditLink>

                        <DangerButton
                            type="button"
                            @click="(data.deleteOpen = true), (data.application = application)"
                            class="px-4 py-2 rounded-md"
                            v-tooltip="lang().tooltip.delete"
                            v-if="props.application.status_id ===1 && application.user_id === authUser.id || isAdmin"
                        >
                            {{ lang().tooltip.delete }}
                            <TrashIcon class="w-5 h-5" />
                        </DangerButton>
                        <Delete
                            :show="data.deleteOpen"
                            @close="data.deleteOpen = false"
                            :application="data.application"
                            :title="props.title"
                        />
                        <ApprovalHistory
                            :show="data.showHistory"
                            :approval="data.historyApproval"
                            :all-approvals="approvals"
                            @close="data.showHistory = false"
                            v-if="application.type !== 2"
                        />
                    </div>
                </div>

                <div class="p-4 bg-gray-100 dark:bg-neutral-800 rounded-xl shadow mb-6" v-if="application.type !== 2">
                    <div class="flex flex-wrap justify-between items-center gap-4 mb-4">
                        <h2 class="text-xl font-bold">{{ lang().label.approval_status }}</h2>
                        <Button
                            icon="pi pi-user-plus"
                            :label="lang().button.edit"
                            severity="info"
                            class="p-button-sm dark:text-white"
                            :disabled="application.status_id === 3"
                            @click="data.editUserOpen = true"
                            v-if="application.user_id === authUser.id || isAdmin && application.status_id !== 3"
                        />
                    </div>

                    <div class="space-y-4">
                        <Message
                            v-if="activeApprovals.length < 2 && (application.user_id === authUser.id || isAdmin)"
                            severity="warn"
                            :closable="false"
                            class="mb-2"
                        >
                            {{ lang().label.min_approvers_warning }}
                        </Message>
                        <div v-if="activeApprovals.length" class="flex flex-col gap-4">
                            <Card
                                v-for="approval in activeApprovals"
                                :key="approval.user_id"
                                class="shadow-md"
                            >
                                <template #content>
                                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                                        <div>
                                            <h3 class="text-lg font-semibold mb-3">👤 {{ approval.user_name }}</h3>
                                        <div class="mb-3">
                                            <span
                                            class="inline-block px-3 py-1 rounded-full text-sm font-semibold"
                                            :class="{
                                              'bg-green-100 text-green-800': approval.approved === 3,
                                              'bg-red-100 text-red-800': approval.approved === -1,
                                              'bg-gray-200 text-gray-700': approval.approved === -2,
                                              'bg-yellow-100 text-yellow-800': approval.approved === 2,
                                              'bg-blue-100 text-blue-800': approval.approved === 1
                                            }"
                                            >
                                              {{
                                                    application.status_id === 1 && approval.approved === 1
                                                        ? lang().status.not_sent
                                                        : approval.approved === 3
                                                            ? lang().status.approved
                                                            : approval.approved === -1
                                                                ? lang().status.rejected
                                                                : approval.approved === -2
                                                                    ? lang().status.invalidated
                                                                    : lang().status.in_progress
                                                }}

                                        </span>
                                            <span
                                                v-if="approval.approved_at"
                                                class="text-sm text-gray-500 ml-2"
                                            >
                                                ({{ approval.approved_at }})
                                            </span>
                                        </div>
                                            <p v-if="approval.reason" class="text-base">
                                                <span class="font-semibold text-gray-800 dark:text-gray-200">
                                                    {{ lang().label.comment }}:
                                                </span>
                                                <span class="text-gray-700 dark:text-gray-300">
                                                    {{ approval.reason }}
                                                </span>
                                            </p>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <Button
                                                icon="pi pi-eye"
                                                severity="info"
                                                class="p-button-sm"
                                                @click="openApprovalHistory(approval)"
                                                v-tooltip.bottom="lang().tooltip.view_details"
                                            />
                                            <Button
                                                v-if="application.user_id === authUser.id || isAdmin && application.status_id !== 3"
                                                type="button"
                                                icon="pi pi-trash"
                                                severity="danger"
                                                class="p-button-sm"
                                                :disabled="approval.approved === 3"
                                                @click="() => confirmRemoveApprover(approval)"
                                            />


                                        </div>
                                    </div>
                                </template>

                            </Card>

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
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ application.id }}</td>
                        </tr>
                        <tr
                            class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                        >
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.title }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ application.title }}</td>
                        </tr>
                        <tr
                            class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                        >
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.type }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">
                                {{ types.find(t => t.id === application.type)?.label || application.type }}
                            </td>
                        </tr>

                        <tr
                            class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                        >
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.project_id }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">
                                <span v-if="props.project">
                                    {{ props.project.title ?? lang().label.undefined }}
                                </span>
                                <span v-else>
                                    {{ lang().label.undefined }}
                                </span>
                            </td>
                        </tr>
                        <tr
                            class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                        >
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.currency_id }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600"> {{ application.currency.name }}</td>
                        </tr>
                        <tr
                            class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                        >
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.user_id }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ application.user.name }}</td>
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
                            v-if="application.status_id === 3 ?? scans.length > 0"
                            class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                        >
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.scans }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">
                                <div v-if="props.scans.length > 0">
                                    <ul class="list-none p-0 flex flex-col gap-1.5">
                                        <li v-for="(file, index) in props.scans" :key="index" class="flex items-center space-x-2">
                                            <a
                                                v-tooltip="lang().tooltip.download"
                                                :href="file.original_url"
                                                target="_blank"
                                                class="text-green-600 hover:text-green-800"
                                            >
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
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.status }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">
                                <span
                                    class="inline-block px-3 py-1 rounded-full text-sm font-semibold"
                                    :class="getStatusClass(application.status_id)"
                                >
                                      {{ getStatusLabel(application.status_id) }}
                                </span>
                            </td>
                        </tr>

                        <tr
                            class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                        >
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.created }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ application.created_at }}</td>
                        </tr>
                        <tr
                            class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                        >
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.updated }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ application.updated_at }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </section>

    </AuthenticatedLayout>
</template>

<script setup>

import {Head, usePage} from '@inertiajs/vue3';
import {defineProps, defineEmits, reactive, computed, ref} from 'vue';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {TrashIcon} from "@heroicons/vue/24/solid";
import EditLink from "@/Components/EditLink.vue";
import Delete from "@/Pages/Application/Delete.vue";
import Approve from "@/Pages/Application/Approve.vue";
import CancelApproval from "@/Pages/Application/CancelApproval.vue";
import DangerButton from "@/Components/DangerButton.vue";
import Button from "primevue/button";
import DeleteUser from "@/Pages/Application/DeleteUser.vue";
import EditUser from "@/Pages/Application/EditUser.vue";
import SendApproval from "@/Pages/Application/SendApproval.vue";
import Message from 'primevue/message';
import {Card} from "primevue";
import ApprovalHistory from "@/Pages/Application/ApprovalHistory.vue";
import {Link} from "@inertiajs/vue3";

const props = defineProps({
    show: Boolean,
    application: Object,
    title: String,
    breadcrumbs: Object,
    users: Object,
    statuses: Array,
    project: Object,
    files: Array,
    approvals: Object,
    types: Object,
    scans: Array
});

const confirmDialogRef = ref();

const authUser = usePage().props.auth.user;

const isAdmin = usePage().props.auth.user.roles?.some(role => role.name === 'superadmin');

const data = reactive({
    deleteOpen: false,
    project: null,
    editUserOpen: false,
    approveOpen: false,
    cancelApproval: false,
    deleteUserOpen: false,
    selectedApprovers: computed(() => props.approvals.map(a => a.user_id)),
    showHistory: false,
    historyApproval: null,
});

const openApprovalHistory = (approval) => {
    data.historyApproval = approval;
    data.showHistory = true;
};

const activeApprovals = computed(() => {
    const uniqueByUser = {};
    props.approvals
        .filter(a => a.approved !== -2)
        .forEach(a => {
            if (!uniqueByUser[a.user_id]) {
                uniqueByUser[a.user_id] = a;
            }
        });
    return Object.values(uniqueByUser);
});

const userApproval = computed(() =>
    activeApprovals.value.find(a => a.user_id === authUser.id && a.approved === 2)
);

const emit = defineEmits(["close"]);

const confirmRemoveApprover = (user) => {
    data.selectedUser = user;
    data.application = props.application;
    data.deleteUserOpen = true;
};

const getStatusClass = (statusId) => {
    const map = {
        1: 'bg-blue-100 text-blue-800',
        2: 'bg-yellow-100 text-yellow-800',
        3: 'bg-green-100 text-green-800',
        '-1': 'bg-red-100 text-red-800',
        '-2': 'bg-gray-200 text-gray-700',
    };

    return map[statusId] || 'bg-slate-200 text-slate-800';
};

const getStatusLabel = (statusId) => {
    const status = props.statuses.find(s => Number(s.id) === Number(statusId));
    return status ? status.label : '';
};

</script>
