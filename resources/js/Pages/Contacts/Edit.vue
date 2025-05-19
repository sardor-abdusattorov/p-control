<template>
    <Head :title="props.title" />
    <AuthenticatedLayout>
        <Breadcrumb :title="props.title" :breadcrumbs="props.breadcrumbs" />

        <section class="space-y-4 bg-white dark:bg-slate-800 shadow sm:rounded-lg p-6">
            <form @submit.prevent="update" class="space-y-6">

                <!-- Название -->
                <div>
                    <InputLabel for="title" :value="lang().label.title" />
                    <InputText id="title" v-model="form.title" class="w-full" :placeholder="lang().label.title" :error="form.errors.title" />
                    <InputError :message="form.errors.title" />
                </div>

                <h2 class="text-base font-semibold text-slate-600 dark:text-slate-300 mt-4">{{lang().section.contact}}</h2>

                <!-- Имя + Фамилия -->
                <div class="grid lg:grid-cols-3 gap-4">
                    <div>
                        <InputLabel for="prefix" :value="lang().label.prefix" />
                        <InputText id="prefix" v-model="form.prefix" class="w-full" :placeholder="lang().label.prefix" :error="form.errors.prefix" />
                        <InputError :message="form.errors.prefix" />
                    </div>
                    <div>
                        <InputLabel for="firstname" :value="lang().label.firstname" />
                        <InputText id="firstname" v-model="form.firstname" class="w-full" :placeholder="lang().label.firstname" :error="form.errors.firstname" />
                        <InputError :message="form.errors.firstname" />
                    </div>
                    <div>
                        <InputLabel for="lastname" :value="lang().label.lastname" />
                        <InputText id="lastname" v-model="form.lastname" class="w-full" :placeholder="lang().label.lastname" :error="form.errors.lastname" />
                        <InputError :message="form.errors.lastname" />
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="w-full sm:w-1/3">
                        <InputLabel for="phone" :value="lang().label.phone"/>
                        <InputText id="phone" v-model="form.phone" class="w-full" :placeholder="lang().label.phone"
                                   :error="form.errors.phone"/>
                        <InputError :message="form.errors.phone"/>
                    </div>
                    <div class="w-full sm:w-1/3">
                        <InputLabel for="cellphone" :value="lang().label.cellphone"/>
                        <InputText id="cellphone" v-model="form.cellphone" class="w-full" :placeholder="lang().label.cellphone"
                                   :error="form.errors.cellphone"/>
                        <InputError :message="form.errors.cellphone"/>
                    </div>
                    <div class="w-full sm:w-1/3">
                        <InputLabel for="email" :value="lang().label.email"/>
                        <InputText id="email" v-model="form.email" class="w-full" :placeholder="lang().label.email"
                                   :error="form.errors.email"/>
                        <InputError :message="form.errors.email"/>
                    </div>
                </div>

                <!-- Язык + Компания -->
                <h2 class="text-base font-semibold text-slate-600 dark:text-slate-300 mt-4">{{lang().section.company}}</h2>


                <div class="grid lg:grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="company" :value="lang().label.company"/>
                        <InputText id="company" v-model="form.company" class="w-full" :placeholder="lang().label.company"
                                   :error="form.errors.company"/>
                        <InputError :message="form.errors.company"/>
                    </div>

                    <div>
                        <InputLabel for="language" :value="lang().label.language"/>
                        <InputText id="language" v-model="form.language" class="w-full" :placeholder="lang().label.language"
                                   :error="form.errors.language"/>
                        <InputError :message="form.errors.language"/>
                    </div>
                </div>

                <!-- Адрес -->
                <h2 class="text-base font-semibold text-slate-600 dark:text-slate-300 mt-4">Адрес</h2>
                <div class="grid lg:grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="country" :value="lang().label.country"/>
                        <Select
                            filter
                            checkmark
                            showClear
                            v-model="form.country"
                            :options="props.countries"
                            optionLabel="name"
                            optionValue="id"
                            class="w-full"
                            :placeholder="lang().placeholder.select_country"
                        />
                        <InputError :message="form.errors.country"/>
                    </div>
                    <div>
                        <InputLabel for="city" :value="lang().label.city" />
                        <Select
                            filter
                            checkmark
                            showClear
                            v-model="form.city"
                            :options="cities"
                            optionLabel="name"
                            optionValue="id"
                            :disabled="!form.country || !cities.length"
                            class="w-full"
                            :placeholder="lang().label.city"
                        />
                        <InputError :message="form.errors.city" />
                    </div>
                    <div>
                        <InputLabel for="post_box" :value="lang().label.post_box"/>
                        <InputText id="post_box" v-model="form.post_box" class="w-full" :placeholder="lang().label.post_box"
                                   :error="form.errors.post_box"/>
                        <InputError :message="form.errors.post_box"/>
                    </div>
                    <div>
                        <InputLabel for="zip_code" :value="lang().label.zip_code"/>
                        <InputText
                            id="zip_code"
                            v-model="form.zip_code"
                            class="w-full"
                            :placeholder="lang().label.zip_code"
                            :error="form.errors.zip_code"
                        />
                        <InputError :message="form.errors.zip_code"/>
                    </div>
                </div>

                <div class="grid lg:grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="address" :value="lang().label.address"/>
                        <Textarea
                            id="address"
                            v-model="form.address"
                            class="w-full"
                            rows="3"
                            autoResize
                            :placeholder="lang().label.address"
                            :error="form.errors.address"
                        />
                        <InputError :message="form.errors.address"/>
                    </div>
                    <div>
                        <InputLabel for="address2" :value="lang().label.address2"/>
                        <Textarea
                            id="address2"
                            v-model="form.address2"
                            class="w-full"
                            rows="3"
                            autoResize
                            :placeholder="lang().label.address2"
                            :error="form.errors.address2"/>
                        <InputError :message="form.errors.address2"
                        />
                    </div>
                </div>

                <h2 class="text-base font-semibold text-slate-600 dark:text-slate-300 mt-4">{{lang().section.basic}}</h2>

                <!-- Категория -->
                <div class="grid lg:grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="category_id" :value="lang().label.category"/>
                        <Select
                            filter
                            checkmark
                            showClear
                            v-model="form.category_id"
                            :options="props.categories"
                            optionLabel="title"
                            optionValue="id"
                            class="w-full"
                            :placeholder="lang().label.select_category"
                        />
                        <InputError :message="form.errors.category_id"/>
                    </div>
                    <div>
                        <InputLabel for="subcategory_id" :value="lang().label.subcategory"/>
                        <Select
                            filter
                            checkmark
                            showClear
                            v-model="form.subcategory_id"
                            :options="subCategories"
                            optionLabel="title"
                            optionValue="id"
                            :disabled="!form.category_id || !subCategories.length"
                            class="w-full"
                            :placeholder="lang().label.select_subcategory"
                        />
                        <InputError :message="form.errors.subcategory_id"/>
                    </div>
                </div>

                <div>
                    <InputLabel for="status" :value="lang().label.status"/>
                    <Select
                        v-model="form.status"
                        :options="props.statuses"
                        optionLabel="label"
                        optionValue="id"
                        class="w-full"
                        :placeholder="lang().label.select_status"
                    />
                    <InputError :message="form.errors.status"/>
                </div>

                <!-- Кнопки -->
                <div class="flex justify-start pt-2">
                    <BackLink :href="route('contacts.index')"/>
                    <PrimaryButton
                        class="ml-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="update"
                    >
                        {{
                            form.processing
                                ? lang().button.save + "..."
                                : lang().button.save
                        }}
                    </PrimaryButton>
                </div>
            </form>
        </section>
    </AuthenticatedLayout>
