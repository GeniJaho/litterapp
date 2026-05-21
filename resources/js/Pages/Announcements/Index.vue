<script setup>
import {Link} from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import AnnouncementCard from "@/Components/AnnouncementCard.vue";

defineProps({
    announcements: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <AppLayout title="Announcements">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Announcements
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div v-if="announcements.data.length" class="flex flex-col gap-6 px-4 sm:px-0">
                    <AnnouncementCard
                        v-for="announcement in announcements.data"
                        :key="announcement.id"
                        :announcement="announcement"
                    />
                </div>

                <div v-else class="text-center text-gray-500 dark:text-gray-400 py-24">
                    No announcements yet — check back soon.
                </div>

                <div
                    v-if="announcements.data.length"
                    class="mt-10 flex justify-center space-x-2 px-4 sm:px-0"
                >
                    <template v-for="link in announcements.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            v-html="link.label"
                            :class="`px-4 py-2 rounded ${link.active ? 'bg-blue-500 text-white' : 'bg-white text-blue-500 dark:bg-gray-800 dark:text-white'}`"
                        />
                        <span
                            v-else
                            v-html="link.label"
                            :class="`px-4 py-2 rounded text-gray-400 dark:text-gray-500`"
                        />
                    </template>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
