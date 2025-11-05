<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";
import {
    CubeIcon,
    InboxIcon,
    DocumentTextIcon,
} from "@heroicons/vue/24/solid";

const props = defineProps({
    widget: {
        type: Object,
        required: true,
    },
});

const iconComponent = computed(() => {
    const icons = {
        cube: CubeIcon,
        inbox: InboxIcon,
        document: DocumentTextIcon,
    };
    return icons[props.widget.icon] || CubeIcon;
});

const buttonColorClass = computed(() => {
    const colors = {
        blue: "bg-blue-600 hover:bg-blue-700",
        green: "bg-green-600 hover:bg-green-700",
        purple: "bg-purple-600 hover:bg-purple-700",
    };
    return colors[props.widget.color] || "bg-gray-600 hover:bg-gray-700";
});
</script>

<template>
    <div class="p-6 rounded-xl shadow-lg bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-100 relative overflow-hidden flex flex-col justify-between">
        <div>
            <component
                :is="iconComponent"
                class="absolute top-4 right-4 w-16 h-16 opacity-10"
            />
            <h2 class="text-2xl font-bold mb-4">{{ widget.title }}</h2>

            <!-- Applications Widget -->
            <div v-if="widget.type === 'applications'" class="space-y-2">
                <p class="text-lg">
                    {{ lang().label.total }}:
                    <strong>{{ widget.data.total }}</strong>
                </p>

                <p
                    v-if="widget.data.new !== null"
                    class="text-lg text-blue-600 dark:text-blue-400"
                >
                    <Link
                        :href="route('application.index', { status_id: 1, order: 'asc', perPage: 10 })"
                        class="hover:underline"
                    >
                        {{ lang().status.new }}:
                        <strong>{{ widget.data.new }}</strong>
                    </Link>
                </p>

                <p class="text-lg text-yellow-600 dark:text-yellow-400">
                    <Link
                        :href="route('application.index', { status_id: 2, order: 'asc', perPage: 10 })"
                        class="hover:underline"
                    >
                        {{ lang().status.in_progress }}:
                        <strong>{{ widget.data.inProgress }}</strong>
                    </Link>
                </p>

                <p class="text-lg text-green-600 dark:text-green-400">
                    <Link
                        :href="route('application.index', { status_id: 3, order: 'asc', perPage: 10 })"
                        class="hover:underline"
                    >
                        {{ lang().status.approved }}:
                        <strong>{{ widget.data.approved }}</strong>
                    </Link>
                </p>

                <p class="text-lg text-red-600 dark:text-red-400">
                    <Link
                        :href="route('application.index', { status_id: -1, order: 'asc', perPage: 10 })"
                        class="hover:underline"
                    >
                        {{ lang().status.rejected }}:
                        <strong>{{ widget.data.rejected }}</strong>
                    </Link>
                </p>
            </div>

            <!-- Contracts Widget -->
            <div v-else-if="widget.type === 'contracts'" class="space-y-2">
                <p class="text-lg">
                    {{ lang().label.total }}:
                    <strong>{{ widget.data.total }}</strong>
                </p>

                <p
                    v-if="widget.data.new !== null"
                    class="text-lg text-blue-600 dark:text-blue-400"
                >
                    <Link
                        :href="route('contract.index', { status: 1, order: 'asc', perPage: 10 })"
                        class="hover:underline"
                    >
                        {{ lang().status.new }}:
                        <strong>{{ widget.data.new }}</strong>
                    </Link>
                </p>

                <p class="text-lg text-yellow-600 dark:text-yellow-400">
                    <Link
                        :href="route('contract.index', { status: 2, order: 'asc', perPage: 10 })"
                        class="hover:underline"
                    >
                        {{ lang().status.in_progress }}:
                        <strong>{{ widget.data.inProgress }}</strong>
                    </Link>
                </p>

                <p class="text-lg text-green-600 dark:text-green-400">
                    <Link
                        :href="route('contract.index', { status: 3, order: 'asc', perPage: 10 })"
                        class="hover:underline"
                    >
                        {{ lang().status.approved }}:
                        <strong>{{ widget.data.approved }}</strong>
                    </Link>
                </p>

                <p class="text-lg text-red-600 dark:text-red-400">
                    <Link
                        :href="route('contract.index', { status: -1, order: 'asc', perPage: 10 })"
                        class="hover:underline"
                    >
                        {{ lang().status.rejected }}:
                        <strong>{{ widget.data.rejected }}</strong>
                    </Link>
                </p>
            </div>

            <!-- Products Widget -->
            <div v-else-if="widget.type === 'products'" class="space-y-3">
                <!-- Statistics -->
                <div class="space-y-2">
                    <p class="text-lg">
                        {{ lang().label.total }}:
                        <strong>{{ widget.data.total }}</strong>
                    </p>
                    <p class="text-lg text-green-600 dark:text-green-400">
                        {{ lang().label.active || "Active" }}:
                        <strong>{{ widget.data.active }}</strong>
                    </p>
                    <p class="text-lg text-red-600 dark:text-red-400">
                        {{ lang().label.inactive || "Inactive" }}:
                        <strong>{{ widget.data.inactive }}</strong>
                    </p>
                </div>

                <!-- Products List -->
                <div v-if="widget.data.products && widget.data.products.length > 0" class="mt-4 space-y-2 border-t border-gray-300 dark:border-gray-600 pt-3">
                    <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-2">
                        {{ lang().label.recent || "Recent Items" }}:
                    </h3>
                    <div class="space-y-2">
                        <div
                            v-for="product in widget.data.products"
                            :key="product.id"
                            class="text-sm border-l-2 border-purple-400 dark:border-purple-600 pl-3 py-1"
                        >
                            <div class="font-semibold text-gray-900 dark:text-gray-100">
                                {{ product.title }}
                            </div>
                            <div class="text-xs text-gray-600 dark:text-gray-400 space-y-0.5">
                                <div v-if="product.category || product.brand">
                                    <span v-if="product.category">{{ product.category }}</span>
                                    <span v-if="product.category && product.brand"> • </span>
                                    <span v-if="product.brand">{{ product.brand }}</span>
                                </div>
                                <div v-if="product.serial_number" class="font-mono">
                                    S/N: {{ product.serial_number }}
                                </div>
                                <div v-if="product.inventory_number" class="font-mono">
                                    INV: {{ product.inventory_number }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Generic Widget -->
            <div v-else class="space-y-2">
                <pre class="text-sm">{{ widget.data }}</pre>
            </div>
        </div>

        <!-- View All Button -->
        <Link
            :href="widget.type === 'applications' ? route('application.index') :
                   widget.type === 'contracts' ? route('contract.index') :
                   widget.type === 'products' ? route('products.index') : '#'"
            :class="buttonColorClass"
            class="mt-6 inline-block text-white px-4 py-2 rounded-md font-medium transition-colors duration-200 self-start"
        >
            {{ lang().label.view_all }}
        </Link>
    </div>
</template>
