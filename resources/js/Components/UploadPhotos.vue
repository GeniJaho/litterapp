<script setup>
import {Link, usePage} from '@inertiajs/vue3'
// Import FilePond
import vueFilePond from 'vue-filepond';

// Import plugins
import FilePondPluginImagePreview from 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview';
import FilePondPluginImageTransform from 'filepond-plugin-image-transform';
import FilePondPluginImageResize from 'filepond-plugin-image-resize';
import FilePondPluginImageExifOrientation from 'filepond-plugin-image-exif-orientation';
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type';
import FilePondPluginFileValidateSize from 'filepond-plugin-file-validate-size';

// Import styles
import 'filepond/dist/filepond.min.css';
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css';
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {ref} from "vue";

const page = usePage();

// Create FilePond component
const FilePond = vueFilePond(
    FilePondPluginImagePreview,
    FilePondPluginImageTransform,
    FilePondPluginImageResize,
    FilePondPluginImageExifOrientation,
    FilePondPluginFileValidateType,
    FilePondPluginFileValidateSize,
);

const server = {
    url: '.', // current host
    process: {
        url: '/upload',
        method: 'POST',
        withCredentials: false,
        headers: {
            'X-CSRF-TOKEN': page.props.csrf_token,
        },
        timeout: 120000, // 2 minutes
        onload: null,
        onerror: null,
        ondata: null,
    },
    fetch: null,
    revert: null,
};

const acceptedFileTypes = [
    'image/jpeg',
    'image/jpg',
    'image/png',
    'image/webp',
    '.heic',
    '.heif',
];

const customFileValidation = (source, type) => new Promise((resolve, reject) => {
    if (type) {
        return resolve(type);
    }

    // Unrecognized mime type, looking for a file extension
    const uploadedFileExtension = source.name.split('.').pop()?.toLowerCase();

    // Checking if the file extension is accepted
    const isAllowed = acceptedFileTypes.find(fileType => fileType.split('.').pop() === uploadedFileExtension) !== undefined;

    if (isAllowed) {
        // Resolve with our "false" mime type
        resolve('.' + uploadedFileExtension);
    } else {
        // Even the extension is not accepted, reject
        reject('.' + uploadedFileExtension);
    }
});

const isIdle = ref(true);
const uploadProgress = ref(0);
const pond = ref(null);

const updateProgress = () => {
    const files = pond.value.getFiles();

    // https://pqina.nl/filepond/docs/api/exports/#filestatus
    const processedFiles = files.filter(file => file.status === 5); // PROCESSING_COMPLETE

    uploadProgress.value = files.length
        ? (processedFiles.length / files.length) * 100
        : 0;
}

// Stupid solution, but it works for now
// The Vue filepond does not currently expose the status property
// https://github.com/pqina/vue-filepond/issues/139
setInterval(() => {
    if (pond.value) {
        isIdle.value = [0, 1, 4].includes(pond.value._pond?.status); // EMPTY, IDLE, READY
    }
}, 1000);

</script>

<template>
    <div>
        <div
            class="p-6 lg:p-8 bg-white dark:bg-gray-800 dark:bg-gradient-to-bl dark:from-gray-700/50 dark:via-transparent border-b border-gray-200 dark:border-gray-700">
            <h1 class="text-2xl font-medium text-gray-900 dark:text-white flex flex-row justify-between">
                <span>Drag and drop your photos here</span>
                <span v-if="uploadProgress > 0">{{ uploadProgress.toFixed() }}%</span>
            </h1>

            <div class="mt-6 text-gray-500 dark:text-gray-400">
                <div class="mt-2">
                    <div v-if="uploadProgress > 0"
                        class="mb-4 flex justify-center">
                        <Link
                            :href="route('my-photos')">
                            <PrimaryButton :disabled="uploadProgress < 100 && ! isIdle">Go to My Photos</PrimaryButton>
                        </Link>
                    </div>
                    <file-pond
                        name="photo"
                        ref="pond"
                        allow-multiple="true"
                        allow-revert="false"
                        :accepted-file-types="acceptedFileTypes"
                        :file-validate-type-detect-type="customFileValidation"
                        file-validate-type-label-expected-types="Only images are allowed"
                        max-file-size="20MB"
                        image-transform-output-strip-image-head="false"
                        image-transform-output-quality="75"
                        image-resize-target-width="1024"
                        image-resize-target-height="1024"
                        image-resize-mode="contain"
                        image-resize-upscale="false"
                        :server="server"
                        @processfile="updateProgress"
                        @error="updateProgress"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
