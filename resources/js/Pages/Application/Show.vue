<template>
    <Head :title="props.title" />
    <AuthenticatedLayout>
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs" />
        <section class="space-y-4 bg-white dark:bg-slate-800 shadow rounded-t-lg">
            <!-- Header -->
            <div class="border-b border-gray-300 dark:border-neutral-600 card-header flex flex-col md:flex-row justify-between items-start md:items-center p-4 bg-gray-100 dark:bg-slate-900 rounded-t-md gap-4">
                <div class="flex justify-start gap-4">
                    <Link
                        :href="route('application.show', { application: application.id })"
                        class="px-6 py-3 rounded-md bg-blue-600 text-white hover:bg-blue-700 shadow-lg transition-all duration-300"
                    >
                        {{ lang().label.information }}
                    </Link>
                    <Link
                        :href="route('application.chat', { id: application.id })"
                        class="px-6 py-3 rounded-md bg-green-600 text-white hover:bg-green-700 shadow-lg transition-all duration-300"
                    >
                        {{ lang().label.chat }}
                    </Link>
                </div>
            </div>

            <div class="mt-0 p-4">
                <div class="block-header mb-5 flex flex-col md:flex-row justify-between items-start md:items-center pb-3 border-b border-gray-300 dark:border-neutral-600 gap-4">
                    <h1 class="text-xl md:text-2xl font-bold">{{ application.title }}</h1>
                    <div class="actions flex flex-wrap gap-2">
                        <template v-if="application.type === 1">
                            <Button
                                v-show="can_approve"
                                type="button"
                                icon="pi pi-check-circle"
                                :label="lang().button.approve"
                                severity="success"
                                class="p-button-sm dark:text-white"
                                @click="(data.approveOpen = true), (data.application = application)"
                            />

                            <Button
                                v-show="can_approve"
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
                            />

                            <EditUser
                                :show="data.editUserOpen"
                                @close="data.editUserOpen = false"
                                :application="props.application"
                                :users="props.users"
                                :approvals="props.approvals"
                                :title="props.title"
                            />

                            <Approve
                                :show="can(['approve application']) && (application.status_id === 1 || application.status_id === 2) && data.approveOpen"
                                @close="data.approveOpen = false"
                                :application="data.application"
                                :title="props.title"
                            />


                            <CancelApproval
                                :show="can(['approve application']) && data.cancelApproval"
                                @close="data.cancelApproval = false"
                                :application="data.application"
                                :title="props.title"
                            />

                        </template>

                        <EditLink
                            v-if="application.type === 1 && authUser?.roles?.length > 0 &&
                              (authUser.roles[0].name === 'superadmin' ||
                              (can(['update application']) && application.user_id === authUser.id))"
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
                            v-show="can(['delete application'])"
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
                    </div>
                </div>

                <div class="p-2 sm:p-4 xs:p-3 bg-gray-100 dark:bg-neutral-800 rounded-lg shadow-md mb-4" v-if="application.type === 1">
                    <div class=" flex flex-wrap gap-2 items-center mb-3 justify-between">
                        <h2 class="text-lg font-bold">{{ lang().label.approval_status }}</h2>
                        <Button
                            type="button"
                            icon="pi pi-user-plus"
                            :label="lang().button.edit"
                            severity="info"
                            class="p-button-sm dark:text-white"
                            :disabled="application.status_id === 3"
                            @click="data.editUserOpen = true"
                            v-show="application.user_id === authUser.id"
                        />

                    </div>

                    <div class="space-y-2">
                        <div
                            v-if="approvals.length < 2 && application.user_id === authUser.id"
                            class="p-3 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300"
                        >
                            ⚠ {{ lang().label.min_approvers_warning }}
                        </div>


                        <div v-if="approvals.length">
                            <div v-for="approval in approvals" :key="approval.user_id"
                                 class="p-2 sm:p-4 xs:p-3 border border-gray-300 dark:border-neutral-700 rounded-md bg-white dark:bg-gray-900 shadow-md flex justify-between items-center">
                                <div class="details">
                                    <h3 class="text-md font-semibold mb-2">👤 {{ approval.user_name }}</h3>
                                    <p v-if="approval.approved === true" class="text-green-600 font-semibold">
                                        ✔ {{ lang().label.approved }} ({{ approval.approved_at }})
                                    </p>

                                    <p v-else class="text-orange-500 font-semibold">
                                        ✖ {{ lang().label.not_approved }}
                                        <span v-if="approval.reason" class="block text-sm font-normal text-red-600 dark:text-red-400 mt-1">
                                            📝 {{ approval.reason }}
                                        </span>
                                    </p>
                                </div>
                                <div class="flex gap-2 items-center" v-show="application.user_id === authUser.id">
                                    <form>
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

                        <p v-else class="text-gray-500 dark:text-gray-400 text-center">
                            {{ lang().label.no_data }}
                        </p>

                    </div>

                </div>

                <!-- Table -->
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
                            class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                        >
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.status }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">
                                <Badge :value="getStatusLabel(application.status_id)" :severity="getStatusSeverity(application.status_id)" />
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
import {Head, Link, useForm, usePage} from '@inertiajs/vue3';
import {defineProps, defineEmits, reactive, computed} from 'vue';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import Badge from "primevue/badge";
import {TrashIcon} from "@heroicons/vue/24/solid";
import EditLink from "@/Components/EditLink.vue";
import Delete from "@/Pages/Application/Delete.vue";
import Approve from "@/Pages/Application/Approve.vue";
import CancelApproval from "@/Pages/Application/CancelApproval.vue";
import DangerButton from "@/Components/DangerButton.vue";
import Button from "primevue/button";
import DeleteUser from "@/Pages/Application/DeleteUser.vue";
import EditUser from "@/Pages/Application/EditUser.vue";

const props = defineProps({
    show: Boolean,
    application: Object,
    title: String,
    breadcrumbs: Object,
    users: Object,
    statuses: Array,
    project: Object,
    files: Array,
    can_approve: Boolean,
    approvals: Object,
    types: Object,
});

const form = useForm({});

const authUser = usePage().props.auth.user;

const data = reactive({
    deleteOpen: false,
    project: null,
    editUserOpen: false,
    approveOpen: false,
    cancelApproval: false,
    deleteUserOpen: false,
    selectedApprovers: computed(() => props.approvals.map(a => a.user_id)),
});

const emit = defineEmits(["close"]);


const confirmRemoveApprover = (user) => {
    data.selectedUser = user;
    data.application = props.application;
    data.deleteUserOpen = true;
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
        case -1:
            return 'danger';
        default:
            return 'contrast';
    }
};

</script>
