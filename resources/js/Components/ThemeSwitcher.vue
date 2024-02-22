<script setup>

import {ref, onMounted, watch} from 'vue';
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";

const systemDarkMode = window.matchMedia('(prefers-color-scheme: dark)');
const option = ref(localStorage.getItem('option'));
const isDropdownOpen = ref(false);

const toggleDropdown = () => {
    isDropdownOpen.value = !isDropdownOpen.value;
};

const setOption = (selectedOption) => {
    localStorage.setItem('option', selectedOption);
    option.value = selectedOption
    isDropdownOpen.value = false;
}

const setTheme = () => {
    if (option.value === 'system') {
        window.matchMedia('(prefers-color-scheme: dark)').matches ? toggleDarkClass('dark') : toggleDarkClass('light')
    } else {
        option.value === 'dark' ? toggleDarkClass('dark') : toggleDarkClass('light')
    }
};

const toggleDarkClass = (className) => {
    if (className === 'dark') {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
};

watch(option, setTheme);

onMounted(() => {
    if (!option.value) {
        setOption('system')
    }

    setTheme();

    systemDarkMode.addEventListener('change', (event) => {
        if (option.value === 'system') {
            if (event.matches) {
                toggleDarkClass('dark')
            } else {
                toggleDarkClass('light')
            }
        }
    });
});
</script>

<template>
    <Dropdown align="right" width="36">
        <template #trigger>
            <span class="inline-flex rounded-md">
                <button type="button"
                        @click="toggleDropdown"
                        class="inline-flex items-center p-1 border border-transparent font-medium rounded-md text-darkBlue bg-turqoFocus hover:text-gray-700 focus:outline-none focus:bg-mainWhite active:bg-mainWhite transition ease-in-out duration-150"
                >
                    <i class="far fa-sun h-6 w-6 text-base" v-if="option === 'light'"></i>
                    <i class="far fa-moon h-6 w-6 text-base" v-if="option === 'dark'"></i>
                    <i class="fas fa-desktop h-6 w-6 text-base" v-if="option === 'system'"></i>
                </button>
            </span>
        </template>

        <template #content>
            <form @submit.prevent="setOption('light')">
                <DropdownLink as="button">
                    <div class="flex items-center">
                        <i class="far fa-fw fa-sun h-5 w-5 mr-2 mt-1"></i>
                        Light
                    </div>
                </DropdownLink>
            </form>

            <form @submit.prevent="setOption('dark')">
                <DropdownLink as="button">
                    <div class="flex items-center">
                        <i class="far fa-fw fa-moon h-5 w-5 mr-2 mt-1"></i>
                        Dark
                    </div>
                </DropdownLink>
            </form>

            <form @submit.prevent="setOption('system')">
                <DropdownLink as="button">
                    <div class="flex items-center">
                        <i class="fas fa-fw fa-desktop h-5 w-5 mr-2 mt-1"></i>
                        System
                    </div>
                </DropdownLink>
            </form>
        </template>
    </Dropdown>
</template>
