<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Micex')</title>
    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <style>
        body, * {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-[#181A20] md:bg-gray-800 min-h-screen flex items-center justify-center">
    <div class="w-full md:max-w-[450px] h-screen flex flex-col mx-auto bg-gray-900 md:shadow-2xl overflow-hidden text-white">
        @yield('header')

        <main class="flex-1 overflow-y-auto hide-scrollbar text-base leading-relaxed pb-20" style="background-color: #181A20;">
            @yield('content')
        </main>

        @include('components.bottom-nav')
    </div>

    @include('components.toast')

    @stack('scripts')
</body>
</html>

