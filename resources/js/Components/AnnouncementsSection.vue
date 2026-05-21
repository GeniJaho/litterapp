<script setup>
import {Link} from "@inertiajs/vue3";
import {ref, watch} from "vue";
import AnnouncementCard from "@/Components/AnnouncementCard.vue";

defineProps({
    announcements: {
        type: Array,
        required: true,
    },
});

const stored = localStorage.getItem('showAnnouncements');
const showAnnouncements = ref(stored === null ? true : stored === 'true');

watch(showAnnouncements, (value) => {
    localStorage.setItem('showAnnouncements', value ? 'true' : 'false');
});
</script>

<template>
    <section v-if="announcements.length" class="pt-12">
        <div class="flex justify-end">
            <button
                type="button"
                @click="showAnnouncements = !showAnnouncements"
                class="text-sm font-bold tracking-wider text-darkBlue dark:text-mainWhite hover:underline"
            >
                {{ showAnnouncements ? 'Hide latest announcements' : 'Show latest announcements' }}
            </button>
        </div>

        <div v-if="showAnnouncements" class="mt-6">
            <div class="flex flex-col gap-6">
                <AnnouncementCard
                    v-for="announcement in announcements"
                    :key="announcement.id"
                    :announcement="announcement"
                />
            </div>

            <div class="mt-6 flex justify-end">
                <Link
                    :href="route('announcements')"
                    class="inline-flex items-center text-sm font-bold tracking-wider text-darkBlue dark:text-mainWhite hover:underline"
                >
                    See all announcements
                    <i class="fa-solid fa-arrow-right text-xs pl-2"></i>
                </Link>
            </div>
        </div>
    </section>
</template>
