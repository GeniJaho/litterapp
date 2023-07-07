<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import UploadPhotos from "@/Components/UploadPhotos.vue";
</script>

<template>
    <AppLayout title="Upload Photos">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Upload Photos
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                    <UploadPhotos />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
