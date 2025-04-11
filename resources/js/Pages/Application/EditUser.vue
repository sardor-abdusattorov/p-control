<script setup>
import Dialog from "primevue/dialog";
import MultiSelect from "primevue/multiselect";
import Button from "primevue/button";
import { useForm } from "@inertiajs/vue3";
import { watch, defineProps, defineEmits, ref } from "vue";

const props = defineProps({
    show: Boolean,
    title: String,
    application: Object,
    users: Array,
    approvals: Array,
});

const emit = defineEmits(["close"]);

const visible = ref(false);

watch(
    () => props.show,
    (val) => {
        visible.value = val;
    },
    { immediate: true }
);

const close = () => {
    visible.value = false;
    emit("close");
};

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
        onSuccess: () => close(),
    });
};
</script>

<template>
    <Dialog
        v-model:visible="visible"
        :header="lang().label.edit_approvers"
        modal
        :style="{ width: '50vw' }"
        :breakpoints="{ '960px': '95vw' }"
        @hide="close"
    >
        <form @submit.prevent="updateApprovers">
            <div class="mb-4">
                <MultiSelect
                    v-model="form.user_ids"
                    display="chip"
                    :options="props.users"
                    optionGroupLabel="label"
                    optionGroupChildren="items"
                    optionLabel="name"
                    optionValue="id"
                    filter
                    checkmark
                    :highlightOnSelect="false"
                    :placeholder="lang().placeholder.select_recipients"
                    :maxSelectedLabels="8"
                    class="w-full"
                />
            </div>

            <div class="flex justify-end gap-2 mt-6">
                <Button
                    type="button"
                    severity="secondary"
                    :label="lang().button.cancel"
                    @click="close"
                    :disabled="form.processing"
                />
                <Button
                    type="submit"
                    severity="primary"
                    :label="form.processing ? lang().button.saving : lang().button.save"
                    :disabled="form.processing"
                />
            </div>
        </form>
    </Dialog>
</template>
