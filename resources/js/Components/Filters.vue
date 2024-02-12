<script setup>

import {ref} from "vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TagBox from "@/Components/TagBox.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";

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
    <div class="flex flex-col lg:flex-row lg:space-x-4 w-full px-4 lg:p-0">
        <div class="w-full">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-4">
                <div class="col-span-1 lg:col-span-2">
                    <InputLabel for="item-filter" value="Items" />
                    <TagBox
                        id="item-filter"
                        v-model="selectedItems"
                        :items="items"
                        :multiple="true"
                        class="mt-1 block w-full"
                    ></TagBox>
                </div>
                <div>
                    <InputLabel for="material-filter" value="Materials" />
                    <TagBox
                        id="material-filter"
                        v-model="selectedMaterials"
                        :items="tags.material"
                        :multiple="true"
                        class="mt-1 block w-full"
                    ></TagBox>
                </div>
                <div>
                    <InputLabel for="brand-filter" value="Brands" />
                    <TagBox
                        id="brand-filter"
                        v-model="selectedBrands"
                        :items="tags.brand"
                        :multiple="true"
                        class="mt-1 block w-full"
                    ></TagBox>
                </div>
                <div>
                    <InputLabel for="event-filter" value="Events" />
                    <TagBox
                        id="event-filter"
                        v-model="selectedEvents"
                        :items="tags.event"
                        :multiple="true"
                        class="mt-1 block w-full"
                    ></TagBox>
                </div>
                <div>
                    <InputLabel for="uploaded-from" value="Uploaded From" />
                    <TextInput
                        v-model="filters.uploaded_from"
                        type="datetime-local"
                        id="uploaded-from"
                        class="mt-1 block w-full"
                    />
                </div>
                <div>
                    <InputLabel for="uploaded-until" value="Uploaded Until" />
                    <TextInput
                        v-model="filters.uploaded_until"
                        type="datetime-local"
                        id="uploaded-until"
                        class="mt-1 block w-full"
                    />
                </div>
            </div>
        </div>

        <div class="mt-4 lg:mt-0 flex flex-row lg:flex-col gap-4 items-center justify-center">
            <PrimaryButton @click="clear">Clear</PrimaryButton>
            <PrimaryButton @click="filter">
                <i class="fa-solid fa-filter mr-2 "></i>
                Filter
            </PrimaryButton>
        </div>
    </div>
</template>
