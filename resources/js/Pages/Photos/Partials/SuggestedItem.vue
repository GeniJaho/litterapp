<script setup>

import Tooltip from "@/Components/Tooltip.vue";
import TextInput from "@/Components/TextInput.vue";
import ToggleInput from "@/Components/ToggleInput.vue";
import DangerButton from "@/Components/DangerButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import {usePage} from "@inertiajs/vue3";

const props = defineProps({
    suggestedItem: Object,
});

const page = usePage();
const pickedUp = page.props.auth.user.settings.picked_up_by_default || false;
const recycled = page.props.auth.user.settings.recycled_by_default || false;
const deposit = page.props.auth.user.settings.deposit_by_default || false;

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
    <li class="col-span-1 flex flex-col divide-y divide-dashed divide-gray-200 dark:divide-gray-700 rounded-lg bg-white/10 dark:bg-gray-800/10 shadow border border-dashed border-gray-800/70 dark:border-white/70">
        <div class="px-4 py-5 sm:p-6 flex-1 flex flex-col justify-between">
            <div>
                <h3 class="truncate text-lg font-bold text-gray-900 dark:text-gray-100">
                    1 {{ suggestedItem.item.name }}
                </h3>
                <div class="mt-6 flex justify-between gap-2">
                    <SecondaryButton
                        class="group relative w-full justify-center"
                        @click="addSuggestedItem"
                    >
                        <Tooltip>
                            <span class="whitespace-nowrap text-white">Ctrl (⌘) + Enter</span>
                        </Tooltip>
                        Accept
                    </SecondaryButton>
                    <DangerButton
                        @click="rejectSuggestedItem"
                        class="w-full"
                    >
                        Reject
                    </DangerButton>
                </div>
            </div>

            <div class="mt-6 text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">
                <i class="fas fa-wand-magic-sparkles text-gray-900 dark:text-turqoFocus mr-2"></i>
                <span class="font-bold">{{ suggestedItem.score.toFixed() }}%</span>
                AI Confidence
            </div>
        </div>
        <div class="px-4 py-5 sm:p-6 flex flex-row justify-between">
            <div class="flex flex-col justify-center space-y-3">
                <div class="flex flex-row items-center opacity-70">
                    <TextInput
                        id="quantity"
                        type="number"
                        disabled="disabled"
                        :value="1"
                        class="w-14 px-2 mr-2"
                        required
                        min="1"
                        max="1000"
                    />
                    <label for="quantity" class="block font-medium text-sm text-gray-900 dark:text-gray-100">
                        Quantity
                    </label>
                </div>

                <ToggleInput
                    :model-value="pickedUp"
                    class="block w-full opacity-70"
                    disabled="disabled"
                >
                    <template #label>Picked Up</template>
                </ToggleInput>
                <ToggleInput
                    :model-value="recycled"
                    class="block w-full opacity-70"
                    disabled="disabled"
                >
                    <template #label>Recycled</template>
                </ToggleInput>
                <ToggleInput
                    :model-value="deposit"
                    class="block w-full opacity-70"
                    disabled="disabled"
                >
                    <template #label>Deposit</template>
                </ToggleInput>
            </div>
        </div>
    </li>
</template>

<style scoped>

</style>
