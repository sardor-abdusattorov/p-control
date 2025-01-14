<template>
    <div class="text-slate-300 pb-20">
        <div class="border-b border-slate-700 dark:border-slate-800 py-3">
            <Link :href="route('dashboard')" class="flex items-center gap-2 hover:text-primary transition-colors duration-300">
                <img src="/images/main_logo.png" alt="Logo" class="rounded-full w-8 h-auto" />
                <span class="brand-text text-xl font-semibold">
                    ACDF
                </span>
            </Link>
        </div>

        <div class="user-panel py-3 border-b border-slate-700 dark:border-slate-800 flex items-center gap-2">
            <Link :href="route('profile.edit')" class="flex items-center gap-2 hover:text-primary transition-colors duration-300">
                <div class="user-image">
                    <img :src="usePage().props.auth.user.profile_image" alt="User" class="h-8 rounded-full w-8">
                </div>
                <div class="user-name font-semibold">
                    {{ usePage().props.auth.user.name }}
                </div>
            </Link>
        </div>

        <ul class="space-y-2 my-4">

            <li
                class="text-white rounded-lg hover:bg-primary transition dark:hover:bg-primary"
                :class="route().current('task.*') ? 'bg-primary' : 'bg-slate-700/40 dark:bg-slate-800/40'"
            >
                <Link :href="route('task.index')" class="flex items-center py-2 px-4">
                    <ClipboardDocumentListIcon class="w-6 h-5" />
                    <span class="ml-3">{{ lang().label.tasks }}</span>
                </Link>
            </li>

            <li
                class="text-white rounded-lg hover:bg-primary transition dark:hover:bg-primary"
                :class="route().current('application.*') ? 'bg-primary' : 'bg-slate-700/40 dark:bg-slate-800/40'"
            >
                <Link :href="route('application.index')" class="flex items-center py-2 px-4">
                    <DocumentIcon class="w-6 h-5" />
                    <span class="ml-3">{{ lang().label.applications }}</span>
                </Link>
            </li>

            <li
                class="text-white rounded-lg hover:bg-primary transition dark:hover:bg-primary"
                :class="route().current('contract.*') ? 'bg-primary' : 'bg-slate-700/40 dark:bg-slate-800/40'"
            >
                <Link :href="route('contract.index')" class="flex items-center py-2 px-4">
                    <DocumentIcon class="w-6 h-5" />
                    <span class="ml-3">{{ lang().label.contracts }}</span>
                </Link>
            </li>

            <li
                class="text-white rounded-lg hover:bg-primary transition dark:hover:bg-primary"
                :class="route().current('projects.*') ? 'bg-primary' : 'bg-slate-700/40 dark:bg-slate-800/40'"
            >
                <Link :href="route('projects.index')" class="flex items-center py-2 px-4">
                    <FolderIcon class="w-6 h-5" />
                    <span class="ml-3">{{ lang().label.projects }}</span>
                </Link>
            </li>

            <li v-show="can(['manage department'])"
                class="text-white rounded-lg hover:bg-primary transition dark:hover:bg-primary"
                :class="route().current('departments.*') ? 'bg-primary' : 'bg-slate-700/40 dark:bg-slate-800/40'"
            >
                <Link :href="route('departments.index')" class="flex items-center py-2 px-4">
                    <UsersIcon class="w-6 h-5" />
                    <span class="ml-3">{{ lang().label.departments }}</span>
                </Link>
            </li>

            <li
                v-show="can(['manage position'])"
                class="text-white rounded-lg hover:bg-primary transition dark:hover:bg-primary"
                :class="route().current('positions.*') ? 'bg-primary' : 'bg-slate-700/40 dark:bg-slate-800/40'"
            >
                <Link :href="route('positions.index')" class="flex items-center py-2 px-4">
                    <BriefcaseIcon class="w-6 h-5" />
                    <span class="ml-3">{{ lang().label.positions }}</span>
                </Link>
            </li>

            <li
                v-show="can(['manage status'])"
                class="text-white rounded-lg hover:bg-primary transition dark:hover:bg-primary"
                :class="route().current('status.*') ? 'bg-primary' : 'bg-slate-700/40 dark:bg-slate-800/40'"
            >
                <Link :href="route('status.index')" class="flex items-center py-2 px-4">
                    <ExclamationCircleIcon class="w-6 h-5" />
                    <span class="ml-3">{{ lang().label.status }}</span>
                </Link>
            </li>

            <li v-show="can(['manage currency'])"
                class="text-white rounded-lg hover:bg-primary transition dark:hover:bg-primary"
                :class="route().current('currency.*') ? 'bg-primary' : 'bg-slate-700/40 dark:bg-slate-800/40'"
            >
                <Link :href="route('currency.index')" class="flex items-center py-2 px-4">
                    <CurrencyDollarIcon class="w-6 h-5" />
                    <span class="ml-3">{{ lang().label.currency }}</span>
                </Link>
            </li>

            <li v-show="can(['view logs'])"
                class="text-white rounded-lg hover:bg-primary transition dark:hover:bg-primary"
                :class="route().current('logs.*') ? 'bg-primary' : 'bg-slate-700/40 dark:bg-slate-800/40'"
            >
                <Link :href="route('logs.index')" class="flex items-center py-2 px-4">
                    <CurrencyDollarIcon class="w-6 h-5" />
                    <span class="ml-3">{{ lang().label.logs }}</span>
                </Link>
            </li>

            <li
                v-show="can(['manage rbac'])"
                @click="toggleRbacMenu"
                class="text-white rounded-lg hover:bg-primary transition dark:hover:bg-primary cursor-pointer flex items-center justify-between px-4 py-2"
                :class="showRbacMenu ? 'bg-primary' : 'bg-slate-700/40 dark:bg-slate-800/40'"
            >
                <div class="flex items-center">
                    <KeyIcon class="w-6 h-5" />
                    <span class="ml-3">{{ lang().label.settings }}</span>
                </div>
                <ChevronDownIcon
                    class="w-5 h-5 transform transition-transform"
                    :class="{ 'rotate-180': showRbacMenu }"
                />
            </li>
            <!-- Submenu -->
            <ul
                v-show="showRbacMenu"
                class="space-y-1 overflow-hidden transition-all duration-300 ease-in-out"
                :style="{ height: showRbacMenu ? 'auto' : '0px' }"
            >
                <li
                    class="text-white rounded-lg hover:bg-primary transition dark:hover:bg-primary"
                    :class="route().current('user.*') ? 'bg-primary' : 'bg-slate-700/40 dark:bg-slate-800/40'"
                >
                    <Link :href="route('user.index')" class="flex items-center py-2 px-4">
                        <UserIcon class="w-6 h-5"/>
                        <span class="ml-3">{{ lang().label.user }}</span>
                    </Link>
                </li>
                <li
                    class="text-white rounded-lg hover:bg-primary transition dark:hover:bg-primary"
                    :class="route().current('role.*') ? 'bg-primary' : 'bg-slate-700/40 dark:bg-slate-800/40'"
                >
                    <Link :href="route('role.index')" class="flex items-center py-2 px-4">
                        <KeyIcon class="w-6 h-5"/>
                        <span class="ml-3">{{ lang().label.role }}</span>
                    </Link>
                </li>
                <li
                    class="text-white rounded-lg hover:bg-primary transition dark:hover:bg-primary"
                    :class="route().current('permission.*') ? 'bg-primary' : 'bg-slate-700/40 dark:bg-slate-800/40'"
                >
                    <Link :href="route('permission.index')" class="flex items-center py-2 px-4">
                        <ShieldCheckIcon class="w-6 h-5"/>
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
import {Link, usePage} from "@inertiajs/vue3";
import {ref, onMounted} from "vue";

const showRbacMenu = ref(false);
const user = usePage().props.auth.user;

function toggleRbacMenu() {
    showRbacMenu.value = !showRbacMenu.value;
}

function checkRbacActiveRoutes() {
    const activeRoutes = ['user.index', 'role.index', 'permission.index'];
    showRbacMenu.value = activeRoutes.some(routeName => route().current(routeName));
}

onMounted(() => {
    checkRbacActiveRoutes();
});
</script>
