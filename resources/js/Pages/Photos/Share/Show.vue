<script setup>
import { computed, Head, Link } from '@inertiajs/vue3';
import TaggedIcon from '@/Components/TaggedIcon.vue';

const props = defineProps({
    photo: Object,
});

const formatDate = (date) => {
    if (!date) return null;
    return new Date(date).toLocaleDateString('en-US', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};

const googleMapsUrl = computed(() => {
    if (!props.photo.latitude || !props.photo.longitude) return null;
    return `https://www.google.com/maps?q=${props.photo.latitude},${props.photo.longitude}`;
});
</script>

<template>
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <Head title="Shared photo - LitterApp" />

        <div class="py-10">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 shadow-xl rounded-lg overflow-hidden">
                    <div class="relative">
                        <img
                            :src="photo.full_path"
                            :alt="photo.original_file_name"
                            class="w-full h-auto max-h-[600px] object-contain bg-gray-900"
                        />
                    </div>

                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <img
                                    v-if="photo.user?.profile_photo_url"
                                    :src="photo.user.profile_photo_url"
                                    :alt="photo.user.name"
                                    class="h-10 w-10 rounded-full object-cover"
                                />
                                <div
                                    v-else
                                    class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center"
                                >
                                    <span class="text-lg font-medium text-gray-700">
                                        {{ photo.user?.name?.charAt(0).toUpperCase() }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ photo.user?.name }}
                                    </p>
                                    <p v-if="photo.taken_at_local" class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ formatDate(photo.taken_at_local) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div v-if="photo.photo_items?.length" class="mt-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">
                                Items in this photo ({{ photo.photo_items.length }})
                            </h3>

                            <div class="space-y-3">
                                <div
                                    v-for="(photoItem, index) in photo.photo_items"
                                    :key="index"
                                    class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg"
                                >
                                    <div class="flex items-center space-x-3">
                                        <TaggedIcon class="w-5 h-5" />
                                        <span class="font-medium text-gray-900 dark:text-gray-100">
                                            {{ photoItem.item?.name }}
                                        </span>
                                        <span v-if="photoItem.quantity > 1" class="text-sm text-gray-500">
                                            (x{{ photoItem.quantity }})
                                        </span>
                                    </div>

                                    <div class="flex flex-col items-end gap-2">
                                        <div class="flex items-center gap-1 flex-wrap justify-end">
                                            <span
                                                v-for="(tag, tagIndex) in photoItem.tags"
                                                :key="tagIndex"
                                                class="inline-flex items-center gap-x-1.5 rounded-full px-2 py-1 text-xs font-medium text-gray-900 dark:text-gray-100 ring-1 ring-inset ring-gray-200"
                                            >
                                                {{ tag.name }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <span
                                                v-if="photoItem.picked_up"
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200"
                                            >
                                                Picked up
                                            </span>
                                            <span
                                                v-if="photoItem.recycled"
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200"
                                            >
                                                Recycled
                                            </span>
                                            <span
                                                v-if="photoItem.deposit"
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200"
                                            >
                                                Deposit
                                            </span>
                                </div>
                            </div>

                            <a
                                v-if="googleMapsUrl"
                                :href="googleMapsUrl"
                                target="_blank"
                                class="text-turqoFocus hover:text-turqoFocus/80 text-sm flex items-center gap-1"
                            >
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 0C7.31 0 3.5 3.81 3.5 8.5c0 6.094 7.313 14.313 7.625 14.656l.875.844.875-.844C13.188 22.813 20.5 14.594 20.5 8.5 20.5 3.81 16.69 0 12 0zm0 12c-1.933 0-3.5-1.567-3.5-3.5S10.067 5 12 5s3.5 1.567 3.5 3.5S13.933 12 12 12z"/>
                                </svg>
                                View on Google Maps
                            </a>
                        </div>
                            </div>
                        </div>

                        <div v-if="photo.photo_items?.length" class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                                <span>
                                    {{ photo.photo_items.filter(i => i.picked_up).length }} picked up
                                    /
                                    {{ photo.photo_items.filter(i => i.recycled).length }} recycled
                                </span>
                                <span>
                                    {{ photo.share_view_count }} views
                                    <template v-if="photo.share_expires_at">
                                        &middot; expires {{ formatDate(photo.share_expires_at) }}
                                    </template>
                                </span>
                            </div>
                        </div>

                        <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <Link
                                href="/"
                                class="text-turqoFocus hover:text-turqoFocus/80 font-medium"
                            >
                                &larr; Go to LitterApp
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
