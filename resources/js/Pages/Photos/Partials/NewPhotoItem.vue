<script setup>
import {computed, ref} from "vue";
import IconPrimaryButton from "@/Components/IconPrimaryButton.vue";
import ToggleInput from "@/Components/ToggleInput.vue";
import IconDangerButton from "@/Components/IconDangerButton.vue";
import TextInput from "@/Components/TextInput.vue";
import ItemTagFields from "@/Pages/Photos/Partials/ItemTagFields.vue";

const props = defineProps({
    propItem: Object,
    tags: Object,
});

const item = ref(props.propItem);

const itemTags = computed(() => {
    return item.value.tag_ids.map(function (tagId) {
        for (const tags of Object.values(props.tags)) {
            const tag = tags.find(tag => tag.id === tagId);
            if (tag) {
                return tag;
            }
        }
    });
});

const addTagsToItem = (tagIds) => {
    item.value.tag_ids = [...new Set([...item.value.tag_ids, ...tagIds])];

    emit('change', item.value);
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
            <ItemTagFields
                class="mt-6"
                :tags="tags"
                :item-tags="itemTags"
                @add-tags="addTagsToItem"
                @remove-tag="removeTagFromItem"
            />
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
