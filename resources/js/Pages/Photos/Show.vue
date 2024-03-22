<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import {onMounted, onUnmounted, ref, watch} from "vue";
import PhotoItem from "@/Pages/Photos/Partials/PhotoItem.vue";
import {Link} from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import debounce from 'lodash.debounce'
import { router } from '@inertiajs/vue3'
import TagBox from "@/Components/TagBox.vue";
import Tooltip from "@/Components/Tooltip.vue";
import TaggedIcon from "@/Components/TaggedIcon.vue";
import ConfirmDeleteButton from "@/Components/ConfirmDeleteButton.vue";
import TagShortcutBox from "@/Components/TagShortcutBox.vue";

const props = defineProps({
    photoId: Number,
    items: Array,
    tags: Object,
    nextPhotoUrl: String,
    previousPhotoUrl: String,
    tagShortcuts: Array,
});

const photo = ref(null);
const photoItems = ref([]);
const selectedItem = ref(null);
const tagShortcut = ref(null);

onMounted(() => {
    getPhoto();

    window.addEventListener('keydown', onKeyDown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', onKeyDown);
});

const getPhoto = () => {
    axios.get(`/photos/${props.photoId}`)
        .then(response => {
            photo.value = response.data.photo;
            photoItems.value = response.data.items;
        })
        .catch(error => {
            console.log(error);
        })
}

const deletePhoto = () => {
    router.delete(`/photos/${photo.value.id}`);
};

const addItems = () => {
    axios.post(`/photos/${photo.value.id}/items`, {
        item_ids: [selectedItem.value.id],
    }).then(() => {
        selectedItem.value = null;
        getPhoto();
    });
};

const removeItem = (photoItemId) => {
    axios.delete(`/photo-items/${photoItemId}`)
        .then(() => {
            getPhoto();
        });
};

const copyItem = (photoItemId) => {
    axios.post(`/photo-items/${photoItemId}/copy`)
        .then(() => {
            getPhoto();
        });
};

const addTagsToItem = (photoItem, tagIds) => {
    axios.post(`/photo-items/${photoItem.id}/tags`, {
        tag_ids: tagIds,
    }).then(() => {
        getPhoto();
    });
};

const removeTagFromItem = (photoItem, tagId) => {
    axios.delete(`/photo-items/${photoItem.id}/tags/${tagId}`)
        .then(() => {
            getPhoto();
        });
};

const toggleItemPickedUp = debounce((photoItemId, pickedUp) => {
    axios.post(`/photo-items/${photoItemId}`, {
        picked_up: pickedUp,
    }).then(() => {
        getPhoto();
    });
}, 1000, {leading: true, trailing: true});

const toggleItemRecycled = debounce((photoItemId, recycled) => {
    axios.post(`/photo-items/${photoItemId}`, {
        recycled: recycled,
    }).then(() => {
        getPhoto();
    });
}, 1000, {leading: true, trailing: true});

const toggleItemDeposit = debounce((photoItemId, deposit) => {
    axios.post(`/photo-items/${photoItemId}`, {
        deposit: deposit,
    }).then(() => {
        getPhoto();
    });
}, 1000, {leading: true, trailing: true});

const updateItemQuantity = debounce((photoItemId, quantity) => {
    axios.post(`/photo-items/${photoItemId}`, {
        quantity: quantity,
    }).then(() => {
        getPhoto();
    });
}, 1000, {leading: true, trailing: true});

const onKeyDown = (event) => {
    if (event.ctrlKey || event.metaKey) {
        if (event.code === "ArrowLeft" && props.previousPhotoUrl) {
            event.preventDefault();
            router.visit(props.previousPhotoUrl);
        } else if (event.code === "ArrowRight" && props.nextPhotoUrl) {
            event.preventDefault();
            router.visit(props.nextPhotoUrl);
        }
    }
};

const applyTagShortcut = () => {
    if (! tagShortcut.value) {
        return;
    }

    axios.post(`/photos/${photo.value.id}/tag-shortcuts/${tagShortcut.value.id}`)
        .then(() => getPhoto());

    tagShortcut.value = null;
};

</script>

<template>
    <AppLayout title="See Photo">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                See Photo
            </h2>
        </template>

        <div v-if="photo">
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row md:space-x-8">
                    <div class="w-full md:w-1/2 xl:w-1/3 px-4">
                        <div class="relative">
                            <img
                                :src="photo.full_path"
                                :alt="photo.id"
                                class="w-full sm:max-w-2xl sm:overflow-hidden rounded-lg shadow-lg"
                            >

                            <TaggedIcon v-if="photoItems.length" class="absolute top-4 left-4" />

                            <ConfirmDeleteButton
                                @delete="deletePhoto"
                                class="absolute top-4 right-4"
                            />
                        </div>
                        <div v-if="previousPhotoUrl || nextPhotoUrl" class="flex justify-between mt-4">
                            <Link v-if="previousPhotoUrl" :href="previousPhotoUrl">
                                <PrimaryButton class="group relative">
                                    <Tooltip>
                                        <span class="whitespace-nowrap dark:text-white">Ctrl (⌘) + &larr;</span>
                                    </Tooltip>
                                    Previous
                                </PrimaryButton>
                            </Link>
                            <Link v-if="nextPhotoUrl" :href="nextPhotoUrl" class="ml-auto">
                                <PrimaryButton class="group relative">
                                    <Tooltip>
                                        <span class="whitespace-nowrap dark:text-white">Ctrl (⌘) + &rarr;</span>
                                    </Tooltip>
                                    Next
                                </PrimaryButton>
                            </Link>
                        </div>
                    </div>

                    <div class="w-full md:w-1/2 xl:w-2/3 px-4 min-h-96">
                        <div class="flex flex-row items-center mt-6 md:mt-0">
                            <TagShortcutBox
                                class="w-full sm:w-96"
                                v-model="tagShortcut"
                                :items="tagShortcuts"
                                :autofocus="true"
                                placeholder="Tag Shortcuts"
                            ></TagShortcutBox>
                            <div class="ml-4">
                                <PrimaryButton
                                    class="whitespace-nowrap"
                                    @click="applyTagShortcut"
                                    :disabled="!tagShortcut"
                                >
                                    Apply Shortcut
                                </PrimaryButton>
                            </div>
                        </div>

                        <div class="flex flex-row items-center mt-6">
                            <TagBox
                                :autofocus="false"
                                class="w-full sm:w-96"
                                :items="items"
                                v-model="selectedItem"
                                placeholder="Litter Objects"
                            ></TagBox>
                            <div class="ml-4">
                                <PrimaryButton
                                    class="whitespace-nowrap"
                                    @click="addItems"
                                    :disabled="!selectedItem"
                                >
                                    Add Object
                                </PrimaryButton>
                            </div>
                        </div>

                        <div class="mt-8" v-if="photoItems.length">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
                                Litter Objects
                            </h3>
                            <div class="mt-2">
                                <TransitionGroup tag="ul" name="items" role="list" class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                                    <PhotoItem
                                        v-for="item in photoItems"
                                        :key="item.pivot.id"
                                        :item="item"
                                        :tags="tags"
                                        @remove-item="removeItem"
                                        @add-tags-to-item="addTagsToItem"
                                        @remove-tag-from-item="removeTagFromItem"
                                        @copy-item="copyItem"
                                        @toggle-picked-up="toggleItemPickedUp"
                                        @toggle-recycled="toggleItemRecycled"
                                        @toggle-deposit="toggleItemDeposit"
                                        @update-quantity="updateItemQuantity"
                                    />
                                </TransitionGroup>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
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
