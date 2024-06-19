<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {computed, ref} from "vue";
import ConfirmDeleteButton from "@/Components/ConfirmDeleteButton.vue";
import TextInput from "@/Components/TextInput.vue";

const props = defineProps({
    groups: Array,
});

const search = ref('');
const filteredGroups = computed(() => {
    if (! search.value) {
        return props.groups;
    }
    return props.groups.filter(group => {
        return group.name
            .toLowerCase().replace(/\s+/g, '')
            .includes(search.value.toLowerCase().replace(/\s+/g, ''));
    });
});

const deleteGroup = (groupId) => {
    console.log('Delete group with id:', groupId);
};

</script>

<template>
    <AppLayout title="Groups">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Groups
            </h2>
        </template>

        <div class="max-w-9xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h1 class="text-base font-semibold leading-6 text-gray-900 dark:text-gray-100">Groups</h1>
                    <p class="mt-2 text-sm text-gray-700 dark:text-gray-200">
                        A list of all the groups in your account.
                        Consider them as folders/directories to organize your photos however you like.
                    </p>
                </div>
            </div>
            <div class="mt-6 flex justify-between">
                <TextInput
                    class="min-w-40"
                    v-model="search"
                    placeholder="Search names"
                />
                <PrimaryButton class="whitespace-nowrap ml-4">Add group</PrimaryButton>
            </div>
            <div v-if="filteredGroups.length" class="mt-4 flow-root">
                <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-300 dark:divide-gray-500">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100 sm:pl-6">Name</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Photos</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Actions</th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-600 bg-white dark:bg-gray-700">
                                <tr
                                    v-for="group in filteredGroups"
                                    :key="group.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-600"
                                >
                                    <td class="whitespace-nowrap cursor-pointer py-4 pl-4 pr-3 text-sm font-medium text-gray-700 dark:text-gray-200 sm:pl-6">
                                        {{ group.name }}
                                    </td>
                                    <td class="cursor-pointer px-3 py-4 w-full min-w-[24rem]">
                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                                            Photos go here
                                        </div>
                                    </td>
                                    <td class="px-3 py-4 flex flex-row gap-2">
                                        <ConfirmDeleteButton @delete="deleteGroup(group.id)" />
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </AppLayout>
</template>

<style scoped>

</style>
