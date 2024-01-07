<script setup>
import { ref } from "vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import IconPrimaryButton from "@/Components/IconPrimaryButton.vue";
import ToggleInput from "@/Components/ToggleInput.vue";
import IconDangerButton from "@/Components/IconDangerButton.vue";
import TextInput from "@/Components/TextInput.vue";

const props = defineProps({
    item: Object,
    tags: Object,
});

const selectedMaterialTag = ref(props.tags.material[0].id);
const selectedBrandTag = ref(props.tags.brand[0].id);
const selectedEventTag = ref(props.tags.event[0].id);
</script>

<template>
    <li class="col-span-1 flex flex-col divide-y divide-gray-200 dark:divide-gray-700 rounded-lg bg-white dark:bg-gray-800 shadow">
        <div class="px-4 py-5 sm:p-6 flex-1">
            <div class="flex items-center justify-between space-x-3">
                <h3 class="truncate text-lg font-bold text-gray-900 dark:text-gray-100">
                    {{ item.pivot.quantity }}
                    {{ item.name }}
                </h3>
                <IconDangerButton
                    @click="$emit('remove-item', item.pivot.id)"
                >
                    <i class="fas fa-fw fa-trash-alt text-xs"></i>
                </IconDangerButton>
            </div>
            <div class="mt-6">
                <div class="flex flex-row justify-between space-x-2">
                    <select
                        id="add-material-tag"
                        v-model="selectedMaterialTag"
                        name="add-tag"
                        class="block w-full lg:w-48 rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        <option
                            v-for="material in tags.material"
                            :value="material.id"
                        >{{ material.name }}
                        </option>
                    </select>

                    <PrimaryButton
                        class="whitespace-nowrap"
                        @click="$emit('add-tag-to-item', item.pivot, selectedMaterialTag)"
                        :disabled="!selectedMaterialTag"
                    >
                        Add Material
                    </PrimaryButton>
                </div>

                <div class="mt-2 flex flex-row justify-between space-x-2">
                    <select
                        id="add-brand-tag"
                        v-model="selectedBrandTag"
                        name="add-tag"
                        class="block w-full lg:w-48 rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        <option
                            v-for="brand in tags.brand"
                            :value="brand.id"
                        >{{ brand.name }}
                        </option>
                    </select>

                    <PrimaryButton
                        class="whitespace-nowrap"
                        @click="$emit('add-tag-to-item', item.pivot, selectedBrandTag)"
                        :disabled="!selectedBrandTag"
                    >
                        Add Brand
                    </PrimaryButton>
                </div>

                <div class="mt-2 flex flex-row justify-between space-x-2">
                    <select
                        id="add-event-tag"
                        v-model="selectedEventTag"
                        name="add-tag"
                        class="block w-full lg:w-48 rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        <option
                            v-for="event in tags.event"
                            :value="event.id"
                        >{{ event.name }}
                        </option>
                    </select>

                    <PrimaryButton
                        class="whitespace-nowrap"
                        @click="$emit('add-tag-to-item', item.pivot, selectedEventTag)"
                        :disabled="!selectedEventTag"
                    >
                        Add Event
                    </PrimaryButton>
                </div>

                <div class="mt-4 text-sm text-gray-500 flex flex-wrap space-x-1">
                    <span
                        v-for="tag in item.pivot.tags"
                        :key="tag.id"
                        @click="$emit('remove-tag-from-item', item.pivot, tag.id)"
                        class="inline-flex cursor-pointer items-center gap-x-1.5 rounded-full px-2 py-1 mb-2 mr-2 text-xs font-medium text-gray-900 dark:text-gray-100 ring-1 ring-inset ring-gray-200"
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
                        :model-value="item.pivot.quantity"
                        class="w-12 mr-2"
                        required
                        min="1"
                        max="1000"
                        @input="$emit('update-quantity', item.pivot.id, $event.target.value)"
                    />
                    <label for="quantity" class="block font-medium text-sm text-gray-900 dark:text-gray-100">
                        Quantity
                    </label>
                </div>

                <ToggleInput
                    v-model="item.pivot.picked_up"
                    @update:modelValue="$emit('toggle-picked-up', item.pivot.id, item.pivot.picked_up)"
                    class="block w-full"
                >
                    <template #label>Picked Up</template>
                </ToggleInput>
            </div>

            <div class="flex flex-col justify-end">
                <IconPrimaryButton
                    @click="$emit('copy-item', item.pivot.id)"
                >
                    <i class="far fa-fw fa-copy text-xs"></i>
                </IconPrimaryButton>
            </div>
        </div>
    </li>
</template>

<style scoped>

</style>
