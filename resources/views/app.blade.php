<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta property="og:title" content="E-commerce app with Vue.js,Laravel,Inertia.js" />
    <meta property="og:type" content="website" />
    <meta property="og:description" content="Futballmezek online.">

    <meta name="twitter:card" content="futball mezek kép">
    <meta name="twitter:title" content="Futball E-commerce Áruház">
    <meta name="twitter:description" content="Futballmezek és szurkolói felszerelések online.">


    <link rel="icon" href="/favicon.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <script>
        (function () {
            try {
                var saved = localStorage.getItem('theme');
                var dark = saved ? saved === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
                document.documentElement.classList.toggle('dark', dark);
            } catch (e) {}
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
    <x-inertia::head />
</head>

<body>
    <x-inertia::app />
</body>

</html>