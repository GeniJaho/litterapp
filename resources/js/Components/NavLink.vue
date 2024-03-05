<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: String,
    active: Boolean,
    externalLink: {
        type: Boolean,
        default: false,
    },
});

const classes = computed(() => {
    return props.active
        ? 'inline-flex items-center px-1 pt-1 border-b-4 border-darkBlue dark:border-mainWhite text-sm tracking-widest font-semibold leading-4 text-darkBlue focus:outline-none focus:border-darkblue transition duration-150 ease-in-out'
        : 'inline-flex items-center px-1 pt-1 border-b-4 border-transparent text-sm tracking-widest font-semibold leading-4 text-darkBlue hover:text-gray-700 hover:border-darkBlue hover:dark:border-mainWhite focus:outline-none focus:text-gray-700 focus:border-darkBlue focus:dark:border-white transition duration-150 ease-in-out';
});
</script>

<template>
    <a v-if="externalLink" :href="href" :class="classes">
        <slot />
    </a>
    <Link v-else :href="href" :class="classes">
        <slot />
    </Link>
</template>
