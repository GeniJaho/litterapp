<script setup>
import IconPrimaryButton from "@/Components/IconPrimaryButton.vue";
import ToggleInput from "@/Components/ToggleInput.vue";
import IconDangerButton from "@/Components/IconDangerButton.vue";
import TextInput from "@/Components/TextInput.vue";
import ItemTagFields from "@/Pages/Photos/Partials/ItemTagFields.vue";

defineProps({
    pivotItem: Object,
    tags: Object,
});

defineEmits([
    'add-tags-to-item',
    'remove-item',
    'remove-tag-from-item',
    'update-quantity',
    'toggle-picked-up',
    'toggle-recycled',
    'toggle-deposit',
    'copy-item',
]);
</script>

<template>
    <li class="col-span-1 flex flex-col divide-y divide-gray-200 dark:divide-gray-700 rounded-lg bg-white dark:bg-gray-800 shadow">
        <div class="px-4 py-5 sm:p-6 flex-1">
            <h3 class="truncate text-lg font-bold text-gray-900 dark:text-gray-100">
                {{ pivotItem.quantity }}
                {{ pivotItem.item.name }}
            </h3>

            <ItemTagFields
                class="mt-6"
                :tags="tags"
                :item-tags="pivotItem.tags"
                @add-tags="$emit('add-tags-to-item', pivotItem, $event)"
                @remove-tag="$emit('remove-tag-from-item', pivotItem, $event)"
            />
        </div>
        <div class="px-4 py-5 sm:p-6 flex flex-row justify-between">
            <div class="flex flex-col justify-center space-y-3">
                <div class="flex flex-row items-center">
                    <TextInput
                        id="quantity"
                        type="number"
                        :model-value="pivotItem.quantity"
                        class="w-14 px-2 mr-2"
                        required
                        min="1"
                        max="1000"
                        @input="$emit('update-quantity', pivotItem.id, $event.target.value)"
                    />
                    <label for="quantity" class="block font-medium text-sm text-gray-900 dark:text-gray-100">
                        Quantity
                    </label>
                </div>

                <ToggleInput
                    v-model="pivotItem.picked_up"
                    @update:modelValue="$emit('toggle-picked-up', pivotItem.id, pivotItem.picked_up)"
                    class="block w-full"
                >
                    <template #label>Picked Up</template>
                </ToggleInput>
                <ToggleInput
                    v-model="pivotItem.recycled"
                    @update:modelValue="$emit('toggle-recycled', pivotItem.id, pivotItem.recycled)"
                    class="block w-full"
                >
                    <template #label>Recycled</template>
                </ToggleInput>
                <ToggleInput
                    v-model="pivotItem.deposit"
                    @update:modelValue="$emit('toggle-deposit', pivotItem.id, pivotItem.deposit)"
                    class="block w-full"
                >
                    <template #label>Deposit</template>
                </ToggleInput>
            </div>

            <div class="flex flex-row flex-1 items-end justify-end gap-3">
                <IconPrimaryButton
                    @click="$emit('copy-item', pivotItem.id)"
                >
                    <i class="far fa-fw fa-copy text-xs"></i>
                </IconPrimaryButton>
                <IconDangerButton
                    @click="$emit('remove-item', pivotItem.id)"
                >
                    <i class="fas fa-fw fa-trash-alt text-xs"></i>
                </IconDangerButton>
            </div>
        </div>
    </li>
</template>

<style scoped>

</style>
