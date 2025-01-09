<template>
    <section class="space-y-6">
        <Modal :show="props.show" @close="emit('close')" :maxWidth="'2xl'">
            <div class="p-6 text-slate-900 dark:text-slate-100">
                <h2 class="text-lg font-medium">{{ title }}</h2>
                <div class="my-6">
                    <table class="table-auto w-full text-left">
                        <thead>
                        <tr>
                            <th>{{ lang().label.currency }}</th>
                            <th>{{ lang().label.contracts_length }}</th>
                            <th>Общая сумма</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(group, index) in groupedContracts" :key="index">
                            <td>{{ group.currency }}</td>
                            <td>{{ group.count }}</td>
                            <td>{{ formatNumber(group.total) }} {{ group.short_name }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-6 flex justify-end gap-4">
                    <Link :href="route('projects.related-contracts', { project: project.id })"
                          class="inline-flex items-center px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-200"
                          v-tooltip="lang().tooltip.view_contracts">
                        {{ lang().label.more }}
                    </Link>
                    <SecondaryButton @click="emit('close')" class="px-4 py-2">
                        {{ lang().button.close }}
                    </SecondaryButton>
                </div>
            </div>
        </Modal>
    </section>
</template>

<script setup>
import Modal from "@/Components/Modal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import { computed } from "vue";
import {Link} from "@inertiajs/vue3";

const props = defineProps({
    show: Boolean,
    title: String,
    project: Object,
    currencies: Array,
});
const formatNumber = (amount) => {
    if (!amount) return "-";
    return new Intl.NumberFormat("ru-RU", {
        style: "decimal",
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount);
};
const emit = defineEmits(["close"]);
const groupedContracts = computed(() => {
    if (!props.project?.contracts || props.project.contracts.length === 0) return [];
    const grouped = props.project.contracts.reduce((acc, contract) => {
        const currencyId = contract.currency_id || "unknown";
        const currency = props.currencies.find(curr => curr.id === currencyId);
        const currencyName = currency ? currency.name : "Неизвестно";
        const currencyShortName = currency ? currency.short_name : "";
        if (!acc[currencyId]) {
            acc[currencyId] = { currency: currencyName, short_name: currencyShortName, count: 0, total: 0 };
        }
        acc[currencyId].count += 1;
        acc[currencyId].total += parseFloat(contract.budget_sum) || 0;

        return acc;
    }, {});
    return Object.values(grouped);
});
</script>
