<script setup>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {Link} from "@inertiajs/vue3";
</script>

<template>

    <div>
        <div class="flex flex-col px-40 space-y-12 bg-darkBlue">
            <div class="ml-10">
                <h1 class="text-xl leading-8 font-black tracking-widest text-mainWhite pt-6">
                    Welcome to LitterApp!
                </h1>
                <div class="pt-40">
                    <div class="text-6xl font-normal leading-10 tracking-widest text-mainWhite">From
                        <span class="font-black tracking-widest">waste to data</span>, step
                        <div class="pt-6"> by step</div>
                    </div>
                    <div class="text-base font-normal tracking-widest text-mainWhite pt-12 pb-2">I
                        already know the story
                    </div>
                    <Link :href="route('my-photos')">
                        <button
                            class="inline-flex items-center px-4 py-3 bg-turqoFocus border border-transparent rounded-md font-black text-sm tracking-widest text-darkBlue hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 uppercase">
                            Start uploading <i class="fa-solid fa-arrow-right text-base pl-2"></i>
                        </button>
                    </Link>
                </div>

                <div class="flex flex-col items-center pt-32">
                    <div class="h-2 w-2 bg-mainWhite rounded-full"></div>
                    <div class="text-2xl leading-8 font-black tracking-widest text-mainWhite pt-8">A
                        common procedure
                    </div>
                </div>
            </div>

            <div class="ml-10 flex flex-col space-y-40">
                <div class="flex flex-row items-center space-x-16">
                    <img src="/img/first-landing.png" alt="LitterApp" class="w-3/6 h-80"/>
                    <div>
                        <div class="tracking-widest text-mainWhite">
                            <div class="text-3xl"># 1 <strong>Tons</strong> of litter photos.</div>
                            <div class="text-mainWhite text-xs pt-2">
                                Take a photo, either from your phone or desktop.
                            </div>
                            <div class="text-mainWhite font-bold text-xs pt-4">
                                GPS location is recommended. But not required to upload photos.
                            </div>
                            <div class="text-mainWhite text-xs">
                                Allowing geolocation detection in your settings, you can contribute to build up the
                                world map of our litter, it's open source.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-row items-center space-x-16">
                    <img src="/img/second-landing.png" alt="LitterApp" class="w-3/6 h-80"/>
                    <div>
                        <div class="tracking-widest text-mainWhite">
                            <div class="text-3xl"># 2 <strong>Upload</strong> photos.</div>
                            <div class="text-mainWhite text-xs pt-2">
                                Depending on your preferences, you can get creative using the LitterApp platform
                            </div>
                            <div class="text-mainWhite font-bold text-xs pt-4">
                                Our app is coming soon!
                            </div>
                            <div class="text-mainWhite text-xs">
                                Meanwhile you can use the website on your computer or phone to register data.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-row items-center space-x-16">
                    <img src="/img/third-landing.png" alt="LitterApp" class="w-3/6 h-80"/>
                    <div>
                        <div class="tracking-widest text-mainWhite">
                            <div class="text-3xl"># 3 <strong>Tag!</strong> You're it.</div>
                            <div class="text-mainWhite text-xs pt-2">
                                Data registration has some rules to follow... Make sure you know what you're doing.
                            </div>
                            <div class="text-mainWhite text-xs pt-4">
                                When tagging your photos, it's important to consider the <strong>accuracy of the
                                data</strong>. It's possible to add any details necessary from one or more photos,
                                always make sure data
                                is accurate, that's why got this wonderful feature to <strong>re-edit the tags in case of a mistake</strong>.
                            </div>
                            <div class="text-mainWhite text-xs pt-4 pb-2">
                                We got tutorials made with love. Find more about tagging:
                            </div>
                            <button
                                class="inline-flex items-center px-4 py-3 bg-turqoFocus border border-transparent rounded-md font-black text-sm tracking-widest text-darkBlue hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 uppercase">
                                Go to youtube
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex flex-row items-center space-x-16">
                    <img src="/img/fourth-landing.png" alt="LitterApp" class="w-3/6 h-80"/>
                    <div class="tracking-widest text-mainWhite">
                        <div class="text-mainWhite text-2xl">
                            <strong># 4 See litter </strong>at the Global Map.
                        </div>
                        <div class="text-mainWhite text-xs pt-4">
                            Enable page global map for live feed, the possibilities are endless.
                        </div>
                        <div class="text-mainWhite text-xs pt-4 pb-2">
                            We got tutorials made with love. Find more about Graffana:
                        </div>
                        <button
                            class="inline-flex items-center px-4 py-3 bg-turqoFocus border border-transparent rounded-md font-black text-sm tracking-widest text-darkBlue hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 uppercase">
                            Go to youtube
                        </button>
                    </div>
                </div>
            </div>

            <div class="ml-10 py-32">
                <div class="text-6xl font-normal leading-12 tracking-widest text-mainWhite">
                    Capturing data is <span class="font-black">fun</span>, and crucially <span class="font-black">useful</span>.
                </div>
                <div class="pt-12">
                    <div class="tracking-widest text-mainWhite text-lg pb-1">
                        Let's make more success stories:
                    </div>
                    <button
                        class="inline-flex items-center px-4 py-3 bg-turqoFocus border border-transparent rounded-md font-black text-sm tracking-widest text-darkBlue hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 uppercase">
                        Our stories
                    </button>
                </div>
                <div class="pt-10">
                    <div class="tracking-widest text-mainWhite text-lg pb-1">
                        Read about our Vision with this project:
                    </div>
                    <button
                        class="inline-flex items-center px-4 py-3 bg-turqoFocus border border-transparent rounded-md font-black text-sm tracking-widest text-darkBlue hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 uppercase">
                        Our Mission
                    </button>
                </div>
            </div>
        </div>
        <div class="px-40 flex items-center bg-mainWhite">
            <div class="ml-10 tracking-widest leading-12 text-darkBlue py-40">
                <div class="tracking-widest leading-12 text-6xl text-darkBlue">Communities are rising.</div>
                <div class="tracking-widest leading-12 text-6xl text-darkBlue">Let's build, <span class="font-black">together</span>.</div>
                <div class="pt-10">
                    <div class="text-darkBlue text-lg pb-2">
                        Interested? Join our <strong>slack workspace:</strong>
                    </div>
                    <button
                        class="inline-flex items-center px-4 py-3 bg-turqoFocus border border-transparent rounded-md font-black text-sm tracking-widest text-darkBlue hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 uppercase">
                        Go to litterapp
                    </button>
                    <div class="text-darkBlue text-xs pt-2">
                        Disclaimer: Rabbit hole warning, there's a whole story behind it 🐇
                    </div>
                </div>
            </div>
        </div>
    </div>


</template>
