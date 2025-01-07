<script setup>
defineProps(["value"]);
</script>

<template>
    <label class="block font-bold text-sm text-slate-700 dark:text-slate-300 mb-2">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
