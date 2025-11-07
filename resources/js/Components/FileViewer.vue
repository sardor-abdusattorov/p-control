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

    // Excel spreadsheets - только новый формат .xlsx
    if (mimeType === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' ||
        (fileName.endsWith('.xlsx') && mimeType.includes('spreadsheet'))) {
        return 'excel';
    }

    // Старые форматы Excel (.xls) - не поддерживаются
    if (mimeType === 'application/vnd.ms-excel' ||
        fileName.endsWith('.xls')) {
        return 'excel-old';
    }

    // Word documents - только новый формат .docx
    if (mimeType === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' ||
        (fileName.endsWith('.docx') && mimeType.includes('word'))) {
        return 'word';
    }

    // Старые форматы Word (.doc) - не поддерживаются
    if (mimeType === 'application/msword' ||
        fileName.endsWith('.doc')) {
        return 'word-old';
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
        case 'word-old': return 'pi pi-file-word';
        case 'excel': return 'pi pi-file-excel';
        case 'excel-old': return 'pi pi-file-excel';
        default: return 'pi pi-file';
    }
});

const handleDocumentLoad = ({ pageCount: count }) => {
    pageCount.value = count;
    loading.value = false;
};

const handleDocumentError = (err) => {
    error.value = lang().file_viewer.error_loading_pdf;
    loading.value = false;
    console.error('PDF load error:', err);
};

const handleImageLoad = () => {
    loading.value = false;
};

const handleImageError = () => {
    error.value = lang().file_viewer.error_loading_image;
    loading.value = false;
};

const handleOfficeLoad = () => {
    loading.value = false;
};

const handleOfficeError = (err) => {
    error.value = lang().file_viewer.error_loading_document;
    loading.value = false;
    console.error('Office file load error:', err);
};

