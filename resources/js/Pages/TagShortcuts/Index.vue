<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {ref} from "vue";
import TagShortcutModal from "@/Pages/TagShortcuts/Partials/TagShortcutModal.vue";
import {router} from "@inertiajs/vue3";

const props = defineProps({
    tagShortcuts: Array,
});

const showModal = ref(false);
const activeTagShortcutId = ref(null);

const openModal = (tagShortcutId = null) => {
    activeTagShortcutId.value = tagShortcutId;
    showModal.value = true;
};

const closeModal = () => {
    activeTagShortcutId.value = null;
    showModal.value = false;
};

const reload = () => {
    router.reload();
};
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
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                <tr
                                    v-for="tagShortcut in tagShortcuts"
                                    :key="tagShortcut.id"
                                    class="hover:bg-gray-50 cursor-pointer"
                                    @click="openModal(tagShortcut.id)"
                                >
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                        {{ tagShortcut.shortcut }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4">
                                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                                            <div
                                                v-for="tagShortcutItem in tagShortcut.tag_shortcut_items"
                                                :key="tagShortcutItem.id"
                                            >
                                                Item: {{ tagShortcutItem.item.name }} <br>
                                                Picked Up: {{ tagShortcutItem.picked_up }} <br>
                                                Recycled: {{ tagShortcutItem.recycled }} <br>
                                                Deposit: {{ tagShortcutItem.deposit }} <br>
                                                Quantity: {{ tagShortcutItem.quantity }} <br>
                                                Tags:
                                                <div v-for="tag in tagShortcutItem.tags" :key="tag.id">
                                                    {{ tag.name }},
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <TagShortcutModal
                :tagShortcutId="activeTagShortcutId"
                :show="showModal"
                @close="closeModal"
                @changed="reload"
            ></TagShortcutModal>

        </div>

    </AppLayout>
</template>

<style scoped>

</style>