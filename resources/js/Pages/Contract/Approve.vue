<script setup>
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import Textarea from "primevue/textarea";
import { useForm } from "@inertiajs/vue3";
import { ref, watch } from "vue";

const props = defineProps({
    show: Boolean,
    title: String,
    contract: Object,
});

const emit = defineEmits(["close"]);

const visible = ref(false);

watch(() => props.show, (val) => (visible.value = val), { immediate: true });

const close = () => {
    visible.value = false;
    emit("close");
};

const form = useForm({
    comment: '',
});

const approveContract = () => {
    form.post(route("contract.approve", props.contract?.id), {
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
        :header="lang().label.approve_contract + ' ' + props.title"
        modal
        :style="{ width: '40vw' }"
        :breakpoints="{ '960px': '95vw' }"
        @hide="close"
    >
        <div>
            <div class="mb-4">
                <label for="comment" class="block text-xl font-bold text-gray-700 dark:text-gray-300 mb-3">
                    {{ lang().label.comment_optional }}
                </label>
                <Textarea
                    id="comment"
                    v-model="form.comment"
                    autoResize
                    rows="4"
                    class="w-full"
                    :placeholder="lang().placeholder.comment"
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
                    severity="success"
                    :label="form.processing ? lang().button.confirm : lang().button.confirm"
                    @click="approveContract"
                    :disabled="form.processing"
                />
            </div>
        </div>
    </Dialog>
</template>

