<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { Head, useForm } from "@inertiajs/vue3";
import InputText from "primevue/inputtext";

const form = useForm({
    password: "",
});

const submit = () => {
    form.post(route("password.confirm"), {
        onFinish: () => form.reset(),
    });
};
</script>


<template>
    <GuestLayout>
        <Head :title="lang().label.password_confirmation" />

        <div class="mb-4 text-sm text-slate-600 dark:text-slate-400">
            {{ lang().label.confirm_password }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="password" :value="lang().label.password" />
                <InputText
                    id="password"
                    type="password"
                    class="mt-1 w-full"
                    v-model="form.password"
                    autocomplete="current-password"
                    autofocus
                    :placeholder="lang().placeholder.password"
                    :class="{ 'p-invalid': form.errors.password }"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="flex justify-end mt-4">
                <PrimaryButton
                    class="ml-4"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    {{
                        form.processing
                            ? lang().button.confirm_password + "..."
                            : lang().button.confirm_password
                    }}
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>

