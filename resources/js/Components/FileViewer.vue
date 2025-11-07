<script setup>
import { ref, computed, watch } from 'vue';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import VuePdfEmbed from 'vue-pdf-embed';
import VueOfficeDocx from '@vue-office/docx';
import VueOfficeExcel from '@vue-office/excel';
import '@vue-office/docx/lib/index.css';
import '@vue-office/excel/lib/index.css';

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
const isFullscreen = ref(false);
const fileData = ref(null); // Для хранения данных файла
const zoomLevel = ref(1); // Уровень масштаба (1 = 100%)

const dialogVisible = computed({
    get: () => props.visible,
    set: (value) => emit('update:visible', value)
});

const fileType = computed(() => {
    if (!props.file) return null;

    const mimeType = props.file.mime_type.toLowerCase();
    const fileName = props.file.name.toLowerCase();

    // PDF
    if (mimeType === 'application/pdf') return 'pdf';

    // Images
    if (mimeType.startsWith('image/')) return 'image';

    // Excel spreadsheets - ПРОВЕРЯЕМ СНАЧАЛА Excel!
    if (mimeType.includes('excel') ||
        mimeType.includes('spreadsheet') ||
        mimeType === 'application/vnd.ms-excel' ||
        mimeType === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' ||
        fileName.endsWith('.xls') ||
        fileName.endsWith('.xlsx')) {
        return 'excel';
    }

    // Word documents - ПОТОМ Word!
    if (mimeType.includes('word') ||
        mimeType === 'application/msword' ||
        mimeType === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' ||
        fileName.endsWith('.doc') ||
        fileName.endsWith('.docx')) {
        return 'word';
    }

    return 'other';
});

