<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Scrape Web - A web interface for managing and visualizing social media scraping tasks using Scrape.">
    <meta name="keywords" content="Scrape, web scraping, social media, dashboard, data visualization">
    <meta name="author" content="Bahyu Sanciko">
    <meta name="theme-color" content="#4F46E5">
    <meta name="application-name" content="Scrape Web">
    <meta name="apple-mobile-web-app-title" content="Scrape Web">
    <meta name="msapplication-TileColor" content="#4F46E5">
    <meta name="msapplication-config" content="/browserconfig.xml">
    <meta name="theme-color" content="#4F46E5">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <link rel="icon" href="{{ asset('images/favicon/favicon.ico') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('images/favicon/favicon.ico') }}" type="image/x-icon">
    <link rel="apple-touch-icon" href="{{ asset('images/favicon/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('images/favicon/site.webmanifest') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon/favicon-16x16.png') }}">
    <link rel="mask-icon" href="{{ asset('images/favicon/favicon-32x32.png') }}" color="#4F46E5">
    <title>{{ config('app.name', 'Scrape Web') }} - @yield('title', 'Dashboard')</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-white border-b sborder-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="flex-shrink-0 flex items-center">
                            <a href="{{ route('dashboard') }}" class="text-xl font-bold text-indigo-600 flex items-center space-x-2">
                                <img src="{{ asset('images/favicon/favicon-32x32.png') }}" alt="Scrape Web Logo" class="h-7 w-7" />
                                <span>Scrape Web</span>
                            </a>
                        </div>
                        <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                            <a href="{{ route('dashboard') }}"
                               class="{{ request()->routeIs('dashboard') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Dashboard
                            </a>
                            <a href="{{ route('scraping.index') }}"
                               class="{{ (request()->is('scraping') || request()->is('scraping/*')) ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Scraping
                            </a>
                            <a href="{{ route('dashboard.jobs') }}"
                               class="{{ (request()->is('dashboard/jobs') || request()->is('dashboard/jobs/*')) ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Jobs
                            </a>
                            <a href="{{ route('dashboard.data') }}"
                               class="{{ (request()->is('dashboard/data') || request()->is('dashboard/data/*')) ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Data
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                @if (session('success'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
    <footer class="bg-white border-t mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col sm:flex-row items-center justify-between text-sm text-gray-500">
            <div class="flex items-center space-x-2">
                <span>&copy; {{ date('Y') }} Scrape Web. All rights reserved.</span>
            </div>
            <div class="mt-2 sm:mt-0 flex items-center space-x-1">
                <span>V {{ config('app.version', '1.0.0') }}</span>
                <span class="hidden sm:inline">|</span>
                <span>Dev</span>
                <span>by</span>
                <a href="https://github.com/bahyusanciko" target="_blank" rel="noopener noreferrer" class="hover:text-indigo-600 transition-colors font-medium">Bahyu Sanciko</a>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
