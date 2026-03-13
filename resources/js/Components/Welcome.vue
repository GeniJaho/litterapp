<script setup>
import {Link, usePage} from "@inertiajs/vue3";
import {computed} from "vue";
import LinkTile from "@/Components/LinkTile.vue";

const page = usePage()

const grafanaLink = computed(() => page.props.grafana.map_link)

const links = [
    {title: "Welcome", url: "https://litterapp.net/en/", icon: "👋"},
    {title: "Mission and Vision", url: "https://litterapp.net/en/05_Mission-Vision/", icon: "🎯"},
    {title: "Who are we", url: "https://litterapp.net/en/08_Who-Are-We/", icon: "👥"},
    {title: "Success", url: "https://litterapp.net/en/50_Events/", icon: "🏆"},
    {title: "FAQ", url: "https://litterapp.net/en/90_Faq/", icon: "❓"},
    {title: "Contact", url: "https://litterapp.net/en/90_Contact/", icon: "📧"},
    {title: "Support", url: "https://litterapp.net/en/95_Support/", icon: "💚"},
    {title: "Agenda", url: "https://litterapp.net/grafana/public-dashboards/49da49c1260249cd895d87b2f76ebeea", icon: "📅"},
]

const communityLinks = [
    {title: "TreasureHunt", url: "https://litterapp.net/grafana/public-dashboards/85addd6bdcec4d91942426378ad62289", icon: "🗺️"},
    {title: "Litter Around The World", url: "https://litterapp.net/grafana/public-dashboards/e88a57e063704a53a42d73c8c8aa0e6a", icon: "🌍"},
    {title: "Trashdog", url: "https://litterapp.net/grafana/public-dashboards/46806378a10f499d8fda26d1e8b289be", icon: "🐕"},
    {title: "Scenery", url: "https://litterapp.net/grafana/public-dashboards/b67bea6f6c774a12a96278bdf8f289c3", icon: "🏞️"},
    {title: "Special find", url: "https://litterapp.net/grafana/public-dashboards/a51f135fc9d3422a9dd14200c231fe1e", icon: "💎"},
]

const cleanUpLinks = [
    {title: "Before and After", url: "https://litterapp.net/grafana/public-dashboards/51397ab45e954d53952467bf626e80cf", icon: "📸"},
    {title: "Plastic Avengers", url: "https://litterapp.net/grafana/public-dashboards/bf41908e0bbf45c0a2e9de2d6a24e09c", icon: "🦸"},
    {title: "WCD", url: "https://litterapp.net/grafana/public-dashboards/2f664f62876e4fd79c28e6965ed74369", icon: "🌊"},
    {title: "LOD", url: "https://litterapp.net/grafana/public-dashboards/bfa3693a7c0c405caae3097b062a0818", icon: "🧹"},
    {title: "Peukmeuk", url: "https://litterapp.net/grafana/public-dashboards/e67f80e9859345d1ad3cb9c77ecdebf5", icon: "🚬"},
    {title: "Zaanse Estafette", url: "https://litterapp.net/grafana/public-dashboards/9cd53e77f5da4e9b886c1b6838a39874", icon: "🏃"},
    {title: "Zwerfafval Ruimers Assendelft", url: "https://litterapp.net/grafana/public-dashboards/8795c66e4b9a4cc19d5049fa5ebf8a44", icon: "🧤"},
    {title: "Zaanse Schans", url: "https://litterapp.net/grafana/public-dashboards/effd0584398b417cac7b6ba501156be8", icon: "🏘️"},
    {title: "NZK", url: "https://litterapp.net/grafana/public-dashboards/6af5426fe8ed482dbd7275cbecf064be", icon: "🗑️"},
    {title: "Duurzaamheidscafe de Saen", url: "https://litterapp.net/grafana/public-dashboards/4ebd114de579407b9d36a99f595d17b6", icon: "☕"},
    {title: "Noorder-Eiland", url: "https://litterapp.net/grafana/public-dashboards/545755e418dd43659c0f16a0a1450c15", icon: "🏝️"},
    {title: "Zaandewandel", url: "https://litterapp.net/grafana/public-dashboards/ef09b39b4bcd4c22899f795658a7873e", icon: "🚶"},
    {title: "ShowYourImpact", url: "https://litterapp.net/grafana/public-dashboards/3fc460b04e2a453ba323c4fdacbae792", icon: "📊"},
]

