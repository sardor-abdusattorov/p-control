<script setup>
import Breadcrumb from "@/Components/Breadcrumb.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { CheckCircleIcon, ClockIcon, DocumentTextIcon, InboxIcon } from "@heroicons/vue/24/solid";
import { Head, Link } from "@inertiajs/vue3";

const props = defineProps({
    completedTasksCount: Number,
    totalTasksCount: Number,
    applicationsCount: Number,
    contractsCount: Number,
    pendingTasksCount: Number,
});
</script>

<template>
    <Head :title="lang().label.dashboard" />
    <AuthenticatedLayout>
        <Breadcrumb :title="'Dashboard'" :breadcrumbs="[]" />
        <div class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-6">
                <Link
                    :href="route('task.index')"
                    class="px-4 py-6 flex justify-between bg-green-600/70 dark:bg-green-500/80 items-center overflow-hidden rounded-lg shadow-md ripple-effect"
                >
                    <div class="flex flex-col">
                        <p class="text-white text-lg md:text-xl xl:text-2xl font-semibold">
                            {{ lang().label.completed_tasks }}
                        </p>
                        <p class="text-lg md:text-base xl:text-lg uppercase mt-4 text-white">
                            <span class="mr-2">{{ props.completedTasksCount }}</span> /
                            <span class="ml-2">{{ props.totalTasksCount }}</span>
                        </p>
                    </div>
                    <div>
                        <CheckCircleIcon class="w-12 md:w-14 xl:w-16 h-auto text-white" />
                    </div>
                </Link>

                <Link
                    :href="route('task.index')"
                    class="ripple-effect px-4 py-6 flex justify-between bg-amber-600/70 dark:bg-amber-500/80 items-center overflow-hidden rounded-lg shadow-md"
                >
                    <div class="flex flex-col">
                        <p class="text-white text-lg md:text-xl xl:text-2xl font-semibold">
                            {{ lang().label.pending_tasks }}
                        </p>
                        <p class="text-lg md:text-base xl:text-lg uppercase mt-4 text-white">
                            <span class="mr-2">{{ props.pendingTasksCount }}</span>
                        </p>
                    </div>
                    <div>
                        <ClockIcon class="w-12 md:w-14 xl:w-16 h-auto text-white" />
                    </div>
                </Link>

                <Link
                    :href="route('contract.index')"
                    class="ripple-effect px-4 py-6 flex justify-between bg-blue-600/70 dark:bg-blue-500/80 items-center overflow-hidden rounded-lg shadow-md"
                >
                    <div class="flex flex-col">
                        <p class="text-white text-lg md:text-xl xl:text-2xl font-semibold">
                            {{ lang().label.contracts }}
                        </p>
                        <p class="text-lg md:text-base xl:text-lg uppercase mt-4 text-white">
                            <span class="mr-2">{{ props.contractsCount }}</span>
                        </p>
                    </div>
                    <div>
                        <DocumentTextIcon class="w-12 md:w-14 xl:w-16 h-auto text-white" />
                    </div>
                </Link>

                <Link
                    :href="route('application.index')"
                    class="ripple-effect px-4 py-6 flex justify-between bg-red-600/70 dark:bg-red-500/80 items-center overflow-hidden rounded-lg shadow-md"
                >
                    <div class="flex flex-col">
                        <p class="text-white text-lg md:text-xl xl:text-2xl font-semibold">
                            {{ lang().label.applications }}
                        </p>
                        <p class="text-lg md:text-base xl:text-lg uppercase mt-4 text-white">
                            <span class="mr-2">{{ props.applicationsCount }}</span>
                        </p>
                    </div>
                    <div>
                        <InboxIcon class="w-12 md:w-14 xl:w-16 h-auto text-white" />
                    </div>
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
.ripple-effect {
    position: relative;
    overflow: hidden;
    cursor: pointer;
}

.ripple-effect::after {
    content: '';
    position: absolute;
    width: 100px;
    height: 100px;
    top: 50%;
    left: 50%;
    background: rgba(255, 255, 255, 0.1);
    opacity: 0;
    transform: translate(-50%, -50%) scale(0);
    border-radius: 50%;
    pointer-events: none;
    transition: transform 0.1s ease-out, opacity 0.1s ease-out;
}

.ripple-effect:active::after {
    opacity: 1;
    transform: translate(-50%, -50%) scale(4);
}
</style>
