<template>
    <div class="text-slate-300 pb-20">
        <div class="border-b border-slate-700 dark:border-slate-800 py-3">
            <Link :href="route('dashboard')" class="flex items-center gap-2 hover:text-primary transition-colors duration-300">
                <img src="/images/main_logo.png" alt="Logo" class="rounded-full w-8 h-auto" />
                <span class="brand-text text-xl font-semibold">ACDF</span>
            </Link>
        </div>

        <div class="user-panel py-3 border-b border-slate-700 dark:border-slate-800 flex items-center gap-2">
            <Link :href="route('profile.edit')" class="flex items-center gap-2 hover:text-primary transition-colors duration-300">
                <img :src="usePage().props.auth.user.profile_image" alt="User" class="h-8 rounded-full w-8">
                <div class="user-name font-semibold">{{ usePage().props.auth.user.name }}</div>
            </Link>
        </div>

        <ul class="space-y-2 my-4">
            <li class="text-white rounded-lg hover:bg-primary transition dark:hover:bg-primary"
                :class="route().current('task.*') ? 'bg-primary' : 'bg-slate-700/40 dark:bg-slate-800/40'">
                <Link :href="route('task.index')" class="flex items-center py-2 px-4">
                    <ClipboardDocumentListIcon class="w-6 h-5" />
                    <span class="ml-3">{{ lang().label.tasks }}</span>
                </Link>
            </li>

            <li class="text-white rounded-lg hover:bg-primary transition dark:hover:bg-primary"
                :class="route().current('application.*') ? 'bg-primary' : 'bg-slate-700/40 dark:bg-slate-800/40'">
                <Link :href="route('application.index')" class="flex items-center py-2 px-4">
                    <DocumentIcon class="w-6 h-5" />
                    <span class="ml-3">{{ lang().label.applications }}</span>
                </Link>
            </li>

            <li class="text-white rounded-lg hover:bg-primary transition dark:hover:bg-primary"
                :class="route().current('contract.*') ? 'bg-primary' : 'bg-slate-700/40 dark:bg-slate-800/40'">
                <Link :href="route('contract.index')" class="flex items-center py-2 px-4">
                    <DocumentIcon class="w-6 h-5" />
                    <span class="ml-3">{{ lang().label.contracts }}</span>
                </Link>
            </li>

            <li class="text-white rounded-lg hover:bg-primary transition dark:hover:bg-primary"
                :class="route().current('projects.*') ? 'bg-primary' : 'bg-slate-700/40 dark:bg-slate-800/40'">
                <Link :href="route('projects.index')" class="flex items-center py-2 px-4">
                    <FolderIcon class="w-6 h-5" />
                    <span class="ml-3">{{ lang().label.projects }}</span>
                </Link>
            </li>

            <li v-show="can(['manage department'])" class="text-white rounded-lg hover:bg-primary transition dark:hover:bg-primary"
                :class="route().current('departments.*') ? 'bg-primary' : 'bg-slate-700/40 dark:bg-slate-800/40'">
                <Link :href="route('departments.index')" class="flex items-center py-2 px-4">
                    <UsersIcon class="w-6 h-5" />
                    <span class="ml-3">{{ lang().label.departments }}</span>
                </Link>
            </li>

            <li v-show="can(['manage position'])" class="text-white rounded-lg hover:bg-primary transition dark:hover:bg-primary"
                :class="route().current('positions.*') ? 'bg-primary' : 'bg-slate-700/40 dark:bg-slate-800/40'">
                <Link :href="route('positions.index')" class="flex items-center py-2 px-4">
                    <BriefcaseIcon class="w-6 h-5" />
                    <span class="ml-3">{{ lang().label.positions }}</span>
                </Link>
            </li>

            <li v-show="can(['manage status'])" class="text-white rounded-lg hover:bg-primary transition dark:hover:bg-primary"
                :class="route().current('status.*') ? 'bg-primary' : 'bg-slate-700/40 dark:bg-slate-800/40'">
                <Link :href="route('status.index')" class="flex items-center py-2 px-4">
                    <ExclamationCircleIcon class="w-6 h-5" />
                    <span class="ml-3">{{ lang().label.status }}</span>
                </Link>
            </li>

            <li v-show="can(['manage currency'])" class="text-white rounded-lg hover:bg-primary transition dark:hover:bg-primary"
                :class="route().current('currency.*') ? 'bg-primary' : 'bg-slate-700/40 dark:bg-slate-800/40'">
                <Link :href="route('currency.index')" class="flex items-center py-2 px-4">
                    <CurrencyDollarIcon class="w-6 h-5" />
                    <span class="ml-3">{{ lang().label.currency }}</span>
                </Link>
            </li>

            <li v-show="can(['view logs'])" class="text-white rounded-lg hover:bg-primary transition dark:hover:bg-primary"
                :class="route().current('logs.*') ? 'bg-primary' : 'bg-slate-700/40 dark:bg-slate-800/40'">
                <Link :href="route('logs.index')" class="flex items-center py-2 px-4">
                    <CurrencyDollarIcon class="w-6 h-5" />
                    <span class="ml-3">{{ lang().label.logs }}</span>
                </Link>
            </li>

            <!-- Контакты -->
            <li v-show="can(['manage contacts'])" @click="toggleMenu('contacts')" class="text-white rounded-lg cursor-pointer flex items-center justify-between px-4 py-2 hover:bg-primary dark:hover:bg-primary"
                :class="expandedMenus.contacts.expanded ? 'bg-primary' : 'bg-slate-700/40 dark:bg-slate-800/40'">
                <div class="flex items-center">
                    <UserIcon class="w-6 h-5" />
                    <span class="ml-3">{{ lang().label.contacts_group }}</span>
                </div>
                <ChevronDownIcon class="w-5 h-5 transform transition-transform" :class="{ 'rotate-180': expandedMenus.contacts.expanded }" />
            </li>
            <ul v-show="expandedMenus.contacts.expanded" class="space-y-1">
                <li :class="route().current('contacts.*') ? 'bg-primary' : 'bg-slate-700/40 dark:bg-slate-800/40'"
                    class="text-white rounded-lg hover:bg-primary transition dark:hover:bg-primary">
                    <Link :href="route('contacts.index')" class="flex items-center py-2 px-4">
                        <UserIcon class="w-6 h-5" />
                        <span class="ml-3">{{ lang().label.contacts }}</span>
                    </Link>
                </li>
                <li :class="route().current('contact-categories.*') ? 'bg-primary' : 'bg-slate-700/40 dark:bg-slate-800/40'"
                    class="text-white rounded-lg hover:bg-primary transition dark:hover:bg-primary">
                    <Link :href="route('contact-categories.index')" class="flex items-center py-2 px-4">
                        <FolderIcon class="w-6 h-5" />
                        <span class="ml-3">{{ lang().label.contact_categories }}</span>
                    </Link>
                </li>
                <li :class="route().current('contact-subcategories.*') ? 'bg-primary' : 'bg-slate-700/40 dark:bg-slate-800/40'"
                    class="text-white rounded-lg hover:bg-primary transition dark:hover:bg-primary">
                    <Link :href="route('contact-subcategories.index')" class="flex items-center py-2 px-4">
                        <FolderIcon class="w-6 h-5" />
                        <span class="ml-3">{{ lang().label.contact_subcategories }}</span>
                    </Link>
                </li>
            </ul>

            <!-- RBAC -->
            <li v-show="can(['manage rbac'])" @click="toggleMenu('rbac')" class="text-white rounded-lg cursor-pointer flex items-center justify-between px-4 py-2 hover:bg-primary dark:hover:bg-primary"
                :class="expandedMenus.rbac.expanded ? 'bg-primary' : 'bg-slate-700/40 dark:bg-slate-800/40'">
                <div class="flex items-center">
                    <KeyIcon class="w-6 h-5" />
                    <span class="ml-3">{{ lang().label.settings }}</span>
                </div>
                <ChevronDownIcon class="w-5 h-5 transform transition-transform" :class="{ 'rotate-180': expandedMenus.rbac.expanded }" />
            </li>
            <ul v-show="expandedMenus.rbac.expanded" class="space-y-1">
                <li :class="route().current('user.*') ? 'bg-primary' : 'bg-slate-700/40 dark:bg-slate-800/40'"
                    class="text-white rounded-lg hover:bg-primary transition dark:hover:bg-primary">
                    <Link :href="route('user.index')" class="flex items-center py-2 px-4">
                        <UserIcon class="w-6 h-5" />
                        <span class="ml-3">{{ lang().label.user }}</span>
                    </Link>
                </li>
                <li :class="route().current('role.*') ? 'bg-primary' : 'bg-slate-700/40 dark:bg-slate-800/40'"
                    class="text-white rounded-lg hover:bg-primary transition dark:hover:bg-primary">
                    <Link :href="route('role.index')" class="flex items-center py-2 px-4">
                        <KeyIcon class="w-6 h-5" />
                        <span class="ml-3">{{ lang().label.role }}</span>
                    </Link>
                </li>
                <li :class="route().current('permission.*') ? 'bg-primary' : 'bg-slate-700/40 dark:bg-slate-800/40'"
                    class="text-white rounded-lg hover:bg-primary transition dark:hover:bg-primary">
                    <Link :href="route('permission.index')" class="flex items-center py-2 px-4">
                        <ShieldCheckIcon class="w-6 h-5" />
                        <span class="ml-3">{{ lang().label.permission }}</span>
                    </Link>
                </li>
            </ul>
        </ul>
    </div>
