<script setup>
import {
    TransitionRoot,
    TransitionChild,
    Dialog,
    DialogOverlay,
} from "@headlessui/vue";
import SideBarMenu from "@/Components/SideBarMenu.vue";

const props = defineProps({
    open: Boolean,
    is_expanded: Boolean,
});

const emit = defineEmits(["close", "toggleMenu"]);
</script>

<template>
    <!-- Desktop Sidebar -->
    <div class="hidden lg:flex transition-all duration-300">
        <aside
            class="fixed flex flex-col h-screen overflow-hidden bg-gradient-to-b from-slate-900 via-slate-900 to-slate-800 border-r border-slate-700/50 text-slate-300 shadow-xl transition-all duration-300"
            :class="is_expanded ? 'w-64' : 'w-0'"
        >
            <!-- Декоративный градиент сверху -->
            <div class="absolute top-0 left-0 right-0 h-32 bg-gradient-to-b from-blue-600/10 to-transparent pointer-events-none"></div>

            <div class="flex-1 h-screen overflow-y-auto scrollbar-sidebar px-3 relative z-10">
                <SideBarMenu />
            </div>
        </aside>
    </div>

    <!-- Mobile Sidebar -->
    <TransitionRoot :show="open">
        <Dialog
            as="div"
            @close="emit('close')"
            class="fixed inset-0 z-50 flex lg:hidden"
        >
            <TransitionChild
                enter="transition ease-in-out duration-300 transform"
                enter-from="-translate-x-full"
                enter-to="translate-x-0"
                leave="transition ease-in-out duration-300 transform"
                leave-from="translate-x-0"
                leave-to="-translate-x-full"
                as="template"
            >
                <aside
                    class="flex flex-col relative z-10 w-64 bg-gradient-to-b from-slate-900 via-slate-900 to-slate-800 border-r border-slate-700/50 shadow-2xl"
                >
                    <!-- Декоративный градиент сверху -->
                    <div class="absolute top-0 left-0 right-0 h-32 bg-gradient-to-b from-blue-600/10 to-transparent pointer-events-none"></div>

                    <div class="flex flex-col relative h-screen min-h-screen">
                        <div
                            class="overflow-y-auto flex-1 scrollbar-sidebar px-3 relative z-10"
                        >
                            <SideBarMenu />
                        </div>
                    </div>
                </aside>
            </TransitionChild>

            <TransitionChild
                enter="transition-opacity ease-linear duration-300"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="transition-opacity ease-linear duration-300"
                leave-from="opacity-100"
                leave-to="opacity-0"
                as="template"
            >
                <DialogOverlay
                    class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm lg:hidden"
                ></DialogOverlay>
            </TransitionChild>
        </Dialog>
    </TransitionRoot>
</template>

<style scoped>
/* Кастомный scrollbar для sidebar */
.scrollbar-sidebar::-webkit-scrollbar {
    width: 6px;
}

.scrollbar-sidebar::-webkit-scrollbar-track {
    background: transparent;
}

.scrollbar-sidebar::-webkit-scrollbar-thumb {
    background: rgb(71 85 105 / 0.5);
    border-radius: 3px;
}

.scrollbar-sidebar::-webkit-scrollbar-thumb:hover {
    background: rgb(71 85 105 / 0.7);
}
</style>
