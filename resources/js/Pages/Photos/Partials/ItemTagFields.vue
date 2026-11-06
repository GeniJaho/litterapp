<script setup>
import {computed, reactive} from "vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TagBox from "@/Components/TagBox.vue";

const props = defineProps({
    tags: Object,
    itemTags: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['add-tags', 'remove-tag']);

const tagTypes = [
    {slug: 'material', placeholder: 'Material'},
    {slug: 'brand', placeholder: 'Brand'},
    {slug: 'content', placeholder: 'Content'},
    {slug: 'size', placeholder: 'Size'},
    {slug: 'state', placeholder: 'State'},
    {slug: 'event', placeholder: 'Event'},
];

const selectedTags = reactive(Object.fromEntries(tagTypes.map(type => [type.slug, null])));

const selectedTagIds = computed(() => tagTypes
    .map(type => selectedTags[type.slug]?.id)
    .filter(tagId => tagId));

const slugByTagTypeId = computed(() => Object.fromEntries(
    Object.entries(props.tags).map(([slug, tags]) => [tags[0]?.tag_type_id, slug])
));

const itemTagsByType = computed(() => Object.fromEntries(tagTypes.map(type => [
    type.slug,
    props.itemTags.filter(tag => slugByTagTypeId.value[tag.tag_type_id] === type.slug),
])));

const addSelectedTags = () => {
    if (! selectedTagIds.value.length) {
        return;
    }

    emit('add-tags', selectedTagIds.value);

    tagTypes.forEach(type => {
        selectedTags[type.slug] = null;
    });
};
</script>

<template>
    <div class="flex flex-col gap-4">
        <div class="flex flex-col gap-2">
            <div v-for="type in tagTypes" :key="type.slug" class="flex flex-col gap-1.5">
                <TagBox
                    :items="tags[type.slug]"
                    :nullable="true"
                    :placeholder="type.placeholder"
                    v-model="selectedTags[type.slug]"
                    @change="addSelectedTags"
                ></TagBox>

                <div v-if="itemTagsByType[type.slug].length" class="flex flex-wrap gap-1">
                    <span
                        v-for="tag in itemTagsByType[type.slug]"
                        :key="tag.id"
                        @click="$emit('remove-tag', tag.id)"
                        class="inline-flex cursor-pointer items-center gap-x-1.5 rounded-full px-2 py-1 text-xs font-medium text-gray-900 dark:text-gray-100 ring-1 ring-inset ring-gray-200"
                    >
                        <svg class="h-1.5 w-1.5 fill-green-500" viewBox="0 0 6 6"
                             aria-hidden="true"><circle cx="3" cy="3" r="3"/></svg>
                        {{ tag.name }}
                    </span>
                </div>
            </div>
        </div>

        <div v-if="selectedTagIds.length" class="flex justify-center">
            <PrimaryButton
                class="whitespace-nowrap"
                @click="addSelectedTags"
            >
                Add Selected Tags
            </PrimaryButton>
        </div>
    </div>
</template>
