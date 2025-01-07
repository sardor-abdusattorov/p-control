<script setup>
import {defineProps, reactive} from 'vue';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {Head} from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import DangerButton from "@/Components/DangerButton.vue";
import {TrashIcon} from "@heroicons/vue/24/solid";
import EditLink from "@/Components/EditLink.vue";
import Delete from "@/Pages/Projects/Delete.vue";

const props = defineProps({
    project: {
        type: Object,
        required: true,
    },
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

</script>

<template>
    <Head :title="props.title"/>
    <AuthenticatedLayout>
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs"/>

        <div class="space-y-4 bg-white dark:bg-slate-800 shadow rounded-t-lg">

            <div class="p-4 sm:p-8">

                <div class="block-header mb-5 border-b-amber-400">
                    <h1 class="text-2xl font-bold mb-4">{{ project.title }}</h1>
                    <div class="actions flex items-center gap-2">
                        <EditLink
                            :href="route('projects.edit', { project: project.id })"
                            class="px-4 py-2 rounded-md"
                            v-tooltip="lang().tooltip.edit"
                            v-show="can(['update project'])"
                        >
                            {{ lang().tooltip.edit }}
                        </EditLink>
                        <DangerButton
                            type="button"
                            @click="(data.deleteOpen = true), (data.project = project)"
                            class="px-4 py-2 rounded-md"
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
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.budget_sum }}</td>
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ project.budget_sum }}</td>
                    </tr>
                    <tr
                        class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                    >
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.budget_balance }}</td>
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ project.budget_balance }}</td>
                    </tr>
                    <tr
                        class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                    >
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.project_year }}</td>
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ project.project_year }}</td>
                    </tr>

                    <tr
                        class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                    >
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.deadline }}</td>
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ project.deadline }}</td>
                    </tr>

                    <tr class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800">
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.user_id }}</td>
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ project.user ? project.user.name : 'No user assigned' }}</td>
                    </tr>

                    <tr class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800">
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.status }}</td>
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ project.status ? project.status.name : 'No status assigned' }}</td>
                    </tr>

                    <tr class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800">
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.currency }}</td>
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ project.currency ? project.currency.name : 'No currency assigned' }}</td>
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
    </AuthenticatedLayout>
</template>
