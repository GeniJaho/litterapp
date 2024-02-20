<script setup>

import SecondaryButton from "@/Components/SecondaryButton.vue";
import DialogModal from "@/Components/DialogModal.vue";
import {useForm} from "@inertiajs/vue3";
import {ref} from "vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const confirmingUserDeletion = ref(false);

const form = useForm({
    photo_ids: [],
    items: [{
        id: null,
        tag_ids: [],
        picked_up: false,
        recycled: false,
        deposit: false,
        quantity: 1,
    }]
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    // setTimeout(() => passwordInput.value.focus(), 250);
};

const deleteUser = () => {
    form.post(route('bulk-photo-items.store'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        // onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.reset();
};
</script>

<template>
    <PrimaryButton @click="confirmUserDeletion">
        Tag Multiple Photos
    </PrimaryButton>

    <DialogModal :show="confirmingUserDeletion" @close="closeModal">
        <template #title>
            Tag Multiple Photos
        </template>

        <template #content>
            Add items and tags to multiple photos at once. You can also mark them as picked up, recycled, or deposited.
            After you have added all the items and tags, click the "Save" button to save the changes.

            <div class="mt-4">
<!--                <TextInput-->
<!--                    ref="passwordInput"-->
<!--                    v-model="form.password"-->
<!--                    type="password"-->
<!--                    class="mt-1 block w-3/4"-->
<!--                    placeholder="Password"-->
<!--                    autocomplete="current-password"-->
<!--                    @keyup.enter="deleteUser"-->
<!--                />-->

<!--                <InputError :message="form.errors.password" class="mt-2" />-->
            </div>
        </template>

        <template #footer>
            <SecondaryButton @click="closeModal">
                Cancel
            </SecondaryButton>

            <PrimaryButton
                class="ml-3"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
                @click="deleteUser"
            >
                Save
            </PrimaryButton>
        </template>
    </DialogModal>
</template>

<style scoped>

</style>