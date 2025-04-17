<script setup>
import Dialog from "primevue/dialog";
import Textarea from "primevue/textarea";
import Button from "primevue/button";
import { useForm } from "@inertiajs/vue3";
import { ref, watch } from "vue";

const props = defineProps({
    show: Boolean,
    title: String,
    contract: Object,
});

const emit = defineEmits(["close"]);

const visible = ref(false);

watch(
    () => props.show,
    (val) => (visible.value = val),
    { immediate: true }
);

const close = () => {
    visible.value = false;
    emit("close");
};

const form = useForm({
    reason: "",
});

const cancelContract = () => {
    form.post(route("contract.cancel", props.contract?.id), {
        preserveScroll: true,
        onSuccess: () => {
            close();
            form.reset();
        },
    });
};
</script>

<template>
    <Dialog
        v-model:visible="visible"
        :header="lang().label.cancel_contract + ' - ' + props.title"
        modal
        :style="{ width: '40vw' }"
        :breakpoints="{ '960px': '95vw' }"
        @hide="close"
    >
        <div class="mb-4">
            <label
                for="reason"
                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
            >
                {{ lang().label.reason }}
            </label>
            <Textarea
                id="reason"
                v-model="form.reason"
                rows="4"
                class="w-full"
                :placeholder="lang().placeholder.enter_note"
            />
        </div>

        <div class="flex justify-end gap-2 mt-6">
            <Button
                type="button"
                severity="secondary"
                :label="lang().button.close"
                @click="close"
                :disabled="form.processing"
            />
            <Button
                type="button"
                severity="danger"
                :label="form.processing ? lang().button.confirm : lang().button.confirm"
                @click="cancelContract"
                :disabled="form.processing"
            />
        </div>
    </Dialog>
</template>
