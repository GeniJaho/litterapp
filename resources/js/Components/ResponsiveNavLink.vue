<script setup>
import {computed} from 'vue';
import {Link} from '@inertiajs/vue3';

const props = defineProps({
    active: Boolean,
    href: String,
    as: String,
    target: {
        type: String,
        default: '_self',
    },
});

const classes = computed(() => {
    return props.active
        ? 'block w-full pl-3 pr-4 py-2 border-l-4 border-darkBlue text-left text-base font-medium text-darkBlue bg-mainWhite focus:outline-none focus:text-darkBlue focus:bg-mainWhite focus:border-darkBlue focus:opacity-100 transition duration-150 ease-in-out'
        : 'block w-full pl-3 pr-4 py-2 border-l-4 border-transparent text-left text-base font-medium text-darkBlue hover:text-slate-900 hover:bg-mainwhite hover:border-slate-800 focus:outline-none focus:text-darkBlue focus:bg-mainWhite focus:border-slate-600 transition duration-150 ease-in-out';
});
</script>

<template>
    <div>
        <button v-if="as == 'button'" :class="classes" class="w-full text-left">
            <slot/>
        </button>

        <a v-else-if="as == 'externalLink'"
           :href="href"
           :class="classes"
           :target="target"
        >
            <slot/>
        </a>

        <Link v-else :href="href" :class="classes">
            <slot/>
        </Link>
    </div>
</template>
