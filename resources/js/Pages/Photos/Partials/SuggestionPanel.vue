<script setup>
import {ref, computed} from "vue";
import Tooltip from "@/Components/Tooltip.vue";

import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";


const props = defineProps({
    suggestion: Object,
    photoItems: Array,
});

const emit = defineEmits([
    'accept-suggestion',
    'reject-suggestion',
]);

const existingItemIds = computed(() => {
    return (props.photoItems || []).map(pi => pi.item_id);
});

const displayItems = computed(() => {
    if (!props.suggestion?.prediction_items?.items) return [];
    return props.suggestion.prediction_items.items.filter(
        item => !existingItemIds.value.includes(item.id)
    );
});

const displayBrands = computed(() => {
    return props.suggestion?.prediction_items?.brands || [];
});

const displayContent = computed(() => {
    return props.suggestion?.prediction_items?.content || [];
});

const selectedRank = ref(1);
const selectedBrandIds = ref([]);
const selectedContentIds = ref([]);

// Pre-populate brand/content checkboxes based on confidence >= 50
const initSelections = () => {
    selectedRank.value = 1;
    selectedBrandIds.value = displayBrands.value
        .filter(b => b.confidence >= 50)
        .map(b => b.id);
    selectedContentIds.value = displayContent.value
        .filter(c => c.confidence >= 50)
        .map(c => c.id);
};

initSelections();

const selectedItem = computed(() => {
    return displayItems.value[selectedRank.value - 1] || null;
});

const selectCard = (index) => {
    selectedRank.value = index + 1;
};

const toggleBrand = (id) => {
    const idx = selectedBrandIds.value.indexOf(id);
    if (idx >= 0) {
        selectedBrandIds.value.splice(idx, 1);
    } else {
        selectedBrandIds.value.push(id);
    }
};

const toggleContent = (id) => {
    const idx = selectedContentIds.value.indexOf(id);
    if (idx >= 0) {
        selectedContentIds.value.splice(idx, 1);
    } else {
        selectedContentIds.value.push(id);
    }
};

const findOriginalRank = (itemId) => {
    const items = props.suggestion?.prediction_items?.items || [];
    const idx = items.findIndex(i => i.id === itemId);
    return idx >= 0 ? idx + 1 : null;
};

const acceptSuggestion = () => {
    if (!selectedItem.value) return;
    emit('accept-suggestion', {
        itemId: selectedItem.value.id,
        rank: findOriginalRank(selectedItem.value.id),
        brandTagIds: [...selectedBrandIds.value],
        contentTagIds: [...selectedContentIds.value],
    });
};

const rejectSuggestion = () => {
    emit('reject-suggestion');
};

const fillStyle = (confidence) => {
    const skew = 12;
    return {
        clipPath: `polygon(0 0, calc(${confidence}% + ${skew}px) 0, ${confidence}% 100%, 0 100%)`,
    };
};

defineExpose({ acceptSuggestion });
</script>

<template>
    <li class="col-span-1 lg:col-span-2 self-start flex flex-col rounded-lg bg-white/10 dark:bg-gray-800/10 shadow border border-dashed border-gray-800/70 dark:border-white/70">
        <div class="px-4 py-4 sm:px-5 sm:py-4">

            <!-- Item Cards (stacked) -->
            <div class="flex flex-col gap-2" v-if="displayItems.length">
                <button
                    v-for="(item, index) in displayItems"
                    :key="item.id"
                    @click="selectCard(index)"
                    class="relative rounded-lg border-2 px-3 py-2 transition-all cursor-pointer text-left overflow-hidden"
                    :class="selectedRank === index + 1
                        ? 'border-turqoFocus'
                        : 'border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500'"
                >
                    <span
                        class="absolute inset-0 pointer-events-none"
                        :class="selectedRank === index + 1
                            ? 'bg-turqoFocus/15 dark:bg-turqoFocus/20'
                            : 'bg-gray-200/50 dark:bg-gray-700/30'"
                        :style="fillStyle(item.confidence)"
                    ></span>
                    <span class="relative flex items-baseline gap-1.5">
                        <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">{{ item.confidence }}%</span>
                        <span class="text-sm font-bold text-gray-900 dark:text-gray-100 truncate">{{ item.name }}</span>
                    </span>
                </button>
            </div>

            <!-- Brand Tags -->
            <div v-if="displayBrands.length" class="flex flex-wrap items-center gap-1 mt-3">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 mr-1">Brands:</span>
                <button
                    v-for="brand in displayBrands"
                    :key="brand.id"
                    @click="toggleBrand(brand.id)"
                    class="inline-flex items-center gap-x-1.5 rounded-full px-2 py-1 text-xs font-medium text-gray-900 dark:text-gray-100 ring-1 ring-inset transition-all cursor-pointer"
                    :class="selectedBrandIds.includes(brand.id)
                        ? 'ring-turqoFocus'
                        : 'ring-gray-200 dark:ring-gray-700 hover:ring-gray-400'"
                >
                    <svg class="h-1.5 w-1.5" :class="selectedBrandIds.includes(brand.id) ? 'fill-turqoFocus' : 'fill-gray-400 dark:fill-gray-500'" viewBox="0 0 6 6"><circle cx="3" cy="3" r="3"/></svg>
                    <span class="text-gray-500 dark:text-gray-400">{{ brand.confidence }}%</span>
                    {{ brand.name }}
                </button>
            </div>

            <!-- Content Tags -->
            <div v-if="displayContent.length" class="flex flex-wrap items-center gap-1 mt-2">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 mr-1">Content:</span>
                <button
                    v-for="content in displayContent"
                    :key="content.id"
                    @click="toggleContent(content.id)"
                    class="inline-flex items-center gap-x-1.5 rounded-full px-2 py-1 text-xs font-medium text-gray-900 dark:text-gray-100 ring-1 ring-inset transition-all cursor-pointer"
                    :class="selectedContentIds.includes(content.id)
                        ? 'ring-turqoFocus'
                        : 'ring-gray-200 dark:ring-gray-700 hover:ring-gray-400'"
                >
                    <svg class="h-1.5 w-1.5" :class="selectedContentIds.includes(content.id) ? 'fill-turqoFocus' : 'fill-gray-400 dark:fill-gray-500'" viewBox="0 0 6 6"><circle cx="3" cy="3" r="3"/></svg>
                    <span class="text-gray-500 dark:text-gray-400">{{ content.confidence }}%</span>
                    {{ content.name }}
                </button>
            </div>

            <!-- Actions -->
            <div class="flex gap-2 mt-4">
                <PrimaryButton
                    class="group relative w-full justify-center"
                    type="button"
                    @click="acceptSuggestion"
                    :disabled="!selectedItem"
                >
                    <Tooltip>
                        <span class="whitespace-nowrap text-white">Ctrl (⌘) + Enter</span>
                    </Tooltip>
                    Accept
                </PrimaryButton>
                <SecondaryButton
                    @click="rejectSuggestion"
                    class="w-full justify-center"
                >
                    Reject
                </SecondaryButton>
            </div>
        </div>
    </li>
</template>
