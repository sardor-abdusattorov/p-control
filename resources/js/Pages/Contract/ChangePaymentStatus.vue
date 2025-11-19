<script setup>
import Dialog from "primevue/dialog";
import Select from "primevue/select";
import Button from "primevue/button";
import Message from 'primevue/message';
import { useForm } from "@inertiajs/vue3";
import { watch, defineProps, defineEmits, ref } from "vue";

const props = defineProps({
    show: Boolean,
    contract: Object,
    paymentStatuses: Array,
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
    form.clearErrors('payment_status');
    emit('close');
};

const form = useForm({
    payment_status: 0,
});

watch(
    () => visible.value,
    (val) => {
        if (val && props.contract) {
            form.payment_status = props.contract.payment_status || 0;
        }
    },
    { immediate: true }
);


const updatePaymentStatus = () => {
    form.put(route("contract.update-payment-status", { contract: props.contract.id }), {
        preserveScroll: true,
        onSuccess: () => close(),
        onError: () => {},
    });
};

</script>

<template>
    <Dialog
        v-model:visible="visible"
        :header="lang().label.change_payment_status"
        modal
        :style="{ width: '40vw' }"
        :breakpoints="{ '960px': '95vw' }"
        @hide="close"
    >
        <form @submit.prevent="updatePaymentStatus">
            <div class="mb-4">
                <Select
                    v-model="form.payment_status"
                    :options="props.paymentStatuses"
                    optionLabel="label"
                    optionValue="id"
                    class="w-full"
                    checkmark
                    :highlightOnSelect="false"
                    :placeholder="lang().placeholder.select_payment_status"
                />
                <Message v-if="form.errors.payment_status" severity="error" :closable="false" class="mt-4">
                    {{ form.errors.payment_status }}
                </Message>
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
