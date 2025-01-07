<script setup>
import {defineProps, reactive} from 'vue';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {Head} from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import DangerButton from "@/Components/DangerButton.vue";
import {TrashIcon} from "@heroicons/vue/24/solid";
import EditLink from "@/Components/EditLink.vue";
import Delete from "@/Pages/User/Delete.vue";

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    position: {
        type: Object,
        required: false,
    },
    department: {
        type: Object,
        required: false,
    },
    title: {
        type: String,
        required: true,
    },
    breadcrumbs: {
        type: Object,
        required: true,
    },
    image: {
        type: String,
        required: false,
        default: '',
    },
});

const data = reactive({
    deleteOpen: false,
    user: null,
});

</script>


<template>
    <Head :title="props.title"/>
    <AuthenticatedLayout>
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs"/>

        <div class="space-y-4 bg-white dark:bg-slate-800 shadow rounded-t-lg">

            <div class="p-4 sm:p-8">

                <div class="block-header mb-5 border-b-amber-400">
                    <h1 class="text-2xl font-bold mb-4">{{user.name }}</h1>
                    <div class="actions flex items-center gap-2">
                        <EditLink
                            :href="route('user.edit', { user: user.id })"
                            class="px-4 py-2 rounded-md"
                            v-tooltip="lang().tooltip.edit"
                        >
                            {{ lang().tooltip.edit }}
                        </EditLink>
                        <DangerButton
                            type="button"
                            @click="(data.deleteOpen = true), (data.user = user)"
                            class="px-4 py-2 rounded-md"
                            v-tooltip="lang().tooltip.delete"
                        >
                            {{ lang().tooltip.delete }}
                            <TrashIcon class="w-5 h-5" />
                        </DangerButton>

                        <Delete
                            :show="data.deleteOpen"
                            @close="data.deleteOpen = false"
                            :user="data.user"
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
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ user.id }}</td>
                    </tr>
                    <tr
                        class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                    >
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.name }}</td>
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ user.name }}</td>
                    </tr>
                    <tr
                        class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                    >
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.email }}</td>
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ user.email }}</td>
                    </tr>
                    <tr
                        class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                    >
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.email_verified_at }}</td>
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ user.email_verified_at }}</td>
                    </tr>
                    <tr
                        class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                    >
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.department_id }}</td>
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ department.name }}</td>
                    </tr>
                    <tr
                        class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                    >
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.position_id }}</td>
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ position.name }}</td>
                    </tr>
                    <tr
                        class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                    >
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.telegram_id }}</td>
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ user.telegram_id }}</td>
                    </tr>

                    <tr
                        class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                    >
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.created }}</td>
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ user.created_at }}</td>
                    </tr>
                    <tr class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800">
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.updated }}</td>
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ user.updated_at }}</td>
                    </tr>
                    <tr
                        class="odd:bg-white even:bg-gray-100 dark:odd:bg-neutral-900 dark:even:bg-neutral-800"
                    >
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">{{ lang().label.image }}</td>
                        <td class="py-4 px-4 border border-gray-300 dark:border-neutral-600">

                            <img v-if="props.image" :src="props.image" alt="User Image" class="w-16 h-16 object-cover rounded-full" />

                            <span v-else>{{ lang().label.no_image }}</span>
                        </td>
                    </tr>

                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
