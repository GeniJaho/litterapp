<script setup>
</script>

<template>
    <div>
        <div class="relative mt-10">
            <div class="absolute inset-0 flex items-center" aria-hidden="true">
                <div class="w-full border-t border-gray-200"></div>
            </div>
            <div class="relative flex justify-center text-sm font-medium leading-6">
                <span class="bg-white dark:bg-gray-800 px-6 text-gray-900 dark:text-gray-200">Or continue with</span>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-2 gap-4">
            <a :href="route('auth.google.redirect')"
               class="flex w-full items-center justify-center gap-3 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-xs ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus-visible:ring-transparent">
                <i class="fab fa-google text-lg"></i>
                <span class="text-sm font-semibold leading-6">Google</span>
            </a>

            <a :href="route('auth.github.redirect')"
               class="flex w-full items-center justify-center gap-3 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-xs ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus-visible:ring-transparent">
                <i class="fab fa-github text-lg"></i>
                <span class="text-sm font-semibold leading-6">GitHub</span>
            </a>
        </div>
    </div>
</template>
