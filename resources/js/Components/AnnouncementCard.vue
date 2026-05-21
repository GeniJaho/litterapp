<script setup>
import {computed} from "vue";

const props = defineProps({
    announcement: {
        type: Object,
        required: true,
    },
});

const formattedDate = computed(() => {
    if (!props.announcement.published_at) {
        return null;
    }

    return new Date(props.announcement.published_at).toLocaleDateString('en-US', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
});
</script>

<template>
    <article
        class="flex flex-col sm:flex-row min-h-[12rem] rounded-xl overflow-hidden bg-white dark:bg-gray-800 shadow-md hover:shadow-xl transition duration-300"
    >
        <img
            v-if="announcement.image_url"
            :src="announcement.image_url"
            :alt="announcement.title"
            class="w-full h-44 sm:h-auto sm:w-64 sm:flex-shrink-0 object-cover"
            loading="lazy"
        />

        <div class="flex flex-col flex-1 p-6">
            <div
                v-if="formattedDate"
                class="text-xs uppercase tracking-widest text-gray-500 dark:text-gray-400"
            >
                {{ formattedDate }}
            </div>

            <h3 class="mt-2 text-lg font-bold tracking-wider text-darkBlue dark:text-mainWhite">
                {{ announcement.title }}
            </h3>

            <p class="mt-2 text-sm leading-relaxed text-gray-700 dark:text-gray-200 whitespace-pre-line">
                {{ announcement.body }}
            </p>

            <a
                v-if="announcement.link_url"
                :href="announcement.link_url"
                class="mt-4 inline-flex items-center text-sm font-bold tracking-wider text-darkBlue dark:text-turqo hover:underline"
            >
                {{ announcement.link_label || 'Read more' }}
                <i class="fa-solid fa-arrow-right text-xs pl-2"></i>
            </a>
        </div>
    </article>
</template>
