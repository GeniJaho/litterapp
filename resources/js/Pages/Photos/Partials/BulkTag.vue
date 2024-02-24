<script setup>

import SecondaryButton from "@/Components/SecondaryButton.vue";
import {useForm} from "@inertiajs/vue3";
import {ref} from "vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TagBox from "@/Components/TagBox.vue";
import NewPhotoItem from "@/Pages/Photos/Partials/NewPhotoItem.vue";
import BulkTagModal from "@/Pages/Photos/Partials/BulkTagModal.vue";

const props = defineProps({
    items: Array,
    tags: Object,
});

const selectedItem = ref(null);
const showModal = ref(false);
const form = useForm({
    photo_ids: [2],
    items: []
});
const message = ref('');

const addItem = () => {
    form.items.unshift({
        key: Math.floor(Math.random() * 100000) + 1, // random int 1 to 100'000
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

const removeItem = (key) => {
    form.items = form.items.filter(item => item.key !== key);
};

const updateItem = (item) => {
    form.items.splice(form.items.findIndex(formItem => formItem.key === item.key), 1, item);
};

const copyItem = (item) => {
    form.items.push({
        ...item,
        tag_ids: [...item.tag_ids],
        key: Math.floor(Math.random() * 100000) + 1, // random int 1 to 100'000
    });
};

const save = () => {
    form.post(route('bulk-photo-items.store'), {
        preserveScroll: true,
        onSuccess: () => closeModalWithSuccess(),
    });
};

const closeModalWithSuccess = () => {
    form.reset();
    message.value = 'Tagged successfully!';

    setTimeout(() => showModal.value = false, 2000);
};

const closeModal = () => {
    showModal.value = false;

    form.reset();
};

const openModal = () => {
    message.value = '';
    showModal.value = true;
};
</script>

<template>
    <PrimaryButton @click="openModal">
        Tag Multiple Photos
    </PrimaryButton>

    <BulkTagModal max-width="7xl" :show="showModal" @close="closeModal">
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
            <div class="mt-4 w-full h-full min-h-96 px-4">
                <div class="flex flex-col md:flex-row mt-6 md:mt-0">
                    <TagBox
                        class="w-full md:w-96"
                        :items="items"
                        v-model="selectedItem"
                    ></TagBox>
                    <div class="ml-0 md:ml-4 mt-4 md:mt-0.5 ml-auto">
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
                        <TransitionGroup tag="ul" name="items" role="list" class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
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
                class="ml-3"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing || !form.items.length"
                @click="save"
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
