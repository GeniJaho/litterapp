<script setup>

import Tooltip from "@/Components/Tooltip.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import SuggestedTagShortcutItem from "@/Pages/Photos/Partials/SuggestedTagShortcutItem.vue";

const props = defineProps({
    suggestedTagShortcut: Object,
});

const emit = defineEmits([
    'add-suggested-tag-shortcut',
]);

const addSuggestedTagShortcut = () => {
    emit('add-suggested-tag-shortcut', props.suggestedTagShortcut);
};
</script>

<template>
    <li class="col-span-1 flex flex-col divide-y divide-dashed divide-gray-200 dark:divide-gray-700 rounded-lg bg-white/10 dark:bg-gray-800/10 shadow border border-dashed border-gray-800/70 dark:border-white/70">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="truncate text-lg font-bold text-gray-900 dark:text-gray-100">
                {{ suggestedTagShortcut.shortcut }} (Shortcut)
            </h3>

            <div class="mt-6 flex justify-between gap-2">
                <SecondaryButton
                    class="group relative w-full justify-center"
                    @click="addSuggestedTagShortcut"
                >
                    <Tooltip>
                        <span class="whitespace-nowrap text-white">Ctrl (⌘) + Shift + Enter</span>
                    </Tooltip>
                    Accept
                </SecondaryButton>
            </div>
        </div>
        <div class="px-4 py-5 sm:p-6">
            <div class="flex flex-col justify-center space-y-3">
                <SuggestedTagShortcutItem
                    v-for="suggestedItem in suggestedTagShortcut.tag_shortcut_items"
                    :key="suggestedItem.id"
                    :suggestedItem="suggestedItem"
                />
            </div>
        </div>
    </li>
</template>

<style scoped>

</style>
