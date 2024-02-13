<script setup>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {Link} from "@inertiajs/vue3";
</script>

<template>

    <div>
        <div class="flex flex-col px-40 space-y-12 bg-darkBlue dark:bg-mainWhite">
            <div class="">
                <h1 class="text-xl font-bold text-mainWhite dark:text-darkBlue pt-4">
                    Welcome to LitterApp!
                </h1>
                <div class="pt-32">
                    <div class="text-6xl font-bold text-mainWhite dark:text-darkBlue">From
                        <strong>waste</strong> to <strong>data</strong>, step by step
                    </div>
                    <div class="text-base font-normal text-mainWhite dark:text-darkBlue pt-12 pb-2">I already know the story</div>
                    <Link :href="route('my-photos')">
                        <button
                            class="inline-flex items-center px-4 py-2 bg-turqoFocus border border-transparent rounded-md font-semibold text-xs text-mainWhite dark:text-darkBlue hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 uppercase">
                            Start uploading
                        </button>
                    </Link>
                </div>

                <div class="flex flex-col items-center pt-36">
                    <div class="h-4 w-4 bg-mainWhite dark:bg-darkBlue rounded-full"></div>
                    <div class="text-4xl font-bold text-mainWhite dark:text-darkBlue pt-8">A common procedure</div>
                </div>
            </div>

            <div class="flex flex-col space-y-40">
                <div class="flex flex-row items-center -space-x-1">
                    <img src="/img/first-landing.png" alt="LitterApp" class="w-3/6 h-80"/>
                    <div>
                        <div class="text-2xl text-mainWhite dark:text-darkBlue">#1</div>
                        <div class="text-mainWhite dark:text-darkBlue">
                            <div class="text-3xl"><strong>Tons</strong> of litter photos.</div>
                            <div class="text-mainWhite dark:text-darkBlue text-xs pt-2">
                                Take a photo, either from your phone or desktop.
                            </div>
                            <div class="text-mainWhite dark:text-darkBlue font-bold text-xs pt-4">
                                GPS location is recommended. But not required to upload photos.
                            </div>
                            <div class="text-mainWhite dark:text-darkBlue text-xs">
                                Allowing geolocation detection in your settings, you can contribute to build up the
                                world map of our litter, it's open source.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-row items-center -space-x-1">
                    <img src="/img/second-landing.png" alt="LitterApp" class="w-3/6 h-80"/>
                    <div>
                        <div class="text-2xl text-mainWhite dark:text-darkBlue">#2</div>
                        <div class="text-mainWhite dark:text-darkBlue">
                            <div class="text-3xl"><strong>Upload</strong> photos.</div>
                            <div class="text-mainWhite dark:text-darkBlue text-xs pt-2">
                                Depending on your preferences, you can get creative using the LitterApp platform
                            </div>
                            <div class="text-mainWhite dark:text-darkBlue font-bold text-xs pt-4">
                                Our app is coming soon!
                            </div>
                            <div class="text-mainWhite dark:text-darkBlue text-xs">
                                Meanwhile you can use the website on your computer or phone to register data.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-row items-center -space-x-1">
                    <img src="/img/third-landing.png" alt="LitterApp" class="w-3/6 h-80"/>
                    <div>
                        <div class="text-2xl text-mainWhite dark:text-darkBlue">#3</div>
                        <div class="text-mainWhite dark:text-darkBlue">
                            <div class="text-3xl"><strong>Tag!</strong> You're it.</div>
                            <div class="text-mainWhite dark:text-darkBlue text-xs pt-2">
                                Data registration has some rules to follow... Make sure you know what you're doing.
                            </div>
                            <div class="text-mainWhite dark:text-darkBlue text-xs pt-4">
                                When tagging your photos, it's important to consider the <strong>accuracy of the
                                data</strong>. It's
                                possible to add any details necessary from one or more photos, always make sure data
                                is
                                accurate, that's why got this wonderful feature to <strong>edit the tags whenever
                                you
                                need it</strong>.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-row items-center -space-x-1">
                    <img src="/img/fourth-landing.png" alt="LitterApp" class="w-3/6 h-80"/>
                    <div>
                        <div class="flex flex-row items-center space x-4">
                            <div class="text-2xl text-mainWhite dark:text-darkBlue ">#4</div>
                        </div>
                        <div class="text-mainWhite dark:text-darkBlue text-2xl">
                            <strong>See litter </strong>at the Global Map.
                        </div>
                        <div class="text-mainWhite dark:text-darkBlue text-xs pt-4">
                            Enable page global map for live feed, the possibilities are endless.
                        </div>
                        <div class="text-mainWhite dark:text-darkBlue text-md pt-6 pb-1">Watch demo</div>
                        <button
                            class="inline-flex items-center px-4 py-2 bg-turqoFocus border border-transparent rounded-md font-semibold text-xs text-mainWhite dark:text-darkBlue hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 uppercase">
                            Go to youtube
                        </button>
                    </div>
                </div>
            </div>

            <div class="py-32">
                <div class="text-6xl text-mainWhite dark:text-darkBlue">
                    Capturing data is fun, and crucially useful.
                </div>
                <div class="pt-12">
                    <div class="text-mainWhite dark:text-darkBlue text-lg pb-1">
                        Let's make more success stories:
                    </div>
                    <button
                        class="inline-flex items-center px-4 py-2 bg-turqoFocus border border-transparent rounded-md font-semibold text-xs text-mainWhite dark:text-darkBlue hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 uppercase">
                        Our stories
                    </button>
                </div>
                <div class="pt-10">
                    <div class="text-mainWhite dark:text-darkBlue text-lg pb-1">
                        Read about our Vision with this project:
                    </div>
                    <button
                        class="inline-flex items-center px-4 py-2 bg-turqoFocus border border-transparent rounded-md font-semibold text-xs text-mainWhite dark:text-darkBlue hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 uppercase">
                        Our Mission
                    </button>
                </div>
            </div>
        </div>
        <div class="px-40 w-full flex items-center bg-mainWhite dark:bg-darkBlue">
            <div class="text-darkBlue dark:text-mainWhite py-40">
                <div class="text-6xl text-darkBlue dark:text-mainWhite">The communities are rising.</div>
                <div class="text-6xl text-darkBlue dark:text-mainWhite">Let's build it, <strong>together</strong>.</div>
                <div class="pt-10">
                    <div class="text-darkBlue dark:text-mainWhite text-lg pb-2">
                        Interested? Join our <strong>slack workspace:</strong>
                    </div>
                    <button
                        class="inline-flex items-center px-4 py-2 bg-turqoFocus border border-transparent rounded-md font-semibold text-xs text-mainWhite dark:text-darkBlue hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 uppercase">
                        Go to litterapp
                    </button>
                    <div class="text-darkBlue dark:text-mainWhite text-xs pt-2">
                        Disclaimer: Rabbit hole warning, there's a whole story behind it 🐇
                    </div>
                </div>
            </div>
        </div>
    </div>


</template>
