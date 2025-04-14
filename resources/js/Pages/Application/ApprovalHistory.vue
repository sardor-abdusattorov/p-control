<script setup>
import Dialog from 'primevue/dialog';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import { ref, watch, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    show: Boolean,
    approval: Object,
    allApprovals: Array,
});

const emit = defineEmits(['close']);
const visible = ref(false);

watch(() => props.show, (val) => (visible.value = val));
const close = () => {
    visible.value = false;
    emit('close');
};

const lang = () => usePage().props.language;

const historyList = computed(() =>
    props.allApprovals
        .filter(a => a.user_id === props.approval.user_id)
        .sort((a, b) => new Date(a.created_at) - new Date(b.created_at))
);
</script>

<template>
    <Dialog
        v-model:visible="visible"
        modal
        :header="approval?.user_name"
        :style="{ width: '60vw' }"
        :breakpoints="{ '960px': '95vw' }"
        @hide="close"
    >
        <DataTable :value="historyList" class="p-datatable-sm">
            <!-- ФИО и дата создания -->
            <Column :header="lang().label.user_id">
                <template #body="slotProps">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 flex items-center justify-center rounded-full text-sm font-bold bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-100 uppercase">
                            {{ slotProps.data.user_name?.charAt(0) || '?' }}
                        </div>
                        <div>
                            <div class="font-semibold text-sm">
                                {{ slotProps.data.user_name }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ slotProps.data.created_at || '-' }}
                            </div>
                        </div>
                    </div>
                </template>
            </Column>

            <!-- Комментарий -->
            <Column :header="lang().label.comment">
                <template #body="slotProps">
      <span class="text-sm text-gray-800 dark:text-gray-100">
        {{ slotProps.data.reason || '-' }}
      </span>
                </template>
            </Column>

            <!-- Статус -->
            <Column :header="lang().label.status">
                <template #body="slotProps">
      <span
          :class="[
          'px-2 py-1 rounded-full text-xs font-semibold w-fit',
          slotProps.data.approved === 3 ? 'bg-green-100 text-green-800' :
          slotProps.data.approved === -1 ? 'bg-red-100 text-red-800' :
          slotProps.data.approved === -2 ? 'bg-gray-200 text-gray-700' :
          'bg-yellow-100 text-yellow-800'
        ]"
      >
        {{
              slotProps.data.approved === 3 ? lang().status.approved :
                  slotProps.data.approved === -1 ? lang().status.rejected :
                      slotProps.data.approved === -2 ? lang().status.invalidated :
                          lang().status.in_progress
          }}
      </span>
                </template>
            </Column>

            <Column :header="lang().label.approved_at">
                <template #body="slotProps">
      <span class="text-xs text-gray-600 dark:text-gray-400">
        {{ slotProps.data.approved_at || '-' }}
      </span>
                </template>
            </Column>

            <!-- Дата обновления -->
            <Column :header="lang().label.updated_at">
                <template #body="slotProps">
      <span class="text-xs text-gray-600 dark:text-gray-400">
        {{ slotProps.data.updated_at || '-' }}
      </span>
                </template>
            </Column>
        </DataTable>

    </Dialog>
</template>
