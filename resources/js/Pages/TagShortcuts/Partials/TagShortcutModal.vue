<script setup>

import SecondaryButton from "@/Components/SecondaryButton.vue";
import BulkTagModal from "@/Pages/Photos/Partials/BulkTagModal.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import ActionMessage from "@/Components/ActionMessage.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {tagShortcutState} from "@/Pages/TagShortcuts/stores/TagShortcutStore";
import TagBox from "@/Components/TagBox.vue";
import {inject, ref} from "vue";
import {router} from "@inertiajs/vue3";
import TagShortcutItem from "@/Pages/TagShortcuts/Partials/TagShortcutItem.vue";
import PhotoItem from "@/Pages/Photos/Partials/PhotoItem.vue";

const emit = defineEmits(['close']);

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
})

const items = inject('items');
const tags = inject('tags');
const selectedItem = ref(null);

const addItem = () => {
    axios.post(route('tag-shortcut-items.store', tagShortcutState.value.tagShortcut.id), {
        item_id: selectedItem.value.id,
    }).then(() => {
        selectedItem.value = null;
        tagShortcutState.value.reloadTagShortcut();
    });
};

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
                        <div class="w-full sm:max-w-96">
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
                    <div class="flex flex-row mt-6">
                        <TagBox
                            :autofocus="true"
                            class="w-full sm:w-96"
                            :items="items"
                            v-model="selectedItem"
                            placeholder="Litter Objects"
                        ></TagBox>
                        <div class="ml-4">
                            <PrimaryButton
                                class="whitespace-nowrap"
                                @click="addItem"
                                :disabled="!selectedItem"
                            >
                                Add Object
                            </PrimaryButton>
                        </div>
                    </div>

                    <div class="mt-8" v-if="tagShortcutState.tagShortcut?.tag_shortcut_items.length">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
                            Litter Objects
                        </h3>
                        <div class="mt-2">
                            <TransitionGroup tag="ul" name="items" role="list" class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                                <TagShortcutItem
                                    v-for="item in tagShortcutState.tagShortcut?.tag_shortcut_items"
                                    :key="item.id"
                                    :item="item"
                                    :tags="tags"
                                    @remove-item="tagShortcutState.removeItem(item.id)"
                                />
                            </TransitionGroup>
                        </div>
                    </div>
                </div>

                </div>
        </template>

        <template #footer>
            <SecondaryButton @click="close">
                Close
            </SecondaryButton>
        </template>
    </BulkTagModal>
</template>

<style scoped>
.items-move, /* apply transition to moving elements */
.items-enter-active,
.items-leave-active {
    transition: all 0.5s ease;
}

.items-enter-from,
.items-leave-to {
    opacity: 0;
    transform: translateX(30px);
}

/* ensure leaving items are taken out of layout flow so that moving
   animations can be calculated correctly. */
.items-leave-active {
    position: absolute;
}
</style>
