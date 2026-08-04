@php $settings = \App\Support\Settings::all() + config('theme.defaults'); @endphp
<!doctype html>
<html lang="fr" data-theme="{{ session('theme', 'light') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $settings['site_title'] }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <style>
        :root {
            --color-canvas: {{ \App\Support\Color::toRgbTriplet($settings['theme_light_bg']) }};
            --color-surface: {{ \App\Support\Color::toRgbTriplet($settings['theme_light_surface']) }};
            --color-ink: {{ \App\Support\Color::toRgbTriplet($settings['theme_light_text']) }};
            --color-muted: {{ \App\Support\Color::toRgbTriplet($settings['theme_light_muted']) }};
            --color-brand: {{ \App\Support\Color::toRgbTriplet($settings['theme_light_primary']) }};
            --color-accent: {{ \App\Support\Color::toRgbTriplet($settings['theme_light_accent']) }};
            --font-sans: {{ $settings['theme_font'] }};
        }
        [data-theme='dark'] {
            --color-canvas: {{ \App\Support\Color::toRgbTriplet($settings['theme_dark_bg']) }};
            --color-surface: {{ \App\Support\Color::toRgbTriplet($settings['theme_dark_surface']) }};
            --color-ink: {{ \App\Support\Color::toRgbTriplet($settings['theme_dark_text']) }};
            --color-muted: {{ \App\Support\Color::toRgbTriplet($settings['theme_dark_muted']) }};
            --color-brand: {{ \App\Support\Color::toRgbTriplet($settings['theme_dark_primary']) }};
            --color-accent: {{ \App\Support\Color::toRgbTriplet($settings['theme_dark_accent']) }};
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col items-center justify-center bg-canvas px-4 py-10 font-sans antialiased">
    <a href="{{ route('home') }}" class="mb-6 flex items-center gap-2 text-lg font-semibold text-ink">
        <x-icon name="chat" class="h-6 w-6 text-brand" />
        {{ $settings['site_title'] }}
    </a>

    <div class="card w-full max-w-sm p-6">
        {{ $slot }}
    </div>

    <x-license-credit class="mt-6 text-xs text-muted hover:text-ink" />
</body>
</html>
