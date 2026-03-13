<script setup>
defineProps({
    title: {
        type: String,
        required: true
    },
    url: {
        type: String,
        required: true
    },
    icon: {
        type: String,
        default: "♻️"
    }
})
</script>

<template>
    <a
        :href="url"
        target="_blank"
        class="group relative flex flex-col min-h-[140px] rounded-xl overflow-hidden
               shadow-md hover:shadow-xl hover:-translate-y-1 transition duration-300"
        style="background: url('/img/tiles/abstract-waves.svg') center/cover no-repeat"
    >
        <div class="relative flex flex-col flex-1 p-5">
            <div class="flex items-start justify-between">
                <span class="text-3xl" aria-hidden="true">{{ icon }}</span>
                <span class="text-white/30 group-hover:text-turqo transition" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z" />
                        <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z" />
                    </svg>
                </span>
            </div>

            <h3 class="mt-auto pt-3 text-base font-semibold text-white group-hover:text-turqo transition">
                {{ title }}
            </h3>
        </div>
    </a>
</template>
