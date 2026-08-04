@props(['label' => 'Supprimer', 'confirmLabel' => 'Confirmer ?', 'class' => 'text-muted hover:text-red-600'])

{{-- À placer à l'intérieur d'un <form> : bascule vers une confirmation en ligne avant de soumettre,
     sans dialogue natif du navigateur (qui bloquerait l'interface). --}}
<span x-data="{ confirming: false }" @click.outside="confirming = false" class="inline-flex items-center gap-2">
    <button x-show="!confirming" @click="confirming = true" type="button" {{ $attributes->merge(['class' => $class]) }}>{{ $label }}</button>
    <span x-show="confirming" x-cloak class="inline-flex items-center gap-2">
        <button type="submit" class="font-medium text-red-600 hover:underline">{{ $confirmLabel }}</button>
        <button type="button" @click="confirming = false" class="text-muted hover:text-ink">Annuler</button>
    </span>
</span>
