<script setup>
import { ref } from "vue";
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

const selectedMaterialTag = ref(props.tags.material[0]);
const selectedBrandTag = ref(props.tags.brand[0]);
const selectedEventTag = ref(props.tags.event[0]);
const selectedStateTag = ref(props.tags.state[0]);
const selectedContentTag = ref(props.tags.content[0]);
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
                    <TagBox
                        class="w-full lg:w-48"
                        :items="tags.material"
                        v-model="selectedMaterialTag"
                    ></TagBox>

                    <PrimaryButton
                        class="whitespace-nowrap"
                        @click="$emit('add-tag-to-item', item.pivot, selectedMaterialTag.id)"
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
                        @click="$emit('add-tag-to-item', item.pivot, selectedBrandTag.id)"
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
                        @click="$emit('add-tag-to-item', item.pivot, selectedEventTag.id)"
                        :disabled="!selectedEventTag"
                    >
                        Add Event
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
                        @click="$emit('add-tag-to-item', item.pivot, selectedStateTag.id)"
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
                        @click="$emit('add-tag-to-item', item.pivot, selectedContentTag.id)"
                        :disabled="!selectedContentTag"
                    >
                        Add Content
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
                <ToggleInput
                    v-model="item.pivot.recycled"
                    @update:modelValue="$emit('toggle-recycled', item.pivot.id, item.pivot.recycled)"
                    class="block w-full"
                >
                    <template #label>Recycled</template>
                </ToggleInput>
                <ToggleInput
                    v-model="item.pivot.deposit"
                    @update:modelValue="$emit('toggle-deposit', item.pivot.id, item.pivot.deposit)"
                    class="block w-full"
                >
                    <template #label>Deposit</template>
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
