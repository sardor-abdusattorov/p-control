<script setup>
import { defineProps } from 'vue';
import { TrashIcon } from '@heroicons/vue/24/solid';
import { Link } from '@inertiajs/vue3';

// Определяем свойства компонента
const props = defineProps({
    href: {
        type: String,
        required: true, // Обязательное свойство для ссылки
    },
    class: {
        type: String,
        default: 'bg-red-600 hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-slate-800 flex items-center justify-center w-10 h-10 border border-transparent rounded-lg shadow-md text-white transition-all duration-300 ease-in-out',
    },
    title: {
        type: String,
        default: 'Удалить',
    },
});
</script>

<template>
    <Link :href="href"
          :class="class"
          v-tooltip="lang().tooltip.delete"
    >
    <TrashIcon class="w-5 h-5"/>
    </Link>
</template>
