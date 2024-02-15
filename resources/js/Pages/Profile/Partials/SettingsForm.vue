<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import ActionMessage from '@/Components/ActionMessage.vue';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ToggleInput from "@/Components/ToggleInput.vue";

const passwordInput = ref(null);

const props = defineProps({
    user: Object,
});

const form = useForm({
    picked_up_by_default: props.user.settings.picked_up_by_default,
    recycled_by_default: props.user.settings.recycled_by_default,
    deposit_by_default: props.user.settings.deposit_by_default,
});


const save = () => {
    form.post(route('user-settings.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <FormSection @submitted="save">
        <template #title>
            Settings
        </template>

        <template #description>
            General settings related to tagging, uploads, etc.
        </template>

        <template #form>
            <div class="col-span-6 sm:col-span-4">
                <ToggleInput
                    id="picked_up_by_default"
                    v-model="form.picked_up_by_default"
                    class="mt-1 block w-full"
                >
                    <template #label>
                        Litter is picked up by default
                    </template>
                    <template #description>
                        When enabled, litter objects in your photos will be marked as picked up by default.
                        You can always change the status of each object individually.
                    </template>
                </ToggleInput>
                <InputError :message="form.errors.picked_up_by_default" class="mt-2" />

                <ToggleInput
                    id="recycled_by_default"
                    v-model="form.recycled_by_default"
                    class="mt-4 block w-full"
                >
                    <template #label>
                        Litter is recycled by default
                    </template>
                    <template #description>
                        When enabled, litter objects in your photos will be marked as recycled by default.
                        You can always change the status of each object individually.
                    </template>
                </ToggleInput>
                <InputError :message="form.errors.recycled_by_default" class="mt-2" />

                <ToggleInput
                    id="deposit_by_default"
                    v-model="form.deposit_by_default"
                    class="mt-4 block w-full"
                >
                    <template #label>
                        Litter has deposit by default
                    </template>
                    <template #description>
                        When enabled, litter objects in your photos will be marked as having deposit by default.
                        You can always change the status of each object individually.
                    </template>
                </ToggleInput>
                <InputError :message="form.errors.deposit_by_default" class="mt-2" />

            </div>
        </template>

        <template #actions>
            <ActionMessage :on="form.recentlySuccessful" class="mr-3">
                Saved.
            </ActionMessage>

            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Save
            </PrimaryButton>
        </template>
    </FormSection>
</template>
