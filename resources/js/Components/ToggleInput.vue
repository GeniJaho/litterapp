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
            :class="[modelValue ? 'bg-gray-800 dark:bg-gray-200' : 'bg-gray-200 dark:bg-gray-600', 'relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition duration-200 ease-in-out focus:outline-hidden focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2']"
            role="switch"
            :aria-checked="modelValue"
        >
            <span aria-hidden="true" :class="[modelValue ? 'translate-x-5' : 'translate-x-0', 'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white dark:bg-gray-800 shadow-sm ring-0 transition duration-200 ease-in-out']" />
        </button>
        <span class="ml-3">
            <span class="flex flex-col">
                <span class="text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">
                    <slot name="label" />
                </span>
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    <slot name="description" />
                </span>
            </span>
        </span>
    </div>
</template>
