<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import {Link, router} from '@inertiajs/vue3';
import Filters from "@/Components/Filters.vue";
import BulkTag from "@/Pages/Photos/Partials/BulkTag.vue";
import {ref, watch} from "vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SelectInput from "@/Components/SelectInput.vue";
import TaggedIcon from "@/Components/TaggedIcon.vue";
import ConfirmDeleteButton from "@/Components/ConfirmDeleteButton.vue";

const props = defineProps({
    photos: Object,
    tags: Object,
    items: Array,
    filters: Object,
});

const isSelecting = ref(localStorage.getItem('isSelecting') === 'true' || false);
const selectedPhotos = ref(localStorage.getItem('selectedPhotos') ? JSON.parse(localStorage.getItem('selectedPhotos')) : []);
const showFilters = ref(localStorage.getItem('showFilters') === 'true' || false);
const perPageOptions = [
    {label: '25 per page', value: 25},
    {label: '50 per page', value: 50},
    {label: '100 per page', value: 100},
    {label: '200 per page', value: 200},
];
const perPage = ref(perPageOptions.find(option => option.value === props.photos.per_page));

watch(perPage, (value) => {
    router.get(window.location.pathname, {
        set_per_page: true,
        per_page: value.value,
    });
});

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

const selectPhotos = (photoId) => {
    if (! isSelecting.value) {
        return;
    }

    // Copilot magic ensues
    const lastSelected = selectedPhotos.value[selectedPhotos.value.length - 1];
    const lastIndex = props.photos.data.findIndex(photo => photo.id === lastSelected);
    const currentIndex = props.photos.data.findIndex(photo => photo.id === photoId);

    if (lastIndex === -1 || currentIndex === -1) {
        return;
    }

    const selected = props.photos.data.slice(Math.min(lastIndex, currentIndex), Math.max(lastIndex, currentIndex) + 1);

    selectedPhotos.value = selected.map(photo => photo.id);
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

        <div class="py-6 lg:py-16">
            <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">

                <div class="flex flex-row gap-4 px-4 sm:px-0">
                    <PrimaryButton @click="showFilters = !showFilters">
                        {{ showFilters ? 'Hide' : 'Show' }} Filters
                    </PrimaryButton>

                    <PrimaryButton @click="toggleSelecting">
                        <span v-if="isSelecting">
                            Clear Selection {{ selectedPhotos.length ? `(${selectedPhotos.length})` : '' }}
                        </span>
                        <span v-else>Select Photos</span>
                    </PrimaryButton>

                    <BulkTag
                        v-if="isSelecting && selectedPhotos.length"
                        :photoIds="selectedPhotos"
                        :tags="tags"
                        :items="items"
                        @closeModalWithSuccess="clearSelection"
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

                <div v-if="photos.data.length" class="mt-6 mb-24">
                    <div class="p-6 lg:p-8 bg-white dark:bg-gray-800 dark:bg-gradient-to-bl dark:from-gray-700/50 dark:via-transparent border-b border-gray-200 dark:border-gray-700 sm:rounded-lg shadow-xl">
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
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
                                        @click.shift.exact="selectPhotos(photo.id)"
                                        @click.exact="selectPhoto(photo.id)"
                                    >
                                        <img
                                            :src="photo.full_path"
                                            :alt="photo.id"
                                            class="w-full h-64 object-cover rounded-lg"
                                            loading="lazy"
                                        >
                                    </a>

                                    <TaggedIcon v-if="photo.items_exists" class="absolute top-2 right-2" />

                                    <ConfirmDeleteButton
                                        v-if="! isSelecting"
                                        @delete="deletePhoto(photo.id)"
                                        class="absolute bottom-2 right-2"
                                    />
                                </div>
                            </div>
                        </div>

                        <div v-if="photos.links?.length && photos.last_page > 1" class="mt-8 flex flex-col sm:flex-row sm:justify-between gap-4">
                            <div class="bg-white text-blue-500 dark:bg-gray-800 dark:text-white flex items-center justify-center">
                                Showing {{ photos.from }} to {{ photos.to }} of {{ photos.total }} photos
                            </div>
                            <div class="flex items-center justify-center">
                                <SelectInput
                                    v-model="perPage"
                                    :options="perPageOptions"
                                    class="w-full max-w-36 sm:w-36"
                                />
                            </div>
                            <div class="flex justify-center space-x-2 items-center pt-4 sm:pt-0">
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
        </div>
    </AppLayout>
</template>
