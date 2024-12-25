<script setup>
import {computed, ref} from "vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import IconPrimaryButton from "@/Components/IconPrimaryButton.vue";
import ToggleInput from "@/Components/ToggleInput.vue";
import IconDangerButton from "@/Components/IconDangerButton.vue";
import TextInput from "@/Components/TextInput.vue";
import TagBox from "@/Components/TagBox.vue";

const props = defineProps({
    tags: Object,
});

const emit = defineEmits(['tag-selected']);

const selectedTagIds = computed(() => {
    return [
        selectedMaterialTag.value?.id,
        selectedBrandTag.value?.id,
        selectedEventTag.value?.id,
        selectedStateTag.value?.id,
        selectedContentTag.value?.id,
        selectedSizeTag.value?.id,
    ].filter(tag => tag);
});

const selectedMaterialTag = ref(null);
const selectedBrandTag = ref(null);
const selectedEventTag = ref(null);
const selectedStateTag = ref(null);
const selectedContentTag = ref(null);
const selectedSizeTag = ref(null);

const selectTag = (tag) => {
    if (selectedMaterialTag.value) {
        emit("tag-selected", selectedMaterialTag.value);
    }
    if (selectedBrandTag.value) emit("tag-selected", selectedBrandTag.value);
    if (selectedEventTag.value) emit("tag-selected", selectedEventTag.value);
    if (selectedStateTag.value) emit("tag-selected", selectedStateTag.value);
    if (selectedContentTag.value) emit("tag-selected", selectedContentTag.value);
    if (selectedSizeTag.value) emit("tag-selected", selectedSizeTag.value);

    selectedMaterialTag.value = null;
    selectedBrandTag.value = null;
    selectedEventTag.value = null;
    selectedStateTag.value = null;
    selectedContentTag.value = null;
    selectedSizeTag.value = null;
};

</script>

<template>
    <li class="col-span-1 rounded-lg bg-white dark:bg-gray-800 shadow">
        <div class="px-4 py-5 sm:p-6 flex-1">
            <div>
                <div class="space-y-2">
                    <TagBox
                        :items="tags.material"
                        :nullable="true"
                        placeholder="Material"
                        v-model="selectedMaterialTag"
                        @change="selectTag"
                    ></TagBox>

                    <TagBox
                        :items="tags.brand"
                        :nullable="true"
                        placeholder="Brand"
                        v-model="selectedBrandTag"
                        @change="selectTag"
                    ></TagBox>

                    <TagBox
                        :items="tags.content"
                        :nullable="true"
                        placeholder="Content"
                        v-model="selectedContentTag"
                        @change="selectTag"
                    ></TagBox>

                    <TagBox
                        :items="tags.size"
                        :nullable="true"
                        placeholder="Size"
                        v-model="selectedSizeTag"
                        @change="selectTag"
                    ></TagBox>

                    <TagBox
                        :items="tags.state"
                        :nullable="true"
                        placeholder="State"
                        v-model="selectedStateTag"
                        @change="selectTag"
                    ></TagBox>

                    <TagBox
                        :items="tags.event"
                        :nullable="true"
                        placeholder="Event"
                        v-model="selectedEventTag"
                        @change="selectTag"
                    ></TagBox>
                </div>

                <div v-if="selectedTagIds.length" class="mt-4 flex justify-center">
                    <PrimaryButton
                        class="whitespace-nowrap"
                        @click="selectTag"
                    >
                        Add Selected Tags
                    </PrimaryButton>
                </div>
            </div>
        </div>
    </li>
</template>

<style scoped>

</style>
