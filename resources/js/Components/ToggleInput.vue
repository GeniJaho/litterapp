<script setup>

defineProps({
    modelValue: Boolean,
});

defineEmits(['update:modelValue']);
</script>

<template>
    <div class="flex items-center">
        <button
            type="button"
            :value="modelValue"
            @click="$emit('update:modelValue', ! modelValue)"
            :class="[modelValue ? 'bg-indigo-600' : 'bg-gray-200', 'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2']"
            role="switch"
            :aria-checked="modelValue"
        >
            <span aria-hidden="true" :class="[modelValue ? 'translate-x-5' : 'translate-x-0', 'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out']" />
        </button>
        <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-50">
            <slot name="title" />
        </span>
    </div>
</template>
