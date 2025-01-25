<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {computed, provide, readonly, ref} from "vue";
import TagShortcutModal from "@/Pages/TagShortcuts/Partials/TagShortcutModal.vue";
import {tagShortcutState} from "@/Pages/TagShortcuts/stores/TagShortcutStore";
import SimpleTagShortcutItem from "@/Pages/TagShortcuts/Partials/SimpleTagShortcutItem.vue";
import ConfirmDeleteButton from "@/Components/ConfirmDeleteButton.vue";
import {router} from "@inertiajs/vue3";
import TextInput from "@/Components/TextInput.vue";
import IconPrimaryButton from "@/Components/IconPrimaryButton.vue";

const props = defineProps({
    tagShortcuts: Array,
    items: Array,
    tags: Object,
});

const tagShortcutModal = ref(null);
const showModal = ref(false);
const search = ref('');
const filteredTagShortcuts = computed(() => {
    if (! search.value) {
        return props.tagShortcuts;
    }
    return props.tagShortcuts.filter(tagShortcut => {
        return tagShortcut.shortcut
            .toLowerCase().replace(/\s+/g, '')
            .includes(search.value.toLowerCase().replace(/\s+/g, ''));
    });
});

const openModal = (tagShortcut = null) => {
    tagShortcutState.value.setTagShortcut(tagShortcut);
    tagShortcutState.value.setTagShortcutName(tagShortcut);
    showModal.value = true;
    tagShortcutModal.value.autofocusName();
};

const closeModal = () => {
    tagShortcutState.value.reset();
    router.reload();
    showModal.value = false;
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

        <div class="max-w-9xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h1 class="text-base font-semibold leading-6 text-gray-900 dark:text-gray-100">Tag Shortcuts</h1>
                    <p class="mt-2 text-sm text-gray-700 dark:text-gray-200">
                        A list of all the tag shortcuts in your account.
                        Shortcuts with no items will not appear in your photo tagging pages.
                    </p>
                </div>
            </div>
            <div class="mt-6 flex justify-between">
                <TextInput
                    class="min-w-40"
                    v-model="search"
                    placeholder="Search shortcuts"
                />
                <PrimaryButton class="whitespace-nowrap ml-4" @click="openModal(null)">Add shortcut</PrimaryButton>
            </div>
            <div v-if="filteredTagShortcuts.length" class="mt-4 flow-root">
                <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                        <div class="overflow-hidden shadow-sm ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-300 dark:divide-gray-500">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100 sm:pl-6">Shortcut</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Items & Tags</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Actions</th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-600 bg-white dark:bg-gray-700">
                                <tr
                                    v-for="tagShortcut in filteredTagShortcuts"
                                    :key="tagShortcut.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-600"
                                >
                                    <td @click="openModal(tagShortcut)"
                                        class="whitespace-nowrap cursor-pointer py-4 pl-4 pr-3 text-sm font-medium text-gray-700 dark:text-gray-200 sm:pl-6"
                                    >
                                        {{ tagShortcut.shortcut }}
                                    </td>
                                    <td @click="openModal(tagShortcut)"
                                        class="cursor-pointer px-3 py-4 w-full min-w-[24rem]"
                                    >
                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                                            <SimpleTagShortcutItem
                                                v-for="item in tagShortcut.tag_shortcut_items"
                                                :key="item.id"
                                                :item="item"
                                            />
                                        </div>
                                    </td>
                                    <td class="px-3 py-4 flex flex-row gap-2">
                                        <IconPrimaryButton @click="tagShortcutState.copy(tagShortcut.id)">
                                            <i class="far fa-fw fa-copy text-xs"></i>
                                        </IconPrimaryButton>
                                        <ConfirmDeleteButton @delete="tagShortcutState.delete(tagShortcut.id)" />
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <TagShortcutModal
                ref="tagShortcutModal"
                :show="showModal"
                @close="closeModal"
            ></TagShortcutModal>

        </div>

    </AppLayout>
</template>

<style scoped>

</style>
