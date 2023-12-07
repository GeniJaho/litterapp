<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from "vue";
import { usePage, Link } from '@inertiajs/vue3';

const { props } = usePage();
const photos = ref(props.photos);
</script>

<template>
    <AppLayout title="See Your Photos">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                My Photos
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 lg:p-8 bg-white dark:bg-gray-800 dark:bg-gradient-to-bl dark:from-gray-700/50 dark:via-transparent border-b border-gray-200 dark:border-gray-700">

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            <div v-for="photo in photos.data" :key="photo.id">

                                <a :href="`/photos/${photo.id}`">
                                    <img :src="photo.full_path" :alt="photo.id" class="w-full h-64 object-cover">
                                </a>

                            </div>
                        </div>

                        <div v-if="photos.links?.length" class="flex justify-center space-x-2 my-4">
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