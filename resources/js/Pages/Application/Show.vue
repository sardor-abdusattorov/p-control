<template>
    <Head :title="props.title" />
    <AuthenticatedLayout>
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs" />
        <section class="space-y-4 bg-white dark:bg-slate-800 shadow rounded-t-lg">
            <div class="border-b border-gray-300 dark:border-neutral-600 card-header flex justify-between items-center p-4 bg-gray-100 dark:bg-slate-900 rounded-t-md">
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

                <div class="block-header mb-5 flex-1 flex items-center justify-between pb-3 border-b border-gray-300 dark:border-neutral-600">
                    <h1 class="text-2xl font-bold">{{ application.title }}</h1>
                    <div class="actions flex items-center gap-2">
                        <EditLink
                            :href="route('application.edit', { application: application.id })"
                            class="px-4 py-2 rounded-md uppercase"
                            v-tooltip="lang().tooltip.edit  "
                            v-show="can(['update contract'])"
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
        </section>
    </AuthenticatedLayout>
</template>

<script setup>
import {Head, Link, useForm} from '@inertiajs/vue3';
import {defineProps, defineEmits, reactive} from 'vue';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import Badge from "primevue/badge";
import {TrashIcon} from "@heroicons/vue/24/solid";
import EditLink from "@/Components/EditLink.vue";
import Delete from "@/Pages/Application/Delete.vue";
import DangerButton from "@/Components/DangerButton.vue";

const props = defineProps({
    show: Boolean,
    application: Object,
    title: String,
    breadcrumbs: Object,
    statuses: Array,
    project: Object,
    files: Array,
});

const data = reactive({
    deleteOpen: false,
    project: null,
});


const emit = defineEmits(["close"]);

const form = useForm({
    title: "",
    project_id: "",
    recipients: [],
    files: [],
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
        case 3:
            return 'danger';
        default:
            return 'info';
    }
};
</script>
