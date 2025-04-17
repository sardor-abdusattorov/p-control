<template>
    <GuestLayout>
        <Head :title="lang().label.login" />

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" :value="lang().label.email" />
                <InputText
                    id="email"
                    type="email"
                    class="mt-1 w-full"
                    v-model="form.email"
                    :placeholder="lang().placeholder.email"
                    autocomplete="username"
                    :class="{ 'p-invalid': form.errors.email }"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" :value="lang().label.password" />
                <InputText
                    id="password"
                    type="password"
                    class="mt-1 w-full"
                    v-model="form.password"
                    :placeholder="lang().placeholder.password"
                    autocomplete="current-password"
                    :class="{ 'p-invalid': form.errors.password }"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="block mt-4 w-fit">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ml-2 text-sm text-slate-600 dark:text-slate-400">
            {{ lang().label.remember_me }}
        </span>
                </label>
            </div>

            <div class="flex flex-col mt-4 gap-4">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="underline text-sm text-slate-600 dark:text-slate-400 hover:text-primary transition-colors duration-200 w-fit"
                >
                    {{ lang().label.lost_password }}
                </Link>

                <Button
                    type="submit"
                    severity="info"
                    raised
                    class="w-full lg:w-[180px] px-6 py-2"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    {{
                        form.processing
                            ? lang().button.login + "..."
                            : lang().button.login
                    }}
                </Button>
            </div>

        </form>
    </GuestLayout>
</template>



<script setup>
import Checkbox from "@/Components/Checkbox.vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const submit = () => {
    form.post(route("login"), {
        onFinish: () => form.reset("password"),
    });
};
</script>
