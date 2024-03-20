<script setup>

import SecondaryButton from "@/Components/SecondaryButton.vue";
import BulkTagModal from "@/Pages/Photos/Partials/BulkTagModal.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import ActionMessage from "@/Components/ActionMessage.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {onMounted, ref, watch} from "vue";

const emit = defineEmits(['close', 'changed']);

const props = defineProps({
    tagShortcutId: {
        type: Number,
        default: null,
    },
    show: {
        type: Boolean,
        default: false,
    },
});

const tagShortcut = ref(null);
const shortcutName = ref('');
const processing = ref(false);
const error = ref('');
const message = ref('');

watch(props, (value) => {
    if (value.show) {
        if (! props.tagShortcutId) {
            return;
        }

        axios.get(route('tag-shortcuts.show', props.tagShortcutId)).then((r) => {
            tagShortcut.value = r.data.tagShortcut;
            shortcutName.value = r.data.tagShortcut.shortcut;
        });
    } else {
        tagShortcut.value = null;
        shortcutName.value = '';
        processing.value = false;
        error.value = '';
        message.value = '';
    }
});

const save = () => {
    processing.value = true;
    axios.post(route('tag-shortcuts.store'), {
        shortcut: shortcutName.value,
    }).then((r) => {
        tagShortcut.value = r.data.tagShortcut;
        processing.value = false;
        error.value = '';
        message.value = 'Saved.';
        setTimeout(() => message.value = '', 3000);
        emit('changed');
    }).catch((e) => {
        processing.value = false;
        error.value = e.response.data.message;
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
                {{ tagShortcut ? 'Edit' : 'Add new' }} shortcut
            </div>
            <div class="px-6 text-sm text-gray-700 dark:text-gray-200">
                Add items and tags to the shortcut. You can also set the quantity, and mark it as picked up, recycled,
                or deposit.
            </div>
        </template>

        <template #content>
            <div>
                <form @submit.prevent="save">
                    <div class="flex flex-col sm:flex-row gap-4">
                        <div class="w-full sm:max-w-xs">
                            <TextInput
                                id="shortcut"
                                v-model="shortcutName"
                                type="text"
                                class="block w-full"
                                autocomplete="shortcut"
                                placeholder="Shortcut"
                            />
                            <InputError :message="error" class="mt-2" />
                        </div>

                        <div>
                            <div class="flex sm:flex-row-reverse items-center justify-end gap-4">
                                <ActionMessage :on="message.length > 0">
                                    {{ message }}
                                </ActionMessage>
                                <PrimaryButton :class="{ 'opacity-25': processing }" :disabled="processing">
                                    Save
                                </PrimaryButton>
                            </div>
                        </div>
                    </div>
                </form>

                <div>
                    <div v-for="tagShortcutItem in tagShortcut?.tag_shortcut_items" :key="tagShortcutItem.id">
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