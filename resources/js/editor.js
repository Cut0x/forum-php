export default function editor({ previewUrl, mentionsUrl, emotesUrl, uploadUrl, csrfToken }) {
    return {
        preview: '',
        showPreview: false,
        showEmotes: false,
        emotes: [],
        emotesLoaded: false,
        showMentions: false,
        mentionResults: [],
        mentionStart: null,
        uploading: false,
        previewTimer: null,

        onInput() {
            clearTimeout(this.previewTimer);
            if (this.showPreview) {
                this.previewTimer = setTimeout(() => this.refreshPreview(), 300);
            }
            this.detectMention();
        },

        togglePreview() {
            this.showPreview = !this.showPreview;
            if (this.showPreview) {
                this.refreshPreview();
            }
        },

        refreshPreview() {
            const body = new FormData();
            body.append('content', this.$refs.textarea.value);
            fetch(previewUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                body,
            })
                .then((r) => r.text())
                .then((html) => { this.preview = html || '<p class="text-muted">Aperçu vide.</p>'; });
        },

        insertAtCursor(text) {
            const el = this.$refs.textarea;
            const start = el.selectionStart;
            const end = el.selectionEnd;
            const value = el.value;
            el.value = value.slice(0, start) + text + value.slice(end);
            el.selectionStart = el.selectionEnd = start + text.length;
            el.focus();
            this.onInput();
        },

        toggleEmotes() {
            this.showEmotes = !this.showEmotes;
            if (this.showEmotes && !this.emotesLoaded) {
                fetch(emotesUrl)
                    .then((r) => r.json())
                    .then((items) => { this.emotes = items; this.emotesLoaded = true; });
            }
        },

        pickEmote(emote) {
            this.insertAtCursor(':' + emote.name + ': ');
            this.showEmotes = false;
        },

        detectMention() {
            const el = this.$refs.textarea;
            const pos = el.selectionStart;
            const value = el.value.slice(0, pos);
            const match = value.match(/(^|\s)@([a-zA-Z0-9_]{0,30})$/);
            if (!match || match[2].length < 2) {
                this.showMentions = false;
                return;
            }
            this.mentionStart = pos - match[2].length - 1;
            fetch(mentionsUrl + '?q=' + encodeURIComponent(match[2]))
                .then((r) => r.json())
                .then((items) => {
                    this.mentionResults = items;
                    this.showMentions = items.length > 0;
                });
        },

        pickMention(user) {
            const el = this.$refs.textarea;
            const value = el.value;
            const after = value.slice(el.selectionStart);
            el.value = value.slice(0, this.mentionStart) + '@' + user.username + ' ' + after;
            this.showMentions = false;
            el.focus();
            this.onInput();
        },

        triggerUpload() {
            this.$refs.imageInput.click();
        },

        uploadImage(event) {
            const file = event.target.files && event.target.files[0];
            if (!file) return;
            this.uploading = true;
            const body = new FormData();
            body.append('image', file);
            fetch(uploadUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                body,
            })
                .then((r) => r.json())
                .then((data) => {
                    if (data && data.url) {
                        this.insertAtCursor(`![](${data.url})`);
                    }
                })
                .finally(() => {
                    this.uploading = false;
                    event.target.value = '';
                });
        },
    };
}
