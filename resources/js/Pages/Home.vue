<script setup>
import Welcome from "@/Components/Welcome.vue";
import AppLayout from "@/Layouts/AppLayout.vue";

defineProps({
    announcements: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <AppLayout title="Welcome">
        <Welcome :announcements="announcements"/>
    </AppLayout>
</template>
