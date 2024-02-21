<script setup>

import SecondaryButton from "@/Components/SecondaryButton.vue";
import DialogModal from "@/Components/DialogModal.vue";
import {useForm} from "@inertiajs/vue3";
import {ref} from "vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import PhotoItem from "@/Pages/Photos/Partials/PhotoItem.vue";
import TagBox from "@/Components/TagBox.vue";
import NewPhotoItem from "@/Pages/Photos/Partials/NewPhotoItem.vue";
import BulkTagModal from "@/Pages/Photos/Partials/BulkTagModal.vue";

const props = defineProps({
    items: Array,
    tags: Object,
});

const photoItems = ref([]);
const selectedItem = ref(null);

const addItem = () => {
    form.items.push({
        key: Math.floor(Math.random() * 10000) + 1, // random int 1 to 10000
        id: selectedItem.value.id,
        name: selectedItem.value.name,
        tag_ids: [],
        picked_up: false,
        recycled: false,
        deposit: false,
        quantity: 1,
    });

    selectedItem.value = null;
};

const confirmingUserDeletion = ref(false);

const form = useForm({
    photo_ids: [],
    items: []
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    // setTimeout(() => passwordInput.value.focus(), 250);
};

const deleteUser = () => {
    form.post(route('bulk-photo-items.store'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        // onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.reset();
};
</script>

<template>
    <PrimaryButton @click="confirmUserDeletion">
        Tag Multiple Photos
    </PrimaryButton>

    <BulkTagModal max-width="7xl" :show="confirmingUserDeletion" @close="closeModal">
        <template #header>
            <div class="px-6 py-4 text-lg font-medium text-gray-900 dark:text-gray-100">
                Tag Multiple Photos
            </div>
            <div class="px-6 text-sm text-gray-700 dark:text-gray-200">
                Add items and tags to multiple photos at once. You can also mark them as picked up, recycled, or deposited.<br>
                After you have added all the items and tags, click the "Save" button to save the changes.
            </div>
        </template>

        <template #content>
            <div class="mt-4 w-full px-4">
                <div class="flex flex-row mt-6 md:mt-0">
                    <TagBox
                        :autofocus="true"
                        class="w-full sm:w-96"
                        :items="items"
                        v-model="selectedItem"
                    ></TagBox>
                    <div class="ml-4 mt-0.5">
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
                        <TransitionGroup tag="ul" name="items" role="list" class="grid grid-cols-1 gap-6 lg:grid-cols-2 xl:grid-cols-3">
                            <NewPhotoItem
                                v-for="item in form.items"
                                :key="item.key"
                                :item="item"
                                :tags="tags"
                            />
                        </TransitionGroup>
                    </div>
                </div>
            </div>

        </template>

        <template #footer>
            <SecondaryButton @click="closeModal">
                Cancel
            </SecondaryButton>

            <PrimaryButton
                class="ml-3"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
                @click="deleteUser"
            >
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
