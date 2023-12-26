<script setup>
import { ref } from "vue";

const props = defineProps({
    item: Object,
    tags: Object,
});

const selectedMaterialTag = ref(props.tags.material[0].id);
const selectedBrandTag = ref(props.tags.brand[0].id);
</script>

<template>
    <li class="col-span-1 flex flex-col divide-y divide-gray-200 dark:divide-gray-700 rounded-lg bg-white dark:bg-gray-800 shadow">
        <div class="px-4 py-5 sm:p-6 flex-1">
            <div class="flex items-center justify-between space-x-3">
                <h3 class="truncate text-lg font-bold text-gray-900 dark:text-gray-100">
                    {{ item.name }}
                </h3>
                <button
                    @click="$emit('remove-item', item.pivot.id)"
                    type="button"
                    class="rounded-md bg-indigo-600 px-2.5 py-1 text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                >
                    <i class="fas fa-fw fa-trash-alt text-xs"></i>
                </button>
            </div>
            <div class="mt-6">
                <div class="flex flex-row justify-between">
                    <select
                        id="add-material-tag"
                        v-model="selectedMaterialTag"
                        name="add-tag"
                        class="block w-full sm:w-48 rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        <option
                            v-for="material in tags.material"
                            :value="material.id"
                        >{{ material.name }}
                        </option>
                    </select>

                    <button
                        type="button"
                        class="ml-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none"
                        @click="$emit('add-tag-to-item', item.pivot, selectedMaterialTag)"
                        :disabled="!selectedMaterialTag"
                    >
                        Add Material
                    </button>
                </div>

                <div class="mt-2 flex flex-row justify-between">
                    <select
                        id="add-brand-tag"
                        v-model="selectedBrandTag"
                        name="add-tag"
                        class="block w-full sm:w-48 rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        <option
                            v-for="brand in tags.brand"
                            :value="brand.id"
                        >{{ brand.name }}
                        </option>
                    </select>

                    <button
                        type="button"
                        class="ml-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none"
                        @click="$emit('add-tag-to-item', item.pivot, selectedBrandTag)"
                        :disabled="!selectedBrandTag"
                    >
                        Add Brand
                    </button>
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
        <div class="px-4 py-5 sm:p-6">

            <div class="flex items-center">
                <button
                    type="button"
                    :value="item.pivot.picked_up"
                    @click="$emit('toggle-picked-up', item.pivot.id); item.pivot.picked_up = !item.pivot.picked_up"
                    :class="[item.pivot.picked_up ? 'bg-indigo-600' : 'bg-gray-200', 'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2']"
                    role="switch"
                    :aria-checked="item.pivot.picked_up"
                    aria-labelledby="picked-up-label"
                >
                    <span aria-hidden="true" :class="[item.pivot.picked_up ? 'translate-x-5' : 'translate-x-0', 'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out']" />
                </button>
                <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-100" id="picked-up-label">
                    Picked Up
                </span>
            </div>

        </div>
    </li>
</template>

<style scoped>

</style>