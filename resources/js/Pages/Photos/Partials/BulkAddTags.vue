<script setup>

import SecondaryButton from "@/Components/SecondaryButton.vue";
import {useForm, usePage} from "@inertiajs/vue3";
import {computed, onMounted, onUnmounted, ref} from "vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TagBox from "@/Components/TagBox.vue";
import BulkTagModal from "@/Pages/Photos/Partials/BulkTagModal.vue";
import Tooltip from "@/Components/Tooltip.vue";
import TagSelector from "@/Pages/Photos/Partials/TagSelector.vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";

const props = defineProps({
    photoIds: Array,
    tags: Object,
});

const page = usePage();

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

const showModal = ref(false);
const showConfirmationModal = ref(false);
const form = useForm({
    tag_ids: [],
});
const message = ref('');

onMounted(() => {
    window.addEventListener('keydown', onKeyDown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', onKeyDown);
});

const saveDisabled = computed(() => form.processing || !form.tag_ids.length);

const addTag = (tag) => {
    if (! form.tag_ids.includes(tag.id)) {
        form.tag_ids.unshift(tag.id);
    }
};

const removeTag = (key) => {
    form.tag_ids = form.tag_ids.filter(tagId => tagId !== key);
};

const save = () => {
    showConfirmationModal.value = false;

    form
        .transform((data) => ({
            ...data,
            photo_ids: props.photoIds,
        }))
        .post(route('bulk-photo-tags.add'), {
            preserveScroll: true,
            replace: true,
            onSuccess: () => {
                const result = page.props.flash?.bulkAddTagsResult;
                if (result) {
                    handleResult(result);
                } else {
                    closeModalWithSuccess();
                }
            },
        });
};

const handleResult = (result) => {
    const noItemsCount = result.photos_with_no_items?.length || 0;
    const multipleItemsCount = result.photos_with_multiple_items?.length || 0;
    const tagsAdded = result.tags_added;

    if (!tagsAdded && noItemsCount === 0 && multipleItemsCount === 0) {
        message.value = 'No tags were added.';
    } else if (!tagsAdded && multipleItemsCount > 0) {
        message.value = `Skipped ${multipleItemsCount} photo(s) with more than 1 item.`;
    } else if (!tagsAdded && noItemsCount > 0) {
        message.value = `Skipped ${noItemsCount} photo(s) without items.`;
    } else if (tagsAdded && (noItemsCount > 0 || multipleItemsCount > 0)) {
        let msg = 'Tags added successfully!';
        if (multipleItemsCount > 0) {
            msg += ` (${multipleItemsCount} photo(s) with more than 1 item skipped)`;
        }
        if (noItemsCount > 0) {
            msg += ` (${noItemsCount} photo(s) without items skipped)`;
        }
        message.value = msg;
    } else {
        message.value = 'Tags added successfully!';
    }

    form.reset();
};

const closeModalWithSuccess = () => {
    form.reset();
    message.value = 'Added successfully!';

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
        Add Tags
    </PrimaryButton>

    <BulkTagModal max-width="3xl" :show="showModal" @close="closeModal">
        <template #header>
            <div class="px-6 py-4 text-lg font-medium text-gray-900 dark:text-gray-100">
                Add Tags to Multiple Photos ({{ photoIds.length }} selected)
            </div>
            <div class="px-6 text-sm text-gray-700 dark:text-gray-200">
                Add tags to the item on the selected photos.<br>
                If a photo has more than 1 item, no tags will be added.<br>
                Tags that are already present on an item will not be added again.<br><br>
                After you have selected all the tags you want to add,
                click the "Save" button and confirm the dialog that appears.
            </div>
        </template>

        <template #content>
            <div class="mt-4 w-full h-full min-h-96 sm:px-4 flex flex-col md:flex-row">
                <div class="w-full md:w-1/2">
                    <TagSelector
                        :tags="tags"
                        buttonText="Add"
                        @tag-selected="addTag"
                    ></TagSelector>
                </div>

                <div class="w-full md:w-1/2 mt-8 md:mt-0">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
                        To Add
                    </h3>

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

                    <div v-else class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                        No tags selected yet. Select tags from the list on the left.
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
                @click="showConfirmationModal = true"
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

    <ConfirmationModal :show="showConfirmationModal" @close="showConfirmationModal = false">
        <template #title>
            Confirm Adding Tags
        </template>

        <template #content>
            Are you sure you want to add the tags to the item on the selected photos?<br>
            Be aware, if a photo has more than 1 item, no tags will be added to that photo.<br>
            You have selected {{ form.tag_ids.length }} tag(s).
        </template>

        <template #footer>
            <SecondaryButton @click="showConfirmationModal = false">
                Cancel
            </SecondaryButton>

            <PrimaryButton
                class="ml-3"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
                @click="save"
            >
                Add
            </PrimaryButton>
        </template>
    </ConfirmationModal>
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
