<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#062849">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: #ffffff;
            }

            html.dark {
                background-color: #071322;
            }
        </style>

        <link rel="icon" href="/brand/alco-favicon.svg" type="image/svg+xml">

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        @if ($page['component'] === 'Welcome' || str_starts_with($page['component'], 'Public/'))
            @php($share = \App\Support\ShareMetadata::forPage($page))
            <meta property="og:locale" content="en_NG">
            <meta property="og:type" content="{{ $share['type'] }}">
            <meta property="og:site_name" content="{{ $page['props']['site']['name'] }}">
            <meta property="og:title" content="{{ $share['title'] }}">
            <meta property="og:description" content="{{ $share['description'] }}">
            <meta property="og:url" content="{{ $share['url'] }}">
            <meta property="og:image" content="{{ $share['image'] }}">
            <meta property="og:image:type" content="image/png">
            <meta property="og:image:width" content="1200">
            <meta property="og:image:height" content="630">
            <meta property="og:image:alt" content="{{ $share['imageAlt'] }}">
            <meta name="twitter:card" content="summary_large_image">
            <meta name="twitter:title" content="{{ $share['title'] }}">
            <meta name="twitter:description" content="{{ $share['description'] }}">
            <meta name="twitter:image" content="{{ $share['image'] }}">
            <script type="application/ld+json">{!! app(\App\Support\StructuredData::class)->json(request()->path() === '/') !!}</script>
        @endif

        <x-inertia::head>
            <title>{{ config('app.name', 'Laravel') }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
