<script setup>
import { defineProps } from 'vue';
import {PencilIcon} from '@heroicons/vue/24/solid';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    color: {
        type: String,
        default: 'bg-blue-600 hover:bg-blue-500 active:bg-v-700 focus:ring-blue-500',
    },
});
</script>

<template>
    <Link :href="href"
          :class="`px-2 py-2 rounded-md flex items-center gap-2 justify-center border border-transparent shadow-md text-white transition-all duration-300 ease-in-out ${color}`"
    >
        <slot/>
        <PencilIcon class="w-4 h-4" />
    </Link>
</template>
