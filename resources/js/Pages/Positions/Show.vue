<script setup>
import {defineProps, reactive} from 'vue';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {Head} from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import DangerButton from "@/Components/DangerButton.vue";
import {TrashIcon} from "@heroicons/vue/24/solid";
import EditLink from "@/Components/EditLink.vue";
import Delete from "@/Pages/Positions/Delete.vue";

const props = defineProps({
    position: {
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
    createOpen: false,
    deleteOpen: false,
    position: null,
});
</script>


<template>
    <AuthenticatedLayout>
        <Head :title="title" />
        <Breadcrumb :title="position.name" :breadcrumbs="breadcrumbs" />

        <div class="space-y-4 bg-white dark:bg-slate-800 shadow rounded-t-lg">

            <div class="p-4 sm:p-8">

                <div class="block-header mb-5 border-b-amber-400">
                    <h1 class="text-2xl font-bold mb-4">{{ position.name }}</h1>
                    <div class="actions flex items-center gap-2">
                        <EditLink
                            :href="route('positions.edit', { position: position.id })"
                            class="px-4 py-2 rounded-md"
                            v-tooltip="lang().tooltip.edit"
                        >
                            {{ lang().tooltip.edit }}
                        </EditLink>
                        <DangerButton
                            type="button"
                            @click="(data.deleteOpen = true), (data.position = position)"
                            class="px-4 py-2 rounded-md"
                            v-tooltip="lang().tooltip.delete"
                        >
                            {{ lang().tooltip.delete }}
                            <TrashIcon class="w-5 h-5" />
                        </DangerButton>

                        <Delete
                            :show="data.deleteOpen"
                            @close="data.deleteOpen = false"
                            :position="data.position"
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
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ position.id }}</td>
                    </tr>
                    <tr
                        class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                    >
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.name }}</td>
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ position.name }}</td>
                    </tr>
                    <tr
                        class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                    >
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.created }}</td>
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ position.created_at }}</td>
                    </tr>
                    <tr class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800">
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.updated }}</td>
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ position.updated_at }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
