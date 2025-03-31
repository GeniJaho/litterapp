<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    grafanaDashboard: String,
});

</script>

<template>
    <AppLayout title="Events">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Events
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
                <div class="w-full">
                    <iframe class="w-full"
                            :src="grafanaDashboard"
                            width="450"
                            height="900"
                            frameborder="0"
                    >
                    </iframe>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
