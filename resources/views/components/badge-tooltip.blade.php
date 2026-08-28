@props(['badge', 'size' => 'h-6 w-6'])

{{--
    Icône de badge avec infobulle stylisée en pur CSS (pas de JS) : `group` + `group-hover`/
    `group-focus` sur le conteneur, tabindex pour que le clavier puisse aussi la déclencher.
    Couleurs sur les tokens ink/canvas du thème, donc lisible en clair comme en sombre.
--}}
<span tabindex="0" class="group relative inline-flex shrink-0 rounded-full align-middle outline-none">
    <img
        src="{{ $badge->iconUrl }}"
        alt="{{ $badge->name }}"
        class="{{ $size }} object-contain transition group-hover:scale-110 group-focus:scale-110"
    >
    <span
        role="tooltip"
        class="pointer-events-none absolute bottom-full left-1/2 z-30 mb-2 w-max max-w-[13rem] -translate-x-1/2 translate-y-1 scale-95 rounded-lg bg-ink px-2.5 py-1.5 text-center text-[11px] font-medium leading-snug text-canvas opacity-0 shadow-lg transition duration-150 ease-out group-hover:translate-y-0 group-hover:scale-100 group-hover:opacity-100 group-focus:translate-y-0 group-focus:scale-100 group-focus:opacity-100"
    >
        {{ $badge->name }}
        <span class="absolute left-1/2 top-full -mt-px h-2 w-2 -translate-x-1/2 rotate-45 bg-ink"></span>
    </span>
</span>
