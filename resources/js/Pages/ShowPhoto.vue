<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import {ref} from "vue";

const props = defineProps({
    photo: Object,
    tags: Array,
});

const selectedTag = ref(props.tags[0].id);

const addTag = () => {
    axios.post(`/photos/${props.photo.id}/tags`, {
        tag_id: selectedTag.value,
    }).then(() => {
        window.location.reload();
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

        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                <div class="flex space-x-8">
                    <div>
                        <img
                            :src="`/${photo.path}`"
                            :alt="photo.id"
                            class="w-full sm:max-w-2xl"
                        >
                    </div>

                    <div>
                        <div>
                            <label for="add-tag" class="block text-sm font-medium leading-6 text-gray-900">Add Tag</label>
                            <select
                                id="add-tag"
                                v-model="selectedTag"
                                name="add-tag" class="mt-2 block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                <option
                                    v-for="tag in tags"
                                    :value="tag.id"
                                >{{ tag.name }}</option>
                            </select>

                            <button
                                type="button"
                                class="inline-flex items-center px-4 py-2 mt-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none"
                                @click="addTag"
                                :disabled="selectedTag === ''"
                            >
                                Add
                            </button>
                        </div>

                        <div class="mt-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Photo Tags
                            </h3>
                            <div class="mt-2 max-w-xl text-sm text-gray-500">
                                <ul class="border border-gray-200 rounded-md divide-y divide-gray-200">
                                    <li
                                        v-for="tag in photo.tags"
                                        :key="tag.id"
                                        class="pl-3 pr-4 py-3 flex items-center justify-between text-sm"
                                    >
                                        <div class="w-0 flex-1 flex items-center">
                                            <span class="ml-2 flex-1 w-0 truncate">
                                                {{ tag.name }}
                                            </span>
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
