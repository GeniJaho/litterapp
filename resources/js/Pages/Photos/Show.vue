<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import {onMounted, onUnmounted, ref, watch} from "vue";
import PivotItem from "@/Pages/Photos/Partials/PivotItem.vue";
import {Link} from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import debounce from 'lodash.debounce'
import { router } from '@inertiajs/vue3'
import TagBox from "@/Components/TagBox.vue";
import Tooltip from "@/Components/Tooltip.vue";
import TaggedIcon from "@/Components/TaggedIcon.vue";
import ConfirmDeleteButton from "@/Components/ConfirmDeleteButton.vue";
import TagShortcutBox from "@/Components/TagShortcutBox.vue";
import Dropdown from "@/Components/Dropdown.vue";
import ToggleInput from "@/Components/ToggleInput.vue";
import ZoomIcon from "@/Components/ZoomIcon.vue";
import Modal from "@/Components/Modal.vue";
import VueMagnifier from '@websitebeaver/vue-magnifier';
import '@websitebeaver/vue-magnifier/styles.css';
import LocationIcon from "@/Components/LocationIcon.vue";
import MagicWandIcon from "@/Components/MagicWandIcon.vue";
import SuggestedItem from "@/Pages/Photos/Partials/SuggestedItem.vue";

const props = defineProps({
    photoId: Number,
    items: Array,
    tags: Object,
    nextPhotoUrl: String,
    previousPhotoUrl: String,
    tagShortcuts: Array,
});

const photo = ref(null);
const suggestedItem = ref(null);
const selectedItem = ref(null);
const tagShortcut = ref(null);
const tagShortcutsEnabled = ref(localStorage.getItem('tagShortcutsEnabled') === 'true' || localStorage.getItem('tagShortcutsEnabled') === null);
const zoomingEnabled = ref(localStorage.getItem('zoomingEnabled') !== 'false');
const zoomedPhoto = ref(false);
const zoomLevel = ref(parseFloat(localStorage.getItem('zoomLevel')) || 0.9);
const zoomMagnifierSize = ref(parseInt(localStorage.getItem('zoomMagnifierSize')) || 350);

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

            if (photo.value.photo_item_suggestions.length) {
                const firstSuggestion = photo.value.photo_item_suggestions[0];
                const photoDoesNotHaveItem = photo.value.photo_items.findIndex(item => item.item_id === firstSuggestion.item_id) === -1;

                if (firstSuggestion.is_accepted === null && photoDoesNotHaveItem) {
                    suggestedItem.value = firstSuggestion;
                } else {
                    suggestedItem.value = null;
                }
            } else {
                suggestItem();
            }
        })
        .catch(error => {
            console.log(error);
        })
}


const suggestItem = () => {
    axios.get(route('litterbot.suggest', {photo: props.photoId}))
        .then(response => {
            suggestedItem.value = response.data.id ? response.data : null;
        })
        .catch(error => {
            console.log(error);
        })
}

const deletePhoto = () => {
    router.delete(`/photos/${photo.value.id}`);
};

const addItems = () => {
    const suggestionId = selectedItem.value.id === suggestedItem.value?.item_id
        ? suggestedItem.value.id
        : null;

    axios.post(`/photos/${photo.value.id}/items`, {
        item_ids: [selectedItem.value.id],
        suggestion_id: suggestionId,
    }).then(() => {
        selectedItem.value = null;
        getPhoto();
    });
};

const addSuggestedItem = () => {
    axios.post(`/photos/${photo.value.id}/items`, {
        item_ids: [suggestedItem.value.item_id],
        suggestion_id: suggestedItem.value.id,
    }).then(() => {
        suggestedItem.value = null;
        getPhoto();
    });
};

