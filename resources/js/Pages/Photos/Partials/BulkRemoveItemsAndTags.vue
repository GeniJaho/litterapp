<script setup>

import SecondaryButton from "@/Components/SecondaryButton.vue";
import {useForm, usePage} from "@inertiajs/vue3";
import {computed, onMounted, onUnmounted, ref} from "vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TagBox from "@/Components/TagBox.vue";
import NewPhotoItem from "@/Pages/Photos/Partials/NewPhotoItem.vue";
import BulkTagModal from "@/Pages/Photos/Partials/BulkTagModal.vue";
import Tooltip from "@/Components/Tooltip.vue";
import TagShortcutBox from "@/Components/TagShortcutBox.vue";
import TagSelector from "@/Pages/Photos/Partials/TagSelector.vue";

const props = defineProps({
    photoIds: Array,
    items: Array,
    tags: Object,
});

const emit = defineEmits(['closeModalWithSuccess'])

const allTags = computed(() => {
    return [
        ...props.tags.material,
        ...props.tags.brand,
        ...props.tags.event,
        ...props.tags.state,
        ...props.tags.content,
        ...props.tags.size,
    ];
});

const selectedItem = ref(null);
const showModal = ref(false);
const form = useForm({
    item_ids: [],
    tag_ids: [],
});
const message = ref('');

onMounted(() => {
    window.addEventListener('keydown', onKeyDown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', onKeyDown);
});

const saveDisabled = computed(() => form.processing || !form.item_ids.length && !form.tag_ids.length);

const addItem = () => {
    if (! form.item_ids.includes(selectedItem.value.id)) {
        form.item_ids.unshift(selectedItem.value.id);
    }

    selectedItem.value = null;
};

const removeItem = (key) => {
    form.item_ids = form.item_ids.filter(itemId => itemId !== key);
};

const addTag = (tag) => {
    if (! form.tag_ids.includes(tag.id)) {
        form.tag_ids.unshift(tag.id);
    }
};

const removeTag = (key) => {
    form.tag_ids = form.tag_ids.filter(tagId => tagId !== key);
};

const save = () => {
    form
        .transform((data) => ({
            ...data,
            photo_ids: props.photoIds,
        }))
        .delete(route('bulk-photo-items.destroy'), {
            preserveScroll: true,
            onSuccess: () => closeModalWithSuccess(),
        });
};

const closeModalWithSuccess = () => {
    form.reset();
    message.value = 'Removed successfully!';

    setTimeout(() => {
        showModal.value = false;

        emit('closeModalWithSuccess');
    }, 3000);
};

const closeModal = () => {
    showModal.value = false;

    form.reset();
};

const openModal = () => {
    message.value = '';
    showModal.value = true;
};

const onKeyDown = (event) => {
    if ((event.ctrlKey || event.metaKey) && (event.code === "Enter" || event.code === "ArrowRight") && ! saveDisabled.value) {
        event.preventDefault();
        save();
    }
};
</script>

<template>
    <PrimaryButton @click="openModal">
        Remove Items & Tags
    </PrimaryButton>

    <BulkTagModal max-width="3xl" :show="showModal" @close="closeModal">
        <template #header>
            <div class="px-6 py-4 text-lg font-medium text-gray-900 dark:text-gray-100">
                Remove Items & Tags of Multiple Photos ({{ photoIds.length }} selected)
            </div>
            <div class="px-6 text-sm text-gray-700 dark:text-gray-200">
                Remove items and tags from multiple photos at once.<br>
                When you remove an Item, the tags that belong to it will also be removed.<br>
                When you remove a Tag, it will be removed from all the Items it belongs to.<br><br>
                After you have selected all the items and tags for removal,
                click the "Save" button and confirm the dialog that appears.
            </div>
        </template>

        <template #content>
            <div class="mt-4 w-full h-full min-h-96 sm:px-4 flex flex-col md:flex-row">
                <div class="w-full md:w-1/2">
                    <div class="flex md:mt-0">
                        <TagBox
                            class="w-full md:w-96"
                            :items="items"
                            v-model="selectedItem"
                            :autofocus="true"
                            placeholder="Items"
                        ></TagBox>
                        <div class="ml-4 mt-0.5">
                            <PrimaryButton
                                class="whitespace-nowrap"
                                @click="addItem"
                                :disabled="!selectedItem"
                            >
                                Remove
                            </PrimaryButton>
                        </div>
                    </div>
                    <TagSelector
                        :tags="tags"
                        @tag-selected="addTag"
                    ></TagSelector>
                </div>

                <div class="w-full md:w-1/2">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
                        To Remove
                    </h3>

                    <div class="mt-4" v-if="form.item_ids.length">
                        <h3 class="text-md leading-5 font-medium text-gray-900 dark:text-gray-100">
                            Items
                        </h3>
                        <div class="mt-2">
                            <TransitionGroup tag="div" name="items" role="list" class="flex flex-col gap-3">
                                <SecondaryButton
                                    v-for="itemId in form.item_ids"
                                    :key="itemId"
                                    @click="removeItem(itemId)"
                                    class="whitespace-nowrap max-w-min hover:cursor-cross"
                                >
                                    {{ items.find(item => item.id === itemId)?.name }}
                                </SecondaryButton>
                            </TransitionGroup>
                        </div>
                    </div>

                    <div class="mt-4" v-if="form.tag_ids.length">
                        <h3 class="text-md leading-5 font-medium text-gray-900 dark:text-gray-100">
                            Tags
                        </h3>
                        <div class="mt-2">
                            <TransitionGroup tag="div" name="items" role="list" class="flex flex-col gap-3">
                                <SecondaryButton
                                    v-for="tagId in form.tag_ids"
                                    :key="tagId"
                                    @click="removeTag(tagId)"
                                    class="whitespace-nowrap max-w-min"
                                >
                                    {{ allTags.find(tag => tag.id === tagId)?.name }}
                                </SecondaryButton>
                            </TransitionGroup>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="form.hasErrors" class="flex flex-col items-end mt-8 text-sm text-red-500 dark:text-red-430">
                <p v-for="error in form.errors">{{ error }}</p>
            </div>

        </template>

        <template #footer>
            <div v-if="message" class="flex items-center px-3 text-sm text-gray-700 dark:text-gray-200">
                {{ message }}
            </div>
            <SecondaryButton @click="closeModal">
                Cancel
            </SecondaryButton>

            <PrimaryButton
                class="ml-3 group relative"
                :class="{ 'opacity-25': form.processing }"
                :disabled="saveDisabled"
                @click="save"
            >
                <Tooltip>
                    <div>
                        <div class="whitespace-nowrap dark:text-white">Ctrl (⌘) + Enter</div>
                        <div class="whitespace-nowrap dark:text-white">Ctrl (⌘) + &rarr;</div>
                    </div>
                </Tooltip>
                Save
            </PrimaryButton>
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
