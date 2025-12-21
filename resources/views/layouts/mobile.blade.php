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
        
        /* Prevent body scroll - only allow main content to scroll */
        html, body {
            height: 100%;
            overflow: hidden;
            position: fixed;
            width: 100%;
        }
        
        /* Smooth scrolling for main content */
        main {
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
            overscroll-behavior: contain;
        }
        
        /* Hide scrollbar but keep functionality */
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
    </style>
</head>
<body class="bg-[#181A20] md:bg-gray-800 h-screen w-screen overflow-hidden flex items-center justify-center">
    <div class="w-full md:max-w-[450px] h-full flex flex-col mx-auto bg-gray-900 md:shadow-2xl text-white relative">
        <!-- Fixed Header -->
        <div class="fixed top-0 left-0 right-0 z-40 bg-gray-900 md:left-1/2 md:-translate-x-1/2 md:max-w-[450px]">
        @yield('header')
        </div>

        <!-- Scrollable Main Content -->
        <main class="flex-1 overflow-y-auto hide-scrollbar text-base leading-relaxed" style="background-color: #181A20; padding-top: 64px; padding-bottom: 80px; height: 100%;">
            @yield('content')
        </main>

        <!-- Fixed Bottom Nav -->
        @include('components.bottom-nav')
    </div>

    @include('components.toast')

    @stack('scripts')
</body>
</html>

