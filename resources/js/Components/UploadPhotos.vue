<script setup>
import { useDropzone } from "vue3-dropzone";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {ref} from "vue";

const errors = ref({});

const saveFiles = (files) => {
    const formData = new FormData(); // pass data as a form
    for (let x = 0; x < files.length; x++) {
        // append files as array to the form, feel free to change the array name
        formData.append("photos[]", files[x]);
    }

    // post the formData to your backend where storage is processed.
    // In the backend, you will need to loop through the array and save each file through the loop.
    axios
        .post("/upload", formData, {
            headers: {
                "Content-Type": "multipart/form-data",
            },
        })
        .then(() => {
            window.location.href = route('my-photos');
        })
        .catch((err) => {
            errors.value = err.response.data.errors;
        });
};

function onDrop(acceptFiles, rejectReasons) {
    saveFiles(acceptFiles); // saveFiles as callback
    console.log(rejectReasons);
}

const { getRootProps, getInputProps, isDragActive, ...rest } = useDropzone({ onDrop });

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

                    <p v-if="isDragActive">Drop the files here ...</p>

                    <PrimaryButton v-else v-bind="getRootProps()">
                        <input v-bind="getInputProps()" />
                        Click to upload
                    </PrimaryButton>

                    <div v-if="Object.keys(errors).length" class="text-red-500 mt-2">
                        <div v-for="error in errors">
                            <div v-for="message in error">
                                {{ message }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