const canPreview = computed(() => {
    return ['pdf', 'image', 'word', 'excel'].includes(fileType.value);
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

const handleOfficeLoad = () => {
    loading.value = false;
};

const handleOfficeError = (err) => {
    error.value = 'Ошибка загрузки документа';
    loading.value = false;
    console.error('Office file load error:', err);
};

// Загрузка файла для Office компонентов
const loadOfficeFile = async () => {
    if (!props.file || !['word', 'excel'].includes(fileType.value)) return;

    loading.value = true;
    error.value = null;

    try {
        const response = await fetch(props.file.original_url);
        if (!response.ok) throw new Error('Failed to load file');

        const blob = await response.blob();
        fileData.value = blob;
        loading.value = false;
    } catch (err) {
        error.value = 'Ошибка загрузки файла';
        loading.value = false;
        console.error('File load error:', err);
    }
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

const toggleFullscreen = () => {
    isFullscreen.value = !isFullscreen.value;
};

const zoomIn = () => {
    if (zoomLevel.value < 3) { // Максимум 300%
        zoomLevel.value += 0.25;
    }
};

const zoomOut = () => {
    if (zoomLevel.value > 0.5) { // Минимум 50%
        zoomLevel.value -= 0.25;
    }
};

const resetZoom = () => {
    zoomLevel.value = 1;
};

// Вычисляемая ширина для PDF (в пикселях)
const pdfWidth = computed(() => {
    return Math.round(800 * zoomLevel.value); // Базовая ширина 800px
});

watch(() => props.visible, (newVal) => {
    if (newVal) {
        loading.value = true;
        error.value = null;
        currentPage.value = 1;
        fileData.value = null;
        zoomLevel.value = 1;

        // Загружаем Office файлы
        if (props.file && ['word', 'excel'].includes(fileType.value)) {
            loadOfficeFile();
        }
    } else {
        isFullscreen.value = false;
    }
});
</script>

<template>
    <Dialog
        v-model:visible="dialogVisible"
        :modal="true"
        :closable="true"
        :draggable="false"
        :style="{ width: isFullscreen ? '100vw' : (canPreview ? '90vw' : '50vw'), height: isFullscreen ? '100vh' : 'auto' }"
        :class="['file-viewer-dialog', { 'fullscreen-dialog': isFullscreen }]"
    >
        <template #header>
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-3">
                    <i :class="fileIcon" class="text-2xl"></i>
                    <span class="font-semibold">{{ file?.name || 'Файл' }}</span>
                </div>
                <Button
                    :icon="isFullscreen ? 'pi pi-window-minimize' : 'pi pi-window-maximize'"
                    @click="toggleFullscreen"
                    text
                    rounded
                    v-tooltip.bottom="isFullscreen ? 'Выйти из полноэкранного режима' : 'Полноэкранный режим'"
                />
            </div>
        </template>

        <div v-if="file" class="file-viewer-content">
            <!-- Zoom Controls -->
            <div v-if="canPreview" class="zoom-controls flex justify-center items-center gap-2 mb-4 pb-3 border-b border-gray-200 dark:border-gray-700">
                <Button
                    icon="pi pi-search-minus"
                    @click="zoomOut"
                    :disabled="zoomLevel <= 0.5"
                    size="small"
                    outlined
                    v-tooltip.bottom="'Уменьшить'"
                />
                <Button
                    :label="`${Math.round(zoomLevel * 100)}%`"
                    @click="resetZoom"
                    size="small"
                    outlined
                    v-tooltip.bottom="'Сбросить масштаб'"
                />
                <Button
                    icon="pi pi-search-plus"
                    @click="zoomIn"
                    :disabled="zoomLevel >= 3"
                    size="small"
                    outlined
                    v-tooltip.bottom="'Увеличить'"
                />
            </div>

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

                <div v-show="!loading && !error" class="pdf-container">
                    <vue-pdf-embed
                        :key="`pdf-${zoomLevel}`"
                        :source="file.original_url"
                        @loaded="handleDocumentLoad"
                        @rendering-failed="handleDocumentError"
                        :width="pdfWidth"
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

                <div v-show="!loading && !error" class="image-scroll-container">
                    <div class="image-container">
                        <img
                            :src="file.original_url"
                            :alt="file.name"
                            @load="handleImageLoad"
                            @error="handleImageError"
                            :style="{ transform: `scale(${zoomLevel})`, transformOrigin: 'center center', transition: 'transform 0.2s' }"
                            class="mx-auto rounded-lg"
                        />
                    </div>
                </div>
            </div>

            <!-- Word Viewer -->
            <div v-else-if="fileType === 'word'" class="word-viewer">
                <div v-if="loading" class="flex justify-center items-center py-8">
                    <i class="pi pi-spin pi-spinner text-4xl text-blue-500"></i>
                    <span class="ml-3 text-gray-600 dark:text-gray-300">Загрузка документа...</span>
                </div>

                <div v-if="error" class="text-center py-8 text-red-600">
                    <i class="pi pi-exclamation-circle text-4xl mb-2"></i>
                    <p>{{ error }}</p>
                </div>

                <vue-office-docx
                    v-if="!loading && !error && fileData"
                    :src="fileData"
                    @rendered="handleOfficeLoad"
                    @error="handleOfficeError"
                    :style="{ transform: `scale(${zoomLevel})`, transformOrigin: 'top center', transition: 'transform 0.2s' }"
                    class="office-embed"
                />
            </div>

            <!-- Excel Viewer -->
            <div v-else-if="fileType === 'excel'" class="excel-viewer">
                <div v-if="loading" class="flex justify-center items-center py-8">
                    <i class="pi pi-spin pi-spinner text-4xl text-blue-500"></i>
                    <span class="ml-3 text-gray-600 dark:text-gray-300">Загрузка таблицы...</span>
                </div>

                <div v-if="error" class="text-center py-8 text-red-600">
                    <i class="pi pi-exclamation-circle text-4xl mb-2"></i>
                    <p>{{ error }}</p>
                </div>

                <vue-office-excel
                    v-if="!loading && !error && fileData"
                    :src="fileData"
                    @rendered="handleOfficeLoad"
                    @error="handleOfficeError"
                    :style="{ transform: `scale(${zoomLevel})`, transformOrigin: 'top center', transition: 'transform 0.2s' }"
                    class="office-embed"
                />
            </div>

            <!-- Other Files -->
            <div v-else class="unsupported-viewer text-center py-8">
                <i :class="fileIcon" class="text-6xl text-gray-400 dark:text-gray-500 mb-4"></i>
                <h3 class="text-xl font-semibold mb-2 dark:text-gray-200">
                    Предварительный просмотр недоступен
                </h3>
                <p class="text-gray-600 dark:text-gray-400 mb-4">
                    Для файлов данного типа предварительный просмотр не поддерживается.
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
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    background: white;
}

.dark .pdf-embed {
    border-color: #374151;
    background: #1f2937;
}

.office-embed {
    width: 100%;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    background: white;
}

.word-viewer .office-embed {
    min-height: 70vh;
}

.excel-viewer .office-embed {
    height: 70vh;
}

.dark .office-embed {
    border-color: #374151;
    background: #1f2937;
}

/* Улучшение отображения Excel таблиц */
.excel-viewer :deep(.office-excel-container) {
    width: 100% !important;
    max-height: 70vh !important;
    overflow: auto !important;
}

.excel-viewer :deep(canvas) {
    width: 100% !important;
    height: auto !important;
}

.file-viewer-content {
    /* Dialog сам обрабатывает скролл */
}

.pdf-container {
    display: flex;
    justify-content: center;
    align-items: flex-start;
}

.image-scroll-container {
    max-height: 70vh;
    overflow: auto;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    background: white;
}

.dark .image-scroll-container {
    border-color: #374151;
    background: #1f2937;
}

.image-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 70vh;
    padding: 1rem;
}

/* Fullscreen styles */

.fullscreen-dialog .pdf-embed,
.fullscreen-dialog .office-embed {
    min-height: calc(100vh - 200px);
}

.fullscreen-dialog .image-scroll-container {
    max-height: calc(100vh - 200px);
}

.image-viewer img {
    max-width: 100%;
    height: auto;
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
