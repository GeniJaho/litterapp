<script setup>

import {ref} from "vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TagBox from "@/Components/TagBox.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import SelectInput from "@/Components/SelectInput.vue";

const props = defineProps({
    tags: Object,
    items: Array,
    defaultFilters: {
        type: Object,
        required: false,
    }
});

const yesOrNoOptions = [
    {label: '-', value: null},
    {label: 'Yes', value: true},
    {label: 'No', value: false},
];

const filters = ref({
    item_ids: props.defaultFilters?.item_ids ?? [],
    tag_ids: props.defaultFilters?.tag_ids ?? [],
    uploaded_from: props.defaultFilters?.uploaded_from ?? null,
    uploaded_until: props.defaultFilters?.uploaded_until ?? null,
    taken_from_local: props.defaultFilters?.taken_from_local ?? null,
    taken_until_local: props.defaultFilters?.taken_until_local ?? null,
    has_gps: props.defaultFilters?.has_gps ?? null,
    is_tagged: props.defaultFilters?.is_tagged ?? null,
});

const selectedItems = ref(props.defaultFilters?.item_ids.map(id => props.items.find(item => item.id === parseInt(id))) ?? []);
const selectedMaterials = ref(props.tags.material.filter(material => filters.value.tag_ids.includes(material.id)));
const selectedBrands = ref(props.tags.brand.filter(brand => filters.value.tag_ids.includes(brand.id)));
const selectedEvents = ref(props.tags.event.filter(event => filters.value.tag_ids.includes(event.id)));
const selectedStates = ref(props.tags.state.filter(state => filters.value.tag_ids.includes(state.id)));
const selectedContents = ref(props.tags.content.filter(content => filters.value.tag_ids.includes(content.id)));
const selectedSizes = ref(props.tags.size.filter(size => filters.value.tag_ids.includes(size.id)));
const hasGPS = ref(yesOrNoOptions.find(option => option.value === filters.value.has_gps));
const isTagged = ref(yesOrNoOptions.find(option => option.value === filters.value.is_tagged));

const emit = defineEmits(['change']);

const filter = () => {
    filters.value.item_ids = selectedItems.value.map(item => item.id);
    filters.value.tag_ids = [
        ...selectedMaterials.value.map(material => material.id),
        ...selectedBrands.value.map(brand => brand.id),
        ...selectedEvents.value.map(event => event.id),
        ...selectedStates.value.map(state => state.id),
        ...selectedContents.value.map(content => content.id),
        ...selectedSizes.value.map(size => size.id),
    ];
    filters.value.has_gps = hasGPS.value.value !== null ? (hasGPS.value.value === true ? 1 : 0) : null;
    filters.value.is_tagged = isTagged.value.value !== null ? (isTagged.value.value === true ? 1 : 0) : null;
    emit('change', {...filters.value, store_filters: 1});
}

const clear = () => {
    selectedItems.value = [];
    selectedMaterials.value = [];
    selectedBrands.value = [];
    selectedEvents.value = [];
    selectedStates.value = [];
    selectedContents.value = [];
    selectedSizes.value = [];
    hasGPS.value = yesOrNoOptions[0];
    isTagged.value = yesOrNoOptions[0];

    filters.value = {
        item_ids: [],
        tag_ids: [],
        uploaded_from: null,
        uploaded_until: null,
        taken_from_local: null,
        taken_until_local: null,
        has_gps: null,
        is_tagged: null,
    };

    emit('change', {...filters.value, clear_filters: 1});
}
</script>

<template>
    <div class="flex flex-col lg:flex-row lg:space-x-4 w-full px-4 sm:p-0">
        <div class="w-full">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
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
                    <InputLabel for="state-filter" value="States" />
                    <TagBox
                        id="state-filter"
                        v-model="selectedStates"
                        :items="tags.state"
                        :multiple="true"
                        class="mt-1 block w-full"
                    ></TagBox>
                </div>
                <div>
                    <InputLabel for="content-filter" value="Contents" />
                    <TagBox
                        id="content-filter"
                        v-model="selectedContents"
                        :items="tags.content"
                        :multiple="true"
                        class="mt-1 block w-full"
                    ></TagBox>
                </div>
                <div>
                    <InputLabel for="size-filter" value="Sizes" />
                    <TagBox
                        id="size-filter"
                        v-model="selectedSizes"
                        :items="tags.size"
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
                        class="mt-1 block w-full dark:[color-scheme:dark]"
                    />
                </div>
                <div>
                    <InputLabel for="uploaded-until" value="Uploaded Until" />
                    <TextInput
                        v-model="filters.uploaded_until"
                        type="datetime-local"
                        id="uploaded-until"
                        class="mt-1 block w-full dark:[color-scheme:dark]"
                    />
                </div>
                <div>
                    <InputLabel for="taken-from-local" value="Date taken from (local)" />
                    <TextInput
                        v-model="filters.taken_from_local"
                        type="datetime-local"
                        id="taken-from-local"
                        class="mt-1 block w-full dark:[color-scheme:dark]"
                    />
                </div>
                <div>
                    <InputLabel for="taken-until-local" value="Date taken until (local)" />
                    <TextInput
                        v-model="filters.taken_until_local"
                        type="datetime-local"
                        id="taken-until-local"
                        class="mt-1 block w-full dark:[color-scheme:dark]"
                    />
                </div>
                <div>
                    <InputLabel for="has-gps" value="Has GPS" />
                    <SelectInput
                        v-model="hasGPS"
                        :options="yesOrNoOptions"
                        id="has-gps"
                        class="block w-full mt-1"
                    ></SelectInput>
                </div>
                <div>
                    <InputLabel for="is-tagged" value="Is Tagged" />
                    <SelectInput
                        v-model="isTagged"
                        :options="yesOrNoOptions"
                        id="is-tagged"
                        class="block w-full mt-1"
                    ></SelectInput>
                </div>
            </div>
        </div>

        <div class="mt-4 lg:mt-0 flex flex-row lg:flex-col gap-4 items-center justify-center">
            <PrimaryButton @click="clear">Clear</PrimaryButton>
            <PrimaryButton @click="filter">Filter</PrimaryButton>
        </div>
    </div>
</template>
