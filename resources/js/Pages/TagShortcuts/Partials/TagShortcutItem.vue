<script setup>
import {computed, ref} from "vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import IconPrimaryButton from "@/Components/IconPrimaryButton.vue";
import ToggleInput from "@/Components/ToggleInput.vue";
import IconDangerButton from "@/Components/IconDangerButton.vue";
import TextInput from "@/Components/TextInput.vue";
import TagBox from "@/Components/TagBox.vue";

const props = defineProps({
    item: Object,
    tags: Object,
});

const emit = defineEmits([
    'add-tags-to-item',
    'remove-item',
    'remove-tag-from-item',
    'update-quantity',
    'toggle-picked-up',
    'toggle-recycled',
    'toggle-deposit',
    'copy-item',
]);

const selectedMaterialTag = ref(null);
const selectedBrandTag = ref(null);
const selectedEventTag = ref(null);
const selectedStateTag = ref(null);
const selectedContentTag = ref(null);
const selectedSizeTag = ref(null);

const selectedTagIds = computed(() => {
    return [
        selectedMaterialTag.value?.id,
        selectedBrandTag.value?.id,
        selectedEventTag.value?.id,
        selectedStateTag.value?.id,
        selectedContentTag.value?.id,
        selectedSizeTag.value?.id,
    ].filter(tag => tag);
});

const addTagsToItem = () => {
    emit("add-tags-to-item", props.item, selectedTagIds.value);

    selectedMaterialTag.value = null;
    selectedBrandTag.value = null;
    selectedEventTag.value = null;
    selectedStateTag.value = null;
    selectedContentTag.value = null;
    selectedSizeTag.value = null;
};

</script>

<template>
    <li class="col-span-1 flex flex-col divide-y divide-gray-200 dark:divide-gray-700 rounded-lg bg-white dark:bg-gray-800 shadow">
        <div class="px-4 py-5 sm:p-6 flex-1">
            <h3 class="truncate text-lg font-bold text-gray-900 dark:text-gray-100">
                {{ item.quantity }}
                {{ item.item.name }}
            </h3>

            <div class="mt-6">
                <div class="space-y-2">
                    <TagBox
                        :items="tags.material"
                        :nullable="true"
                        placeholder="Material"
                        v-model="selectedMaterialTag"
                        @change="addTagsToItem"
                    ></TagBox>

                    <TagBox
                        :items="tags.brand"
                        :nullable="true"
                        placeholder="Brand"
                        v-model="selectedBrandTag"
                        @change="addTagsToItem"
                    ></TagBox>

                    <TagBox
                        :items="tags.content"
                        :nullable="true"
                        placeholder="Content"
                        v-model="selectedContentTag"
                        @change="addTagsToItem"
                    ></TagBox>

                    <TagBox
                        :items="tags.size"
                        :nullable="true"
                        placeholder="Size"
                        v-model="selectedSizeTag"
                        @change="addTagsToItem"
                    ></TagBox>

                    <TagBox
                        :items="tags.state"
                        :nullable="true"
                        placeholder="State"
                        v-model="selectedStateTag"
                        @change="addTagsToItem"
                    ></TagBox>

                    <TagBox
                        :items="tags.event"
                        :nullable="true"
                        placeholder="Event"
                        v-model="selectedEventTag"
                        @change="addTagsToItem"
                    ></TagBox>
                </div>

                <div v-if="item.tags?.length" class="mt-4 text-sm text-gray-500 flex flex-wrap gap-1">
                    <span
                        v-for="tag in item.tags"
                        :key="tag.id"
                        @click="$emit('remove-tag-from-item', item, tag.id)"
                        class="inline-flex cursor-pointer items-center gap-x-1.5 rounded-full px-2 py-1 text-xs font-medium text-gray-900 dark:text-gray-100 ring-1 ring-inset ring-gray-200"
                    >
                        <svg class="h-1.5 w-1.5 fill-green-500" viewBox="0 0 6 6"
                             aria-hidden="true"><circle cx="3" cy="3" r="3"/></svg>
                        {{ tag.name }}
                    </span>
                </div>

                <div v-if="selectedTagIds.length" class="mt-4 flex justify-center">
                    <PrimaryButton
                        class="whitespace-nowrap"
                        @click="addTagsToItem"
                    >
                        Add Selected Tags
                    </PrimaryButton>
                </div>
            </div>
        </div>
        <div class="px-4 py-5 sm:p-6 flex flex-row justify-between">
            <div class="flex flex-col justify-center space-y-3">
                <div class="flex flex-row items-center">
                    <TextInput
                        id="quantity"
                        type="number"
                        :model-value="item.quantity"
                        class="w-12 mr-2"
                        required
                        min="1"
                        max="1000"
                        @input="$emit('update-quantity', item.id, $event.target.value)"
                    />
                    <label for="quantity" class="block font-medium text-sm text-gray-900 dark:text-gray-100">
                        Quantity
                    </label>
                </div>

                <ToggleInput
                    v-model="item.picked_up"
                    @update:modelValue="$emit('toggle-picked-up', item.id, item.picked_up)"
                    class="block w-full"
                >
                    <template #label>Picked Up</template>
                </ToggleInput>
                <ToggleInput
                    v-model="item.recycled"
                    @update:modelValue="$emit('toggle-recycled', item.id, item.recycled)"
                    class="block w-full"
                >
                    <template #label>Recycled</template>
                </ToggleInput>
                <ToggleInput
                    v-model="item.deposit"
                    @update:modelValue="$emit('toggle-deposit', item.id, item.deposit)"
                    class="block w-full"
                >
                    <template #label>Deposit</template>
                </ToggleInput>
            </div>

            <div class="flex flex-row flex-1 items-end justify-end gap-3">
                <IconPrimaryButton
                    @click="$emit('copy-item', item.id)"
                >
                    <i class="far fa-fw fa-copy text-xs"></i>
                </IconPrimaryButton>
                <IconDangerButton
                    @click="$emit('remove-item', item.id)"
                >
                    <i class="fas fa-fw fa-trash-alt text-xs"></i>
                </IconDangerButton>
            </div>
        </div>
    </li>
</template>

<style scoped>

</style>
