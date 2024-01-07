<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from "vue";
import {usePage, Link, router} from '@inertiajs/vue3';
import IconDangerButton from "@/Components/IconDangerButton.vue";

const { props } = usePage();
const photos = ref(props.photos);

const deletePhoto = (photoId) => {
    router.delete(`/photos/${photoId}`, {
        preserveScroll: true,
        preserveState: false,
    });
};
</script>

<template>
    <AppLayout title="See Your Photos">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                My Photos
            </h2>
        </template>

        <div v-if="photos.data.length" class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 lg:p-8 bg-white dark:bg-gray-800 dark:bg-gradient-to-bl dark:from-gray-700/50 dark:via-transparent border-b border-gray-200 dark:border-gray-700">

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            <div
                                v-for="photo in photos.data"
                                :key="photo.id"
                                class="relative"
                            >

                                <a :href="`/photos/${photo.id}`">
                                    <img :src="photo.full_path" :alt="photo.id" class="w-full h-64 object-cover rounded-lg">
                                </a>

                                <span v-if="photo.items_exists" class="absolute top-2 right-2 flex items-center justify-center bg-gray-50/30 w-8 h-8 rounded-full">
                                    <i class="fas fa-tags text-green-500  mt-0.5 ml-0.5"></i>
                                </span>

                                <IconDangerButton
                                    class="absolute bottom-2 right-2"
                                    @click="deletePhoto(photo.id)"
                                >
                                    <i class="fas fa-fw fa-trash-alt text-xs"></i>
                                </IconDangerButton>

                            </div>
                        </div>

                        <div v-if="photos.links?.length && photos.last_page > 1" class="flex justify-center space-x-2 my-4">
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