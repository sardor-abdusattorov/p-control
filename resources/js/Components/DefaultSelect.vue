<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    modelValue: [String, Number],
    dataSet: Array
});

const emit = defineEmits(['update:modelValue']);

const selectedValue = ref(props.modelValue);

watch(selectedValue, (newValue) => {
    emit('update:modelValue', newValue);
});
</script>


<template>
    <select class="py-3 px-4 pe-9 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            ref="input"

    >
        <option selected="">Open this select menu</option>
        <option
            v-for="(data, index) in dataSet"
            :key="index"
            :value="data.value"
        >
            {{ data.label }}
        </option>
    </select>
</template>

<style scoped>

</style>
