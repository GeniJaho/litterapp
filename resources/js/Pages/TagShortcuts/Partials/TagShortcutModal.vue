<script setup>

import SecondaryButton from "@/Components/SecondaryButton.vue";
import BulkTagModal from "@/Pages/Photos/Partials/BulkTagModal.vue";
import {useForm} from "@inertiajs/vue3";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import ActionMessage from "@/Components/ActionMessage.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {ref} from "vue";

const emit = defineEmits(['close']);

const props = defineProps({
    existingTagShortcut: {
        type: Object,
        default: null,
    },
    show: {
        type: Boolean,
        default: false,
    },
});

const tagShortcut = ref(props.existingTagShortcut);
const shortcutName = ref(props.existingTagShortcut?.shortcut || '');
const processing = ref(false);
const error = ref('');
const message = ref('');

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
                {{ existingTagShortcut ? 'Edit' : 'Add new' }} shortcut
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
                        <div>
                            <TextInput
                                id="shortcut"
                                v-model="shortcutName"
                                type="text"
                                class="block w-full sm:max-w-sm"
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