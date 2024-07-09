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

const props = defineProps({
    photoIds: Array,
    tagShortcuts: Array,
});

const emit = defineEmits(['closeModalWithSuccess'])

const page = usePage();
const selectedItem = ref(null);
const showModal = ref(false);
const form = useForm({
    items: []
});
const message = ref('');
const tagShortcut = ref(null);

onMounted(() => {
    window.addEventListener('keydown', onKeyDown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', onKeyDown);
});

const saveDisabled = computed(() => form.processing || !form.items.length);

const addItem = () => {
    form.items.unshift({
        key: Math.floor(Math.random() * 100000) + 1, // random int 1 to 100'000
        id: selectedItem.value.id,
        name: selectedItem.value.name,
        tag_ids: [],
        picked_up: page.props.auth.user.settings.picked_up_by_default || false,
        recycled: page.props.auth.user.settings.recycled_by_default || false,
        deposit: page.props.auth.user.settings.deposit_by_default || false,
        quantity: 1,
    });

    selectedItem.value = null;
};

const save = () => {
    form
        .transform((data) => ({
            ...data,
            photo_ids: props.photoIds,
        }))
        .post(route('groups.photos.store'), {
            preserveScroll: true,
            onSuccess: () => closeModalWithSuccess(),
        });
};

const closeModalWithSuccess = () => {
    form.reset();
    message.value = 'Added to group!';

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
        Add to Group
    </PrimaryButton>

    <BulkTagModal max-width="lg" :show="showModal" @close="closeModal">
        <template #header>
            <div class="px-6 py-4 text-lg font-medium text-gray-900 dark:text-gray-100">
                Tag Multiple Photos ({{ photoIds.length }} selected)
            </div>
            <div class="px-6 text-sm text-gray-700 dark:text-gray-200">
                Add items and tags to multiple photos at once. You can also mark them as picked up, recycled,
                or being an item with deposit on it.<br>
                The existing items and tags of the selected photos will not be affected.<br><br>
                After you have added all the items and tags, click the "Save" button to save the changes.
            </div>
        </template>

        <template #content>
            <div class="mt-4 w-full h-full min-h-36 px-4">
                <div
                    v-if="tagShortcutsEnabled"
                    class="flex flex-col md:flex-row mt-6 md:mt-0 mb-4"
                >
                    <TagShortcutBox
                        class="w-full md:w-96"
                        v-model="tagShortcut"
                        :items="tagShortcuts"
                        :autofocus="true"
                        layout="bulk"
                        placeholder="Tag Shortcuts"
                    ></TagShortcutBox>
                    <div class="ml-auto md:ml-4 mt-4 md:mt-0.5">
                        <PrimaryButton
                            class="whitespace-nowrap"
                            @click="applyTagShortcut"
                            :disabled="!tagShortcut"
                        >
                            Apply Shortcut
                        </PrimaryButton>
                    </div>
                </div>
                <div class="flex flex-col md:flex-row mt-6 md:mt-0">
                    <TagBox
                        class="w-full md:w-96"
                        :items="items"
                        v-model="selectedItem"
                        :autofocus="! tagShortcutsEnabled"
                        placeholder="Litter Objects"
                    ></TagBox>
                    <div class="md:ml-4 mt-4 md:mt-0.5 ml-auto">
                        <PrimaryButton
                            class="whitespace-nowrap"
                            @click="addItem"
                            :disabled="!selectedItem"
                        >
                            Add Object
                        </PrimaryButton>
                    </div>
                </div>

                <div class="mt-8" v-if="form.items.length">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
                        Litter Objects
                    </h3>
                    <div class="mt-2">
                        <TransitionGroup tag="ul" name="items" role="list" class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                            <NewPhotoItem
                                v-for="item in form.items"
                                :key="item.key"
                                :prop-item="item"
                                :tags="tags"
                                @change="updateItem"
                                @remove-item="removeItem"
                                @copy-item="copyItem"
                            />
                        </TransitionGroup>
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
