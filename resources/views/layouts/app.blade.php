<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Deshabilita zoom en móviles -->
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Dulce Leche') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400..700&display=swap" rel="stylesheet">

        <!-- Iconos -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />

        <!-- TomSelect CSS -->
        <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.default.css" rel="stylesheet">

        <!-- Vite: app.css + app.js (incluye Livewire & Toaster JS) -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- SweetAlert2 -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <!-- Livewire Styles -->
        @livewireStyles

        <!-- Modo oscuro automático -->
        <script>
            if (localStorage.getItem('dark-mode') === 'false' || !('dark-mode' in localStorage)) {
                document.documentElement.classList.remove('dark');
                document.documentElement.style.colorScheme = 'light';
            } else {
                document.documentElement.classList.add('dark');
                document.documentElement.style.colorScheme = 'dark';
            }
        </script>
    </head>
    <body
        class="font-sans antialiased bg-gray-100 dark:bg-gray-900 text-gray-600 dark:text-gray-400"
        :class="{ 'sidebar-expanded': sidebarExpanded }"
        x-data="{ sidebarOpen: false, sidebarExpanded: localStorage.getItem('sidebar-expanded') == 'true' }"
        x-init="$watch('sidebarExpanded', value => localStorage.setItem('sidebar-expanded', value))"
    >
        <!-- Ajuste inicial del sidebar -->
        <script>
            if (localStorage.getItem('sidebar-expanded') == 'true') {
                document.body.classList.add('sidebar-expanded');
            } else {
                document.body.classList.remove('sidebar-expanded');
            }
        </script>

        <!-- Contenedor de Toaster -->
      {{-- Después, con posicionamiento y z-index elevado --}}
<div class="fixed inset-0 pointer-events-none z-[9999]">
    <x-toaster-hub class="pointer-events-auto top-4 right-4" />
</div>


        <!-- Wrapper -->
        <div class="flex h-[100dvh] overflow-hidden">
            <x-app.sidebar :variant="$attributes['sidebarVariant']" />

            <div class="relative flex flex-col flex-1 overflow-y-auto overflow-x-hidden @if($attributes['background']){{ $attributes['background'] }}@endif" x-ref="contentarea">
                <x-app.header :variant="$attributes['headerVariant']" />

                <main class="grow">
                    {{ $slot }}
                </main>
            </div>
        </div>

        <!-- Livewire Scripts -->
        @livewireScripts

        <!-- TomSelect JS -->
        <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
    </body>
</html>
