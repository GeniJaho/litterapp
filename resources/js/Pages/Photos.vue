<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import {onMounted, ref} from "vue";

const photos = ref({});

const getPhotos = () => {
    axios
        .get("/photos")
        .then((response) => {
            photos.value = response.data;
        })
        .catch((err) => {
            console.error(err);
        });
};

onMounted(() => {
    getPhotos();
});
</script>

<template>
    <AppLayout title="See Your Photos">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                See Your Photos
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 lg:p-8 bg-white dark:bg-gray-800 dark:bg-gradient-to-bl dark:from-gray-700/50 dark:via-transparent border-b border-gray-200 dark:border-gray-700">

                        <a href="/upload">
                            Upload Photos
                        </a>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            <div v-for="photo in photos.data" :key="photo.id">

                                <a :href="`/photos/${photo.id}`">
                                    <img :src="photo.path" :alt="photo.id" class="w-full h-64 object-cover">
                                </a>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
