<script setup>
import {ref, computed, onMounted, onUnmounted} from "vue";
import Tooltip from "@/Components/Tooltip.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import ToggleInput from "@/Components/ToggleInput.vue";
import {usePage} from "@inertiajs/vue3";

const props = defineProps({
    suggestion: Object,
    photoItems: Array,
});

const emit = defineEmits([
    'accept-suggestion',
    'reject-suggestion',
]);

const page = usePage();
const pickedUp = page.props.auth.user.settings.picked_up_by_default || false;
const recycled = page.props.auth.user.settings.recycled_by_default || false;
const deposit = page.props.auth.user.settings.deposit_by_default || false;

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

const onKeyDown = (event) => {
    if (event.target.tagName === 'INPUT' || event.target.tagName === 'TEXTAREA') return;

    const num = parseInt(event.key);
    if (num >= 1 && num <= displayItems.value.length) {
        event.preventDefault();
        selectedRank.value = num;
    }
};

defineExpose({ acceptSuggestion });

onMounted(() => {
    window.addEventListener('keydown', onKeyDown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', onKeyDown);
});
</script>

<template>
    <li class="col-span-1 lg:col-span-2 xl:col-span-3 flex flex-col divide-y divide-dashed divide-gray-200 dark:divide-gray-700 rounded-lg bg-white/10 dark:bg-gray-800/10 shadow border border-dashed border-gray-800/70 dark:border-white/70">
        <div class="px-4 py-5 sm:p-6">
            <!-- Item Cards -->
            <div class="flex gap-3 mb-4" v-if="displayItems.length">
                <button
                    v-for="(item, index) in displayItems"
                    :key="item.id"
                    @click="selectCard(index)"
                    class="flex-1 rounded-lg border-2 p-3 transition-all cursor-pointer text-left"
                    :class="selectedRank === index + 1
                        ? 'border-turqoFocus bg-turqoFocus/10 dark:bg-turqoFocus/20'
                        : 'border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500'"
                >
                    <div class="text-xs font-mono text-gray-500 dark:text-gray-400 mb-1">
                        {{ index + 1 }}
                    </div>
                    <div class="text-sm font-bold text-gray-900 dark:text-gray-100 truncate">
                        {{ item.name }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        {{ item.confidence }}%
                    </div>
                    <div class="mt-1.5 h-1.5 w-full bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                        <div
                            class="h-full rounded-full"
                            :class="selectedRank === index + 1 ? 'bg-turqoFocus' : 'bg-gray-400 dark:bg-gray-500'"
                            :style="{ width: item.confidence + '%' }"
                        ></div>
                    </div>
                </button>
            </div>

            <!-- Brand Checkboxes -->
            <div v-if="displayBrands.length" class="mb-3">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 mr-2">Brands:</span>
                <label
                    v-for="brand in displayBrands"
                    :key="brand.id"
                    class="inline-flex items-center mr-3 cursor-pointer"
                >
                    <input
                        type="checkbox"
                        :checked="selectedBrandIds.includes(brand.id)"
                        @change="toggleBrand(brand.id)"
                        class="rounded border-gray-300 dark:border-gray-600 text-turqoFocus focus:ring-turqoFocus dark:bg-gray-700"
                    >
                    <span class="ml-1.5 text-sm text-gray-700 dark:text-gray-300">{{ brand.name }}</span>
                    <span class="ml-1 text-xs text-gray-400">{{ brand.confidence }}%</span>
                </label>
            </div>

            <!-- Content Checkboxes -->
            <div v-if="displayContent.length" class="mb-4">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 mr-2">Content:</span>
                <label
                    v-for="content in displayContent"
                    :key="content.id"
                    class="inline-flex items-center mr-3 cursor-pointer"
                >
                    <input
                        type="checkbox"
                        :checked="selectedContentIds.includes(content.id)"
                        @change="toggleContent(content.id)"
                        class="rounded border-gray-300 dark:border-gray-600 text-turqoFocus focus:ring-turqoFocus dark:bg-gray-700"
                    >
                    <span class="ml-1.5 text-sm text-gray-700 dark:text-gray-300">{{ content.name }}</span>
                    <span class="ml-1 text-xs text-gray-400">{{ content.confidence }}%</span>
                </label>
            </div>

            <!-- Actions -->
            <div class="flex justify-between gap-2">
                <SecondaryButton
                    class="group relative w-full justify-center"
                    @click="acceptSuggestion"
                    :disabled="!selectedItem"
                >
                    <Tooltip>
                        <span class="whitespace-nowrap text-white">Ctrl (⌘) + Enter</span>
                    </Tooltip>
                    Accept
                </SecondaryButton>
                <DangerButton
                    @click="rejectSuggestion"
                    class="w-full"
                >
                    Reject
                </DangerButton>
            </div>
        </div>
        <div class="px-4 py-4 sm:px-6 flex flex-row justify-between items-center">
            <div class="text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">
                <i class="fas fa-wand-magic-sparkles text-gray-900 dark:text-turqoFocus mr-2"></i>
                <span class="font-bold">{{ suggestion.item_score }}%</span>
                AI Confidence
            </div>
            <div class="flex flex-row gap-4">
                <ToggleInput
                    :model-value="pickedUp"
                    class="opacity-70"
                    disabled="disabled"
                >
                    <template #label>Picked Up</template>
                </ToggleInput>
                <ToggleInput
                    :model-value="recycled"
                    class="opacity-70"
                    disabled="disabled"
                >
                    <template #label>Recycled</template>
                </ToggleInput>
                <ToggleInput
                    :model-value="deposit"
                    class="opacity-70"
                    disabled="disabled"
                >
                    <template #label>Deposit</template>
                </ToggleInput>
            </div>
        </div>
    </li>
</template>
