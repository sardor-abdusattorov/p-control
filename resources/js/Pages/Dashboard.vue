<script setup>
import Breadcrumb from "@/Components/Breadcrumb.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { DocumentTextIcon, InboxIcon } from "@heroicons/vue/24/solid";
import { Head, Link } from "@inertiajs/vue3";

const props = defineProps({
    applicationsCount: Number,
    approvedApplicationsCount: Number,
    rejectedApplicationsCount: Number,
    inProgressApplicationsCount: Number,
    newApplicationsCount: Number,
    contractsCount: Number,
    approvedContractsCount: Number,
    rejectedContractsCount: Number,
    inProgressContractsCount: Number,
    newContractsCount: Number,
});
</script>

<template>
    <Head :title="lang().label.dashboard" />
    <AuthenticatedLayout>
        <Breadcrumb :title="lang().label.dashboard" :breadcrumbs="[]" />

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <div class="p-6 rounded-xl shadow-lg bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-100 relative overflow-hidden flex flex-col justify-between">
                <div>
                    <InboxIcon class="absolute top-4 right-4 w-16 h-16 opacity-10" />
                    <h2 class="text-2xl font-bold mb-4">{{ lang().label.applications }}</h2>
                    <div class="space-y-2">
                        <p class="text-lg">
                            {{ lang().label.total }}:
                            <strong>{{ props.applicationsCount }}</strong>
                        </p>

                        <p
                            v-if="props.newApplicationsCount !== null"
                            class="text-lg text-blue-600 dark:text-blue-400"
                        >
                            <Link
                                :href="route('application.index', { status_id: 1, order: 'asc', perPage: 10 })"
                                class="hover:underline"
                            >
                                {{ lang().status.new }}:
                                <strong>{{ props.newApplicationsCount }}</strong>
                            </Link>
                        </p>

                        <p class="text-lg text-yellow-600 dark:text-yellow-400">
                            <Link :href="route('application.index', { status_id: 2, order: 'asc', perPage: 10 })" class="hover:underline">
                                {{ lang().status.in_progress }}:
                                <strong>{{ props.inProgressApplicationsCount }}</strong>
                            </Link>
                        </p>

                        <p class="text-lg text-green-600 dark:text-green-400">
                            <Link :href="route('application.index', { status_id: 3, order: 'asc', perPage: 10 })" class="hover:underline">
                                {{ lang().status.approved }}:
                                <strong>{{ props.approvedApplicationsCount }}</strong>
                            </Link>
                        </p>

                        <p class="text-lg text-red-600 dark:text-red-400">
                            <Link :href="route('application.index', { status_id: -1, order: 'asc', perPage: 10 })" class="hover:underline">
                                {{ lang().status.rejected }}:
                                <strong>{{ props.rejectedApplicationsCount }}</strong>
                            </Link>
                        </p>

                        <p class="text-lg text-gray-600 dark:text-gray-400">
                            <Link :href="route('application.index', { status_id: -2, order: 'asc', perPage: 10 })" class="hover:underline">
                                {{ lang().status.invalidated }}:
                                <strong>{{ props.invalidatedApplicationsCount }}</strong>
                            </Link>
                        </p>
                    </div>

                </div>
                <Link
                    :href="route('application.index')"
                    class="mt-6 inline-block bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors duration-200 self-start"
                >
                    {{ lang().label.view_all }}
                </Link>
            </div>

            <div class="p-6 rounded-xl shadow-lg bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-100 relative overflow-hidden flex flex-col justify-between">
                <div>
                    <DocumentTextIcon class="absolute top-4 right-4 w-16 h-16 opacity-10" />
                    <h2 class="text-2xl font-bold mb-4">{{ lang().label.contracts }}</h2>
                    <div class="space-y-2">
                        <p class="text-lg">
                            {{ lang().label.total }}:
                            <strong>{{ props.contractsCount }}</strong>
                        </p>

                        <p
                            v-if="props.newContractsCount !== null"
                            class="text-lg text-blue-600 dark:text-blue-400"
                        >
                            <Link
                                :href="route('contract.index', { status: 1, order: 'asc', perPage: 10 })"
                                class="hover:underline"
                            >
                                {{ lang().status.new }}:
                                <strong>{{ props.newContractsCount }}</strong>
                            </Link>
                        </p>

                        <p class="text-lg text-yellow-600 dark:text-yellow-400">
                            <Link :href="route('contract.index', { status: 2, order: 'asc', perPage: 10 })" class="hover:underline">
                                {{ lang().status.in_progress }}:
                                <strong>{{ props.inProgressContractsCount }}</strong>
                            </Link>
                        </p>

                        <p class="text-lg text-green-600 dark:text-green-400">
                            <Link :href="route('contract.index', { status: 3, order: 'asc', perPage: 10 })" class="hover:underline">
                                {{ lang().status.approved }}:
                                <strong>{{ props.approvedContractsCount }}</strong>
                            </Link>
                        </p>

                        <p class="text-lg text-red-600 dark:text-red-400">
                            <Link :href="route('contract.index', { status: -1, order: 'asc', perPage: 10 })" class="hover:underline">
                                {{ lang().status.rejected }}:
                                <strong>{{ props.rejectedContractsCount }}</strong>
                            </Link>
                        </p>

                        <p class="text-lg text-gray-600 dark:text-gray-400">
                            <Link :href="route('contract.index', { status: -2, order: 'asc', perPage: 10 })" class="hover:underline">
                                {{ lang().status.invalidated }}:
                                <strong>{{ props.invalidatedContractsCount }}</strong>
                            </Link>
                        </p>
                    </div>

                </div>
                <Link
                    :href="route('contract.index')"
                    class="mt-6 inline-block bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md font-medium transition-colors duration-200 self-start"
                >
                    {{ lang().label.view_all }}
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>