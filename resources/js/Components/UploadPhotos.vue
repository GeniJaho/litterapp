<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import vueFilePond from "vue-filepond";
import "filepond/dist/filepond.min.css";

const FilePond = vueFilePond();

</script>

<template>
    <div>
        <div
            class="p-6 lg:p-8 bg-white dark:bg-gray-800 dark:bg-gradient-to-bl dark:from-gray-700/50 dark:via-transparent border-b border-gray-200 dark:border-gray-700">
            <ApplicationLogo class="block h-12 w-auto"/>

            <h1 class="mt-8 text-2xl font-medium text-gray-900 dark:text-white">
                Drag and drop your photos here
            </h1>

            <div class="mt-6 text-gray-500 dark:text-gray-400">
                <div class="mt-2">
                    <file-pond
                        name="test"
                        ref="pond"
                        label-idle="Drop files here..."
                        v-bind:allow-multiple="true"
                        accepted-file-types="image/jpeg, image/png"
                        server="/api"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