const specialItems = [
    {title: "TimHortons", url: "https://litterapp.net/grafana/public-dashboards/17cd2fbfba8e4c6092d00d85d4497bed", icon: "☕"},
    {title: "Statiegeldalliantie", url: "https://litterapp.net/grafana/public-dashboards/8358d80789a84f3fa7ca777f54ec5f70", icon: "🥤"},
    {title: "Styrofoam", url: "https://litterapp.net/grafana/public-dashboards/0c1f944bf895422386dfa8885303dfbc", icon: "📦"},
    {title: "BermBende", url: "https://litterapp.net/grafana/public-dashboards/d75e295d8c4d4d2387f944e354ce8f6c", icon: "🛣️"},
    {title: "VapePens", url: "https://litterapp.net/grafana/public-dashboards/aff35d8e2ad64018a472407b0ca1220d", icon: "💨"},
    {title: "Car Debris", url: "https://litterapp.net/grafana/public-dashboards/fc67df2742a34a089732e9d131b147ac", icon: "🚗"},
    {title: "Diving Litter", url: "https://litterapp.net/grafana/public-dashboards/b8b156acc131418386e258e749854e9f", icon: "🤿"},
    {title: "Lighters", url: "https://litterapp.net/grafana/public-dashboards/1d3a6f42efab4db490522703be08009d", icon: "🔥"},
    {title: "TieWraps", url: "https://litterapp.net/grafana/public-dashboards/9c89a67a8f1647009c870b912a7b0afc", icon: "🔗"},
    {title: "Esso", url: "https://litterapp.net/grafana/public-dashboards/0537cdd165e544f68d47e398da330e47", icon: "⛽"},
    {title: "Mars", url: "https://litterapp.net/grafana/public-dashboards/0a326840431e4ae39844bc61feb0a9bd", icon: "🍫"},
]


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

                <div class="pt-32">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <LinkTile
                            v-for="link in links"
                            :key="link.url"
                            :title="link.title"
                            :url="link.url"
                            :icon="link.icon"
                        />
                    </div>

                    <div class="pt-12">
                        <div class="text-center text-xl font-black tracking-widest dark:text-mainWhite text-darkBlue pb-6">
                            Fun
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <LinkTile
                                v-for="link in communityLinks"
                                :key="link.url"
                                :title="link.title"
                                :url="link.url"
                                :icon="link.icon"
                            />
                        </div>
                    </div>

                    <div class="pt-12">
                        <div class="text-center text-xl font-black tracking-widest dark:text-mainWhite text-darkBlue pb-6">
                            Clean-Up
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <LinkTile
                                v-for="link in cleanUpLinks"
                                :key="link.url"
                                :title="link.title"
                                :url="link.url"
                                :icon="link.icon"
                            />
                        </div>
                    </div>

                    <div class="pt-12">
                        <div class="text-center text-xl font-black tracking-widest dark:text-mainWhite text-darkBlue pb-6">
                            Special Items
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <LinkTile
                                v-for="link in specialItems"
                                :key="link.url"
                                :title="link.title"
                                :url="link.url"
                                :icon="link.icon"
                            />
                        </div>
                    </div>
                </div>

                <div class="pt-32 w-full">
                    <iframe class="w-full"
                            :src="grafanaLink"
                            width="450"
                            height="750"
                            frameborder="0"
                    >
                    </iframe>
                </div>

                <div class="flex flex-col items-center pt-24 pb-12 md:pt-32">
                    <div class="h-2 w-2 bg-darkBlue dark:bg-mainWhite rounded-full"></div>
                    <div
                        class="text-center text-2xl leading-8 font-black tracking-widest-xl dark:text-mainWhite text-darkBlue pt-8">
                        A common procedure
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
                    href="https://join.slack.com/t/helpingearthtogether/shared_invite/zt-2ykmkvdvk-hI94GonH46xrWN9NBhp_vA"
                    target="_blank"
                >
                    <button
                        class="whitespace-nowrap inline-flex items-center px-4 py-3 bg-turqoFocus border border-transparent rounded-md font-black text-sm tracking-widest-xl text-darkBlue hover:bg-turqo focus:bg-turqoFocus active:bg-turqoFocus focus:outline-none focus:ring-2 focus:ring-turqo focus:ring-offset-2 transition ease-in-out duration-150 uppercase">
                        Go to Litterapp
                    </button>
                </a>
            </div>
        </div>
    </div>


</template>
