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
        axios.post(route('tag-shortcuts.store'), {
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
    }
});