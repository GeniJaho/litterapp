<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {provide, readonly, ref} from "vue";
import TagShortcutModal from "@/Pages/TagShortcuts/Partials/TagShortcutModal.vue";
import {tagShortcutState} from "@/Pages/TagShortcuts/stores/TagShortcutStore";
import TagShortcutItem from "@/Pages/TagShortcuts/Partials/TagShortcutItem.vue";
import SimpleTagShortcutItem from "@/Pages/TagShortcuts/Partials/SimpleTagShortcutItem.vue";
import ConfirmDeleteButton from "@/Components/ConfirmDeleteButton.vue";

const props = defineProps({
    tagShortcuts: Array,
    items: Array,
    tags: Object,
});

const showModal = ref(false);

const openModal = (tagShortcut = null) => {
    tagShortcutState.value.setTagShortcut(tagShortcut);
    showModal.value = true;
};

const closeModal = () => {
    tagShortcutState.value.reset();
    showModal.value = false;
};

const deleteTagShortcut = (tagShortcutId) => {
    tagShortcutState.value.delete(tagShortcutId);
};

provide('items', readonly(props.items));
provide('tags', readonly(props.tags));

</script>

<template>
    <AppLayout title="Tag Shortcuts">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Tag Shortcuts
            </h2>
        </template>

        <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h1 class="text-base font-semibold leading-6 text-gray-900">Tag Shortcuts</h1>
                    <p class="mt-2 text-sm text-gray-700">A list of all the tag shortcuts in your account.</p>
                </div>
                <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                    <PrimaryButton @click="openModal(null)">Add shortcut</PrimaryButton>
                </div>
            </div>
            <div class="mt-8 flow-root">
                <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Shortcut</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Items & Tags</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Actions</th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                <tr
                                    v-for="tagShortcut in tagShortcuts"
                                    :key="tagShortcut.id"
                                    class="hover:bg-gray-50"
                                >
                                    <td @click="openModal(tagShortcut)"
                                        class="whitespace-nowrap cursor-pointer py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6"
                                    >
                                        {{ tagShortcut.shortcut }}
                                    </td>
                                    <td @click="openModal(tagShortcut)"
                                        class="whitespace-nowrap cursor-pointer px-3 py-4 w-full min-w-[24rem]"
                                    >
                                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                                            <SimpleTagShortcutItem
                                                v-for="item in tagShortcut.tag_shortcut_items"
                                                :key="item.id"
                                                :item="item"
                                                :tags="tags"
                                            />
                                        </div>
                                    </td>
                                    <td class="px-3 py-4">
                                        <ConfirmDeleteButton @delete="deleteTagShortcut(tagShortcut.id)" />
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <TagShortcutModal
                :show="showModal"
                @close="closeModal"
            ></TagShortcutModal>

        </div>

    </AppLayout>
</template>

<style scoped>

</style>
