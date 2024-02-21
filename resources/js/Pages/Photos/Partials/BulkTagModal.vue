<script setup>

import Modal from "@/Components/Modal.vue";

const emit = defineEmits(['close']);

defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: '2xl',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
});

const close = () => {
    emit('close');
};
</script>

<template>
    <Modal
        :show="show"
        :max-width="maxWidth"
        :closeable="closeable"
        @close="close"
    >
        <slot name="header" />

        <div class="mt-4 px-6 py-4 text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700">
            <slot name="content" />
        </div>

        <div class="flex flex-row justify-end px-6 py-4 bg-gray-100 dark:bg-gray-700 text-right">
            <slot name="footer" />
        </div>
    </Modal>
</template>
