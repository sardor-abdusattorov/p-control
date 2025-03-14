<script setup>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import Modal from "@/Components/Modal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import { useForm } from "@inertiajs/vue3";
import {Textarea} from "primevue";

const props = defineProps({
    show: Boolean,
    title: String,
    task: Object,
});

const emit = defineEmits(["close"]);

const form = useForm({
    completion_note: '',
    task_id: props.task?.id,
    user_id: props.task?.user_id,
    completed_at: new Date().toISOString(),
});

const completeTask = () => {
    form.post(route("task.complete", props.task?.id), {
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
            <form class="p-3 sm:p-6" @submit.prevent="completeTask">
                <h2 class="text-lg font-medium text-slate-900 dark:text-slate-100">
                    {{ lang().label.complete_task }} {{ props.title }}
                </h2>

                <div class="mt-4">
                    <label for="completion_note" class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                        {{ lang().label.completion_note }}
                    </label>

                    <Textarea
                        id="completion_note"
                        v-model="form.completion_note"
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
                        @click="completeTask"
                    >
                        {{
                            form.processing
                                ? lang().button.save + "..."
                                : lang().button.save
                        }}
                    </PrimaryButton>
                </div>
            </form>
        </Modal>
    </section>
</template>
