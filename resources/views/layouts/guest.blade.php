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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- dynamic brand color -->
    @php
        $brandColor = \App\Models\Setting::get('brand_color', '#0e8ceb');
        
        // Convert hex to rgb for tailwind shadows
        list($r, $g, $b) = sscanf($brandColor, "#%02x%02x%02x");
        $brandColorRgb = "$r, $g, $b";
    @endphp
    <style>
        :root {
            --brand-color: {{ $brandColor }};
            --brand-color-rgb: {{ $brandColorRgb }};
            --brand-color-hover: {{ $brandColor }}dd;
        }
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .mesh-gradient {
            background-color: #f8fafc;
            background-image: 
                radial-gradient(at 0% 0%, rgba(14, 140, 235, 0.05) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(99, 102, 241, 0.05) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(14, 140, 235, 0.05) 0px, transparent 50%),
                radial-gradient(at 0% 100%, rgba(99, 102, 241, 0.05) 0px, transparent 50%);
        }
    </style>

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="mesh-gradient min-h-screen antialiased flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md animate-in">
        {{ $slot }}
    </div>
    @livewireScripts
</body>
</html>
