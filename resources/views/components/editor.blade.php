@props(['name', 'id' => null, 'value' => '', 'placeholder' => 'Votre message…', 'rows' => 6])

@php $id = $id ?? $name; @endphp

<div
    x-data="editor({
        previewUrl: '{{ route('markdown.preview') }}',
        mentionsUrl: '{{ route('mentions.search') }}',
        emotesUrl: '{{ route('emotes.index') }}',
        uploadUrl: '{{ route('uploads.images') }}',
        csrfToken: '{{ csrf_token() }}',
    })"
    class="space-y-2"
>
    <div class="flex flex-wrap items-center gap-1.5">
        <button type="button" @click="toggleEmotes" class="btn-ghost !px-2 !py-1.5 text-xs" :class="showEmotes && 'bg-ink/5'">
            <x-icon name="smile" class="h-4 w-4" /> Émotes
        </button>
        <button type="button" @click="triggerUpload" class="btn-ghost !px-2 !py-1.5 text-xs" :disabled="uploading">
            <x-icon name="image" class="h-4 w-4" />
            <span x-text="uploading ? 'Envoi…' : 'Image'"></span>
        </button>
        <input x-ref="imageInput" @change="uploadImage" type="file" accept="image/png,image/jpeg,image/gif,image/webp" class="hidden">
        <button type="button" @click="togglePreview" class="btn-ghost ml-auto !px-2 !py-1.5 text-xs" :class="showPreview && 'bg-ink/5'">
            <span x-text="showPreview ? 'Édition' : 'Aperçu'"></span>
        </button>
    </div>

    <div x-show="showEmotes" x-cloak x-transition @click.outside="showEmotes = false" class="card grid max-h-48 grid-cols-8 gap-1 overflow-y-auto p-2 sm:grid-cols-10">
        <template x-for="emote in emotes" :key="emote.name">
            <button type="button" @click="pickEmote(emote)" class="flex items-center justify-center rounded p-1 hover:bg-ink/5" :title="':' + emote.name + ':'">
                <img :src="emote.file" :alt="emote.name" class="h-6 w-6">
            </button>
        </template>
        <p x-show="emotes.length === 0" class="col-span-full text-xs text-muted">Aucune émote disponible.</p>
    </div>

    <div class="relative">
        <textarea
            x-ref="textarea"
            @input="onInput"
            id="{{ $id }}"
            name="{{ $name }}"
            rows="{{ $rows }}"
            placeholder="{{ $placeholder }}"
            x-show="!showPreview"
            class="field font-mono text-[13px] leading-relaxed"
        >{{ $value }}</textarea>

        <div x-show="showMentions" x-cloak @click.outside="showMentions = false" class="card absolute z-10 mt-1 w-64 divide-y divide-ink/10 shadow-lg">
            <template x-for="user in mentionResults" :key="user.username">
                <button type="button" @click="pickMention(user)" class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm hover:bg-ink/5">
                    <img :src="user.avatar" class="h-6 w-6 rounded-full object-cover" alt="">
                    <span class="font-medium" x-text="user.name"></span>
                    <span class="text-muted" x-text="'@' + user.username"></span>
                </button>
            </template>
        </div>
    </div>

    <div x-show="showPreview" x-cloak class="prose-forum card min-h-[8rem] p-3" x-html="preview"></div>
</div>
