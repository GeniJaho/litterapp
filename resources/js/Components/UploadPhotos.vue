<script setup>
import { usePage } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'

const page = usePage();

// Import FilePond
import vueFilePond from 'vue-filepond';

// Import plugins
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type';
import FilePondPluginImagePreview from 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview';
import FilePondPluginImageExifOrientation from 'filepond-plugin-image-exif-orientation';

// Import styles
import 'filepond/dist/filepond.min.css';
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css';
import PrimaryButton from "@/Components/PrimaryButton.vue";

// Create FilePond component
const FilePond = vueFilePond(FilePondPluginFileValidateType, FilePondPluginImagePreview, FilePondPluginImageExifOrientation);

const server = {
    url: '.', // current host
    process: {
        url: '/upload',
        method: 'POST',
        withCredentials: false,
        headers: {
            'X-CSRF-TOKEN': page.props.csrf_token,
        },
        timeout: 20000,
        onload: null,
        onerror: null,
        ondata: null,
    },
    fetch: null,
    revert: null,
};

</script>

<template>
    <div>
        <div
            class="p-6 lg:p-8 bg-white dark:bg-gray-800 dark:bg-gradient-to-bl dark:from-gray-700/50 dark:via-transparent border-b border-gray-200 dark:border-gray-700">
            <h1 class="text-2xl font-medium text-gray-900 dark:text-white">
                Drag and drop your photos here
            </h1>

            <div class="mt-6 text-gray-500 dark:text-gray-400">
                <div class="mt-2">

                    <file-pond
                        name="photo"
                        ref="pond"
                        label-idle="Drop photos here..."
                        allow-multiple="true"
                        accepted-file-types="image/jpeg, image/png"
                        :server="server"
                    />

                    <div class="flex justify-center">
                        <Link :href="route('my-photos')">
                            <PrimaryButton>My Photos</PrimaryButton>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
