<script setup>
import {Link} from "@inertiajs/vue3";
</script>

<template>

    <div>
        <div class="flex flex-col px-12 lg:px-40 space-y-12 bg-mainWhite dark:bg-darkBlue ">
            <div class="mt-12">
                <h1 class="text-xl leading-8 font-black tracking-widest-xl dark:text-mainWhite text-darkBlue">
                    Welcome to LitterApp!
                </h1>
                <div class="flex flex-col md:flex-row md:space-x-24 md:items-end mt-16 md:mt-16">
                    <div
                        class="tracking-widest-xl md:leading-12 text-4xl md:text-6xl dark:text-mainWhite text-darkBlue">
                        From
                        <span class="font-black tracking-widest-xl">waste to data</span>, step by step
                    </div>
                    <div class="">
                        <div
                            class="whitespace-nowrap text-base font-normal tracking-widest-xl dark:text-mainWhite text-darkBlue pt-12 pb-4">
                            I
                            already know the story
                        </div>
                        <Link :href="route('upload')">
                            <button
                                class="whitespace-nowrap inline-flex items-center px-4 py-3 bg-turqoFocus border border-transparent rounded-md font-black text-sm tracking-widest-xl text-darkBlue hover:bg-turqo focus:bg-turqoFocus active:bg-turqoFocus focus:outline-none focus:ring-2 focus:ring-turqo focus:ring-offset-2 transition ease-in-out duration-150 uppercase">
                                Start uploading <i class="fa-solid fa-arrow-right text-base pl-2"></i>
                            </button>
                        </Link>
                    </div>
                </div>

                <div v-if="$page.props.auth?.user?.id == 1" class="pt-32 w-full">
                    <iframe class="w-full"
                            src="https://litterapp.net/grafana/d-solo/aadcc1c0-7a57-4d18-87cb-f13448fb1d45/main-dashboard?orgId=1&from=1709037342679&to=1711629342680&panelId=38"
                            width="450"
                            height="350"
                            frameborder="0"
                    >
                    </iframe>
                </div>

                <div class="flex flex-col items-center pt-24 pb-12 md:pt-32">
                    <div class="h-2 w-2 bg-darkBlue dark:bg-mainWhite rounded-full"></div>
                    <div
                        class="text-center text-2xl leading-8 font-black tracking-widest-xl dark:text-mainWhite text-darkBlue pt-8">
                        A
                        common procedure
                    </div>
                </div>
            </div>

            <div class="flex flex-col space-y-28 md:space-y-40">
                <div class="flex flex-col space-y-6 md:space-y-0 md:flex-row items-center md:space-x-16">
                    <img src="/img/first-landing.png" alt="LitterApp"
                         class="w-full md:w-3/6 h-80 object-cover object-cover"/>
                    <div>
                        <div class="tracking-wider dark:text-mainWhite text-darkBlue lg:pl-4 lg:pr-24">
                            <div class="text-3xl"><strong># 1 Tons of litter</strong> photos.</div>
                            <div class="dark:text-mainWhite text-darkBlue font-black text-xs pt-6">
                                Take a photo, either from your phone or desktop.
                            </div>
                            <div class="dark:text-mainWhite text-darkBlue text-xs pt-4">
                                GPS location is recommended. But not required to upload photos.
                            </div>
                            <div class="dark:text-mainWhite text-darkBlue text-xs pt-4">
                                Allowing geolocation detection in your settings, you can contribute to build up the
                                world map of our litter, it's open source.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col space-y-6 md:space-y-0 md:flex-row items-center md:space-x-16">
                    <img src="/img/second-landing.png" alt="LitterApp" class="w-full md:w-3/6 h-80 object-cover"/>
                    <div>
                        <div class="tracking-wider dark:text-mainWhite text-darkBlue lg:pl-4 lg:pr-24">
                            <div class="text-3xl"><strong># 2 Upload</strong> photos.</div>
                            <div class="dark:text-mainWhite text-darkBlue font-bold text-xs pt-6">
                                You can get creative using the LitterApp
                                platform.
                            </div>
                            <div class="dark:text-mainWhite text-darkBlue text-xs pt-4">
                                Our app is coming soon.
                                Meanwhile you can use a web browser on your computer or phone to start transforming your
                                data.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col space-y-6 md:space-y-0 md:flex-row items-center md:space-x-16">
                    <img src="/img/third-landing.png" alt="LitterApp" class="w-full md:w-3/6 h-80 object-cover"/>
                    <div>
                        <div class="dark:text-mainWhite text-darkBlue lg:pl-4 lg:pr-24">
                            <div class="tracking-wider text-3xl"><strong># 3 Tag!</strong> You're it.</div>
                            <div class="tracking-wider dark:text-mainWhite text-darkBlue font-bold text-xs pt-2">
                                Data registration has some rules to follow... Make sure you know what you're doing.
                            </div>
                            <div class="tracking-wider dark:text-mainWhite text-darkBlue text-xs pt-4">
                                When tagging your photos, it's important to consider the accuracy of the
                                data. It's possible to add any details necessary from one or more photos,
                                always ensure that your data is precise, that's why we have this wonderful feature to
                                re-edit the tags in case of a mistake.
                            </div>
                            <div class="tracking-wider dark:text-mainWhite text-darkBlue text-xs pt-8 pb-4">
                                Watch our demo tutorial explaining more about tagging:
                            </div>
                            <a href="https://www.youtube.com/watch?v=ACv1KYS2tFc&ab_channel=LitterApp"
                               target="_blank"
                            >
                                <button
                                    class="inline-flex items-center px-4 py-3 bg-turqoFocus border border-transparent rounded-md font-black text-sm tracking-widest-xl text-darkBlue hover:bg-turqo focus:bg-turqoFocus active:bg-turqoFocus focus:outline-none focus:ring-2 focus:ring-turqo focus:ring-offset-2 transition ease-in-out duration-150 uppercase">
                                    Go to youtube
                                </button>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col space-y-6 md:space-y-0 md:flex-row items-center md:space-x-16">
                    <img src="/img/fourth-landing.png" alt="LitterApp" class="w-full md:w-3/6 h-80 object-cover"/>
                    <div class="dark:text-mainWhite text-darkBlue lg:pl-4 lg:pr-24">
                        <div class="tracking-wider dark:text-mainWhite text-darkBlue text-3xl">
                            <strong># 4 See litter </strong>at the Global Map.
                        </div>
                        <div class="tracking-wider dark:text-mainWhite text-darkBlue font-bold text-xs pt-4">
                            If we all tagged our litter, the world map
                            would be completely
                            <a class="text-reddish underline"
                               href="https://globalmap.litterapp.net/"
                               target="_blank">red</a>.
                        </div>
                        <div class="tracking-wide dark:text-mainWhite text-darkBlue font-bold text-xs pt-4">
                            Enable page global map for live feed, the possibilities are endless.
                            <span class="dark:text-mainWhite text-darkBlue font-light text-xs pt-4 pb-4">
                            If you want to add up to our
                            Global Map, find out more about how to work with
                                <a href="https://globalmap.litterapp.net/"
                                   target="_blank"
                                   class="underline">Grafana</a>.
                        </span>
                        </div>
                        <div class="dark:text-mainWhite text-darkBlue text-xs pt-4 pb-4">
                            Watch our demo tutorial, made with love:
                        </div>
                        <a href="https://www.youtube.com/watch?v=Bapx1kYgDsE&ab_channel=LitterApp"
                           target="_blank"
                        >
                            <button
                                class="inline-flex items-center px-4 py-3 bg-turqoFocus border border-transparent rounded-md font-black text-sm tracking-widest-xl text-darkBlue hover:bg-turqo focus:bg-turqoFocus active:bg-turqoFocus focus:outline-none focus:ring-2 focus:ring-turqo focus:ring-offset-2 transition ease-in-out duration-150 uppercase">
                                Go to youtube
                            </button>
                        </a>
                    </div>
                </div>
            </div>

            <div class="flex flex-col space-y-2 items-center pt-24 pb-6 md:pt-26">
                <div class="h-2 w-2 bg-darkBlue dark:bg-mainWhite rounded-full"></div>
                <div class="h-2 w-2 bg-darkBlue dark:bg-mainWhite rounded-full"></div>
                <div class="h-2 w-2 bg-darkBlue dark:bg-mainWhite rounded-full"></div>
            </div>

            <div class="flex flex-col md:flex-row md:space-x-32 md:items-end pt-12 pb-24 md:pb-32">

                <div class="tracking-widest-xl md:leading-12 text-4xl md:text-6xl dark:text-mainWhite text-darkBlue">
                    Capturing data is <span class="font-black">fun</span>, and crucially <span
                    class="font-black">useful</span>.
                </div>

                <!--                <div class="pt-12">-->
                <!--                    <div class="tracking-widest-xl dark:text-mainWhite text-darkBlue text-lg pb-4">-->
                <!--                        Let's make more success stories:-->
                <!--                    </div>-->
                <!--                    <button-->
                <!--                        class="inline-flex items-center px-4 py-3 bg-turqoFocus border border-transparent rounded-md font-black text-sm tracking-widest-xl text-darkBlue hover:bg-turqo focus:bg-turqoFocus active:bg-turqoFocus focus:outline-none focus:ring-2 focus:ring-turqo focus:ring-offset-2 transition ease-in-out duration-150 uppercase">-->
                <!--                        Our stories-->
                <!--                    </button>-->
                <!--                </div>-->

                <div class="pt-12 md:pt-0">
                    <div class="tracking-widest-xl dark:text-mainWhite text-darkBlue text-lg pb-4">
                        Read about our Mission and join the ride:
                    </div>
                    <a
                        href="https://litterapp.net/en/05_Mission-Vision/"
                        target="_blank"
                    >
                        <button
                            class="whitespace-nowrap inline-flex items-center px-4 py-3 bg-turqoFocus border border-transparent rounded-md font-black text-sm tracking-widest-xl text-darkBlue hover:bg-turqo focus:bg-turqoFocus active:bg-turqoFocus focus:outline-none focus:ring-2 focus:ring-turqo focus:ring-offset-2 transition ease-in-out duration-150 uppercase">
                            Our Mission
                        </button>
                    </a>
                </div>
            </div>
        </div>


        <div
            class="flex flex-col md:flex-row md:space-x-28 md:items-end py-20 md:py-32 px-12 lg:px-40 bg-darkBlue dark:bg-mainWhite">
            <div
                class="tracking-widest-xl md:leading-12 text-4xl md:text-6xl text-mainWhite dark:text-darkBlue">
                The
                Global community is rising.
                Let's
                <span class="font-black">tag litter,</span> together.
            </div>
            <div class="pt-12 md:pt-0">
                <div class="tracking-widest-xl text-mainWhite dark:text-darkBlue text-lg pb-2">
                    Interested? Join our slack workspace:
                </div>
                <a
                    href="https://join.slack.com/t/helpingearthtogether/shared_invite/zt-2c81hu09y-Edwy2o74yk7FulbHACjnGw"
                    target="_blank"
                >
                    <button
                        class="whitespace-nowrap inline-flex items-center px-4 py-3 bg-turqoFocus border border-transparent rounded-md font-black text-sm tracking-widest-xl text-darkBlue hover:bg-turqo focus:bg-turqoFocus active:bg-turqoFocus focus:outline-none focus:ring-2 focus:ring-turqo focus:ring-offset-2 transition ease-in-out duration-150 uppercase">
                        Go to litterapp
                    </button>
                </a>
            </div>
        </div>
    </div>


</template>
