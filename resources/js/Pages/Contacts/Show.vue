<template>
    <Head :title="props.title"/>
    <AuthenticatedLayout>
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs"/>
        <div class="space-y-4 bg-white dark:bg-slate-800 shadow rounded-t-lg">
            <div class="p-4 sm:p-8">
                <div class="block-header pb-3 mb-5 border-b border-gray-300 dark:border-neutral-600 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <h1 class="text-xl md:text-2xl font-bold">{{ contact.title }}</h1>
                    <div class="actions flex flex-wrap gap-4">
                        <EditLink
                            :href="route('contacts.edit', { contact: props.contact.id })"
                            class="px-4 py-2 rounded-md bg-blue-500 text-white hover:bg-blue-400 transition-all duration-300"
                            v-tooltip="lang().tooltip.edit"
                        >
                            {{ lang().tooltip.edit }}
                        </EditLink>
                        <DangerButton
                            type="button"
                            @click="(data.deleteOpen = true), (data.contact = contact)"
                            class="px-4 py-2 rounded-md bg-red-500 text-white hover:bg-red-400 transition-all duration-300"
                            v-tooltip="lang().tooltip.delete"
                        >
                            {{ lang().tooltip.delete }}
                            <TrashIcon class="w-5 h-5" />
                        </DangerButton>
                        <Delete
                            :show="data.deleteOpen"
                            @close="data.deleteOpen = false"
                            :contact="data.contact"
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
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ contact.id }}</td>
                        </tr>
                        <tr class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800">
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.title }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ contact.title }}</td>
                        </tr>
                        <tr class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800">
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.prefix }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ contact.prefix }}</td>
                        </tr>
                        <tr class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800">
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.firstname }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ contact.firstname }}</td>
                        </tr>
                        <tr class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800">
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.lastname }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ contact.lastname }}</td>
                        </tr>
                        <tr class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800">
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.company }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ contact.company }}</td>
                        </tr>
                        <tr class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800">
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.phone }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ contact.phone }}</td>
                        </tr>
                        <tr class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800">
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.email }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ contact.email }}</td>
                        </tr>
                        <tr class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800">
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.cellphone }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ contact.cellphone }}</td>
                        </tr>

                        <tr class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800">
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.address }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ contact.address }}</td>
                        </tr>

                        <tr class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800">
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.address2 }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ contact.address2 }}</td>
                        </tr>

                        <tr class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800">
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.post_box }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ contact.post_box }}</td>
                        </tr>

                        <tr class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800">
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.zip_code }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ contact.zip_code }}</td>
                        </tr>

                        <tr class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800">
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.country }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ country?.name }}</td>
                        </tr>

                        <tr class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800">
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.city }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ city?.name }}</td>
                        </tr>

                        <tr class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800">
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.language }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ contact.language }}</td>
                        </tr>


                        <tr class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800">
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.user_id }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ owner?.name }}</td>
                        </tr>

                        <tr class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800">
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.category }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ category?.title }}</td>
                        </tr>

                        <tr class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800">
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.subcategory }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ subcategory?.title }}</td>
                        </tr>

                        <tr class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800">
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.status }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">
                            <span
                                :class="[
                                    'inline-flex items-center rounded-md px-2 py-1 text-sm font-medium ring-1 ring-inset',
                                    contact.status === 1 ? 'bg-green-50 text-green-700 ring-green-600/20' : 'bg-red-50 text-red-700 ring-red-600/20'
                                ]"
                            >
                                {{ contact.status === 1 ? lang().label.active : lang().label.inactive }}
                            </span>
                            </td>
                        </tr>
                        <tr class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800">
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.created }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ contact.created_at }}</td>
                        </tr>
                        <tr class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800">
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.updated }}</td>
                            <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ contact.updated_at }}</td>
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
import {Head} from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import DangerButton from "@/Components/DangerButton.vue";
import {TrashIcon} from "@heroicons/vue/24/solid";
import EditLink from "@/Components/EditLink.vue";
import Delete from "@/Pages/Contacts/Delete.vue";

const props = defineProps({
    contact: Object,
    category: Object,
    subcategory: Object,
    owner: Object,
    country: Object,
    city: Object,
    title: String,
    breadcrumbs: Object,
});

const data = reactive({
    deleteOpen: false,
    category: null,
});

</script>
