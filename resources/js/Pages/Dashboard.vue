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
                        <p class="text-lg">{{ lang().label.total }}: <strong>{{ props.applicationsCount }}</strong></p>
                        <p class="text-lg text-green-600 dark:text-green-400">{{ lang().status.approved }}: <strong>{{ props.approvedApplicationsCount }}</strong></p>
                        <p class="text-lg text-red-600 dark:text-red-400">{{ lang().status.rejected }}: <strong>{{ props.rejectedApplicationsCount }}</strong></p>
                        <p class="text-lg text-yellow-600 dark:text-yellow-400">{{ lang().status.in_progress }}: <strong>{{ props.inProgressApplicationsCount }}</strong></p>
                        <p class="text-lg text-blue-600 dark:text-blue-400">{{ lang().status.new }}: <strong>{{ props.newApplicationsCount }}</strong></p>
                    </div>
                </div>
                <Link
                    :href="route('application.index')"
                    class="mt-6 inline-block bg-gray-800 dark:bg-gray-700 text-white px-4 py-2 rounded shadow font-semibold hover:bg-gray-700 dark:hover:bg-gray-600 self-start"
                >
                    {{ lang().label.view_all }}
                </Link>
            </div>

            <div class="p-6 rounded-xl shadow-lg bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-100 relative overflow-hidden flex flex-col justify-between">
                <div>
                    <DocumentTextIcon class="absolute top-4 right-4 w-16 h-16 opacity-10" />
                    <h2 class="text-2xl font-bold mb-4">{{ lang().label.contracts }}</h2>
                    <div class="space-y-2">
                        <p class="text-lg">{{ lang().label.total }}: <strong>{{ props.contractsCount }}</strong></p>
                        <p class="text-lg text-green-600 dark:text-green-400">{{ lang().status.approved }}: <strong>{{ props.approvedContractsCount }}</strong></p>
                        <p class="text-lg text-red-600 dark:text-red-400">{{ lang().status.rejected }}: <strong>{{ props.rejectedContractsCount }}</strong></p>
                        <p class="text-lg text-yellow-600 dark:text-yellow-400">{{ lang().status.in_progress }}: <strong>{{ props.inProgressContractsCount }}</strong></p>
                        <p class="text-lg text-blue-600 dark:text-blue-400">{{ lang().status.new }}: <strong>{{ props.newContractsCount }}</strong></p>
                    </div>
                </div>
                <Link
                    :href="route('contract.index')"
                    class="mt-6 inline-block bg-gray-800 dark:bg-gray-700 text-white px-4 py-2 rounded shadow font-semibold hover:bg-gray-700 dark:hover:bg-gray-600 self-start"
                >
                    {{ lang().label.view_all }}
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.bg-gray-100, .bg-gray-800 {
    transition: transform 0.3s;
}
.bg-gray-100:hover, .bg-gray-800:hover {
    transform: translateY(-5px);
}
</style>
