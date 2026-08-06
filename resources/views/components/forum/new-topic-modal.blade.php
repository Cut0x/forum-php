@props(['categories', 'selected' => null, 'label' => 'Nouveau sujet'])

@php
    $isStaff = auth()->check() && auth()->user()->isStaff();
    $postable = $isStaff ? $categories : $categories->where('is_readonly', false);
    $default = $selected && ($isStaff || ! $selected->is_readonly) ? $selected : $postable->first();
@endphp

<div x-data="{
    open: false,
    categorySlug: '{{ $default?->slug }}',
    formAction() { return '{{ url('categories') }}/' + this.categorySlug + '/topics'; },
}">
    <button type="button" @click="open = true" class="btn-primary" {{ $postable->isEmpty() ? 'disabled' : '' }}>
        <x-icon name="plus" class="h-4 w-4" /> {{ $label }}
    </button>

    <div x-show="open" x-cloak class="fixed inset-0 z-30 flex items-center justify-center bg-ink/40 p-4" x-transition.opacity>
        <div @click.outside="open = false" x-transition class="card w-full max-w-lg p-5 shadow-xl">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-base font-semibold text-ink">{{ $label }}</h2>
                <button type="button" @click="open = false" class="btn-ghost !p-1.5"><x-icon name="x" class="h-4 w-4" /></button>
            </div>

            <form method="post" :action="formAction()" class="space-y-3">
                @csrf
                <div>
                    <label class="mb-1 block text-xs font-medium text-ink">Catégorie</label>
                    <select x-model="categorySlug" class="field">
                        @foreach($categories as $category)
                            <option value="{{ $category->slug }}" {{ (!$isStaff && $category->is_readonly) ? 'disabled' : '' }}>
                                {{ $category->name }}{{ $category->is_readonly ? ' (lecture seule)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-ink">Titre</label>
                    <input type="text" name="title" required maxlength="180" class="field">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-ink">Message</label>
                    <x-editor name="content" placeholder="Votre message… (Markdown pris en charge)" />
                </div>
                <div class="flex justify-end gap-2 pt-1">
                    <button type="button" @click="open = false" class="btn-secondary">Annuler</button>
                    <button type="submit" class="btn-primary">Publier</button>
                </div>
            </form>
        </div>
    </div>
</div>
