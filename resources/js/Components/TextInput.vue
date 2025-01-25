<script setup>
import { onMounted, ref } from 'vue';

defineProps({
    modelValue: String|Number,
});

defineEmits(['update:modelValue']);

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        ref="input"
        class="block rounded-md border-0 py-1.5 text-gray-900 dark:text-gray-300 dark:bg-gray-900 shadow-xs ring-1 ring-inset ring-gray-300 dark:ring-gray-700 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-500 dark:focus:ring-indigo-600 sm:text-sm sm:leading-6"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
    >
</template>
