<script setup>
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import { useForm } from "@inertiajs/vue3";
import { ref, watch } from "vue";

const props = defineProps({
    show: Boolean,
    title: String,
    application: Object,
});

const emit = defineEmits(["close"]);

const visible = ref(false);

// Синхронизируем `props.show` с локальной переменной
watch(() => props.show, (val) => (visible.value = val), { immediate: true });

const close = () => {
    visible.value = false;
    emit("close");
};

const form = useForm({});

const approveApplication = () => {
    form.post(route("application.approve", props.application?.id), {
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
        :header="lang().label.approve_application + ' ' + props.title"
        modal
        :style="{ width: '40vw' }"
        :breakpoints="{ '960px': '95vw' }"
        @hide="close"
    >
        <div>
            <p class="text-sm text-slate-600 dark:text-slate-400 mb-4">
                {{ lang().label.approve_application_confirm }}
            </p>

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
                    @click="approveApplication"
                    :disabled="form.processing"
                />
            </div>
        </div>
    </Dialog>
</template>
