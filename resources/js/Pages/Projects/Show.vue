<template>
    <AuthenticatedLayout :title="props.title">
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs"/>
        <div class="space-y-4 bg-white dark:bg-slate-800 shadow rounded-t-lg">
            <!-- Header -->
            <div class="p-4 sm:p-8">
                <div class="block-header pb-3 mb-5 border-b border-gray-300 dark:border-neutral-600 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <h1 class="text-xl md:text-2xl font-bold">{{ project.title }}</h1>
                    <div class="actions flex flex-wrap gap-4">
                        <EditLink
                            :href="route('projects.edit', { project: project.id })"
                            class="px-4 py-2 rounded-md bg-blue-500 text-white hover:bg-blue-400 transition-all duration-300"
                            v-tooltip="lang().tooltip.edit"
                            v-show="can(['update project'])"
                        >
                            {{ lang().tooltip.edit }}
                        </EditLink>
                        <Link :href="route('projects.related-contracts', { project: project.id })"
                              class="px-4 py-2 rounded-md flex items-center gap-2 justify-center border border-transparent shadow-lg bg-green-500 text-white hover:bg-green-400 transition-all duration-300"
                              v-tooltip="lang().tooltip.view_contracts"
                        >
                            {{ lang().label.related_contracts }}
                            <DocumentTextIcon class="w-4 h-4" />
                        </Link>
                        <DangerButton
                            type="button"
                            @click="(data.deleteOpen = true), (data.project = project)"
                            class="px-4 py-2 rounded-md bg-red-500 text-white hover:bg-red-400 transition-all duration-300"
                            v-tooltip="lang().tooltip.delete"
                            v-show="can(['delete project'])"
                        >
                            {{ lang().tooltip.delete }}
                            <TrashIcon class="w-5 h-5" />
                        </DangerButton>
                        <Delete
                            :show="data.deleteOpen"
                            @close="data.deleteOpen = false"
                            :project="data.project"
                            :title="props.title"
                        />
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
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ project.id }}</td>
                        </tr>
                        <tr
                            class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                        >
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.project_number }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ project.project_number }}</td>
                        </tr>
                        <tr
                            class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                        >
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.title }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ project.title }}</td>
                        </tr>
                        <tr
                            class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                        >
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.project_category }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ project.category ? project.category.title : lang().label.undefined }}</td>
                        </tr>
                        <tr
                            class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                        >
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.sort }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ project.sort }}</td>
                        </tr>

                        <tr class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800">
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.status }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">
                                <Badge :value="getStatusLabel(project.status_id)" :severity="getStatusSeverity(project.status_id)" />
                            </td>
                        </tr>

                        <tr
                            class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                        >
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.created }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ project.created_at }}</td>
                        </tr>
                        <tr class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800">
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.updated }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ project.updated_at }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>

<script setup>
import {defineProps, reactive} from 'vue';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {Link} from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import DangerButton from "@/Components/DangerButton.vue";
import {DocumentTextIcon, TrashIcon} from "@heroicons/vue/24/solid";
import EditLink from "@/Components/EditLink.vue";
import Delete from "@/Pages/Projects/Delete.vue";
import Badge from "primevue/badge";

const props = defineProps({
    project: {
        type: Object,
        required: true,
    },
    statuses: Object,
    title: {
        type: String,
        required: true,
    },
    breadcrumbs: {
        type: Object,
        required: true,
    },
});

const data = reactive({
    deleteOpen: false,
    project: null,
});

const getStatusLabel = (statusId) => {
    const status = props.statuses.find(s => s.id === statusId);
    return status ? status.label : '';
};

const getStatusSeverity = (statusId) => {
    switch (statusId) {
        case 1:
            return 'info';
        case 2:
            return 'success';
        case -1:
            return 'danger';
        default:
            return 'info';
    }
};


</script>
