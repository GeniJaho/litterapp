import {ref} from "vue";
import {router} from "@inertiajs/vue3";

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
            router.reload();
        }).catch((e) => {
            this.processing = false;
            this.error = e.response.data.message;
        });
    },

    delete(tagShortcutId) {
       axios.delete(route('tag-shortcuts.destroy', tagShortcutId))
              .then(() => router.reload());
    }
});
