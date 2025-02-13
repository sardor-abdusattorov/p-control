<script setup>
import Modal from "@/Components/Modal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import MultiSelect from "primevue/multiselect";
import { useForm } from "@inertiajs/vue3";
import { watch, defineProps, defineEmits } from "vue";

const props = defineProps({
    show: Boolean,
    title: String,
    application: Object,
    users: Array,
    approvals: Array,
});

const emit = defineEmits(["close"]);

const form = useForm({
    user_ids: [],
});

watch(
    () => props.approvals,
    (newApprovals) => {
        form.user_ids = newApprovals ? newApprovals.map(a => a.user_id) : [];
    },
    { immediate: true }
);
const updateApprovers = () => {
    form.put(route("application.update-approvers", { application: props.application.id }), {
        preserveScroll: true,
        onSuccess: () => {
            emit("close");
        },
    });
};
</script>

<template>
    <Modal :show="props.show" @close="emit('close')" :maxWidth="'lg'">
        <form class="p-6" @submit.prevent="updateApprovers">
            <h2 class="text-lg font-medium text-slate-900 dark:text-slate-100">
                {{ lang().label.edit_approvers }}
            </h2>

            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                    {{ lang().label.select_approvers }}
                </label>
                <MultiSelect
                    v-model="form.user_ids"
                    display="chip"
                    optionValue="id"
                    :options="props.users"
                    optionLabel="name"
                    filter
                    checkmark
                    :highlightOnSelect="false"
                    :placeholder="lang().placeholder.select_recipients"
                    :maxSelectedLabels="8"
                    class="w-full"
                    :pt="{
                                option: { class: 'custom-option' },
                                dropdown: { style: { maxWidth: '300px' } },
                                overlay: { class: 'parent-wrapper-class' }
                            }"
                />
            </div>

            <div class="mt-6 flex justify-end">
                <SecondaryButton @click="emit('close')">
                    {{ lang().button.cancel }}
                </SecondaryButton>
                <PrimaryButton class="ml-3" :disabled="form.processing">
                    {{ form.processing ? lang().button.saving + "..." : lang().button.save }}
                </PrimaryButton>
            </div>
        </form>
    </Modal>
</template>
