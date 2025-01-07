<script setup>
import {defineProps, reactive} from 'vue';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {Head} from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import DangerButton from "@/Components/DangerButton.vue";
import {TrashIcon} from "@heroicons/vue/24/solid";
import EditLink from "@/Components/EditLink.vue";
import Delete from "@/Pages/Departments/Delete.vue";

const props = defineProps({
    head_of_department_name: String || null,
    department: {
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
    department: null,
});

</script>


<template>
    <Head :title="props.title"/>
    <AuthenticatedLayout>
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs"/>

        <div class="space-y-4 bg-white dark:bg-slate-800 shadow rounded-t-lg">

            <div class="p-4 sm:p-8">

                <div class="block-header mb-5 border-b-amber-400">
                    <h1 class="text-2xl font-bold mb-4">{{ department.name }}</h1>
                    <div class="actions flex items-center gap-2">
                        <EditLink
                            :href="route('departments.edit', { department: department.id })"
                            class="px-4 py-2 rounded-md"
                            v-tooltip="lang().tooltip.edit"
                        >
                            {{ lang().tooltip.edit }}
                        </EditLink>
                        <DangerButton
                            type="button"
                            @click="(data.deleteOpen = true), (data.department = department)"
                            class="px-4 py-2 rounded-md"
                            v-tooltip="lang().tooltip.delete"
                        >
                            {{ lang().tooltip.delete }}
                            <TrashIcon class="w-5 h-5" />
                        </DangerButton>

                        <Delete
                            :show="data.deleteOpen"
                            @close="data.deleteOpen = false"
                            :department="data.department"
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
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ department.id }}</td>
                    </tr>
                    <tr
                        class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                    >
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.name }}</td>
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ department.name }}</td>
                    </tr>
                    <tr class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800">
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.head_of_department }}</td>
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">
                            {{ props.head_of_department_name ?? lang().label.not_assigned }}
                        </td>
                    </tr>

                    <tr
                        class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                    >
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.created }}</td>
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ department.created_at }}</td>
                    </tr>
                    <tr class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800">
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.updated }}</td>
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ department.updated_at }}</td>
                    </tr>
                    <tr class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800">
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.status }}</td>
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">
                             <span
                                 :class="[
                                        '.inline-flex items-center rounded-md px-2 py-1 text-sm font-medium ring-1 ring-inset',
                                        department.status === 1 ? 'bg-green-50 text-green-700 ring-green-600/20' : 'bg-red-50 text-red-700 ring-red-600/20'
                                    ]"
                             >
                                    {{ department.status === 1 ? lang().label.active : lang().label.inactive }}
                                </span>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
