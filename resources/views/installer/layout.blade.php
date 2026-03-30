<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalador del Sistema</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
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
</head>
<body class="mesh-gradient min-h-screen antialiased flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <!-- Minimal Logo -->
        <div class="flex justify-center mb-6">
            <div class="w-12 h-12 bg-blue-600 rounded-2xl flex items-center justify-center shadow-lg shadow-blue-600/20">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-xl animate-in">
        <div class="bg-white dark:bg-slate-900 py-10 px-6 shadow-2xl shadow-slate-200 dark:shadow-none sm:rounded-[2rem] sm:px-12 border border-slate-100 dark:border-slate-800">
            @yield('content')
        </div>
        
        <div class="mt-8 text-center text-slate-400 text-xs font-semibold tracking-wide uppercase">
            &copy; {{ date('Y') }} Instalador de Aplicación
        </div>
    </div>
</body>
</html>
