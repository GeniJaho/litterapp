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

const selectedMaterialTag = ref(null);
const selectedBrandTag = ref(null);
const selectedEventTag = ref(null);
const selectedStateTag = ref(null);
const selectedContentTag = ref(null);
const selectedSizeTag = ref(null);

const removeMaterialTag = () => {
    if (!selectedMaterialTag.value) return;
    emit("tag-selected", selectedMaterialTag.value);
    selectedMaterialTag.value = null;
};

const removeBrandTag = () => {
    if (!selectedBrandTag.value) return;
    emit("tag-selected", selectedBrandTag.value);
    selectedBrandTag.value = null;
};

const removeEventTag = () => {
    if (!selectedEventTag.value) return;
    emit("tag-selected", selectedEventTag.value);
    selectedEventTag.value = null;
};

const removeStateTag = () => {
    if (!selectedStateTag.value) return;
    emit("tag-selected", selectedStateTag.value);
    selectedStateTag.value = null;
};

const removeContentTag = () => {
    if (!selectedContentTag.value) return;
    emit("tag-selected", selectedContentTag.value);
    selectedContentTag.value = null;
};

const removeSizeTag = () => {
    if (!selectedSizeTag.value) return;
    emit("tag-selected", selectedSizeTag.value);
    selectedSizeTag.value = null;
};

</script>

<template>
    <div class="mt-8 space-y-2">
        <div class="flex">
            <TagBox
                class="w-full md:w-96"
                :items="tags.material"
                :nullable="true"
                placeholder="Material"
                v-model="selectedMaterialTag"
            ></TagBox>
            <div class="ml-4 mt-0.5">
                <PrimaryButton
                    class="whitespace-nowrap"
                    :disabled="!selectedMaterialTag"
                    @click="removeMaterialTag"
                >
                    Remove
                </PrimaryButton>
            </div>
        </div>

        <div class="flex">
            <TagBox
                class="w-full md:w-96"
                :items="tags.brand"
                :nullable="true"
                placeholder="Brand"
                v-model="selectedBrandTag"
            ></TagBox>
            <div class="ml-4 mt-0.5">
                <PrimaryButton
                    class="whitespace-nowrap"
                    :disabled="!selectedBrandTag"
                    @click="removeBrandTag"
                >
                    Remove
                </PrimaryButton>
            </div>
        </div>

        <div class="flex">
            <TagBox
                class="w-full md:w-96"
                :items="tags.content"
                :nullable="true"
                placeholder="Content"
                v-model="selectedContentTag"
            ></TagBox>
            <div class="ml-4 mt-0.5">
                <PrimaryButton
                    class="whitespace-nowrap"
                    :disabled="!selectedContentTag"
                    @click="removeContentTag"
                >
                    Remove
                </PrimaryButton>
            </div>
        </div>

        <div class="flex">
            <TagBox
                class="w-full md:w-96"
                :items="tags.size"
                :nullable="true"
                placeholder="Size"
                v-model="selectedSizeTag"
            ></TagBox>
            <div class="ml-4 mt-0.5">
                <PrimaryButton
                    class="whitespace-nowrap"
                    :disabled="!selectedSizeTag"
                    @click="removeSizeTag"
                >
                    Remove
                </PrimaryButton>
            </div>
        </div>

        <div class="flex">
            <TagBox
                class="w-full md:w-96"
                :items="tags.state"
                :nullable="true"
                placeholder="State"
                v-model="selectedStateTag"
            ></TagBox>
            <div class="ml-4 mt-0.5">
                <PrimaryButton
                    class="whitespace-nowrap"
                    :disabled="!selectedStateTag"
                    @click="removeStateTag"
                >
                    Remove
                </PrimaryButton>
            </div>
        </div>

        <div class="flex">
            <TagBox
                class="w-full md:w-96"
                :items="tags.event"
                :nullable="true"
                placeholder="Event"
                v-model="selectedEventTag"
            ></TagBox>
            <div class="ml-4 mt-0.5">
                <PrimaryButton
                    class="whitespace-nowrap"
                    :disabled="!selectedEventTag"
                    @click="removeEventTag"
                >
                    Remove
                </PrimaryButton>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
