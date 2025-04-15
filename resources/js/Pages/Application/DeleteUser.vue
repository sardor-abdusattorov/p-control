<script setup>
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import { useForm } from "@inertiajs/vue3";
import { watchEffect, ref } from "vue";
import Message from 'primevue/message';

const props = defineProps({
    show: Boolean,
    title: String,
    application: Object,
    user: Object,
});

const emit = defineEmits(["close"]);

const form = useForm({
    user_id: "",
});

const visible = ref(false);

watchEffect(() => {
    visible.value = props.show;
    form.user_id = props.user?.user_id || "";
});

const close = () => {
    visible.value = false;
    form.clearErrors();
    emit("close");
};


const destroy = () => {
    form.post(route("application.remove-approver", { application: props.application.id }), {
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
        :header="lang().label.delete"
        modal
        :style="{ width: '35vw' }"
        :breakpoints="{ '960px': '95vw' }"
        @hide="close"
    >
        <p class="text-sm text-slate-600 dark:text-slate-400 mb-4">
            {{ lang().label.delete_user_confirm }}
            <b>{{ props.user?.user_name || lang().label.unknown }}</b>?
        </p>

        <Message
            v-if="form.hasErrors"
            severity="error"
            :closable="false"
            class="mb-4"
        >
            {{ Object.values(form.errors)[0] }}
        </Message>


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
                :label="form.processing ? lang().button.delete : lang().button.delete"
                @click="destroy"
                :disabled="form.processing"
            />
        </div>
    </Dialog>
</template>
