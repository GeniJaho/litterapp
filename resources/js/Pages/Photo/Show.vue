<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import {onMounted, ref} from "vue";
import PhotoItem from "@/Pages/Photo/PhotoItem.vue";
import {Link} from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const props = defineProps({
    photoId: Number,
    items: Array,
    tags: Object,
    nextPhotoUrl: String,
});

const photo = ref(null);
const photoItems = ref([]);
const selectedItem = ref(props.items[0].id);

onMounted(() => {
    getPhoto();
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

const addItem = () => {
    axios.post(`/photos/${photo.value.id}/items`, {
        item_id: selectedItem.value,
    }).then(() => {
        getPhoto();
    });
};

const removeItem = (photoItemId) => {
    axios.delete(`/photo-items/${photoItemId}`)
        .then(() => {
            getPhoto();
        });
};

const addTagToItem = (photoItem, tagId) => {
    axios.post(`/photo-items/${photoItem.id}/tags`, {
        tag_id: tagId,
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

const toggleItemPickedUp = (photoItemId) => {
    axios.post(`/photo-items/${photoItemId}/picked-up`)
        .then(() => {
            getPhoto();
        });
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
                <div class="flex flex-col sm:flex-row space-x-8">
                    <div class="w-full sm:w-1/2 md:w-1/3">
                        <img
                            :src="photo.full_path"
                            :alt="photo.id"
                            class="w-full sm:max-w-2xl sm:mx-auto sm:rounded-lg sm:overflow-hidden"
                        >
                        <div class="flex justify-center mt-6">
                            <Link v-if="nextPhotoUrl" :href="nextPhotoUrl">
                                <PrimaryButton>Next Photo</PrimaryButton>
                            </Link>
                            <Link v-else :href="route('my-photos')">
                                <PrimaryButton>All Photos</PrimaryButton>
                            </Link>
                        </div>
                    </div>

                    <div class="w-full sm:w-1/2 md:w-2/3">
                        <div class="flex flex-row">
                            <select
                                id="add-item"
                                v-model="selectedItem"
                                name="add-item"
                                class="block w-full sm:w-48 rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                <option
                                    v-for="item in items"
                                    :value="item.id"
                                >{{ item.name }}
                                </option>
                            </select>

                            <PrimaryButton
                                class="ml-4"
                                @click="addItem"
                                :disabled="selectedItem === ''"
                            >
                                Add Object
                            </PrimaryButton>
                        </div>

                        <div class="mt-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
                                Litter Objects
                            </h3>
                            <div class="mt-2">
                                <TransitionGroup tag="ul" name="items" role="list" class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                    <PhotoItem
                                        v-for="item in photoItems"
                                        :key="item.pivot.id"
                                        :item="item"
                                        :tags="tags"
                                        @remove-item="removeItem"
                                        @add-tag-to-item="addTagToItem"
                                        @remove-tag-from-item="removeTagFromItem"
                                        @toggle-picked-up="toggleItemPickedUp"
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
