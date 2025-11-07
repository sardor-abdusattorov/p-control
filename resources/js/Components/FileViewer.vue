<script setup>
import { ref, computed, watch } from 'vue';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import VuePdfEmbed from 'vue-pdf-embed';

const props = defineProps({
    file: {
        type: Object,
        default: null
    },
    visible: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:visible']);

const loading = ref(true);
const error = ref(null);
const currentPage = ref(1);
const pageCount = ref(0);

const dialogVisible = computed({
    get: () => props.visible,
    set: (value) => emit('update:visible', value)
});

const fileType = computed(() => {
    if (!props.file) return null;

    const mimeType = props.file.mime_type.toLowerCase();

    if (mimeType === 'application/pdf') return 'pdf';
    if (mimeType.startsWith('image/')) return 'image';
    if (mimeType.includes('word') || mimeType.includes('document')) return 'word';
    if (mimeType.includes('excel') || mimeType.includes('spreadsheet')) return 'excel';

    return 'other';
});

const canPreview = computed(() => {
    return fileType.value === 'pdf' || fileType.value === 'image';
});

const fileIcon = computed(() => {
    switch (fileType.value) {
        case 'pdf': return 'pi pi-file-pdf';
        case 'image': return 'pi pi-image';
        case 'word': return 'pi pi-file-word';
        case 'excel': return 'pi pi-file-excel';
        default: return 'pi pi-file';
    }
});

const handleDocumentLoad = ({ pageCount: count }) => {
    pageCount.value = count;
    loading.value = false;
};

const handleDocumentError = (err) => {
    error.value = 'Ошибка загрузки PDF документа';
    loading.value = false;
    console.error('PDF load error:', err);
};

const handleImageLoad = () => {
    loading.value = false;
};

const handleImageError = () => {
    error.value = 'Ошибка загрузки изображения';
    loading.value = false;
};

const nextPage = () => {
    if (currentPage.value < pageCount.value) {
        currentPage.value++;
    }
};

const prevPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--;
    }
};

const downloadFile = () => {
    if (props.file) {
        window.open(props.file.original_url, '_blank');
    }
};

watch(() => props.visible, (newVal) => {
    if (newVal) {
        loading.value = true;
        error.value = null;
        currentPage.value = 1;
    }
});
</script>

<template>
    <Dialog
        v-model:visible="dialogVisible"
        :modal="true"
        :closable="true"
        :draggable="false"
        :style="{ width: canPreview ? '90vw' : '50vw' }"
        class="file-viewer-dialog"
    >
        <template #header>
            <div class="flex items-center gap-3">
                <i :class="fileIcon" class="text-2xl"></i>
                <span class="font-semibold">{{ file?.name || 'Файл' }}</span>
            </div>
        </template>

        <div v-if="file" class="file-viewer-content">
            <!-- PDF Viewer -->
            <div v-if="fileType === 'pdf'" class="pdf-viewer">
                <div v-if="loading" class="flex justify-center items-center py-8">
                    <i class="pi pi-spin pi-spinner text-4xl text-blue-500"></i>
                    <span class="ml-3 text-gray-600 dark:text-gray-300">Загрузка PDF...</span>
                </div>

                <div v-if="error" class="text-center py-8 text-red-600">
                    <i class="pi pi-exclamation-circle text-4xl mb-2"></i>
                    <p>{{ error }}</p>
                </div>

                <div v-show="!loading && !error">
                    <vue-pdf-embed
                        :source="file.original_url"
                        @loaded="handleDocumentLoad"
                        @rendering-failed="handleDocumentError"
                        class="pdf-embed"
                    />

                    <div v-if="pageCount > 1" class="pdf-controls flex justify-center items-center gap-4 mt-4">
                        <Button
                            icon="pi pi-chevron-left"
                            @click="prevPage"
                            :disabled="currentPage === 1"
                            size="small"
                            outlined
                        />
                        <span class="text-sm dark:text-gray-300">
                            Страница {{ currentPage }} из {{ pageCount }}
                        </span>
                        <Button
                            icon="pi pi-chevron-right"
                            @click="nextPage"
                            :disabled="currentPage === pageCount"
                            size="small"
                            outlined
                        />
                    </div>
                </div>
            </div>

            <!-- Image Viewer -->
            <div v-else-if="fileType === 'image'" class="image-viewer">
                <div v-if="loading" class="flex justify-center items-center py-8">
                    <i class="pi pi-spin pi-spinner text-4xl text-blue-500"></i>
                    <span class="ml-3 text-gray-600 dark:text-gray-300">Загрузка изображения...</span>
                </div>

                <div v-if="error" class="text-center py-8 text-red-600">
                    <i class="pi pi-exclamation-circle text-4xl mb-2"></i>
                    <p>{{ error }}</p>
                </div>

                <img
                    v-show="!loading && !error"
                    :src="file.original_url"
                    :alt="file.name"
                    @load="handleImageLoad"
                    @error="handleImageError"
                    class="max-w-full h-auto mx-auto rounded-lg"
                />
            </div>

            <!-- Word/Excel/Other Files -->
            <div v-else class="unsupported-viewer text-center py-8">
                <i :class="fileIcon" class="text-6xl text-gray-400 dark:text-gray-500 mb-4"></i>
                <h3 class="text-xl font-semibold mb-2 dark:text-gray-200">
                    Предварительный просмотр недоступен
                </h3>
                <p class="text-gray-600 dark:text-gray-400 mb-4">
                    Для файлов {{ fileType === 'word' ? 'Word' : fileType === 'excel' ? 'Excel' : 'данного типа' }}
                    предварительный просмотр не поддерживается.
                </p>
                <Button
                    icon="pi pi-download"
                    label="Скачать файл"
                    @click="downloadFile"
                    severity="info"
                />
            </div>
        </div>

        <template #footer>
            <div class="flex justify-between items-center w-full">
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    {{ file?.mime_type }}
                    <span v-if="file?.size"> • {{ (file.size / 1024 / 1024).toFixed(2) }} MB</span>
                </div>
                <div class="flex gap-2">
                    <Button
                        v-if="canPreview"
                        icon="pi pi-download"
                        label="Скачать"
                        @click="downloadFile"
                        outlined
                    />
                    <Button
                        label="Закрыть"
                        @click="dialogVisible = false"
                        severity="secondary"
                    />
                </div>
            </div>
        </template>
    </Dialog>
</template>

<style scoped>
.pdf-embed {
    width: 100%;
    min-height: 70vh;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    background: white;
}

.dark .pdf-embed {
    border-color: #374151;
    background: #1f2937;
}

.file-viewer-content {
    max-height: 80vh;
    overflow-y: auto;
}

.image-viewer img {
    max-height: 75vh;
    object-fit: contain;
}

:deep(.p-dialog-header) {
    border-bottom: 1px solid #e5e7eb;
}

:deep(.dark .p-dialog-header) {
    border-bottom-color: #374151;
}

:deep(.p-dialog-footer) {
    border-top: 1px solid #e5e7eb;
    padding-top: 1rem;
}

:deep(.dark .p-dialog-footer) {
    border-top-color: #374151;
}
</style>
