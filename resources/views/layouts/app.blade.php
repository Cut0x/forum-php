<!doctype html>
<html lang="fr" data-theme="{{ session('theme', 'light') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title.' - '.$siteSettings['site_title'] : $siteSettings['site_title'] }}</title>
    <meta name="description" content="{{ $siteSettings['site_description'] }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <style>
        :root {
            --color-canvas: {{ \App\Support\Color::toRgbTriplet($siteSettings['theme_light_bg']) }};
            --color-surface: {{ \App\Support\Color::toRgbTriplet($siteSettings['theme_light_surface']) }};
            --color-ink: {{ \App\Support\Color::toRgbTriplet($siteSettings['theme_light_text']) }};
            --color-muted: {{ \App\Support\Color::toRgbTriplet($siteSettings['theme_light_muted']) }};
            --color-brand: {{ \App\Support\Color::toRgbTriplet($siteSettings['theme_light_primary']) }};
            --color-accent: {{ \App\Support\Color::toRgbTriplet($siteSettings['theme_light_accent']) }};
            --font-sans: {{ $siteSettings['theme_font'] }};
        }
        [data-theme='dark'] {
            --color-canvas: {{ \App\Support\Color::toRgbTriplet($siteSettings['theme_dark_bg']) }};
            --color-surface: {{ \App\Support\Color::toRgbTriplet($siteSettings['theme_dark_surface']) }};
            --color-ink: {{ \App\Support\Color::toRgbTriplet($siteSettings['theme_dark_text']) }};
            --color-muted: {{ \App\Support\Color::toRgbTriplet($siteSettings['theme_dark_muted']) }};
            --color-brand: {{ \App\Support\Color::toRgbTriplet($siteSettings['theme_dark_primary']) }};
            --color-accent: {{ \App\Support\Color::toRgbTriplet($siteSettings['theme_dark_accent']) }};
        }
        [x-cloak] { display: none !important; }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col font-sans antialiased">

