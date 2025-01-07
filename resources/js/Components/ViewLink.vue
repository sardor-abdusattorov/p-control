<script setup>
import { defineProps } from 'vue';
import { EyeIcon } from '@heroicons/vue/24/solid';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    color: {
        type: String,
        default: 'bg-purple-600 hover:bg-purple-500 active:bg-purple-700 focus:ring-purple-500',
    },
});
</script>

<template>
    <Link
        :href="href"
        :class="`px-2 py-2 rounded-md flex items-center gap-2 justify-center border border-transparent shadow-md text-white transition-all duration-300 ease-in-out ${color}`"
    >
        <EyeIcon class="w-4 h-4" />
    </Link>
</template>

<style scoped>
</style>
