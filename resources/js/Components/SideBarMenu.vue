<template>
    <div class="text-slate-300 pb-20">
        <!-- Logo Section -->
        <div class="border-b border-slate-700/50 py-4 mb-2">
            <Link :href="route('dashboard')" class="flex items-center gap-3 px-2 hover:text-white transition-colors duration-300 group">
                <img src="/images/main_logo.png" alt="Logo" class="rounded-full w-10 h-10 ring-2 ring-slate-700 group-hover:ring-sky-500 transition-all duration-300" />
                <span class="brand-text text-xl font-bold text-white">ACDF</span>
            </Link>
        </div>

        <!-- User Panel -->
        <div class="user-panel py-3 mb-2 border-b border-slate-700/50">
            <Link :href="route('profile.edit')" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-slate-800/50 transition-all duration-300 group">
                <img :src="usePage().props.auth.user.profile_image" alt="User"
                     class="h-10 w-10 rounded-full ring-2 ring-slate-700 group-hover:ring-sky-500 transition-all duration-300 object-cover">
                <div class="user-name font-semibold text-slate-200 group-hover:text-white transition-colors duration-300">
                    {{ usePage().props.auth.user.name }}
                </div>
            </Link>
        </div>

        <!-- Menu Items -->
        <ul class="space-y-1 my-4">
            <!-- Applications -->
            <li>
                <Link :href="route('application.index')"
                      class="flex items-center py-2.5 px-3 rounded-lg transition-all duration-300 group relative overflow-hidden"
                      :class="route().current('application.*')
                        ? 'bg-sky-600/20 text-sky-400 font-medium border-l-2 border-sky-500'
                        : 'text-slate-300 hover:bg-slate-800/50 hover:text-white'">
                    <div v-if="route().current('application.*')"
                         class="absolute left-0 top-0 bottom-0 w-1 bg-sky-500 rounded-r"></div>
                    <DocumentIcon class="w-5 h-5 transition-transform duration-300 group-hover:scale-110" />
                    <span class="ml-3 font-medium">{{ lang().label.applications }}</span>
                </Link>
            </li>

            <!-- Contracts -->
            <li>
                <Link :href="route('contract.index')"
                      class="flex items-center py-2.5 px-3 rounded-lg transition-all duration-300 group relative overflow-hidden"
                      :class="route().current('contract.*')
                        ? 'bg-sky-600/20 text-sky-400 font-medium border-l-2 border-sky-500'
                        : 'text-slate-300 hover:bg-slate-800/50 hover:text-white'">
                    <div v-if="route().current('contract.*')"
                         class="absolute left-0 top-0 bottom-0 w-1 bg-sky-500 rounded-r"></div>
                    <DocumentIcon class="w-5 h-5 transition-transform duration-300 group-hover:scale-110" />
                    <span class="ml-3 font-medium">{{ lang().label.contracts }}</span>
                </Link>
            </li>

            <!-- Projects -->
            <li>
                <Link :href="route('projects.index')"
                      class="flex items-center py-2.5 px-3 rounded-lg transition-all duration-300 group relative overflow-hidden"
                      :class="route().current('projects.*')
                        ? 'bg-sky-600/20 text-sky-400 font-medium border-l-2 border-sky-500'
                        : 'text-slate-300 hover:bg-slate-800/50 hover:text-white'">
                    <div v-if="route().current('projects.*')"
                         class="absolute left-0 top-0 bottom-0 w-1 bg-sky-500 rounded-r"></div>
                    <FolderIcon class="w-5 h-5 transition-transform duration-300 group-hover:scale-110" />
                    <span class="ml-3 font-medium">{{ lang().label.projects }}</span>
                </Link>
            </li>

            <!-- Справочники Dropdown -->
            <li v-show="can(['manage department', 'manage position', 'manage status', 'manage currency'])">
                <button @click="toggleMenu('dictionaries')"
                        class="w-full flex items-center justify-between py-2.5 px-3 rounded-lg transition-all duration-300 group"
                        :class="expandedMenus.dictionaries.expanded
                          ? 'bg-slate-800/70 text-white'
                          : 'text-slate-300 hover:bg-slate-800/50 hover:text-white'">
                    <div class="flex items-center">
                        <BookOpenIcon class="w-5 h-5 transition-transform duration-300 group-hover:scale-110" />
                        <span class="ml-3 font-medium">{{ lang().label.dictionaries || 'Справочники' }}</span>
                    </div>
                    <ChevronDownIcon class="w-5 h-5 transform transition-transform duration-300"
                                     :class="{ 'rotate-180': expandedMenus.dictionaries.expanded }" />
                </button>
                <ul v-show="expandedMenus.dictionaries.expanded" class="space-y-1 mt-1 ml-4 pl-4 border-l-2 border-slate-700/50">
                    <!-- Departments -->
                    <li v-show="can(['manage department'])">
                        <Link :href="route('departments.index')"
                              class="flex items-center py-2 px-3 rounded-lg text-sm transition-all duration-300 group"
                              :class="route().current('departments.*')
                                ? 'bg-sky-600/20 text-sky-400 font-medium'
                                : 'text-slate-400 hover:text-white hover:bg-slate-800/30'">
                            <UsersIcon class="w-4 h-4" />
                            <span class="ml-2">{{ lang().label.departments }}</span>
                        </Link>
                    </li>

                    <!-- Positions -->
                    <li v-show="can(['manage position'])">
                        <Link :href="route('positions.index')"
                              class="flex items-center py-2 px-3 rounded-lg text-sm transition-all duration-300 group"
                              :class="route().current('positions.*')
                                ? 'bg-sky-600/20 text-sky-400 font-medium'
                                : 'text-slate-400 hover:text-white hover:bg-slate-800/30'">
                            <BriefcaseIcon class="w-4 h-4" />
                            <span class="ml-2">{{ lang().label.positions }}</span>
                        </Link>
                    </li>

                    <!-- Currency -->
                    <li v-show="can(['manage currency'])">
                        <Link :href="route('currency.index')"
                              class="flex items-center py-2 px-3 rounded-lg text-sm transition-all duration-300 group"
                              :class="route().current('currency.*')
                                ? 'bg-sky-600/20 text-sky-400 font-medium'
                                : 'text-slate-400 hover:text-white hover:bg-slate-800/30'">
                            <CurrencyDollarIcon class="w-4 h-4" />
                            <span class="ml-2">{{ lang().label.currency }}</span>
                        </Link>
                    </li>
                </ul>
            </li>

            <!-- Products Dropdown -->
            <li v-show="can(['view products', 'manage products'])">
                <button @click="toggleMenu('products')"
                        class="w-full flex items-center justify-between py-2.5 px-3 rounded-lg transition-all duration-300 group"
                        :class="expandedMenus.products.expanded
                          ? 'bg-slate-800/70 text-white'
                          : 'text-slate-300 hover:bg-slate-800/50 hover:text-white'">
                    <div class="flex items-center">
                        <CubeIcon class="w-5 h-5 transition-transform duration-300 group-hover:scale-110" />
                        <span class="ml-3 font-medium">{{ lang().label.products }}</span>
                    </div>
                    <ChevronDownIcon class="w-5 h-5 transform transition-transform duration-300"
                                     :class="{ 'rotate-180': expandedMenus.products.expanded }" />
                </button>
                <ul v-show="expandedMenus.products.expanded" class="space-y-1 mt-1 ml-4 pl-4 border-l-2 border-slate-700/50">
                    <li v-show="can(['manage products'])">
                        <Link :href="route('product_brands.index')"
                              class="flex items-center py-2 px-3 rounded-lg text-sm transition-all duration-300 group"
                              :class="route().current('product_brands.*')
                                ? 'bg-sky-600/20 text-sky-400 font-medium'
                                : 'text-slate-400 hover:text-white hover:bg-slate-800/30'">
                            <BriefcaseIcon class="w-4 h-4" />
                            <span class="ml-2">{{ lang().label.product_brands }}</span>
                        </Link>
                    </li>
                    <li v-show="can(['manage products'])">
                        <Link :href="route('product_categories.index')"
                              class="flex items-center py-2 px-3 rounded-lg text-sm transition-all duration-300 group"
                              :class="route().current('product_categories.*')
                                ? 'bg-sky-600/20 text-sky-400 font-medium'
                                : 'text-slate-400 hover:text-white hover:bg-slate-800/30'">
                            <FolderIcon class="w-4 h-4" />
                            <span class="ml-2">{{ lang().label.product_categories }}</span>
                        </Link>
                    </li>
                    <li v-show="can(['view products', 'manage products'])">
                        <Link :href="route('products.index')"
                              class="flex items-center py-2 px-3 rounded-lg text-sm transition-all duration-300 group"
                              :class="route().current('products.*')
                                ? 'bg-sky-600/20 text-sky-400 font-medium'
                                : 'text-slate-400 hover:text-white hover:bg-slate-800/30'">
                            <ClipboardDocumentListIcon class="w-4 h-4" />
                            <span class="ml-2">{{ lang().label.products }}</span>
                        </Link>
                    </li>
                </ul>
            </li>

            <!-- Contacts Dropdown -->
            <li v-show="can(['read contact', 'create contact', 'manage contact categories'])">
                <button @click="toggleMenu('contacts')"
                        class="w-full flex items-center justify-between py-2.5 px-3 rounded-lg transition-all duration-300 group"
                        :class="expandedMenus.contacts.expanded
                          ? 'bg-slate-800/70 text-white'
                          : 'text-slate-300 hover:bg-slate-800/50 hover:text-white'">
                    <div class="flex items-center">
                        <UserIcon class="w-5 h-5 transition-transform duration-300 group-hover:scale-110" />
                        <span class="ml-3 font-medium">{{ lang().label.contacts_group }}</span>
                    </div>
                    <ChevronDownIcon class="w-5 h-5 transform transition-transform duration-300"
                                     :class="{ 'rotate-180': expandedMenus.contacts.expanded }" />
                </button>
                <ul v-show="expandedMenus.contacts.expanded" class="space-y-1 mt-1 ml-4 pl-4 border-l-2 border-slate-700/50">
                    <li v-show="can(['read contact', 'create contact'])">
                        <Link :href="route('contacts.index')"
                              class="flex items-center py-2 px-3 rounded-lg text-sm transition-all duration-300 group"
                              :class="route().current('contacts.*')
                                ? 'bg-sky-600/20 text-sky-400 font-medium'
                                : 'text-slate-400 hover:text-white hover:bg-slate-800/30'">
                            <UserIcon class="w-4 h-4" />
                            <span class="ml-2">{{ lang().label.contacts }}</span>
                        </Link>
                    </li>
                    <li v-show="can(['manage contact categories'])">
                        <Link :href="route('contact-categories.index')"
                              class="flex items-center py-2 px-3 rounded-lg text-sm transition-all duration-300 group"
                              :class="route().current('contact-categories.*')
                                ? 'bg-sky-600/20 text-sky-400 font-medium'
                                : 'text-slate-400 hover:text-white hover:bg-slate-800/30'">
                            <FolderIcon class="w-4 h-4" />
                            <span class="ml-2">{{ lang().label.contact_categories }}</span>
                        </Link>
                    </li>
                    <li v-show="can(['manage contact categories'])">
                        <Link :href="route('contact-subcategories.index')"
                              class="flex items-center py-2 px-3 rounded-lg text-sm transition-all duration-300 group"
                              :class="route().current('contact-subcategories.*')
                                ? 'bg-sky-600/20 text-sky-400 font-medium'
                                : 'text-slate-400 hover:text-white hover:bg-slate-800/30'">
                            <FolderIcon class="w-4 h-4" />
                            <span class="ml-2">{{ lang().label.contact_subcategories }}</span>
                        </Link>
                    </li>
                </ul>
            </li>

            <!-- Settings Dropdown -->
            <li v-show="can(['manage rbac', 'view logs'])">
                <button @click="toggleMenu('settings')"
                        class="w-full flex items-center justify-between py-2.5 px-3 rounded-lg transition-all duration-300 group"
                        :class="expandedMenus.settings.expanded
                          ? 'bg-slate-800/70 text-white'
                          : 'text-slate-300 hover:bg-slate-800/50 hover:text-white'">
                    <div class="flex items-center">
                        <CogIcon class="w-5 h-5 transition-transform duration-300 group-hover:scale-110" />
                        <span class="ml-3 font-medium">{{ lang().label.settings }}</span>
                    </div>
                    <ChevronDownIcon class="w-5 h-5 transform transition-transform duration-300"
                                     :class="{ 'rotate-180': expandedMenus.settings.expanded }" />
                </button>
                <ul v-show="expandedMenus.settings.expanded" class="space-y-1 mt-1 ml-4 pl-4 border-l-2 border-slate-700/50">
                    <li v-show="can(['manage rbac'])">
                        <Link :href="route('user.index')"
                              class="flex items-center py-2 px-3 rounded-lg text-sm transition-all duration-300 group"
                              :class="route().current('user.*')
                                ? 'bg-sky-600/20 text-sky-400 font-medium'
                                : 'text-slate-400 hover:text-white hover:bg-slate-800/30'">
                            <UserIcon class="w-4 h-4" />
                            <span class="ml-2">{{ lang().label.user }}</span>
                        </Link>
                    </li>
                    <li v-show="can(['manage rbac'])">
                        <Link :href="route('role.index')"
                              class="flex items-center py-2 px-3 rounded-lg text-sm transition-all duration-300 group"
                              :class="route().current('role.*')
                                ? 'bg-sky-600/20 text-sky-400 font-medium'
                                : 'text-slate-400 hover:text-white hover:bg-slate-800/30'">
                            <KeyIcon class="w-4 h-4" />
                            <span class="ml-2">{{ lang().label.role }}</span>
                        </Link>
                    </li>
                    <li v-show="can(['manage rbac'])">
                        <Link :href="route('permission.index')"
                              class="flex items-center py-2 px-3 rounded-lg text-sm transition-all duration-300 group"
                              :class="route().current('permission.*')
                                ? 'bg-sky-600/20 text-sky-400 font-medium'
                                : 'text-slate-400 hover:text-white hover:bg-slate-800/30'">
                            <ShieldCheckIcon class="w-4 h-4" />
                            <span class="ml-2">{{ lang().label.permission }}</span>
                        </Link>
                    </li>
                    <li v-show="can(['view logs'])">
                        <Link :href="route('logs.index')"
                              class="flex items-center py-2 px-3 rounded-lg text-sm transition-all duration-300 group"
                              :class="route().current('logs.*')
                                ? 'bg-sky-600/20 text-sky-400 font-medium'
                                : 'text-slate-400 hover:text-white hover:bg-slate-800/30'">
                            <ClipboardDocumentListIcon class="w-4 h-4" />
                            <span class="ml-2">{{ lang().label.logs }}</span>
                        </Link>
                    </li>
                </ul>
            </li>
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
    BookOpenIcon,
    CubeIcon,
    CogIcon,
} from "@heroicons/vue/24/solid";
import { Link, usePage } from "@inertiajs/vue3";
import { ref, onMounted, reactive } from "vue";

const lang = () => usePage().props.language;

const expandedMenus = reactive({
    dictionaries: {
        expanded: false,
        routes: ['departments.*', 'positions.*', 'status.*', 'currency.*']
    },
    contacts: {
        expanded: false,
        routes: ['contacts.*', 'contact-categories.*', 'contact-subcategories.*']
    },
    settings: {
        expanded: false,
        routes: ['user.*', 'role.*', 'permission.*', 'logs.*']
    },
    products: {
        expanded: false,
        routes: ['product_brands.*', 'product_categories.*', 'products.*']
    },
});

const routeMatches = (patterns) => patterns.some(p => route().current(p));

onMounted(() => {
    for (const key in expandedMenus) {
        expandedMenus[key].expanded = routeMatches(expandedMenus[key].routes);
    }
});

function toggleMenu(key) {
    expandedMenus[key].expanded = !expandedMenus[key].expanded;
}
</script>
