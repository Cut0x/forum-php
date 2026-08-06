@props(['class' => 'text-xs text-muted hover:text-ink'])

<a href="https://valloic.fr?refer=PHPForum" target="_blank" rel="noopener" {{ $attributes->merge(['class' => $class]) }}>
    Forum PHP, par <span class="font-medium">Loic VALENCE</span>
</a>
