<template>
    <Dialog v-model:visible="visible" modal :header="lang().label.confirmation" :style="{ width: '400px' }">
        <p>{{lang().label.submit_confirmation}}</p>
        <template #footer>
            <Button :label="lang().button.cancel" class="p-button-text" @click="visible = false" />
            <Button :label="lang().button.confirm" class="p-button-danger" @click="send" />
        </template>
    </Dialog>
</template>

<script setup>
import { ref } from 'vue';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import { router } from '@inertiajs/vue3';

const visible = ref(false);
const contract = ref(null);

const open = (app) => {
    contract.value = app;
    visible.value = true;
};

const send = () => {
    if (!contract.value) return;
    router.post(route("contract.submit", { contract: contract.value.id }), {}, {
        preserveScroll: true,
        onSuccess: () => {
            visible.value = false;
            contract.value = null;
        },
    });
};

defineExpose({ open });
</script>
