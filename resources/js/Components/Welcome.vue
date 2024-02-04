<script setup>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {Link} from "@inertiajs/vue3";
</script>

<template>
    <div>
        <div class="">
            <h1 class="text-2xl font-medium text-gray-900 dark:text-white">
                Welcome to LitterApp!
            </h1>

            <div class="flex flex-col space-y-12 lg:p-12">
                <p class="mt-6 text-gray-600 dark:text-white leading-relaxed">
                    It's in a very Work In Progress state right now.
                </p>

                <div class="">
                    <div class="text-sm py-2 dark:text-white">Scroll down to see how to</div>
                    <Link :href="route('my-photos')">
                    <PrimaryButton class="uppercase">Start uploading</PrimaryButton>
                    </Link>
                </div>

                <div class="flex flex-col pl-4 space-y-20">
                    <div>
                        <div class="flex flex-row items-center space x-4">
                            <div class="w-1.5 h-1.5  bg-gray-500 dark:bg-white rounded-full"></div>
                            <div class="text-2xl text-gray-600 dark:text-white pl-4">1</div>
                        </div>
                        <div class="text-gray-600 dark:text-white text-2xl">
                            Take a <strong>picture</strong> of the litter.
                        </div>
                        <div>
                            <div class="text-gray-600 dark:text-white">
                                GPS location is recommended.
                            </div>
                            <div class="text-gray-600 dark:text-white text-xs">
                                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor
                                incididunt ut labore et dolore magna aliqua.
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="flex flex-row items-center space x-4">
                            <div class="w-1.5 h-1.5  bg-gray-500 dark:bg-white rounded-full"></div>
                            <div class="text-2xl text-gray-600 dark:text-white  pl-4">2</div>
                        </div>
                        <div class="text-gray-600 dark:text-white text-2xl">
                            <strong>Upload</strong> it into your photos.
                        </div>
                        <div class="text-gray-600 dark:text-white">
                            Access your library of lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do
                            eiusmod tempor incididunt ut labore et dolore magna aliqua.
                        </div>
                    </div>

                    <div>
                        <div class="flex flex-row items-center space x-4">
                            <div class="w-1.5 h-1.5  bg-gray-500 dark:bg-white rounded-full"></div>
                            <div class="text-2xl text-gray-600 dark:text-white  pl-4">3</div>
                        </div>
                        <div class="text-gray-600 dark:text-white text-2xl">
                            Open it and <strong>register data</strong>.
                        </div>
                        <div class="text-gray-600 dark:text-white">
                            Add lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do
                            eiusmod tempor incididunt ut labore et dolore magna aliqua.
                        </div>
                    </div>
                </div>


            </div>
        </div>

    </div>
</template>
