<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import UploadPhotos from "@/Components/UploadPhotos.vue";
</script>

<template>
    <AppLayout title="Documentation">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Documentation
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">

                        <div class="p-6 lg:p-8 bg-white dark:bg-gray-800 dark:bg-gradient-to-bl dark:from-gray-700/50 dark:via-transparent border-b border-gray-200 dark:border-gray-700">
                            <p class="text-gray-500 dark:text-gray-400 leading-relaxed">
                                Documentation will appear here soon, or somewhere else...
                            </p>
                        </div>

                </div>
            </div>
        </div>
    </AppLayout>
</template>
