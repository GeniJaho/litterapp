<script setup>
import {computed, onMounted, ref} from 'vue'
import {
    Combobox,
    ComboboxButton,
    ComboboxInput,
    ComboboxOption,
    ComboboxOptions,
    TransitionRoot,
} from '@headlessui/vue';
import SimpleTagShortcutItem from "@/Pages/TagShortcuts/Partials/SimpleTagShortcutItem.vue";
import {Link} from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const props = defineProps({
    items: Array,
    modelValue: Object,
    autofocus: Boolean,
    nullable: {
        type: Boolean,
        default: false,
    },
    placeholder: {
        type: String,
        default: '',
    },
    layout: {
        type: String,
        default: 'default',
    },
});

defineEmits(['update:modelValue']);

let query = ref('')

let filteredItems = computed(() => {
    return query.value === ''
        ? props.items
        : props.items.filter((item) =>
            item.shortcut
                .toLowerCase()
                .replace(/\s+/g, '')
                .includes(query.value.toLowerCase().replace(/\s+/g, ''))
        );
})

const input = ref(null);

onMounted(() => {
    if (props.autofocus) {
        setTimeout(() => input.value.el?.focus(), 300)
    }
})
</script>

<template>
    <Combobox
        :modelValue="modelValue"
        @update:modelValue="value => $emit('update:modelValue', value)"
        :nullable="nullable"
        v-slot="{ activeOption }"
        by="id"
    >
            <div class="relative">
                <div class="relative">
                    <ComboboxInput
                        ref="input"
                        class="w-full rounded-md border-0 bg-white dark:bg-gray-900 py-1.5 pl-3 pr-12 text-gray-900 dark:text-gray-300 shadow-xs ring-1 ring-inset ring-gray-300 dark:ring-gray-700 focus:ring-2 focus:ring-inset focus:ring-indigo-500 dark:focus:ring-indigo-600 sm:text-sm sm:leading-6"
                        :displayValue="(item) => item?.shortcut"
                        :placeholder="placeholder"
                        @change="query = $event.target.value"
                        @focus="$event.target.select()"
                        autocomplete="off"
                    />
                    <ComboboxButton
                        class="absolute inset-y-0 right-0 flex items-center pr-2"
                    >
                        <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 3a.75.75 0 01.55.24l3.25 3.5a.75.75 0 11-1.1 1.02L10 4.852 7.3 7.76a.75.75 0 01-1.1-1.02l3.25-3.5A.75.75 0 0110 3zm-3.76 9.2a.75.75 0 011.06.04l2.7 2.908 2.7-2.908a.75.75 0 111.1 1.02l-3.25 3.5a.75.75 0 01-1.1 0l-3.25-3.5a.75.75 0 01.04-1.06z" clip-rule="evenodd" />
                        </svg>
                    </ComboboxButton>
                </div>
                <TransitionRoot
                    leave="transition ease-in duration-100"
                    leaveFrom="opacity-100"
                    leaveTo="opacity-0"
                    @after-leave="query = ''"
                >
                    <ComboboxOptions
                        class="absolute z-10 mt-1 max-h-96 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black/5 focus:outline-hidden sm:text-sm"
                    >
                        <div
                            v-if="filteredItems.length === 0"
                            class="relative cursor-default select-none"
                        >
                            <Link :href="route('tag-shortcuts.index')" class="flex justify-center">
                                <PrimaryButton class="whitespace-nowrap">
                                    Add Shortcut
                                </PrimaryButton>
                            </Link>
                        </div>

                        <ComboboxOption
                            v-for="item in filteredItems"
                            as="template"
                            :key="item.id"
                            :value="item"
                            v-slot="{ selected, active }"
                        >
                            <li
                                class="relative cursor-default select-none py-2 pl-10 pr-4"
                                :class="{
                                  'bg-indigo-600 text-white': active,
                                  'text-gray-900': !active,
                                }"
                            >
                                <span
                                    class="block truncate"
                                    :class="{ 'font-medium': selected, 'font-normal': !selected }"
                                >
                                  {{ item.shortcut }}
                                </span>
                                <span
                                    v-if="selected"
                                    class="absolute inset-y-0 left-0 flex items-center pl-3"
                                    :class="{ 'text-white': active, 'text-indigo-600': !active }"
                                >
                                  <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                                  </svg>
                                </span>
                            </li>
                        </ComboboxOption>
                    </ComboboxOptions>
                </TransitionRoot>

                <div
                    class="fixed md:absolute bottom-0 md:top-0 transform transition-all md:min-w-96 z-10"
                    :class="{
                        'min-w-full md:right-full': layout === 'default',
                        'min-w-64 md:left-full': layout === 'bulk',
                    }"
                    v-if="activeOption"
                >
                    <div
                        class="mb-4 md:mb-0 bg-white/50 dark:bg-gray-800/30 backdrop-blur-xl rounded-lg shadow-xl overflow-hidden"
                        :class="{
                            'mr-8 sm:mr-20 md:mr-4': layout === 'default',
                            'md:ml-4': layout === 'bulk',
                        }"
                    >
                        <div class="px-3 py-4">
                            <div class="grid grid-cols-1 gap-3 max-h-36 md:max-h-144 overflow-y-auto ">
                                <SimpleTagShortcutItem
                                    v-for="item in activeOption.tag_shortcut_items"
                                    :key="item.id"
                                    :item="item"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Combobox>
</template>
