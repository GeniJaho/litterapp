<script setup>

import {ref} from "vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TagBox from "@/Components/TagBox.vue";

const props = defineProps({
    tags: Object,
    items: Array,
    defaultFilters: {
        type: Object,
        required: false,
    }
});

const filters = ref({
    item_ids: props.defaultFilters?.item_ids ?? [],
    material_ids: props.defaultFilters?.material_ids ?? [],
    brand_ids: props.defaultFilters?.brand_ids ?? [],
    event_ids: props.defaultFilters?.event_ids ?? [],
    uploaded_from: props.defaultFilters?.uploaded_from ?? null,
    uploaded_until: props.defaultFilters?.uploaded_until ?? null,
});

const selectedItems = ref(props.defaultFilters?.item_ids.map(id => props.items.find(item => item.id === parseInt(id))) ?? []);
const selectedMaterials = ref(props.defaultFilters?.material_ids.map(id => props.tags.material.find(material => material.id === parseInt(id))) ?? []);
const selectedBrands = ref(props.defaultFilters?.brand_ids.map(id => props.tags.brand.find(brand => brand.id === parseInt(id))) ?? []);
const selectedEvents = ref(props.defaultFilters?.event_ids.map(id => props.tags.event.find(event => event.id === parseInt(id))) ?? []);

const emit = defineEmits(['change']);

const filter = () => {
    filters.value.item_ids = selectedItems.value.map(item => item.id);
    filters.value.material_ids = selectedMaterials.value.map(material => material.id);
    filters.value.brand_ids = selectedBrands.value.map(brand => brand.id);
    filters.value.event_ids = selectedEvents.value.map(event => event.id);
    emit('change', filters.value);
}

const clear = () => {
    selectedItems.value = [];
    selectedMaterials.value = [];
    selectedBrands.value = [];
    selectedEvents.value = [];

    filters.value = {
        item_ids: [],
        material_ids: [],
        brand_ids: [],
        event_ids: [],
        uploaded_from: null,
        uploaded_until: null,
    };

    emit('change', filters.value);
}
</script>

<template>
    <div class="flex flex-col lg:flex-row lg:space-x-4 w-full p-4 lg:p-0">
        <div class="mb-8 w-full">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-4">
                <div class="col-span-1 lg:col-span-2">
                    <label for="item-filter" class="block mb-2 text-sm font-medium text-gray-600 dark:text-gray-200">
                        Items
                    </label>
                    <TagBox
                        id="item-filter"
                        v-model="selectedItems"
                        :items="items"
                        :multiple="true"
                        @update:model-value="filter"
                    ></TagBox>
                </div>
                <div>
                    <label for="material-filter" class="block mb-2 text-sm font-medium text-gray-600 dark:text-gray-200">
                        Materials
                    </label>
                    <TagBox
                        id="material-filter"
                        v-model="selectedMaterials"
                        :items="tags.material"
                        :multiple="true"
                        @update:model-value="filter"
                    ></TagBox>
                </div>
                <div>
                    <label for="brand-filter" class="block mb-2 text-sm font-medium text-gray-600 dark:text-gray-200">
                        Brands
                    </label>
                    <TagBox
                        id="brand-filter"
                        v-model="selectedBrands"
                        :items="tags.brand"
                        :multiple="true"
                        @update:model-value="filter"
                    ></TagBox>
                </div>
                <div>
                    <label for="event-filter" class="block mb-2 text-sm font-medium text-gray-600 dark:text-gray-200">
                        Events
                    </label>
                    <TagBox
                        id="event-filter"
                        v-model="selectedEvents"
                        :items="tags.event"
                        :multiple="true"
                        @update:model-value="filter"
                    ></TagBox>
                </div>
                <div>
                    <label for="uploaded-from" class="block mb-2 text-sm font-medium text-gray-600 dark:text-gray-200">
                        Uploaded From
                    </label>
                    <input
                        v-model="filters.uploaded_from"
                        @change="filter"
                        type="datetime-local"
                        name="uploaded_from" id="uploaded-from"
                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                    />
                </div>
                <div>
                    <label for="uploaded-until" class="block mb-2 text-sm font-medium text-gray-600 dark:text-gray-200">
                        Uploaded Until
                    </label>
                    <input
                        v-model="filters.uploaded_until"
                        @change="filter"
                        type="datetime-local"
                        name="uploaded_until" id="uploaded-until"
                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                    />
                </div>
            </div>
        </div>

        <div class="flex-1 flex items-center justify-center">
            <PrimaryButton @click="clear">Clear</PrimaryButton>
        </div>
    </div>
</template>
