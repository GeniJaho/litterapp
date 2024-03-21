<script setup>

import SecondaryButton from "@/Components/SecondaryButton.vue";
import BulkTagModal from "@/Pages/Photos/Partials/BulkTagModal.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import ActionMessage from "@/Components/ActionMessage.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {tagShortcutState} from "@/Pages/TagShortcuts/stores/TagShortcutStore";

const emit = defineEmits(['close']);

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
})

const close = () => {
    emit('close');
};
</script>

<template>
    <BulkTagModal max-width="7xl" :show="show" @close="close">
        <template #header>
            <div class="px-6 py-4 text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ tagShortcutState.tagShortcut ? 'Edit' : 'Add new' }} shortcut
            </div>
            <div class="px-6 text-sm text-gray-700 dark:text-gray-200">
                Add items and tags to the shortcut. You can also set the quantity, and mark it as picked up, recycled,
                or deposit.
            </div>
        </template>

        <template #content>
            <div>
                <form @submit.prevent="tagShortcutState.save">
                    <div class="flex flex-col sm:flex-row gap-4">
                        <div class="w-full sm:max-w-xs">
                            <TextInput
                                id="shortcut"
                                v-model="tagShortcutState.shortcutName"
                                type="text"
                                class="block w-full"
                                autocomplete="shortcut"
                                placeholder="Shortcut"
                            />
                            <InputError :message="tagShortcutState.error" class="mt-2" />
                        </div>

                        <div>
                            <div class="flex sm:flex-row-reverse items-center justify-end gap-4">
                                <ActionMessage :on="tagShortcutState.message.length > 0">
                                    {{ tagShortcutState.message }}
                                </ActionMessage>
                                <PrimaryButton :class="{ 'opacity-25': tagShortcutState.processing }" :disabled="tagShortcutState.processing">
                                    Save
                                </PrimaryButton>
                            </div>
                        </div>
                    </div>
                </form>

                <div>
                    <div v-for="tagShortcutItem in tagShortcutState.tagShortcut?.tag_shortcut_items" :key="tagShortcutItem.id">
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
                </div>
        </template>

        <template #footer>
            <SecondaryButton @click="close">
                Cancel
            </SecondaryButton>
        </template>
    </BulkTagModal>
</template>

<style scoped>

</style>