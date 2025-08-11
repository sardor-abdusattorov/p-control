<template>
    <Head :title="props.title" />

    <AuthenticatedLayout>
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs" />

        <div class="space-y-4">
            <div class="px-0">
                <div class="rounded-lg overflow-hidden w-fit">
                    <CreateLink :href="route('contacts.create')" />
                    <Delete
                        :show="data.deleteOpen"
                        @close="data.deleteOpen = false"
                        :contact="data.contact"
                        :title="props.title"
                    />
                    <DeleteBulk
                        :show="data.deleteBulkOpen"
                        @close="() => { data.deleteBulkOpen = false;
                        data.multipleSelect = false;
                        data.selectedId = []; }" :selectedId="data.selectedId"
                        :title="props.title"
                    />
                </div>
            </div>

            <div class="relative bg-white dark:bg-slate-800 shadow sm:rounded-lg">
                <div class="flex justify-between p-2">
                    <div class="flex space-x-2">
                        <Select
                            v-model="data.params.perPage"
                            :options="data.dataSet"
                            optionLabel="label"
                            optionValue="value"
                        />
                        <DangerButton
                            @click="data.deleteBulkOpen = true"
                            v-show="data.selectedId.length !== 0"
                            class="px-3 py-1.5"
                            :v-tooltip="lang().tooltip?.delete_selected ?? 'Delete selected'"
                        >
                            <TrashIcon class="w-5 h-5" />
                        </DangerButton>
                    </div>
                </div>

                <div class="overflow-x-auto scrollbar-table">
                    <table class="w-full select-width">
                        <thead class="text-sm border-t border-slate-200 dark:border-slate-700">
                        <tr class="dark:bg-slate-900/50 text-left border-b border-slate-300 dark:border-slate-600">
                            <th class="px-2 py-4 text-center w-10">
                                <Checkbox v-model:checked="data.multipleSelect" @change="selectAll" />
                            </th>
                            <th class="px-2 py-4 w-10">#</th>
                            <th class="px-2 py-4 cursor-pointer" @click="order('title')">
                                <div class="flex justify-between items-center">
                                    <span>{{ lang().label.title }}</span>
                                    <ChevronUpDownIcon class="w-4 h-4" />
                                </div>
                            </th>
                            <th class="px-2 py-4 cursor-pointer " @click="order('email')">
                                <div class="flex justify-between items-center">
                                    <span> {{ lang().label.email }}</span>
                                    <ChevronUpDownIcon class="w-4 h-4" />
                                </div>
                            </th>
                            <th class="px-2 py-4 cursor-pointer " @click="order('category_id')">
                                <div class="flex justify-between items-center">
                                    <span>{{ lang().label.category }}</span>
                                    <ChevronUpDownIcon class="w-4 h-4" />
                                </div>
                            </th>
                            <th class="px-2 py-4 cursor-pointer " @click="order('country')">
                                <div class="flex justify-between items-center">
                                    <span>{{ lang().label.country }}</span>
                                    <ChevronUpDownIcon class="w-4 h-4" />
                                </div>
                            </th>
                            <th class="px-2 py-4 cursor-pointer" @click="order('owner_id')">
                                <div class="flex justify-between items-center">
                                    <span>{{ lang().label.user_id }}</span>
                                    <ChevronUpDownIcon class="w-4 h-4" />
                                </div>
                            </th>
                            <th class="px-2 py-4 text-center">{{ lang().label.actions }}</th>
                        </tr>

                        <tr class="dark:bg-slate-900/50 text-left">
                            <th class="px-2 py-4" colspan="2"></th>
                            <th class="px-2 py-4">
                                <InputText v-model="data.params.title" :placeholder="lang().label.title" class="w-full" />
                            </th>
                            <th class="px-2 py-4">
                                <InputText v-model="data.params.email" :placeholder="lang().label.email" class="w-full" />
                            </th>
                            <th class="px-2 py-4">
                                <Select
                                    showClear
                                    v-model="data.params.category_id"
                                    :options="props.categories"
                                    optionLabel="title"
                                    optionValue="id"
                                    filter
                                    checkmark
                                    :highlightOnSelect="false"
                                    :placeholder="lang().placeholder.select_country"
                                    class="w-full"
                                    :pt="{
                                option: { class: 'custom-option' },
                                dropdown: { style: { maxWidth: '300px' } },
                                overlay: { class: 'parent-wrapper-class' }
                            }"
                                />
                            </th>
                            <th class="px-2 py-4">
                                <Select
                                    showClear
                                    v-model="data.params.country"
                                    :options="props.countries"
                                    optionLabel="name"
                                    optionValue="id"
                                    filter
                                    checkmark
                                    :highlightOnSelect="false"
                                    :placeholder="lang().placeholder.select_country"
                                    class="w-full"
                                    :pt="{
                                option: { class: 'custom-option' },
                                dropdown: { style: { maxWidth: '300px' } },
                                overlay: { class: 'parent-wrapper-class' }
                            }"
                                />
                            </th>
                            <th class="px-2 py-4">
                                <Select
                                    showClear
                                    v-model="data.params.owner_id"
                                    :options="props.users"
                                    optionLabel="name"
                                    optionValue="id"
                                    filter
                                    checkmark
                                    :highlightOnSelect="false"
                                    :placeholder="lang().placeholder.select_user"
                                    class="w-full"
                                    :pt="{
                                option: { class: 'custom-option' },
                                dropdown: { style: { maxWidth: '300px' } },
                                overlay: { class: 'parent-wrapper-class' }
                            }"
                                />
                            </th>
                            <th class="px-2 py-4"></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(contact, index) in contacts.data" :key="contact.id">
                            <td class="whitespace-pre-wrap py-4 px-2 text-center w-10">
                                <input type="checkbox" @change="select" :value="contact.id" v-model="data.selectedId" class="rounded border-slate-300 dark:border-slate-700" />
                            </td>
                            <td class="whitespace-nowrap py-4 px-2 sm:py-3">{{ (contacts.current_page - 1) * contacts.per_page + index + 1 }}</td>
                            <td class="whitespace-nowrap py-4 px-2 sm:py-3">
                                <Link :href="route('contacts.show', { contact: contact.id })" class="text-blue-500 hover:underline">
                                    {{ contact.title || lang().label.no_available }}
                                </Link>
                            </td>
                            <td class="whitespace-nowrap py-4 px-2 sm:py-3">{{ contact.email || lang().label.no_available }}</td>
                            <td class="whitespace-nowrap py-4 px-2 sm:py-3">{{ contact.category?.title || lang().label.no_available }}</td>
                            <td class="whitespace-nowrap py-4 px-2 sm:py-3">{{ contact.country?.name || lang().label.no_available }}</td>
                            <td class="whitespace-nowrap py-4 px-2 sm:py-3">{{ contact.owner?.name || lang().label.no_available }}</td>
                            <td class="whitespace-nowrap py-4 px-2 sm:py-3  text-center">
                                <Button icon="pi pi-ellipsis-v" @click="toggleMenu($event, contact)" />
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-between items-center p-2">
                    <Pagination :links="contacts" :filters="data.params" />
                </div>
            </div>
        </div>

        <Menu ref="menu" :model="items" :popup="true" />
    </AuthenticatedLayout>
