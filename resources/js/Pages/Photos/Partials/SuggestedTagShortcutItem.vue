<script setup>
const props = defineProps({
    suggestedItem: Object,
});

</script>

<template>
    <div class="w-full col-span-1 flex flex-col divide-y divide-gray-200 dark:divide-gray-700 rounded-lg bg-white dark:bg-gray-800 shadow">
        <div class="p-2 md:p-4 flex-1">
            <h3 class="truncate text-md font-bold text-gray-900 dark:text-gray-100">
                {{ suggestedItem.quantity }}
                {{ suggestedItem.item.name }}
            </h3>

            <div v-if="suggestedItem.tags?.length" class="mt-2">
                <div class="flex flex-wrap gap-1">
                    <span
                        v-for="tag in suggestedItem.tags"
                        :key="tag.id"
                        class="inline-flex items-center gap-x-1.5 rounded-full px-2 py-1 text-xs font-medium text-gray-900 dark:text-gray-100 ring-1 ring-inset ring-gray-200"
                    >
                        <svg class="h-1.5 w-1.5 fill-green-500" viewBox="0 0 6 6"
                             aria-hidden="true"><circle cx="3" cy="3" r="3"/></svg>
                        {{ tag.name }}
                    </span>
                </div>
            </div>

            <div v-if="suggestedItem.picked_up || suggestedItem.recycled || suggestedItem.deposit" class="mt-2 divide-x divide-gray-500">
                <span v-if="suggestedItem.picked_up" class="text-xs text-gray-900 dark:text-gray-100">
                    Picked Up
                </span>
                <span v-if="suggestedItem.recycled" class="pl-1 text-xs text-gray-900 dark:text-gray-100">
                    Recycled
                </span>
                <span v-if="suggestedItem.deposit" class="pl-1 text-xs text-gray-900 dark:text-gray-100">
                    Deposit
                </span>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