</template>

<script setup>
import {
    BriefcaseIcon,
    ExclamationCircleIcon,
    UserIcon,
    KeyIcon,
    ShieldCheckIcon,
    ChevronDownIcon,
    ClipboardDocumentListIcon,
    FolderIcon,
    DocumentIcon,
    UsersIcon,
    CurrencyDollarIcon,
} from "@heroicons/vue/24/solid";
import { Link, usePage } from "@inertiajs/vue3";
import { ref, onMounted, reactive } from "vue";

const lang = () => usePage().props.language;

// Универсальное хранилище подменю с маршрутами
const expandedMenus = reactive({
    contacts: {
        expanded: false,
        routes: ['contacts.*', 'contact-categories.*', 'contact-subcategories.*']
    },
    rbac: {
        expanded: false,
        routes: ['user.*', 'role.*', 'permission.*']
    }
});

// Проверка текущего маршрута
const routeMatches = (patterns) => patterns.some(p => route().current(p));

// Автоматически активируем нужное меню
onMounted(() => {
    for (const key in expandedMenus) {
        expandedMenus[key].expanded = routeMatches(expandedMenus[key].routes);
    }
});

// Универсальный переключатель
function toggleMenu(key) {
    expandedMenus[key].expanded = !expandedMenus[key].expanded;
}
</script>
