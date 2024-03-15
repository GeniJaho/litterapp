<script setup>
import {computed, ref} from "vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import IconPrimaryButton from "@/Components/IconPrimaryButton.vue";
import ToggleInput from "@/Components/ToggleInput.vue";
import IconDangerButton from "@/Components/IconDangerButton.vue";
import TextInput from "@/Components/TextInput.vue";
import TagBox from "@/Components/TagBox.vue";

const props = defineProps({
    propItem: Object,
    tags: Object,
});

const item = ref(props.propItem);

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

const tagNames = computed(() => {
    return item.value.tag_ids.map(function (tagId) {
        for (const [key, value] of Object.entries(props.tags)) {
            const tagName = value.find(tag => tag.id === tagId)?.name;
            if (tagName) {
                return {id: tagId, name: tagName};
            }
        }
    });
});

const addTagsToItem = () => {
    item.value.tag_ids = [...new Set([...item.value.tag_ids, ...selectedTagIds.value])];

    emit('change', item.value);

    selectedMaterialTag.value = null;
    selectedBrandTag.value = null;
    selectedEventTag.value = null;
    selectedStateTag.value = null;
    selectedContentTag.value = null;
    selectedSizeTag.value = null;
};

const removeTagFromItem = (tagId) => {
    item.value.tag_ids = item.value.tag_ids.filter(itemTag => itemTag !== tagId);
    emit('change', item.value);
};

const emit = defineEmits([
    'change',
    'copy-item',
    'remove-item',
]);

const change = () => {
    emit('change', item.value);
};

const copyItem = () => {
    emit('copy-item', item.value);
};
</script>

<template>
    <li class="col-span-1 flex flex-col divide-y divide-gray-200 dark:divide-gray-700 rounded-lg bg-white dark:bg-gray-800 shadow">
        <div class="px-4 py-5 sm:p-6 flex-1">
            <h3 class="truncate text-lg font-bold text-gray-900 dark:text-gray-100">
                {{ item.quantity }}
                {{ item.name }}
            </h3>
            <div class="mt-6 space-y-2">
                <TagBox
                    class="w-full"
                    :items="tags.material"
                    v-model="selectedMaterialTag"
                    :nullable="true"
                    placeholder="Material"
                    @change="addTagsToItem"
                ></TagBox>

                <TagBox
                    class="w-full"
                    :items="tags.brand"
                    v-model="selectedBrandTag"
                    :nullable="true"
                    placeholder="Brand"
                    @change="addTagsToItem"
                ></TagBox>

                <TagBox
                    class="w-full"
                    :items="tags.content"
                    v-model="selectedContentTag"
                    :nullable="true"
                    placeholder="Content"
                    @change="addTagsToItem"
                ></TagBox>

                <TagBox
                    class="w-full"
                    :items="tags.size"
                    v-model="selectedSizeTag"
                    :nullable="true"
                    placeholder="Size"
                    @change="addTagsToItem"
                ></TagBox>

                <TagBox
                    class="w-full"
                    :items="tags.state"
                    v-model="selectedStateTag"
                    :nullable="true"
                    placeholder="State"
                    @change="addTagsToItem"
                ></TagBox>

                <TagBox
                    class="w-full"
                    :items="tags.event"
                    v-model="selectedEventTag"
                    :nullable="true"
                    placeholder="Event"
                    @change="addTagsToItem"
                ></TagBox>
            </div>

            <div class="mt-4 text-sm text-gray-500 flex flex-wrap gap-1">
                    <span
                        v-for="tag in tagNames"
                        :key="tag.id"
                        @click="removeTagFromItem(tag.id)"
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
        <div class="px-4 py-5 sm:p-6 flex flex-row justify-between">
            <div class="flex flex-col justify-center space-y-3">
                <div class="flex flex-row items-center">
                    <TextInput
                        id="quantity"
                        type="number"
                        v-model="item.quantity"
                        class="w-12 mr-2"
                        required
                        min="1"
                        max="1000"
                        @input="change"
                    />
                    <label for="quantity" class="block font-medium text-sm text-gray-900 dark:text-gray-100">
                        Quantity
                    </label>
                </div>

                <ToggleInput
                    v-model="item.picked_up"
                    @update:modelValue="change"
                    class="block w-full"
                >
                    <template #label>Picked Up</template>
                </ToggleInput>
                <ToggleInput
                    v-model="item.recycled"
                    @update:modelValue="change"
                    class="block w-full"
                >
                    <template #label>Recycled</template>
                </ToggleInput>
                <ToggleInput
                    v-model="item.deposit"
                    @update:modelValue="change"
                    class="block w-full"
                >
                    <template #label>Deposit</template>
                </ToggleInput>
            </div>

            <div class="flex flex-row flex-1 items-end justify-end gap-3">
                <IconPrimaryButton
                    @click="copyItem"
                >
                    <i class="far fa-fw fa-copy text-xs"></i>
                </IconPrimaryButton>
                <IconDangerButton
                    @click="$emit('remove-item', item.key)"
                >
                    <i class="fas fa-fw fa-trash-alt text-xs"></i>
                </IconDangerButton>
            </div>
        </div>
    </li>
</template>

<style scoped>

</style>
