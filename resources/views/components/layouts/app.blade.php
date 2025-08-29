{{-- resources/views/layouts/app.blade.php --}}

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Emissor NFe/NFCe') }}</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('nfe.listagem') }}" class="text-xl font-bold text-gray-800">
                        Emissor NFe/NFCe
                    </a>
                </div>
                
                <div class="flex items-center space-x-4">
                    <a href="{{ route('nfe.listagem') }}" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                        Listagem
                    </a>
                    <a href="{{ route('nfe.emissor') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        Nova NFe/NFCe
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="py-8">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>