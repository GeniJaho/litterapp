<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import {Link, router} from '@inertiajs/vue3';
import IconDangerButton from "@/Components/IconDangerButton.vue";
import Filters from "@/Components/Filters.vue";
import BulkTag from "@/Pages/Photos/Partials/BulkTag.vue";
import {ref, watch} from "vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const props = defineProps({
    photos: Object,
    tags: Object,
    items: Array,
    filters: Object,
});

const isSelecting = ref(localStorage.getItem('isSelecting') === 'true' || false);
const selectedPhotos = ref(localStorage.getItem('selectedPhotos') ? JSON.parse(localStorage.getItem('selectedPhotos')) : []);
const showFilters = ref(localStorage.getItem('showFilters') === 'true' || false);

watch(isSelecting, (value) => {
    localStorage.setItem('isSelecting', value ? 'true' : 'false');
});

watch(showFilters, (value) => {
    localStorage.setItem('showFilters', value ? 'true' : 'false');
});

const selectPhoto = (photoId) => {
    if (! isSelecting.value) {
        return;
    }

    if (selectedPhotos.value.includes(photoId)) {
        selectedPhotos.value = selectedPhotos.value.filter(id => id !== photoId);
    } else {
        selectedPhotos.value.push(photoId);
    }

    localStorage.setItem('selectedPhotos', JSON.stringify(selectedPhotos.value));
};

const toggleSelecting = () => {
    if (isSelecting.value) {
        clearSelection();
        return;
    }

    isSelecting.value = true;
};

const clearSelection = () => {
    isSelecting.value = false;
    selectedPhotos.value = [];
    localStorage.setItem('selectedPhotos', JSON.stringify(selectedPhotos.value));
};

const deletePhoto = (photoId) => {
    router.delete(`/photos/${photoId}`, {
        preserveScroll: true,
        preserveState: false,
    });
};

const filter = (filters) => {
    clearSelection();
    router.get(window.location.pathname, filters);
}
</script>

<template>
    <AppLayout title="See Your Photos">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                My Photos
            </h2>
        </template>

        <div class="py-6 lg:py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div class="flex flex-row gap-4 px-4 sm:px-0">
                    <PrimaryButton @click="showFilters = !showFilters">
                        {{ showFilters ? 'Hide' : 'Show' }} Filters
                    </PrimaryButton>

                    <PrimaryButton @click="toggleSelecting">
                        {{ isSelecting ? 'Clear Selection' : 'Select Photos' }}
                    </PrimaryButton>

                    <BulkTag
                        v-if="isSelecting && selectedPhotos.length"
                        :photoIds="selectedPhotos"
                        :tags="tags"
                        :items="items"
                    ></BulkTag>
                </div>

                <Filters
                    v-if="showFilters"
                    @change="filter"
                    :tags="tags"
                    :items="items"
                    :default-filters="filters"
                    class="mt-6"
                />

                <div v-if="photos.data.length" class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 lg:p-8 bg-white dark:bg-gray-800 dark:bg-gradient-to-bl dark:from-gray-700/50 dark:via-transparent border-b border-gray-200 dark:border-gray-700">
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            <div
                                v-for="photo in photos.data"
                                :key="photo.id"
                                class="rounded-lg bg-indigo-600 dark:bg-teal-400"
                            >
                                <div
                                    class="relative"
                                    :class="isSelecting && selectedPhotos.includes(photo.id) ? 'scale-[.95]' : ''"
                                >
                                    <a
                                        :href="isSelecting ? null : `/photos/${photo.id}`"
                                        :class="isSelecting ? 'cursor-cell' : 'cursor-pointer'"
                                        @click="selectPhoto(photo.id)"
                                    >
                                        <img :src="photo.full_path" :alt="photo.id" class="w-full h-64 object-cover rounded-lg">
                                    </a>

                                    <span v-if="photo.items_exists" class="absolute top-2 right-2 flex items-center justify-center bg-gray-50 w-8 h-8 rounded-full">
                                    <i class="fas fa-tags text-green-700  mt-0.5 ml-0.5"></i>
                                </span>

                                    <IconDangerButton
                                        v-if="!isSelecting"
                                        class="absolute bottom-2 right-2"
                                        @click="deletePhoto(photo.id)"
                                    >
                                        <i class="fas fa-fw fa-trash-alt text-xs"></i>
                                    </IconDangerButton>
                                </div>
                            </div>
                        </div>

                        <div v-if="photos.links?.length && photos.last_page > 1" class="flex justify-center space-x-2 mt-8 mg-4">
                            <div v-for="link in photos.links" :key="link.url">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    v-html="link.label"
                                    :class="`px-4 py-2 rounded ${link.active ? 'bg-blue-500 text-white' : 'bg-white text-blue-500 dark:bg-gray-800 dark:text-white'}`"
                                ></Link>
                                <span v-else v-html="link.label" :class="`px-4 py-2 rounded ${link.active ? 'bg-blue-500 text-white' : 'bg-white text-blue-500 dark:bg-gray-800 dark:text-white'}`"></span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
