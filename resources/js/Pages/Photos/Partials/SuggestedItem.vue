<script setup>

import PrimaryButton from "@/Components/PrimaryButton.vue";
import IconDangerButton from "@/Components/IconDangerButton.vue";
import Tooltip from "@/Components/Tooltip.vue";

const props = defineProps({
    suggestedItem: Object,
});

const emit = defineEmits([
    'add-suggested-item',
    'reject-suggested-item',
]);

const addSuggestedItem = () => {
    emit('add-suggested-item', props.suggestedItem);
};

const rejectSuggestedItem = () => {
    emit('reject-suggested-item', props.suggestedItem);
};
</script>

<template>
    <div class="flex justify-end sm:justify-start items-center">
        <PrimaryButton class="group relative" @click="addSuggestedItem">
            <Tooltip>
                <span class="whitespace-nowrap dark:text-white">Ctrl (⌘) + Enter</span>
            </Tooltip>
            <i class="fas fa-wand-magic-sparkles text-turqoFocus dark:text-gray-800"></i>
            <span class="ml-2">Add suggested: 1 {{ suggestedItem.item.name }} ({{ suggestedItem.score.toFixed() }}%)</span>
        </PrimaryButton>

        <IconDangerButton class="ml-2 h-8 w-8 group relative" @click="rejectSuggestedItem">
            <Tooltip>
                <span class="whitespace-nowrap dark:text-white normal-case tracking-normal">Reject incorrect suggestion</span>
            </Tooltip>
            <i class="fas fa-fw fa-xmark text-sm"></i>
        </IconDangerButton>

    </div>
</template>

<style scoped>

</style>
