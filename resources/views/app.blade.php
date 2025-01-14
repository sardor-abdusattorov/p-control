<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <meta name="referrer" content="no-referrer">

    <!-- SEO параметры -->
    <meta name="description" content="{{ __('meta.description') }}">
    <meta name="keywords" content="{{ __('meta.keywords') }}">
    <meta name="author" content="{{ __('meta.author') }}">
    <meta name="robots" content="noindex, nofollow">

    <meta property="og:title" content="{{ __('meta.og_title') }}">
    <meta property="og:description" content="{{ __('meta.og_description') }}">
    <meta property="og:image" content="{{ asset('images/main-logo.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">

    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @routes
    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead
</head>
<body class="font-sans antialiased scrollbar bg-slate-100 dark:bg-slate-900">
@inertia
</body>
</html>
