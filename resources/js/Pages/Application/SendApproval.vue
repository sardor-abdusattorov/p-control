<template>
    <Dialog v-model:visible="visible" modal header="Подтверждение" :style="{ width: '400px' }">
        <p>Вы уверены, что хотите отправить заявку на согласование?</p>
        <template #footer>
            <Button label="Отмена" class="p-button-text" @click="visible = false" />
            <Button label="Отправить" class="p-button-danger" @click="send" />
        </template>
    </Dialog>
</template>

<script setup>
import { ref } from 'vue';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import { router } from '@inertiajs/vue3';

const visible = ref(false);
const application = ref(null);

const open = (app) => {
    application.value = app;
    visible.value = true;
};

const send = () => {
    if (!application.value) return;
    router.post(route("application.submit", { application: application.value.id }), {}, {
        preserveScroll: true,
        onSuccess: () => {
            visible.value = false;
            application.value = null;
        },
    });
};

defineExpose({ open });
</script>
