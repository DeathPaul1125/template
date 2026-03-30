<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ \App\Models\Setting::get('site_name', config('app.name', 'Laravel')) }}</title>

    @if($favicon = \App\Models\Setting::get('site_favicon'))
        <link rel="icon" type="image/x-icon" href="{{ $favicon }}">
    @endif

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- dynamic brand color -->
    @php
        $brandColor = \App\Models\Setting::get('brand_color', '#6366f1');
    @endphp
    <style>
        :root {
            --brand-color: {{ $brandColor }};
            --brand-color-hover: {{ $brandColor }}dd;
        }
        .bg-brand-600 { background-color: var(--brand-color) !important; }
        .hover\:bg-brand-700:hover { background-color: var(--brand-color-hover) !important; }
        .text-brand-600 { color: var(--brand-color) !important; }
        .border-brand-500 { border-color: var(--brand-color) !important; }
        .focus\:border-brand-500:focus { border-color: var(--brand-color) !important; }
        .focus\:ring-brand-500:focus { --tw-ring-color: var(--brand-color) !important; }
        .from-brand-500 { --tw-gradient-from: var(--brand-color) !important; --tw-gradient-to: var(--brand-color)00 !important; --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to) !important; }
        .to-brand-700 { --tw-gradient-to: var(--brand-color-hover) !important; }
    </style>

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-50 dark:bg-slate-900 h-full font-sans antialiased">
    {{ $slot }}
    @livewireScripts
</body>
</html>
