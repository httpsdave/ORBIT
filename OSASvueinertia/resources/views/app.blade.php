<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        
        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts - Optimized for performance -->
        <!-- DNS prefetch for faster domain resolution -->
        <link rel="dns-prefetch" href="https://fonts.bunny.net">
        <!-- Preconnect with crossorigin for font files -->
        <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
        <!-- Preload font CSS for faster download -->
        <link rel="preload" href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" as="style">
        <!-- Load font stylesheet -->
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
        <!-- Fallback for browsers without JavaScript -->
        <noscript>
            <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet">
        </noscript>
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <!--<link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">-->
        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