// Загрузка файла для Office компонентов
const loadOfficeFile = async () => {
    if (!props.file || !['word', 'excel'].includes(fileType.value)) return;

    loading.value = true;
    error.value = null;

    try {
        console.log('Loading office file:', {
            name: props.file.name,
            type: fileType.value,
            mime: props.file.mime_type,
            url: props.file.original_url
        });

        const response = await fetch(props.file.original_url);
        console.log('Fetch response:', response.status, response.statusText);

        if (!response.ok) throw new Error(`Failed to load file: ${response.status} ${response.statusText}`);

        const blob = await response.blob();
        console.log('Blob created:', blob.size, 'bytes, type:', blob.type);

        fileData.value = blob;
        loading.value = false;
    } catch (err) {
        error.value = `${lang().file_viewer.error_loading_file}: ${err.message}`;
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
        :closable="false"
        :draggable="false"
        :style="{ width: isFullscreen ? '100vw' : (canPreview ? '90vw' : '50vw'), height: isFullscreen ? '100vh' : 'auto' }"
        :class="['file-viewer-dialog', { 'fullscreen-dialog': isFullscreen }]"
    >
        <template #header>
            <div class="file-viewer-header">
                <div class="header-row-1">
                    <div class="file-info">
                        <i :class="fileIcon" class="text-2xl"></i>
                        <span class="file-name font-semibold">{{ file?.name || lang().file_viewer.file }}</span>
                    </div>

                    <!-- Кнопки зума для мобилки (в одной строке) -->
                    <div v-if="canPreview" class="zoom-controls-mobile">
                        <Button
                            icon="pi pi-search-minus"
                            @click="zoomOut"
                            :disabled="zoomLevel <= 0.5"
                            size="small"
                            outlined
                            v-tooltip.bottom="lang().file_viewer.zoom_out"
                        />
                        <Button
                            :label="`${Math.round(zoomLevel * 100)}%`"
                            @click="resetZoom"
                            size="small"
                            outlined
                            v-tooltip.bottom="lang().file_viewer.reset_zoom"
                        />
                        <Button
                            icon="pi pi-search-plus"
                            @click="zoomIn"
                            :disabled="zoomLevel >= 3"
                            size="small"
                            outlined
                            v-tooltip.bottom="lang().file_viewer.zoom_in"
                        />
                    </div>

                    <Button
                        :icon="isFullscreen ? 'pi pi-window-minimize' : 'pi pi-window-maximize'"
                        @click="toggleFullscreen"
                        text
                        rounded
                        class="fullscreen-btn"
                        v-tooltip.bottom="isFullscreen ? lang().file_viewer.fullscreen_exit : lang().file_viewer.fullscreen_enter"
                    />
                    <Button
                        icon="pi pi-times"
                        @click="dialogVisible = false"
                        text
                        rounded
                        class="close-btn"
                        v-tooltip.bottom="lang().button.close"
                    />
                </div>

                <!-- Zoom Controls в отдельной строке для десктопа -->
                <div v-if="canPreview" class="header-row-2">
                    <div class="zoom-controls">
                        <Button
                            icon="pi pi-search-minus"
                            @click="zoomOut"
                            :disabled="zoomLevel <= 0.5"
                            size="small"
                            outlined
                            v-tooltip.bottom="lang().file_viewer.zoom_out"
                        />
                        <Button
                            :label="`${Math.round(zoomLevel * 100)}%`"
                            @click="resetZoom"
                            size="small"
                            outlined
                            v-tooltip.bottom="lang().file_viewer.reset_zoom"
                        />
                        <Button
                            icon="pi pi-search-plus"
                            @click="zoomIn"
                            :disabled="zoomLevel >= 3"
                            size="small"
                            outlined
                            v-tooltip.bottom="lang().file_viewer.zoom_in"
                        />
                    </div>
                </div>
            </div>
        </template>

        <div v-if="file" :class="['file-viewer-content', { 'fullscreen-content': isFullscreen }]">
            <!-- PDF Viewer -->
            <div v-if="fileType === 'pdf'" class="pdf-viewer">
                <div v-if="loading" class="flex justify-center items-center py-8">
                    <i class="pi pi-spin pi-spinner text-4xl text-blue-500"></i>
                    <span class="ml-3 text-gray-600 dark:text-gray-300">{{ lang().file_viewer.loading_pdf }}</span>
                </div>

                <div v-if="error" class="text-center py-8 text-red-600">
                    <i class="pi pi-exclamation-circle text-4xl mb-2"></i>
                    <p>{{ error }}</p>
                </div>

                <div v-show="!loading && !error" class="pdf-container">
                    <vue-pdf-embed
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
                            {{ lang().file_viewer.page_of.replace(':current', currentPage).replace(':total', pageCount) }}
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
                    <span class="ml-3 text-gray-600 dark:text-gray-300">{{ lang().file_viewer.loading_image }}</span>
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
                    <span class="ml-3 text-gray-600 dark:text-gray-300">{{ lang().file_viewer.loading_document }}</span>
                </div>

                <div v-if="error" class="text-center py-8 text-red-600">
                    <i class="pi pi-exclamation-circle text-4xl mb-2"></i>
                    <p>{{ error }}</p>
                </div>

                <div v-if="!loading && !error && fileData" class="office-embed">
                    <div class="office-zoom-wrapper" :style="{ transform: `scale(${zoomLevel})`, transformOrigin: 'center', transition: 'transform 0.2s' }">
                        <vue-office-docx
                            :src="fileData"
                            @rendered="handleOfficeLoad"
                            @error="handleOfficeError"
                        />
                    </div>
                </div>
            </div>

            <!-- Excel Viewer -->
            <div v-else-if="fileType === 'excel'" class="excel-viewer">
                <div v-if="loading" class="flex justify-center items-center py-8">
                    <i class="pi pi-spin pi-spinner text-4xl text-blue-500"></i>
                    <span class="ml-3 text-gray-600 dark:text-gray-300">{{ lang().file_viewer.loading_spreadsheet }}</span>
                </div>

                <div v-if="error" class="text-center py-8 text-red-600">
                    <i class="pi pi-exclamation-circle text-4xl mb-2"></i>
                    <p>{{ error }}</p>
                </div>

                <div v-if="!loading && !error && fileData" class="office-embed">
                    <div class="office-zoom-wrapper" :style="{ transform: `scale(${zoomLevel})`, transformOrigin: 'center', transition: 'transform 0.2s' }">
                        <vue-office-excel
                            :src="fileData"
                            @rendered="handleOfficeLoad"
                            @error="handleOfficeError"
                        />
                    </div>
                </div>
            </div>

            <!-- Old Office Formats (not supported) -->
            <div v-else-if="fileType === 'word-old' || fileType === 'excel-old'" class="unsupported-viewer text-center py-8">
                <i :class="fileIcon" class="text-6xl text-yellow-500 mb-4"></i>
                <h3 class="text-xl font-semibold mb-2 dark:text-gray-200">
                    {{ lang().file_viewer.old_format_not_supported }}
                </h3>
                <p class="text-gray-600 dark:text-gray-400 mb-2">
                    <span v-if="fileType === 'word-old'" v-html="lang().file_viewer.old_word_format_info"></span>
                    <span v-else-if="fileType === 'excel-old'" v-html="lang().file_viewer.old_excel_format_info"></span>
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-500 mb-4">
                    {{ lang().file_viewer.download_or_convert }}
                </p>
                <Button
                    icon="pi pi-download"
                    :label="lang().file_viewer.download_file"
                    @click="downloadFile"
                    severity="warning"
                />
            </div>

            <!-- Other Files -->
            <div v-else class="unsupported-viewer text-center py-8">
                <i :class="fileIcon" class="text-6xl text-gray-400 dark:text-gray-500 mb-4"></i>
                <h3 class="text-xl font-semibold mb-2 dark:text-gray-200">
                    {{ lang().file_viewer.preview_not_available }}
                </h3>
                <p class="text-gray-600 dark:text-gray-400 mb-4">
                    {{ lang().file_viewer.preview_not_supported }}
                </p>
                <Button
                    icon="pi pi-download"
                    :label="lang().file_viewer.download_file"
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
                        :label="lang().label.download"
                        @click="downloadFile"
                        outlined
                    />
                    <Button
                        :label="lang().button.close"
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
    overflow: auto;
    position: relative;
    padding: 1rem;
    display: flex;
    justify-content: center;
    align-items: flex-start;
}

/* Убираем фон из внутренних элементов Office компонентов */
.office-embed :deep(.docx-wrapper) {
    background: transparent !important;
}

.office-embed :deep(.excel-wrapper) {
    background: transparent !important;
}

.word-viewer .office-embed {
    min-height: 70vh;
}

.excel-viewer .office-embed {
    max-height: 70vh;
}

.dark .office-embed {
    border-color: #374151;
    background: #1f2937;
}

.office-zoom-wrapper {
    display: inline-block;
}

/* Улучшение отображения Excel таблиц */
.excel-viewer :deep(canvas) {
    width: 100% !important;
    height: auto !important;
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
    text-align: center;
    padding: 1rem;
}

.dark .image-scroll-container {
    border-color: #374151;
    background: #1f2937;
}

.image-container {
    display: inline-block;
}

/* Fullscreen styles */
.fullscreen-content {
    padding: 1.5rem !important;
}

.fullscreen-dialog {
    padding: 0 !important;
}

.fullscreen-dialog :deep(.p-dialog-mask) {
    padding: 0 !important;
}

.fullscreen-dialog :deep(.p-dialog) {
    margin: 0 !important;
    max-height: 100vh !important;
    height: 100vh !important;
    border-radius: 0 !important;
}

.fullscreen-dialog :deep(.p-dialog-content) {
    padding: 0 !important;
    overflow: auto;
    max-height: calc(100vh - 130px) !important;
    height: calc(100vh - 130px) !important;
}

.fullscreen-dialog :deep(.p-dialog-header) {
    padding: 1rem 1.5rem;
}

.fullscreen-dialog :deep(.p-dialog-footer) {
    padding: 1rem 1.5rem;
}

.fullscreen-dialog .pdf-embed {
    min-height: calc(100vh - 180px);
}

.fullscreen-dialog .word-viewer .office-embed {
    min-height: calc(100vh - 180px);
}

.fullscreen-dialog .excel-viewer .office-embed {
    max-height: calc(100vh - 180px);
}

.fullscreen-dialog .image-scroll-container {
    max-height: calc(100vh - 180px);
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

/* Header styles */
.file-viewer-header {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.header-row-1 {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    gap: 1rem;
}

.file-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex: 1;
    min-width: 0; /* Важно для работы text-overflow */
}

.file-name {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 100%;
}

.header-row-2 {
    display: flex;
    justify-content: center;
    width: 100%;
    padding-top: 0.5rem;
    border-top: 1px solid #e5e7eb;
}

.dark .header-row-2 {
    border-top-color: #374151;
}

.zoom-controls {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* Мобильные кнопки зума - скрыты на десктопе */
.zoom-controls-mobile {
    display: none;
}

/* Mobile optimization */
@media (max-width: 767px) {
    .file-viewer-dialog :deep(.p-dialog) {
        width: 95vw !important;
        max-width: 95vw !important;
    }

    /* Скрываем название файла и иконку в header */
    .file-name {
        display: none;
    }

    .file-info {
        display: none;
    }

    /* Скрываем вторую строку с кнопками зума на мобилке */
    .header-row-2 {
        display: none;
    }

    /* Показываем мобильные кнопки зума */
    .zoom-controls-mobile {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .zoom-controls-mobile :deep(.p-button) {
        padding: 0.375rem 0.5rem;
        font-size: 0.75rem;
    }

    /* Выравниваем все кнопки справа */
    .header-row-1 {
        justify-content: flex-end;
        gap: 0.5rem;
    }

    .file-viewer-header {
        gap: 0;
    }

    .fullscreen-btn,
    .close-btn {
        padding: 0.375rem !important;
    }

    :deep(.p-dialog-header) {
        padding: 0.75rem 1rem !important;
    }

    :deep(.p-dialog-footer) {
        padding: 0.75rem 1rem !important;
        flex-direction: column;
        gap: 0.5rem;
    }

    /* Скрываем информацию о типе файла в footer */
    :deep(.p-dialog-footer) > div:first-child {
        display: none;
    }

    :deep(.p-dialog-footer) > div {
        width: 100%;
    }

    :deep(.p-dialog-footer .flex) {
        flex-direction: column;
        align-items: stretch;
    }

    :deep(.p-dialog-footer .flex.gap-2) {
        gap: 0.5rem;
    }

    :deep(.p-dialog-footer .p-button) {
        width: 100%;
    }

    /* Уменьшаем размеры контента на мобилке */
    .image-scroll-container,
    .word-viewer .office-embed,
    .excel-viewer .office-embed {
        max-height: 60vh;
    }

    .pdf-container {
        flex-direction: column;
    }

    .pdf-embed {
        width: 100% !important;
    }
}

/* Small mobile devices */
@media (max-width: 480px) {
    .file-viewer-dialog :deep(.p-dialog) {
        width: 100vw !important;
        max-width: 100vw !important;
        margin: 0 !important;
    }

    .header-row-2 {
        padding-top: 0.25rem;
    }

    .file-name {
        font-size: 0.8125rem;
    }

    .zoom-controls :deep(.p-button) {
        padding: 0.25rem 0.375rem;
    }
}
</style>
