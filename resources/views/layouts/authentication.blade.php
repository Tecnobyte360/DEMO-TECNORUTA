<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400..700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Styles -->
        @livewireStyles        

        <script>
            if (localStorage.getItem('dark-mode') === 'false' || !('dark-mode' in localStorage)) {
                document.querySelector('html').classList.remove('dark');
                document.querySelector('html').style.colorScheme = 'light';
            } else {
                document.querySelector('html').classList.add('dark');
                document.querySelector('html').style.colorScheme = 'dark';
            }
        </script>
    </head>
    <body class="font-inter antialiased bg-gray-100 dark:bg-gray-900 text-gray-600 dark:text-gray-400">

        <main class="bg-white dark:bg-gray-900">

            <div class="relative flex">

                <!-- Content -->
                <div class="w-full md:w-1/2">

                    <div class="min-h-[100dvh] h-full flex flex-col after:flex-1">

                        <!-- Header -->
                     <div class="flex-1">
                        <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
                            <!-- Logo con imagen -->
                            <a class="block" href="{{ route('dashboard') }}">
                                <img 
                                    src="{{ asset('images/logoDulceleche.jpeg') }}" 
                                    alt="Logo Dulce Leche" 
                                    class="h-12 w-auto object-contain rounded-xl shadow-md"
                                />
                            </a>
                        </div>
                    </div>


                        <div class="max-w-sm mx-auto w-full px-4 py-8">
                            {{ $slot }}
                        </div>

                    </div>

                </div>

              
      <div class="hidden md:flex w-1/2 h-screen items-center justify-center bg-gradient-to-br from-purple-100 via-white to-violet-200 dark:from-gray-900 dark:via-gray-800 dark:to-black">
            <div class="p-8 bg-white dark:bg-gray-800 rounded-3xl shadow-2xl text-center space-y-6 transform transition-all duration-500 hover:scale-[1.02]">
                <img
                    src="{{ asset('images/logoDulceleche.jpeg') }}"
                    alt="Logo Dulce Leche"
                    class="w-80 max-w-full h-auto object-contain mx-auto rounded-xl"
                />
                <h2 class="text-2xl font-extrabold text-gray-800 dark:text-white">Distribuidora de Lácteos</h2>
                <p class="text-violet-600 dark:text-violet-300 italic text-sm">¡Calidad, frescura y confianza en cada entrega!</p>
            </div>
        </div>

            </div>

        </main> 

        @livewireScriptConfig
    </body>
</html>
