<script setup>
import { Link } from "@inertiajs/vue3";
import { defineProps } from 'vue';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    classes: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <Link
        :class="`inline-flex items-center px-2 sm:px-4 py-2 bg-primary dark:bg-primary border border-transparent rounded-md font-semibold text-xs text-white dark:text-white uppercase tracking-widest hover:bg-primary/80 dark:hover:bg-primary/90 focus:bg-primary/80 dark:focus:bg-primary/80 active:bg-primary dark:active:bg-primary/80 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 dark:focus:ring-offset-primary transition ease-in-out duration-150 disabled:bg-primary/80 ${classes}`"
        :href="href">
        {{ lang().button.add }}
    </Link>
</template>

<style scoped>
</style>
