<script setup>
import {computed, ref} from 'vue';
import {Head, Link, router, usePage} from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import ThemeSwitcher from "@/Components/ThemeSwitcher.vue";

defineProps({
    title: String,
});

const showingNavigationDropdown = ref(false);

const page = usePage()

const grafanaLink = computed(() => page.props.grafana.nav_link)
const facebookLink = computed(() => page.props.nav.facebook_link)
const twitterLink = computed(() => page.props.nav.twitter_link)
const isAdmin = computed(() => page.props.auth.user?.is_admin)
const isImpersonating = computed(() => page.props.auth.user?.is_being_impersonated)

const switchToTeam = (team) => {
    router.put(route('current-team.update'), {
        team_id: team.id,
    }, {
        preserveState: false,
    });
};

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div>
        <Head :title="title"/>

        <Banner/>

        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            <div v-if="isImpersonating"
                 class="fixed w-full z-10 bg-gray-900 py-1 xl:py-2 text-center text-turqoFocus"
            >
                Impersonating {{ $page.props.auth.user.name }}.
            </div>
            <nav class="bg-turqoFocus">
                <!-- Primary Navigation Menu -->
                <div class="px-12 py-6 md:pb-0 md:pt-12 lg:px-40">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center mt-6 md:mt-0">
                                <Link :href="route('home')">
                                    <ApplicationMark class="block h-14"/>
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden sm:space-x-8 lg:space-x-24 sm:-my-px ml-12 lg:ml-14 md:flex">
                                <NavLink :href="grafanaLink"
                                         :externalLink="true"
                                         target="_blank"
                                >
                                    Global Data
                                </NavLink>
                                <NavLink v-if="$page.props.auth.user"
                                         :href="route('my-photos')"
                                         :active="route().current('my-photos')"
                                >
                                    My Photos
                                </NavLink>
                                <NavLink v-if="$page.props.auth.user"
                                         :href="route('upload')"
                                         :active="route().current('upload')"
                                >
                                    Upload
                                </NavLink>
                                <NavLink :href="route('docs')"
                                         :active="route().current('docs')"
                                         :externalLink="true"
                                         target="_blank"
                                >
                                    Docs
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden md:flex md:items-center md:ml-6">
                            <div class="ml-3 relative">
                                <a :href="facebookLink"
                                   target="_blank"
                                   class="inline-flex items-center p-1 border border-transparent text-darkBlue bg-turqoFocus hover:text-gray-700 focus:outline-none"
                                >
                                    <i class="fab fa-facebook text-xl"></i>
                                </a>
                            </div>

                            <div class="ml-3 relative">
                                <a :href="twitterLink"
                                   target="_blank"
                                   class="inline-flex items-center p-1 border border-transparent text-darkBlue bg-turqoFocus hover:text-gray-700 focus:outline-none"
                                >
                                    <i class="fab fa-twitter text-xl"></i>
                                </a>
                            </div>

                            <div class="ml-3 relative">
                                <ThemeSwitcher/>
                            </div>

                            <div class="ml-3 relative">
                                <!-- Teams Dropdown -->
                                <Dropdown v-if="$page.props.jetstream.hasTeamFeatures" align="right" width="64">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button type="button"
                                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:bg-gray-50 dark:focus:bg-gray-700 active:bg-gray-50 dark:active:bg-gray-700 transition ease-in-out duration-150">
                                                {{ $page.props.auth.user.current_team?.name ?? 'My Teams' }}

                                                <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                     fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                                     stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"/>
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <div class="w-60">
                                            <!-- Team Management -->
                                            <template v-if="$page.props.jetstream.hasTeamFeatures">
                                                <div class="block px-4 py-2 text-xs text-gray-400">
                                                    Manage Team
                                                </div>

                                                <!-- Team Settings -->
                                                <DropdownLink v-if="$page.props.auth.user.current_team"
                                                              :href="route('teams.show', $page.props.auth.user.current_team)">
                                                    Team Settings
                                                </DropdownLink>

                                                <DropdownLink v-if="$page.props.jetstream.canCreateTeams"
                                                              :href="route('teams.create')">
                                                    Create New Team
                                                </DropdownLink>

                                                <!-- Team Switcher -->
                                                <template v-if="$page.props.auth.user.all_teams.length > 1">
                                                    <div class="border-t border-gray-200 dark:border-gray-600"/>

                                                    <div class="block px-4 py-2 text-xs text-gray-400">
                                                        Switch Teams
                                                    </div>

                                                    <template v-for="team in $page.props.auth.user.all_teams"
                                                              :key="team.id">
                                                        <form @submit.prevent="switchToTeam(team)">
                                                            <DropdownLink as="button">
                                                                <div class="flex items-center">
                                                                    <svg
                                                                        v-if="team.id == $page.props.auth.user.current_team_id"
                                                                        class="mr-2 h-5 w-5 text-green-400"
                                                                        xmlns="http://www.w3.org/2000/svg"
                                                                        fill="none" viewBox="0 0 24 24"
                                                                        stroke-width="1.5" stroke="currentColor">
                                                                        <path stroke-linecap="round"
                                                                              stroke-linejoin="round"
                                                                              d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                                    </svg>

                                                                    <div>{{ team.name }}</div>
                                                                </div>
                                                            </DropdownLink>
                                                        </form>
                                                    </template>
                                                </template>
                                            </template>
                                        </div>
                                    </template>
                                </Dropdown>
                            </div>

                            <!-- Settings Dropdown -->
                            <div v-if="$page.props.auth.user" class="ml-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button v-if="$page.props.jetstream.managesProfilePhotos"
                                                class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                                            <img class="h-8 w-8 rounded-full object-cover"
                                                 :src="$page.props.auth.user.profile_photo_url"
                                                 :alt="$page.props.auth.user.name">
                                        </button>

                                        <span v-else class="inline-flex rounded-md">
                                            <button type="button"
                                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:bg-gray-50 dark:focus:bg-gray-700 active:bg-gray-50 dark:active:bg-gray-700 transition ease-in-out duration-150">
                                                {{ $page.props.auth.user.name }}

                                                <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                     fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                                     stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <!-- Account Management -->
                                        <div class="block px-4 py-2 text-xs text-gray-400">
                                            Manage Account
                                        </div>

                                        <DropdownLink :href="route('profile.show')">
                                            Profile
                                        </DropdownLink>

                                        <DropdownLink :href="route('tag-shortcuts.index')">
                                            Tag Shortcuts
                                        </DropdownLink>

                                        <DropdownLink v-if="$page.props.jetstream.hasApiFeatures"
                                                      :href="route('api-tokens.index')">
                                            API Tokens
                                        </DropdownLink>

                                        <div class="border-t border-gray-200 dark:border-gray-600"/>

                                        <DropdownLink
                                            v-if="isAdmin"
                                            :href="route('filament.admin.pages.dashboard')"
                                            as="a"
                                        >
                                            Admin Panel
                                        </DropdownLink>

                                        <div class="border-t border-gray-200 dark:border-gray-600"/>

                                        <!-- Authentication -->

                                        <DropdownLink
                                            v-if="isImpersonating"
                                            :href="route('impersonate.leave')"
                                            as="a"
                                        >
                                            Leave Impersonation
                                        </DropdownLink>

                                        <form @submit.prevent="logout">
                                            <DropdownLink as="button">
                                                Log Out
                                            </DropdownLink>
                                        </form>
                                    </template>
                                </Dropdown>
                            </div>

                            <div class="ml-3 h-full sm:space-x-8 sm:-my-px md:flex" v-if="! $page.props.auth.user">
                                <NavLink :href="route('login')"
                                         :active="route().current('login')"
                                >
                                    Log in
                                </NavLink>
                                <NavLink :href="route('register')"
                                         :active="route().current('register')"
                                >
                                    Register
                                </NavLink>
                            </div>
                        </div>

                        <div class="-mr-2 flex items-center md:hidden">
                            <ThemeSwitcher/>

                            <!-- Hamburger -->
                            <button
                                class="ml-2 inline-flex items-center justify-center p-1 rounded-md text-darkBlue hover:text-slate-900 hover:bg-mainWhite hover:opacity-75 focus:outline-none focus:bg-mainWhite focus:text-slate-900 transition duration-150 ease-in-out"
                                @click="showingNavigationDropdown = ! showingNavigationDropdown">
                                <svg
                                    class="h-10 w-10 font-black"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{'hidden': showingNavigationDropdown, 'inline-flex': ! showingNavigationDropdown }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{'hidden': ! showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu MOBILEEEEEE-->
                <div :class="{'block': showingNavigationDropdown, 'hidden': ! showingNavigationDropdown}"
                     class="sm:hidden">
                    <div class="pt-2 pb-3 space-y-1">

                        <ResponsiveNavLink :href="grafanaLink"
                                           as="externalLink"
                                           target="_blank"
                        >
                            Global Data
                        </ResponsiveNavLink>

                        <ResponsiveNavLink v-if="$page.props.auth.user"
                                           :href="route('my-photos')"
                                           :active="route().current('my-photos')"
                        >
                            My Photos
                        </ResponsiveNavLink>
                        <ResponsiveNavLink v-if="$page.props.auth.user"
                                           :href="route('upload')"
                                           :active="route().current('upload')"
                        >
                            Upload
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('docs')"
                                           :active="route().current('docs')"
                                           as="externalLink"
                                           target="_blank"
                        >
                            Docs
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
                        <div v-if="! $page.props.auth.user" class="space-y-1">
                            <ResponsiveNavLink :href="route('login')" :active="route().current('login')">
                                Log in
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('register')" :active="route().current('register')">
                                Register
                            </ResponsiveNavLink>
                        </div>

                        <div v-if="$page.props.auth.user" class="flex items-center px-4">
                            <div v-if="$page.props.jetstream.managesProfilePhotos" class="shrink-0 mr-3">
                                <img class="h-10 w-10 rounded-full object-cover"
                                     :src="$page.props.auth.user.profile_photo_url"
                                     :alt="$page.props.auth.user.name">
                            </div>

                            <div>
                                <div class="font-medium text-base text-gray-800">
                                    {{ $page.props.auth.user.name }}
                                </div>
                                <div class="font-medium text-sm text-slate-700">
                                    {{ $page.props.auth.user.email }}
                                </div>
                            </div>
                        </div>

                        <div v-if="$page.props.auth.user" class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.show')" :active="route().current('profile.show')">
                                Profile
                            </ResponsiveNavLink>

                            <ResponsiveNavLink :href="route('tag-shortcuts.index')" :active="route().current('tag-shortcuts.index')">
                                Tag Shortcuts
                            </ResponsiveNavLink>

                            <ResponsiveNavLink v-if="$page.props.jetstream.hasApiFeatures"
                                               :href="route('api-tokens.index')"
                                               :active="route().current('api-tokens.index')">
                                API Tokens
                            </ResponsiveNavLink>

                            <ResponsiveNavLink
                                v-if="isAdmin"
                                :href="route('filament.admin.pages.dashboard')"
                                as="externalLink"
                            >
                                Admin Panel
                            </ResponsiveNavLink>

                            <ResponsiveNavLink
                                v-if="isImpersonating"
                                :href="route('impersonate.leave')"
                                as="externalLink"
                            >
                                Leave Impersonation
                            </ResponsiveNavLink>

                            <!-- Authentication -->
                            <form method="POST" @submit.prevent="logout">
                                <ResponsiveNavLink as="button">
                                    Log Out
                                </ResponsiveNavLink>
                            </form>

                            <!-- Team Management -->
                            <template v-if="$page.props.jetstream.hasTeamFeatures">
                                <div class="border-t border-gray-200 dark:border-gray-600"/>

                                <div class="block px-4 py-2 text-xs text-gray-400">
                                    Manage Team
                                </div>

                                <!-- Team Settings -->
                                <ResponsiveNavLink v-if="$page.props.auth.user.current_team"
                                                   :href="route('teams.show', $page.props.auth.user.current_team)"
                                                   :active="route().current('teams.show')">
                                    Team Settings
                                </ResponsiveNavLink>

                                <ResponsiveNavLink v-if="$page.props.jetstream.canCreateTeams"
                                                   :href="route('teams.create')"
                                                   :active="route().current('teams.create')">
                                    Create New Team
                                </ResponsiveNavLink>

                                <!-- Team Switcher -->
                                <template v-if="$page.props.auth.user.all_teams.length > 1">
                                    <div class="border-t border-gray-200 dark:border-gray-600"/>

                                    <div class="block px-4 py-2 text-xs text-gray-400">
                                        Switch Teams
                                    </div>

                                    <template v-for="team in $page.props.auth.user.all_teams" :key="team.id">
                                        <form @submit.prevent="switchToTeam(team)">
                                            <ResponsiveNavLink as="button">
                                                <div class="flex items-center">
                                                    <svg v-if="team.id == $page.props.auth.user.current_team_id"
                                                         class="mr-2 h-5 w-5 text-green-400"
                                                         xmlns="http://www.w3.org/2000/svg" fill="none"
                                                         viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    <div>{{ team.name }}</div>
                                                </div>
                                            </ResponsiveNavLink>
                                        </form>
                                    </template>
                                </template>
                            </template>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header v-if="$slots.header" class=" dark:bg-darkBlue shadow">
                <div class="max-w-9xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header"/>
                </div>
            </header>

            <!-- Page Content -->
            <main class="">
                <slot/>
            </main>
        </div>
    </div>
</template>
