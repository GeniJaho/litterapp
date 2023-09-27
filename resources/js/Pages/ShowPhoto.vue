<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import {onMounted, ref} from "vue";

const props = defineProps({
    photoId: Number,
    items: Array,
    tags: Array,
});

const photo = ref(null);
const selectedItem = ref(props.items[0].id);
const selectedTag = ref(props.tags[0].id);

onMounted(() => {
    getPhoto();
});

const getPhoto = () => {
    axios.get(`/photos/${props.photoId}`)
        .then(response => {
            photo.value = response.data;
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

const removeItem = (itemId) => {
    return;
    axios.delete(`/photos/${photo.value.id}/items/${itemId}`)
        .then(() => {
            getPhoto();
        });
};

const addTagToItem = (photoItem) => {
    axios.post(`/photo-items/${photoItem.id}/tags`, {
        tag_id: selectedTag.value,
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
                            class="w-full sm:max-w-2xl"
                        >
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

                            <button
                                type="button"
                                class="ml-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none"
                                @click="addItem"
                                :disabled="selectedItem === ''"
                            >
                                Add Item
                            </button>
                        </div>

                        <div class="mt-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-50">
                                Photo Items
                            </h3>
                            <div class="mt-2">
                                <ul role="list" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                                    <li
                                        v-for="item in photo.items"
                                        :key="item.id"
                                        class="col-span-1 divide-y divide-gray-200 rounded-lg bg-white dark:bg-gray-800 shadow"
                                    >
                                        <div class="p-6 truncate">
                                            <div class="flex items-center space-x-3">
                                                <h3 class="truncate text-sm font-bold text-gray-900 dark:text-gray-50">
                                                    {{ item.name }}
                                                </h3>
                                                <span
                                                    class="inline-flex cursor-pointer items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
                                                    @click="removeItem(item.id)">
                                                    Remove Item
                                                </span>
                                            </div>
                                            <div class="mt-2">
                                                <div class="flex flex-row">
                                                    <select
                                                        id="add-tag"
                                                        v-model="selectedTag"
                                                        name="add-tag"
                                                        class="block w-full sm:w-48 rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                                        <option
                                                            v-for="tag in tags"
                                                            :value="tag.id"
                                                        >{{ tag.name }}
                                                        </option>
                                                    </select>

                                                    <button
                                                        type="button"
                                                        class="ml-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none"
                                                        @click="addTagToItem(item.pivot)"
                                                        :disabled="selectedTag === ''"
                                                    >
                                                        Add Tag
                                                    </button>
                                                </div>

                                                <div class="mt-2 text-sm text-gray-500 space-x-1">
                                                        <span
                                                            v-for="tag in item.pivot.tags"
                                                            :key="tag.id"
                                                            @click="removeTagFromItem(item.pivot, tag.id)"
                                                            class="inline-flex cursor-pointer items-center gap-x-1.5 rounded-full px-2 py-1 text-xs font-medium text-gray-900 dark:text-gray-50 ring-1 ring-inset ring-gray-200"
                                                        >
                                                            <svg class="h-1.5 w-1.5 fill-green-500" viewBox="0 0 6 6"
                                                                 aria-hidden="true"><circle cx="3" cy="3" r="3"/></svg>
                                                            {{ tag.name }}
                                                        </span>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
</template>
