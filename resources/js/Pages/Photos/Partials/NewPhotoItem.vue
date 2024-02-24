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

const selectedMaterialTag = ref(props.tags.material[0]);
const selectedBrandTag = ref(props.tags.brand[0]);
const selectedEventTag = ref(props.tags.event[0]);
const selectedStateTag = ref(props.tags.state[0]);
const selectedContentTag = ref(props.tags.content[0]);
const selectedSizeTag = ref(props.tags.size[0]);

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

const addTagToItem = (tag) => {
    if (item.value.tag_ids.find(itemTag => itemTag === tag.id)) {
        return;
    }

    item.value.tag_ids.push(tag.id);
    emit('change', item.value);
};

const removeTagFromItem = (tagId) => {
    item.value.tag_ids = item.value.tag_ids.filter(itemTag => itemTag !== tagId);
    emit('change', item.value);
};

const emit = defineEmits([
    'change',
    'remove-item',
    'copy-item',
]);

const change = () => {
    emit('change', item.value);
};

const copyItem = () => {
    emit('copy-item', item.value);
};
</script>

<template>
    <li class="ring ring-inset ring-red-500 col-span-1 flex flex-col divide-y divide-gray-200 dark:divide-gray-700 rounded-lg bg-white dark:bg-gray-800 shadow">
        <div class="px-4 py-5 sm:p-6 flex-1">
            <div class="flex items-center justify-between space-x-3">
                <h3 class="truncate text-lg font-bold text-gray-900 dark:text-gray-100">
                    {{ item.quantity }}
                    {{ item.name }}
                </h3>
                <IconDangerButton
                    @click="$emit('remove-item', item.key)"
                >
                    <i class="fas fa-fw fa-trash-alt text-xs"></i>
                </IconDangerButton>
            </div>
            <div class="mt-6">
                <div class="flex flex-row justify-between space-x-2">
                    <TagBox
                        class="w-full lg:w-48"
                        :items="tags.material"
                        v-model="selectedMaterialTag"
                    ></TagBox>

                    <PrimaryButton
                        class="whitespace-nowrap"
                        @click="addTagToItem(selectedMaterialTag)"
                        :disabled="!selectedMaterialTag"
                    >
                        Add Material
                    </PrimaryButton>
                </div>

                <div class="mt-2 flex flex-row justify-between space-x-2">
                    <TagBox
                        class="w-full lg:w-48"
                        :items="tags.brand"
                        v-model="selectedBrandTag"
                    ></TagBox>

                    <PrimaryButton
                        class="whitespace-nowrap"
                        @click="addTagToItem(selectedBrandTag)"
                        :disabled="!selectedBrandTag"
                    >
                        Add Brand
                    </PrimaryButton>
                </div>

                <div class="mt-2 flex flex-row justify-between space-x-2">
                    <TagBox
                        class="w-full lg:w-48"
                        :items="tags.event"
                        v-model="selectedEventTag"
                    ></TagBox>

                    <PrimaryButton
                        class="whitespace-nowrap"
                        @click="addTagToItem(selectedEventTag)"
                        :disabled="!selectedEventTag"
                    >
                        Add Event
                    </PrimaryButton>
                </div>

                <div class="mt-2 flex flex-row justify-between space-x-2">
                    <TagBox
                        class="w-full lg:w-48"
                        :items="tags.size"
                        v-model="selectedSizeTag"
                    ></TagBox>

                    <PrimaryButton
                        class="whitespace-nowrap"
                        @click="addTagToItem(selectedSizeTag)"
                        :disabled="!selectedSizeTag"
                    >
                        Add Size
                    </PrimaryButton>
                </div>

                <div class="mt-2 flex flex-row justify-between space-x-2">
                    <TagBox
                        class="w-full lg:w-48"
                        :items="tags.state"
                        v-model="selectedStateTag"
                    ></TagBox>

                    <PrimaryButton
                        class="whitespace-nowrap"
                        @click="addTagToItem(selectedStateTag)"
                        :disabled="!selectedStateTag"
                    >
                        Add State
                    </PrimaryButton>
                </div>

                <div class="mt-2 flex flex-row justify-between space-x-2">
                    <TagBox
                        class="w-full lg:w-48"
                        :items="tags.content"
                        v-model="selectedContentTag"
                    ></TagBox>

                    <PrimaryButton
                        class="whitespace-nowrap"
                        @click="addTagToItem(selectedContentTag)"
                        :disabled="!selectedContentTag"
                    >
                        Add Content
                    </PrimaryButton>
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

            <div class="flex flex-col justify-end">
                <IconPrimaryButton
                    @click="copyItem"
                >
                    <i class="far fa-fw fa-copy text-xs"></i>
                </IconPrimaryButton>
            </div>
        </div>
    </li>
</template>

<style scoped>

</style>