</template>

<script setup>
import {Head, usePage, router, Link} from '@inertiajs/vue3';
import {ref, reactive, watch, computed} from 'vue';
import {debounce} from 'lodash';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import CreateLink from '@/Components/CreateLink.vue';
import Pagination from '@/Components/Pagination.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Delete from '@/Pages/Contacts/Delete.vue';
import DeleteBulk from '@/Pages/Contacts/DeleteBulk.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Menu from 'primevue/menu';
import Button from 'primevue/button';
import { TrashIcon, ChevronUpDownIcon  } from "@heroicons/vue/24/solid";

const props = defineProps({
    title: String,
    filters: Object,
    contacts: Object,
    categories: Object,
    countries: Object,
    users: Object,
    breadcrumbs: Object,
    perPage: Number,
});

const lang = () => usePage().props.language;

const data = reactive({
    params: {
        title: props.filters.title ?? '',
        email: props.filters.email ?? '',
        category_id: props.filters.category_id ?? null,
        country: props.filters.country ?? null,
        city: props.filters.city ?? '',
        owner_id: props.filters.owner_id ?? null,
        field: props.filters.field ?? '',
        order: props.filters.order ?? 'asc',
        perPage: props.perPage ?? 10,
    },
    dataSet: usePage().props.app.perpage,
    selectedId: [],
    multipleSelect: false,
    deleteOpen: false,
    deleteBulkOpen: false,
    contact: null,
});

const menu = ref();
const selectedContact = ref(null);

const items = computed(() => {
    if (!selectedContact.value) return [];
    return [
        {
            label: lang().label.actions || 'Actions',
            items: [
                {
                    label: lang().tooltip.show,
                    icon: 'pi pi-eye',
                    command: () => router.visit(route('contacts.show', selectedContact.value.id)),
                },
                {
                    label: lang().tooltip.edit,
                    icon: 'pi pi-pencil',
                    command: () => router.visit(route('contacts.edit', selectedContact.value.id)),
                },
                {
                    label: lang().tooltip.delete,
                    icon: 'pi pi-trash',
                    command: () => {
                        data.contact = selectedContact.value;
                        data.deleteOpen = true;
                    },
                },
            ],
        },
    ];
});

const toggleMenu = (event, contact) => {
    selectedContact.value = contact;
    menu.value.toggle(event);
};

watch(
    () => ({...data.params}),
    debounce(() => {
        const query = Object.fromEntries(
            Object.entries(data.params).filter(([, value]) => value !== null && value !== undefined && value !== '')
        );
        router.get(route('contacts.index'), query, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    }, 150),
    {deep: true}
);

const order = (field) => {
    if (data.params.field === field) {
        data.params.order = data.params.order === 'asc' ? 'desc' : 'asc';
    } else {
        data.params.field = field;
        data.params.order = 'asc';
    }
};

const selectAll = () => {
    if (data.multipleSelect) {
        data.selectedId = props.contacts.data.map((contact) => contact.id);
    } else {
        data.selectedId = [];
    }
};
</script>
