
<script setup>
import {defineProps, reactive} from 'vue';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {Head} from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import DangerButton from "@/Components/DangerButton.vue";
import {TrashIcon} from "@heroicons/vue/24/solid";
import EditLink from "@/Components/EditLink.vue";
import Delete from "@/Pages/ProductCategory/Delete.vue";

const props = defineProps({
    category: {
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
    category: null,
});

</script>

<template>
    <Head :title="props.title"/>
    <AuthenticatedLayout>
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs"/>

        <div class="space-y-4 bg-white dark:bg-slate-800 shadow rounded-t-lg">

            <div class="p-4 sm:p-8">
                <div class="block-header pb-3 mb-5 border-b border-gray-300 dark:border-neutral-600 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <h1 class="text-xl md:text-2xl font-bold">{{ category.title }}</h1>
                    <div class="actions flex flex-wrap gap-4">
                        <EditLink
                            :href="route('product_categories.edit', { product_category: category.id })"
                            class="px-4 py-2 rounded-md bg-blue-500 text-white hover:bg-blue-400 transition-all duration-300"
                            v-tooltip="lang().tooltip.edit"
                        >
                            {{ lang().tooltip.edit }}
                        </EditLink>
                        <DangerButton
                            type="button"
                            @click="(data.deleteOpen = true), (data.category = category)"
                            class="px-4 py-2 rounded-md bg-red-500 text-white hover:bg-red-400 transition-all duration-300"
                            v-tooltip="lang().tooltip.delete"
                        >
                            {{ lang().tooltip.delete }}
                            <TrashIcon class="w-5 h-5" />
                        </DangerButton>
                        <Delete
                            :show="data.deleteOpen"
                            @close="data.deleteOpen = false"
                            :category="data.category"
                            :title="props.title"
                        />
                    </div>
                </div>

                <!-- Таблица -->
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-300 dark:border-neutral-700 divide-y divide-gray-200 dark:divide-neutral-700">
                        <tbody>
                        <tr class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800">
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">ID</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ category.id }}</td>
                        </tr>
                        <tr class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800">
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.title }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ category.title }}</td>
                        </tr>
                        <tr class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800">
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.sort }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ category.sort }}</td>
                        </tr>

                        <tr class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800">
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.image }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">
                                <img
                                    v-if="category.image_url"
                                    :src="category.image_url"
                                    alt="category"
                                    class="h-20 w-auto rounded border"
                                />
                                <span v-else class="text-gray-400 italic">–</span>
                            </td>
                        </tr>

                        <tr class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800">
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.created }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ category.created_at }}</td>
                        </tr>
                        <tr class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800">
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.updated }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ category.updated_at }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
