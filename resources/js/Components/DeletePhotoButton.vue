<script setup>
import IconDangerButton from "@/Components/IconDangerButton.vue";
import {ref} from "vue";

const deletingPhoto = ref(false);
const disabled = ref(false);

const emit = defineEmits(['delete']);

const deletePhoto = () => {
    disabled.value = true;
    emit('delete');
};

</script>

<template>
    <IconDangerButton @click="deletingPhoto = ! deletingPhoto" :disabled="disabled">
        <div class="relative">
            <i class="fas fa-fw fa-trash-alt text-xs"></i>
            <div v-if="deletingPhoto"
                 class="absolute right-[2.2rem] top-1/2 transform -translate-y-1/2 bg-white rounded-lg flex flex-row overflow-hidden"
            >
                <span class="p-2 hover:bg-gray-200" @click="deletePhoto">
                    <i class="fas fa-fw fa-check text-sm text-green-500"></i>
                </span>
                <span class="p-2 hover:bg-gray-200">
                    <i class="fas fa-fw fa-xmark text-sm text-red-700"></i>
                </span>
            </div>

        </div>
    </IconDangerButton>
</template>
