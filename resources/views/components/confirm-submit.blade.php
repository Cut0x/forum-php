@props([
    'label' => 'Supprimer',
    'title' => null,
    'message' => null,
    'confirmLabel' => null,
    'cancelLabel' => 'Annuler',
    'danger' => true,
    'class' => 'text-muted hover:text-red-600',
])

{{-- À placer à l'intérieur d'un <form> : ouvre la modale de confirmation globale
     puis soumet le formulaire (via requestSubmit, pour rester compatible avec
     l'interception AJAX de data-remote) si l'utilisateur confirme. Aucun
     window.confirm() natif, qui bloquerait le navigateur. --}}
<button
    type="button"
    x-data
    @click="window.dispatchEvent(new CustomEvent('open-confirm', { detail: {
        title: @js($title ?? $label),
        message: @js($message ?? 'Cette action est irréversible.'),
        confirmLabel: @js($confirmLabel ?? $label),
        cancelLabel: @js($cancelLabel),
        danger: @js($danger),
        onConfirm: () => $el.closest('form').requestSubmit(),
    } }))"
    {{ $attributes->merge(['class' => $class]) }}
>{{ $label }}</button>
