<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import {Link, router, usePage} from '@inertiajs/vue3';
import Filters from "@/Components/Filters.vue";
import BulkTag from "@/Pages/Photos/Partials/BulkTag.vue";
import {onMounted, onUnmounted, ref, watch} from "vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SelectInput from "@/Components/SelectInput.vue";
import TaggedIcon from "@/Components/TaggedIcon.vue";
import ConfirmDeleteButton from "@/Components/ConfirmDeleteButton.vue";
import Tooltip from "@/Components/Tooltip.vue";
import ZoomIcon from "@/Components/ZoomIcon.vue";
import Modal from "@/Components/Modal.vue";
import Dropdown from "@/Components/Dropdown.vue";
import ToggleInput from "@/Components/ToggleInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import BulkRemoveItemsAndTags from "@/Pages/Photos/Partials/BulkRemoveItemsAndTags.vue";
import LocationIcon from "@/Components/LocationIcon.vue";
import MagicWandIcon from "@/Components/MagicWandIcon.vue";

const props = defineProps({
    photos: Object,
    tags: Object,
    items: Array,
    filters: Object,
    tagShortcuts: Array,
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
const sortColumnOptions = [
    {label: 'Date Uploaded', value: 'id'},
    {label: 'Date taken (local)', value: 'taken_at_local'},
    {label: 'File name', value: 'original_file_name'},
];
const sortDirectionOptions = [
    {label: 'Ascending', value: 'asc'},
    {label: 'Descending', value: 'desc'},
];
const page = usePage();
const sortColumn = ref(sortColumnOptions.find(option => option.value === page.props.auth.user.settings.sort_column) || sortColumnOptions[0]);
const sortDirection = ref(sortDirectionOptions.find(option => option.value === page.props.auth.user.settings.sort_direction) || sortDirectionOptions[1]);
const zoomedImage = ref(null);
const tagShortcutsEnabled = ref(localStorage.getItem('tagShortcutsEnabled') === 'true' || false);

watch(perPage, (value) => {
    router.get(window.location.pathname, {set_per_page: true, per_page: value.value});
});

watch(sortColumn, (value) => {
    router.get(window.location.pathname, {set_sort: true, sort_column: value.value, sort_direction: sortDirection.value.value});
});

watch(sortDirection, (value) => {
    router.get(window.location.pathname, {set_sort: true, sort_column: sortColumn.value.value, sort_direction: value.value});
});

watch(isSelecting, (value) => {
    localStorage.setItem('isSelecting', value ? 'true' : 'false');
});

watch(showFilters, (value) => {
    localStorage.setItem('showFilters', value ? 'true' : 'false');
});

watch(tagShortcutsEnabled, (value) => {
    localStorage.setItem('tagShortcutsEnabled', value ? 'true' : 'false');
});

const toggleTagShortcutsEnabled = (enabled) => {
    tagShortcutsEnabled.value = enabled;
};

onMounted(() => {
    window.addEventListener('keydown', onKeyDown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', onKeyDown);
});

const onKeyDown = (event) => {
    if (event.ctrlKey || event.metaKey) {
        if (event.code === "ArrowLeft" && props.photos.prev_page_url) {
            event.preventDefault();
            router.visit(props.photos.prev_page_url);
        } else if (event.code === "ArrowRight" && props.photos.next_page_url) {
            event.preventDefault();
            router.visit(props.photos.next_page_url);
        }
    }
};

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

const exportData = (format) => {
    window.location.href = route('photos.export', {format});
}
</script>

<template>
    <AppLayout title="See Your Photos">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    My Photos
                </h2>

                <!-- Tag Shortcuts Toggle -->
                <ToggleInput
                    v-model="tagShortcutsEnabled"
                    @update:modelValue="toggleTagShortcutsEnabled"
                    class="mt-4 sm:mt-0"
                >
                    <template #label>Tag Shortcuts enabled</template>
                </ToggleInput>
            </div>
        </template>

        <div class="py-6 lg:py-16">
            <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">

                <div class="flex flex-col sm:flex-row sm:justify-between gap-4 px-4 sm:px-0">
                    <div class="flex flex-row gap-4">
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
                            :tagShortcuts="tagShortcuts"
                            :tagShortcutsEnabled="tagShortcutsEnabled"
                            @closeModalWithSuccess="clearSelection"
                        ></BulkTag>

                        <BulkRemoveItemsAndTags
                            v-if="isSelecting && selectedPhotos.length"
                            :photoIds="selectedPhotos"
                            :tags="tags"
                            :items="items"
                            @closeModalWithSuccess="clearSelection"
                        ></BulkRemoveItemsAndTags>
                    </div>

                    <div>
                        <Dropdown align="right" width="36">
                            <template #trigger>
                                <PrimaryButton class="group relative">
                                    <Tooltip>
                                        <div class="w-full min-w-32">
                                            <div class="dark:text-white">
                                                Downloads a file with all the items and
                                                tags of the photos you have filtered
                                            </div>
                                        </div>
                                    </Tooltip>
                                    Export Data
                                </PrimaryButton>
                            </template>

                            <template #content>
                                <DropdownLink @click.prevent.stop="exportData('json')">
                                    JSON
                                </DropdownLink>
                                <DropdownLink @click.prevent.stop="exportData('csv')">
                                    CSV
                                </DropdownLink>
                            </template>
                        </Dropdown>

                    </div>
                </div>

                <Filters
                    v-if="showFilters"
                    @change="filter"
                    :tags="tags"
                    :items="items"
                    :default-filters="filters"
                    class="mt-6"
                />

                <div v-if="photos.total" class="mt-6 px-4 sm:px-0 flex flex-col sm:flex-row sm:justify-between gap-4">
                    <div class="flex items-center text-gray-700 dark:text-white text-sm">
                        Showing {{ photos.from }} to {{ photos.to }} of {{ photos.total }} photos
                    </div>
                    <div class="flex flex-row gap-4">
                        <div>
                            <InputLabel for="sort-column" value="Order by" />
                            <SelectInput
                                id="sort-column"
                                v-model="sortColumn"
                                :options="sortColumnOptions"
                                class="mt-1 block w-full max-w-48 sm:w-48"
                            ></SelectInput>
                        </div>
                        <div>
                            <InputLabel for="sort-direction" value="Order Direction" />
                            <SelectInput
                                id="sort-direction"
                                v-model="sortDirection"
                                :options="sortDirectionOptions"
                                class="mt-1 block w-full max-w-36 sm:w-36"
                            ></SelectInput>
                        </div>
                    </div>
                </div>

                <div v-if="photos.data.length" class="mt-6 mb-24">
                    <div class="p-6 lg:p-8 bg-white dark:bg-gray-800 dark:bg-gradient-to-bl dark:from-gray-700/50 dark:via-transparent border-b border-gray-200 dark:border-gray-700 sm:rounded-lg shadow-xl">
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                            <div
                                v-for="photo in photos.data"
                                :key="photo.id"
                                class="rounded-lg bg-indigo-600 dark:bg-teal-400"
                            >
                                <div
                                    class="relative overflow-hidden group"
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

                                    <ZoomIcon @click="zoomedImage = photo" class="absolute top-0 left-0"/>

                                    <div class="absolute top-2 right-2 flex gap-2">
                                        <LocationIcon v-if="photo.latitude && photo.longitude"/>
                                        <TaggedIcon v-if="photo.items_exists"/>
                                        <MagicWandIcon v-if="photo.photo_item_suggestions_exists"/>
                                    </div>

                                    <div
                                        v-if="photo.taken_at_local"
                                        class="absolute bottom-2 left-2 text-xs shadow bg-black/50 rounded px-2 py-1 text-white"
                                    >
                                        <i class="fas fa-camera mr-1"></i>
                                        {{ photo.taken_at_local }}
                                    </div>

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
                                <div v-for="link in photos.links" :key="link.url" class="group relative">
                                    <Tooltip v-if="link.url && link.url === photos.prev_page_url">
                                        <span class="whitespace-nowrap dark:text-white text-xs">Ctrl (⌘) + &larr;</span>
                                    </Tooltip>
                                    <Tooltip v-else-if="link.url && link.url === photos.next_page_url">
                                        <span class="whitespace-nowrap dark:text-white text-xs">Ctrl (⌘) + &rarr;</span>
                                    </Tooltip>
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

                        <Modal max-width="9xl" @close="zoomedImage = null" :show="zoomedImage !== null">
                            <img
                                :src="zoomedImage?.full_path"
                                :alt="zoomedImage?.id"
                                class="w-full h-full object-contain"
                                @click="zoomedImage = null"
                            >
                        </Modal>

                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
