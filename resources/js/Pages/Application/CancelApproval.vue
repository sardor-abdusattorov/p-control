<script setup>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import Modal from "@/Components/Modal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import {Textarea} from "primevue";

const props = defineProps({
    show: Boolean,
    title: String,
    application: Object,
});

const emit = defineEmits(["close"]);

const reason = ref(""); // Поле для причины отказа

const form = useForm({
    reason: "", // Добавляем поле для формы
});

const cancelApplication = () => {
    form.reason = reason.value; // Записываем введенную причину

    form.post(route("application.cancel", props.application?.id), {
        preserveScroll: true,
        onSuccess: () => {
            emit("close");
            form.reset();
            reason.value = ""; // Очищаем поле после успешного запроса
        },
        onError: () => null,
        onFinish: () => null,
    });
};
</script>

<template>
    <section class="space-y-6">
        <Modal :show="props.show" @close="emit('close')" :maxWidth="'lg'">
            <form class="p-6" @submit.prevent="cancelApplication">
                <h2
                    class="text-lg font-medium text-slate-900 dark:text-slate-100"
                >
                    {{ lang().label.cancel_application }} {{ props.title }}
                </h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                    {{ lang().label.cancel_application_confirm }}
                </p>

                <!-- Поле ввода причины отказа -->
                <div class="mt-4">
                    <label
                        for="reason"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                    >
                        {{ lang().label.reason }}
                    </label>
                    <Textarea
                        id="completion_note"
                        v-model="form.reason"
                        rows="4"
                        class="mt-1 p-2 w-full border border-slate-300 dark:border-slate-700 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-slate-800 dark:text-white"
                        :placeholder=lang().placeholder.enter_note
                    ></Textarea>
                </div>

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