const rejectSuggestedItem = () => {
    axios.post(`/photo-item-suggestions/${suggestedItem.value.id}/reject`)
        .then(() => {
            suggestedItem.value = null;
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
        } else if ((event.code === "Enter" || event.code === "NumpadEnter") && suggestedItem.value?.id) {
            event.preventDefault();
            addSuggestedItem();
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

watch(tagShortcutsEnabled, (value) => {
    localStorage.setItem('tagShortcutsEnabled', value ? 'true' : 'false');
});

watch(zoomingEnabled, (value) => {
    localStorage.setItem('zoomingEnabled', value ? 'true' : 'false');
});

watch(zoomLevel, (value) => {
    localStorage.setItem('zoomLevel', value);
});

watch(zoomMagnifierSize, (value) => {
    localStorage.setItem('zoomMagnifierSize', value);
});

const toggleTagShortcutsEnabled = (enabled) => {
    tagShortcutsEnabled.value = enabled;
};

const toggleZoomingEnabled = (enabled) => {
    zoomingEnabled.value = enabled;
};

const adjustZoomLevelWithMouseWheel = (event) => {
    if (! event.ctrlKey && ! event.metaKey) {
        return;
    }

    event.preventDefault();

    zoomingEnabled.value = true;

    let zoom = parseFloat(event.deltaY > 0 ? zoomLevel.value - 0.05 : zoomLevel.value + 0.05) || 0.9;

    zoomLevel.value = Math.min(2, Math.max(0.4, zoom.toFixed(2)));
};

</script>

<template>
    <AppLayout title="See Photo">
        <template #header>
            <div class="flex justify-between relative">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    See Photo
                </h2>
                <!-- Settings Dropdown -->
                <div class="absolute right-0 top-1/2 transform -translate-y-1/2">
                    <Dropdown align="right" width="64">
                        <template #trigger>
                            <button class="flex items-center justify-center w-8 h-8 border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                                <i class="fas fa-gear text-lg text-gray-800 dark:text-gray-200 mt-0.5 sm:ml-[1px]"></i>
                            </button>
                        </template>

                        <template #content>
                            <div>
                                <ToggleInput
                                    v-model="tagShortcutsEnabled"
                                    @update:modelValue="toggleTagShortcutsEnabled"
                                    class="block w-full px-4 py-2"
                                >
                                    <template #label>Tag Shortcuts enabled</template>
                                </ToggleInput>
                                <ToggleInput
                                    v-model="zoomingEnabled"
                                    @update:modelValue="toggleZoomingEnabled"
                                    class="block w-full px-4 py-2"
                                >
                                    <template #label>Zooming enabled</template>
                                </ToggleInput>
                            </div>
                        </template>
                    </Dropdown>
                </div>
            </div>
        </template>

        <div v-if="photo">
            <div class="max-w-9xl mx-auto py-10 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row md:space-x-8">
                    <div class="w-full md:w-1/2 lg:w-1/3 px-4">

                        <div
                            v-if="zoomingEnabled"
                            class="flex flex-row justify-between mb-6"
                        >
                            <div class="w-24 sm:w-28 xl:w-36">
                                <label for="zoom-level" class="group relative block text-sm font-medium max-w-min whitespace-nowrap">
                                    <Tooltip>
                                        <span class="whitespace-nowrap text-white text-xs">
                                            Ctrl (⌘) + <br class="xl:hidden"> Scroll on photo
                                        </span>
                                    </Tooltip>
                                    <span class="text-gray-900 dark:text-gray-100">Zoom Level</span>
                                </label>
                                <input
                                    id="zoom-level"
                                    type="range"
                                    v-model="zoomLevel"
                                    min="0.4"
                                    max="2"
                                    step="0.05"
                                    class="w-full h-1 bg-gray-200 accent-turqoFocus rounded-lg appearance-none cursor-pointer dark:bg-gray-700"
                                >
                            </div>
                            <div class="w-24 sm:w-28 xl:w-36">
                                <label for="zoom-magnifier-size" class="block text-sm font-medium text-gray-900 dark:text-gray-100">
                                    Magnifier Size
                                </label>
                                <input
                                    id="zoom-magnifier-size"
                                    type="range"
                                    v-model="zoomMagnifierSize"
                                    min="100"
                                    max="500"
                                    step="10"
                                    class="w-full h-1 bg-gray-200 accent-turqoFocus rounded-lg appearance-none cursor-pointer dark:bg-gray-700"
                                >
                            </div>
                        </div>

                        <div class="relative group overflow-hidden"
                             v-on:wheel="adjustZoomLevelWithMouseWheel"
                        >
                            <VueMagnifier
                                :src="photo.full_path"
                                :alt="photo.id"
                                :mg-show="zoomingEnabled"
                                :zoomFactor="zoomLevel"
                                :mgWidth="zoomMagnifierSize"
                                :mgHeight="zoomMagnifierSize"
                                :mgBorderWidth="1"
                                class="w-full sm:max-w-2xl sm:overflow-hidden rounded-lg shadow-lg"
                            />

                            <ZoomIcon @click="zoomedPhoto = true" class="absolute top-0 left-0" />

                            <div class="absolute top-2 right-2 flex gap-2">
                                <LocationIcon v-if="photo.latitude && photo.longitude"/>
                                <TaggedIcon v-if="photo.photo_items.length"/>
                                <MagicWandIcon v-if="suggestedItem && suggestedItem.id"/>
                            </div>

                            <div
                                v-if="photo.taken_at_local"
                                class="absolute bottom-4 left-2 text-xs shadow bg-black/50 rounded px-2 py-1 text-white"
                            >
                                <i class="fas fa-camera mr-1"></i>
                                {{ photo.taken_at_local }}
                            </div>

                            <ConfirmDeleteButton
                                @delete="deletePhoto"
                                class="absolute bottom-4 right-2"
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

                    <div class="w-full md:w-1/2 lg:w-2/3 px-4 min-h-96 space-y-6 mt-6 md:mt-0 mb-36">
                        <div
                            v-if="tagShortcutsEnabled"
                            class="flex flex-row items-center"
                        >
                            <TagShortcutBox
                                class="w-full md:w-96"
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

                        <div class="flex flex-row items-center">
                            <TagBox
                                :autofocus="! tagShortcutsEnabled"
                                class="w-full md:w-96"
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

                        <div v-if="photo.photo_items.length || (suggestedItem && suggestedItem.id)">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
                                Litter Objects
                            </h3>
                            <div class="mt-2">
                                <TransitionGroup tag="ul" name="items" role="list" class="grid grid-cols-1 gap-6 lg:grid-cols-2 xl:grid-cols-3">
                                    <PivotItem
                                        v-for="photoItem in photo.photo_items"
                                        :key="photoItem.id"
                                        :pivotItem="photoItem"
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

                                    <SuggestedItem
                                        v-if="suggestedItem && suggestedItem.id"
                                        :suggestedItem="suggestedItem"
                                        @add-suggested-item="addSuggestedItem"
                                        @reject-suggested-item="rejectSuggestedItem"
                                    ></SuggestedItem>
                                </TransitionGroup>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <Modal max-width="9xl" @close="zoomedPhoto = false" :show="zoomedPhoto">
                <img
                    :src="photo.full_path"
                    :alt="photo.id"
                    class="w-full h-full object-contain"
                    @click="zoomedPhoto = false"
                >
            </Modal>
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
