<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta property="og:title" content="E-commerce app with Vue.js,Laravel,Inertia.js" />
    <meta property="og:type" content="website" />
    <meta property="og:description" content="{{ __('meta.description') }}">
    <meta property="og:image" content="{{ asset('ball.webp') }}">

    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ __('meta.title') }}">
    <meta name="twitter:description" content="{{ __('meta.description') }}">

    <link rel="icon" href="/favicon.ico">
    <script>
        (function () {
            try {
                var saved = localStorage.getItem('theme');
                var dark = saved ? saved === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
                document.documentElement.classList.toggle('dark', dark);
            } catch (e) {}
        })();
    </script>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
    <x-inertia::head />
</head>

<body>
    <x-inertia::app />
</body>

</html>