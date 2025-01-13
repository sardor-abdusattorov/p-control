<script setup>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import Modal from "@/Components/Modal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import { useForm } from "@inertiajs/vue3";

const props = defineProps({
    show: Boolean,
    title: String,
    application: Object,
});

const emit = defineEmits(["close"]);

const form = useForm({});

const approveApplication = () => {
    form.post(route("application.approve", props.application?.id), {
        preserveScroll: true,
        onSuccess: () => {
            emit("close");
            form.reset();
        },
        onError: () => null,
        onFinish: () => null,
    });
};
</script>

<template>
    <section class="space-y-6">
        <Modal :show="props.show" @close="emit('close')" :maxWidth="'lg'">
            <form class="p-6" @submit.prevent="approveApplication">
                <h2
                    class="text-lg font-medium text-slate-900 dark:text-slate-100"
                >
                    {{ lang().label.approve_application }} {{ props.title }}
                </h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                    {{ lang().label.approve_application_confirm }}
                </p>
                <div class="mt-6 flex justify-end">
                    <SecondaryButton
                        :disabled="form.processing"
                        @click="emit('close')"
                    >
                        {{ lang().button.close }}
                    </SecondaryButton>
                    <PrimaryButton
                        class="ml-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="approveApplication"
                    >
                        {{
                            form.processing
                                ? lang().button.confirm + "..."
                                : lang().button.confirm
                        }}
                    </PrimaryButton>
                </div>
            </form>
        </Modal>
    </section>
</template>
