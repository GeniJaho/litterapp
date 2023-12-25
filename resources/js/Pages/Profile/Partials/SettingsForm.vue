<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import ActionMessage from '@/Components/ActionMessage.vue';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import ToggleInput from "@/Components/ToggleInput.vue";

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const props = defineProps({
    user: Object,
});

const form = useForm({
    picked_up_by_default: props.user.settings.picked_up_by_default,
});

const save = () => {
    form.put(route('user-settings.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
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
                    ref="pickedUpByDefault"
                    v-model="form.picked_up_by_default"
                    class="mt-1 block w-full"
                >
                    <template #title>
                        <span>Picked up by default</span>
                    </template>
                </ToggleInput>
                <InputError :message="form.errors.picked_up_by_default" class="mt-2" />
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
