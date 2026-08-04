<!doctype html>
<html lang="fr" data-theme="{{ session('theme', 'light') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $code }} - {{ config('app.name') }}</title>
    <style>
        :root { --color-canvas: 246 244 239; --color-surface: 255 255 255; --color-ink: 31 41 55; --color-muted: 107 114 128; --color-brand: 217 119 6; }
        [data-theme='dark'] { --color-canvas: 14 17 17; --color-surface: 21 26 27; --color-ink: 229 231 235; --color-muted: 156 163 175; --color-brand: 245 158 11; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .5rem; font-family: 'Inter', system-ui, sans-serif; background: rgb(var(--color-canvas)); color: rgb(var(--color-ink)); text-align: center; padding: 1.5rem; }
        .code { font-size: 3rem; font-weight: 700; color: rgb(var(--color-brand)); }
        p { color: rgb(var(--color-muted)); margin: 0; max-width: 28rem; }
        a { margin-top: 1rem; color: rgb(var(--color-brand)); font-weight: 600; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="code">{{ $code }}</div>
    <p>{{ $message }}</p>
    <a href="/">Retour à l'accueil</a>
</body>
</html>
