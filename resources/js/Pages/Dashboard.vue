<script setup>
import Breadcrumb from "@/Components/Breadcrumb.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Widget from "@/Components/Widget.vue";
import { Head } from "@inertiajs/vue3";

const props = defineProps({
    widgets: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <AuthenticatedLayout :title="lang().label.dashboard">
        <Breadcrumb :title="lang().label.dashboard" :breadcrumbs="[]" />

        <!-- Widgets -->
        <div v-if="props.widgets.length > 0" class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
            <Widget
                v-for="widget in props.widgets"
                :key="widget.type"
                :widget="widget"
            />
        </div>

        <!-- Empty State -->
        <div v-else class="p-12 text-center">
            <p class="text-gray-500 dark:text-gray-400">
                {{ lang().label.no_widgets || "No widgets available" }}
            </p>
        </div>
    </AuthenticatedLayout>
</template>
