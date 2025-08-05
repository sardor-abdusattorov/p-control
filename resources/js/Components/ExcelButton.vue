<script setup>
defineProps({
    disabled: Boolean,
    ariaLabel: {
        type: String,
        default: "Export",
    }
});
const emit = defineEmits(['click']);
</script>

<template>
    <button
        :type="'button'"
        :aria-label="ariaLabel"
        :disabled="disabled"
        @click="$emit('click')"
        class="inline-flex items-center justify-center rounded-md bg-green-500 text-white text-sm font-semibold px-2.5 py-2 shadow hover:bg-green-600 active:bg-green-700 disabled:bg-gray-300 disabled:text-gray-500 transition"
    >
        <i class="pi pi-file-excel text-base"></i>
    </button>
</template>
