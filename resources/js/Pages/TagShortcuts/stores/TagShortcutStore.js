import {ref} from "vue";
import {router} from "@inertiajs/vue3";
import debounce from "lodash.debounce";

export let tagShortcutState = ref({
    tagShortcut: null,
    shortcutName: '',
    processing: false,
    error: '',
    message: '',

    setTagShortcut(tagShortcut) {
        this.tagShortcut = tagShortcut;
        this.shortcutName = tagShortcut?.shortcut || '';
    },

    reloadTagShortcut() {
        if (! this.tagShortcut) return;

        axios.get(route('tag-shortcuts.show', this.tagShortcut.id))
             .then((r) => this.setTagShortcut(r.data.tagShortcut));
    },

    reset() {
        this.tagShortcut = null;
        this.error = '';
        this.message = '';
    },

    save() {
        this.processing = true;
        const url = this.tagShortcut
            ? route('tag-shortcuts.update', this.tagShortcut.id)
            : route('tag-shortcuts.store');
        axios.post(url, {
            shortcut: this.shortcutName,
        }).then((r) => {
            this.setTagShortcut(r.data.tagShortcut);
            this.processing = false;
            this.error = '';
            this.message = 'Saved.';
            setTimeout(() => this.message = '', 3000);
        }).catch((e) => {
            this.processing = false;
            this.error = e.response.data.message;
        });
    },

    delete(tagShortcutId) {
       axios.delete(route('tag-shortcuts.destroy', tagShortcutId))
              .then(() => router.reload());
    },

    removeItem(tagShortcutItemId) {
        axios.delete(route('tag-shortcut-items.destroy', tagShortcutItemId))
            .then(() => tagShortcutState.value.reloadTagShortcut());
    },

    copyItem(tagShortcutItemId) {
        axios.post(route('tag-shortcut-items.copy', tagShortcutItemId))
            .then(() => tagShortcutState.value.reloadTagShortcut());
    },

    addTagsToItem(tagShortcutItem, tagIds) {
        if (! tagIds.length) return;

        axios.post(route('tag-shortcut-item-tags.store', tagShortcutItem.id), {
            tag_ids: tagIds,
        }).then(() => tagShortcutState.value.reloadTagShortcut());
    },

    removeTagFromItem(tagShortcutItem, tagId) {
        axios.delete(route('tag-shortcut-item-tags.destroy', [tagShortcutItem.id, tagId]))
            .then(() => tagShortcutState.value.reloadTagShortcut());
    },

    toggleItemPickedUp: debounce((tagShortcutItemId, pickedUp) => {
        axios.post(route('tag-shortcut-items.update', tagShortcutItemId), {
            picked_up: pickedUp,
        }).then(() => tagShortcutState.value.reloadTagShortcut());
    }, 1000, {leading: true, trailing: true}),

    toggleItemRecycled: debounce((tagShortcutItemId, recycled) => {
        axios.post(route('tag-shortcut-items.update', tagShortcutItemId), {
            recycled: recycled,
        }).then(() => tagShortcutState.value.reloadTagShortcut());
    }, 1000, {leading: true, trailing: true}),

    toggleItemDeposit: debounce((tagShortcutItemId, deposit) => {
        axios.post(route('tag-shortcut-items.update', tagShortcutItemId), {
            deposit: deposit,
        }).then(() => tagShortcutState.value.reloadTagShortcut());
    }, 1000, {leading: true, trailing: true}),

    updateItemQuantity: debounce((tagShortcutItemId, quantity) => {
        axios.post(route('tag-shortcut-items.update', tagShortcutItemId), {
            quantity: quantity,
        }).then(() => tagShortcutState.value.reloadTagShortcut());
    }, 1000, {leading: true, trailing: true})
});
