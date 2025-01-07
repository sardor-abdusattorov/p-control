<script setup>
import { Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import Button from "primevue/button";
import Badge from "primevue/badge";
import Delete from "@/Pages/Task/Delete.vue";
import {reactive} from "vue";
import EditLink from "@/Components/EditLink.vue";
import Start from "@/Pages/Task/Start.vue";
import Complete from "@/Pages/Task/Complete.vue";
import {Textarea} from "primevue";

const props = defineProps({
    show: Boolean,
    title: String,
    breadcrumbs: Object,
    task: Object,
    users: Object,
    projects: Array,
    statuses: Array,
    priorities: Array,
    files: Array
});

const getPriorityLabel = (priorityId) => {
    const priority = props.priorities.find(p => p.id === priorityId);
    return priority ? priority.label : '';
};

const getStatusLabel = (statusId) => {
    const status = props.statuses.find(s => s.id === statusId);
    return status ? status.label : '';
};

console.log(getPriorityLabel())


const getPrioritySeverity = (priorityId) => {
    switch (priorityId) {
        case 1:
            return 'contrast';
        case 2:
            return 'warn';
        case 3:
            return 'danger';
        default:
            return 'contrast';
    }
};

const getStatusSeverity = (statusId) => {
    switch (statusId) {
        case 1:
            return 'info';
        case 2:
            return 'info';
        case 3:
            return 'success';
        case 4:
            return 'danger';
        default:
            return 'contrast';
    }
};

const data = reactive({
    deleteOpen: false,
    startOpen: false,
    completeOpen: false,
    task: null,
});

</script>
<template>
    <Head :title="props.title" />
    <AuthenticatedLayout>
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs" />

        <section class="space-y-6 bg-white dark:bg-slate-800 shadow sm:rounded-lg">
            <div class="card overflow-hidden rounded-md">
                <div class="card-header flex justify-between items-center p-4 bg-gray-100 dark:bg-slate-900 rounded-t-md">
                    <div class="flex-1 text-2xl font-semibold text-slate-800 dark:text-slate-100">
                        {{ lang().label.task_details }}
                    </div>
                    <div class="buttons space-x-2 flex align-middle">
                        <EditLink v-show="can(['update task'])"
                                  :href="route('task.edit', { task: task.id })"
                                  class="px-4 py-2 rounded-md"
                                  v-tooltip="lang().tooltip.edit"
                        >
                            {{ lang().tooltip.edit }}
                        </EditLink>

                        <Button
                            v-show="can(['start task']) && task.status === 1"
                            type="button"
                            icon="pi pi-check"
                            :label="lang().button.start_task"
                            severity="info"
                            class="p-button-sm dark:text-white"
                            @click="(data.startOpen = true), (data.task = task)"
                        />

                        <Button
                            v-show="task.status === 2 && can(['complete task'])"
                            type="button"
                            icon="pi pi-check-circle"
                            :label="lang().button.complete_task"
                            severity="success"
                            class="p-button-sm dark:text-white"
                            @click="(data.completeOpen = true), (data.task = task)"
                        />

                        <Button v-show="can(['delete task'])"
                                type="button"
                                v-tooltip="lang().tooltip.delete"
                                icon="pi pi-trash"
                                :label="lang().tooltip.delete"
                                severity="danger"
                                class="p-button-sm dark:text-white"
                                @click="(data.deleteOpen = true), (data.task = task)"
                        />

                        <Delete v-show="can(['delete task'])"
                                :show="data.deleteOpen"
                                @close="data.deleteOpen = false"
                                :task="data.task"
                                :title="props.title"
                        />
                        <Start  v-show="can(['start task']) && task.status === 1"
                                :show="data.startOpen"
                                @close="data.startOpen = false"
                                :task="data.task"
                                :title="props.title"
                        />
                        <Complete
                            v-show="can(['complete task']) && task.status === 2"
                            :show="data.completeOpen"
                            @close="data.completeOpen = false"
                            :task="data.task"
                            :title="props.title"
                        />
                    </div>
                </div>

                <div class="card-body p-4">
                    <div class="flex items-center gap-2 pb-3 border-b">
                        <div class="text-xl font-bold text-black dark:text-white">{{ lang().label.task_name }}: </div>
                        <div class="text-lg font-medium text-slate-800 dark:text-white dark:text-opacity-50">
                            {{ props.task.name }}
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <div class="flex flex-col justify-start items-start gap-2 py-3">
                                <div class="text-lg font-semibold text-black dark:text-white">{{ lang().label.description }}:</div>
                                <div class="text-base font-medium text-slate-800 dark:text-white dark:text-opacity-50" v-html="props.task.description"></div>
                            </div>

                            <div class="flex flex-col justify-start items-start gap-2 border-t py-3">
                                <div class="text-lg font-semibold text-black dark:text-white">{{ lang().label.due_date }}:</div>
                                <div class="text-base font-medium text-slate-800 dark:text-white dark:text-opacity-50">{{ props.task.due_date }}</div>
                            </div>

                            <div class="flex flex-col justify-start items-start gap-2 border-t py-3">
                                <div class="text-lg font-semibold text-black dark:text-white">{{ lang().label.assigned_user }}:</div>
                                <div class="text-base font-medium text-slate-800 dark:text-white dark:text-opacity-50"> {{ props.users[task.assigned_user] ?? lang().label.undefined }}</div>
                            </div>
                        </div>

                        <div>
                            <div class="flex flex-col justify-start items-start gap-2 py-3">
                                <div class="text-lg font-semibold text-black dark:text-white">{{ lang().label.status }}:</div>
                                <div class="text-base font-medium text-slate-800 dark:text-white dark:text-opacity-50">
                                    <Badge :value="getStatusLabel(props.task.status)" :severity="getStatusSeverity(props.task.status)" />
                                </div>
                            </div>

                            <div class="flex flex-col justify-start items-start gap-2 border-t py-3">
                                <div class="text-lg font-semibold text-black dark:text-white">{{ lang().label.priority }}:</div>
                                <div class="text-base font-medium text-slate-800 dark:text-white dark:text-opacity-50">
                                    <Badge :value="getPriorityLabel(props.task.priority)" :severity="getPrioritySeverity(props.task.priority)" />
                                </div>
                            </div>

                            <div class="flex flex-col justify-start items-start gap-2 border-t py-3">
                                <div class="text-lg font-semibold text-black dark:text-white">{{ lang().label.files }}:</div>
                                <div class="text-base font-medium text-slate-800 dark:text-white dark:text-opacity-50">
                                    <div v-if="props.files.length > 0">
                                        <ul class="list-none p-0 flex flex-col gap-1.5">
                                            <li v-for="(file, index) in props.files" :key="index" class="flex items-center space-x-2">
                                                <a v-tooltip="lang().tooltip.download" :href="file.original_url" target="_blank" class="text-blue-600 hover:text-blue-800">
                                                    {{ file.name }}
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div v-else>
                                        {{ lang().label.no_files }}
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col justify-start items-start gap-2 border-t py-3" v-if="props.task.task_completion && props.task.task_completion.completion_note">
                                <div class="mb-4 text-lg font-semibold text-black dark:text-white">{{ lang().label.completion_note }}</div>
                                <Textarea
                                    :readonly="true"
                                    :value="props.task.task_completion.completion_note"
                                    rows="5"
                                    style="resize: none"
                                    class="w-full"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </AuthenticatedLayout>
</template>