</template>

<script setup>
import {useForm, Head, usePage} from "@inertiajs/vue3";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import InputText from "primevue/inputtext";
import Textarea from "primevue/textarea";
import Select from "primevue/select";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import BackLink from "@/Components/BackLink.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import {ref, watch, watchEffect} from "vue";

const props = defineProps({
    title: String,
    breadcrumbs: Object,
    countries: Array,
    categories: Object,
    contact: Object,
    subCategories: Object,
    statuses: Array,
});

const lang = () => usePage().props.language;
const cities = ref([]);
const subCategories = ref([]);

const form = useForm({
    prefix: "",
    firstname: "",
    lastname: "",
    title: "",
    company: "",
    phone: "",
    email: "",
    cellphone: "",
    address: "",
    address2: "",
    post_box: "",
    zip_code: "",
    country: "",
    city: "",
    language: "",
    category_id: null,
    subcategory_id: null,
    status: 1,
});

const update = () => {
    form.put(route("contacts.update", props.contact?.id), {
    });
};

watchEffect(async () => {
    form.errors = {};

    form.prefix = props.contact?.prefix ?? '';
    form.firstname = props.contact?.firstname ?? '';
    form.lastname = props.contact?.lastname ?? '';
    form.title = props.contact?.title ?? '';
    form.company = props.contact?.company ?? '';
    form.phone = props.contact?.phone ?? '';
    form.cellphone = props.contact?.cellphone ?? '';
    form.email = props.contact?.email ?? '';
    form.language = props.contact?.language ?? '';
    form.country = props.contact?.country ? Number(props.contact.country) : '';
    form.city = props.contact?.city ? Number(props.contact.city) : '';
    form.address = props.contact?.address ?? '';
    form.address2 = props.contact?.address2 ?? '';
    form.post_box = props.contact?.post_box ?? '';
    form.zip_code = props.contact?.zip_code ?? '';
    form.category_id = props.contact?.category_id ?? null;
    form.subcategory_id = props.contact?.subcategory_id ?? null;
    form.status = props.contact?.status ?? 1;

    if (form.country) {
        try {
            const response = await axios.post(route('contacts.cities'), {
                country: form.country,
            });
            cities.value = Array.isArray(response.data) ? response.data : [];
        } catch (e) {
            console.error('Ошибка загрузки городов', e);
        }
    }

    if (form.category_id) {
        try {
            const response = await axios.post(route('contacts.subcategories'), {
                category_id: form.category_id,
            });
            subCategories.value = Array.isArray(response.data) ? response.data : [];
        } catch (e) {
            console.error('Ошибка загрузки подкатегорий', e);
        }
    }
});
watch(() => form.country, async (newCountry) => {
    form.city = "";
    cities.value = [];

    if (newCountry) {
        try {
            const response = await axios.post(route('contacts.cities'), {
                country: newCountry
            });
            cities.value = Array.isArray(response.data) ? response.data : [];
        } catch (error) {

        }
    }
});

watch(() => form.category_id, async (newCategory) => {
    form.subcategory_id = null;
    subCategories.value = [];

    if (newCategory) {
        try {
            const response = await axios.post(route('contacts.subcategories'), {
                category_id: newCategory
            });
            subCategories.value = Array.isArray(response.data) ? response.data : [];
        } catch (error) {

        }
    }
});

</script>
