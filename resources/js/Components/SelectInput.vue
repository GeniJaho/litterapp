<script setup>
import { ref } from 'vue'
import {
    Listbox,
    ListboxButton,
    ListboxOptions,
    ListboxOption,
} from '@headlessui/vue'

const selectedPerson = ref(null)
</script>

<template>
    <Listbox v-model="selectedPerson">
        <ListboxButton>{{ selectedPerson.name }}</ListboxButton>
        <ListboxOptions>
            <ListboxOption
                v-for="person in people"
                :key="person.id"
                :value="person"
                :disabled="person.unavailable"
            >
                {{ person.name }}
            </ListboxOption>
        </ListboxOptions>
    </Listbox>
</template>