<header class="border-b border-ink/10 bg-surface">
    <div class="mx-auto flex max-w-6xl items-center gap-4 px-4 py-3 sm:px-6" x-data="{ mobileOpen: false }">
        <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2 font-semibold text-ink">
            <x-icon name="chat" class="h-6 w-6 text-brand" />
            <span>{{ $siteSettings['site_title'] }}</span>
        </a>

        <nav class="hidden items-center gap-1 lg:flex">
            <a href="{{ route('home') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-ink/80 hover:bg-ink/5">Accueil</a>
            <a href="{{ route('categories.index') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-ink/80 hover:bg-ink/5">Catégories</a>
        </nav>

        <form action="{{ route('search') }}" method="get" class="mx-auto hidden max-w-sm flex-1 lg:block">
            <div class="relative">
                <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" />
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Rechercher…" class="field pl-9">
            </div>
        </form>

        <div class="ml-auto flex items-center gap-1.5">
            <form action="{{ route('theme.toggle') }}" method="post">
                @csrf
                <button type="submit" class="btn-ghost !px-2 !py-2" aria-label="Changer de thème">
                    <x-icon name="moon" class="h-5 w-5 dark:hidden" />
                    <x-icon name="sun" class="hidden h-5 w-5 dark:block" />
                </button>
            </form>

            @auth
                <a href="{{ route('notifications.index') }}" class="btn-ghost relative !px-2 !py-2" aria-label="Notifications">
                    <x-icon name="{{ auth()->user()->notifications_enabled ? 'bell' : 'bell-slash' }}" class="h-5 w-5" />
                    @if($unreadNotificationsCount > 0)
                        <span class="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-semibold text-white">
                            {{ $unreadNotificationsCount > 9 ? '9+' : $unreadNotificationsCount }}
                        </span>
                    @endif
                </a>

                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" @click.outside="open = false" class="flex items-center gap-2 rounded-lg py-1.5 pl-1.5 pr-2 hover:bg-ink/5">
                        <img src="{{ auth()->user()->avatar ? asset('storage/'.auth()->user()->avatar) : asset('images/default-avatar.jpg') }}" class="avatar h-7 w-7" alt="">
                        <span class="hidden text-sm font-medium sm:inline">{{ auth()->user()->displayName() }}</span>
                        <x-icon name="chevron-down" class="h-4 w-4 text-muted" />
                    </button>
                    <div x-show="open" x-cloak x-transition class="card absolute right-0 z-20 mt-2 w-56 divide-y divide-ink/10 py-1 shadow-lg">
                        <div class="px-3 py-2 text-xs text-muted">Connecté en tant que <strong class="text-ink">{{ auth()->user()->displayName() }}</strong></div>
                        <div class="py-1">
                            <a href="{{ route('profile.show', auth()->user()) }}" class="block px-3 py-2 text-sm hover:bg-ink/5">Mon profil</a>
                            <a href="{{ route('settings.edit') }}" class="block px-3 py-2 text-sm hover:bg-ink/5">Paramètres</a>
                            @if(auth()->user()->isStaff())
                                <a href="{{ route('moderation.dashboard') }}" class="block px-3 py-2 text-sm hover:bg-ink/5">Modération</a>
                            @endif
                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 text-sm hover:bg-ink/5">Administration</a>
                            @endif
                        </div>
                        <form method="post" action="{{ route('logout') }}" class="py-1">
                            @csrf
                            <button type="submit" class="block w-full px-3 py-2 text-left text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-950">Déconnexion</button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="btn-ghost">Connexion</a>
                <a href="{{ route('register') }}" class="btn-primary">Inscription</a>
            @endauth

            <button @click="mobileOpen = !mobileOpen" class="btn-ghost !px-2 !py-2 lg:hidden" aria-label="Menu">
                <x-icon name="menu" class="h-5 w-5" />
            </button>
        </div>

        <div x-show="mobileOpen" x-cloak x-transition class="absolute inset-x-0 top-full border-b border-ink/10 bg-surface p-4 lg:hidden">
            <form action="{{ route('search') }}" method="get" class="mb-3">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Rechercher…" class="field">
            </form>
            <nav class="flex flex-col gap-1">
                <a href="{{ route('home') }}" class="rounded-lg px-3 py-2 text-sm font-medium hover:bg-ink/5">Accueil</a>
                <a href="{{ route('categories.index') }}" class="rounded-lg px-3 py-2 text-sm font-medium hover:bg-ink/5">Catégories</a>
            </nav>
        </div>
    </div>
</header>

<main class="mx-auto w-full max-w-6xl flex-1 px-4 py-6 sm:px-6">
    @if(session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-200">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900 dark:bg-red-950 dark:text-red-200">
            {{ session('error') }}
        </div>
    @endif

    @isset($header){{ $header }}@endisset

    {{ $slot }}
</main>

<footer class="border-t border-ink/10 bg-surface">
    <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6">
        <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <div class="mb-2 font-semibold text-ink">{{ $siteSettings['footer_text'] }}</div>
                <p class="text-sm text-muted">Communauté, discussions et ressources.</p>
            </div>
            @foreach($footerCategories as $footerCategory)
                <div>
                    <div class="mb-2 text-sm font-semibold text-ink">{{ $footerCategory->name }}</div>
                    <div class="flex flex-col gap-1.5">
                        @foreach($footerCategory->links as $link)
                            <a href="{{ $link->url }}" target="_blank" rel="noopener" class="text-sm text-muted hover:text-ink">{{ $link->label }}</a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-8 flex items-center justify-between border-t border-ink/10 pt-4 text-xs text-muted">
            <span>&copy; {{ date('Y') }} {{ $siteSettings['footer_text'] }}</span>
            <a href="{{ route('sitemap') }}" class="hover:text-ink">Sitemap</a>
        </div>
    </div>
</footer>

</body>
</html>
