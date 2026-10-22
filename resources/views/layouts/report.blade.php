@php
    $user = auth()->user();

    // Not logged in
    if (!$user) {
        echo "<script>
            alert('You must login first.');
            window.location.href = '".route('homepage')."';
        </script>";
        exit;
    }

    // Logged in but role not allowed
    if ($user->role !== 'admin') {
        echo "<script>
            alert('Authorized access only.');
            window.location.href = '".route('homepage')."';
        </script>";
        exit;
    }
@endphp



<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ open: false }" x-cloak>
    <head>
        <meta charset="utf-8">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">


        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
        [x-cloak] { display: none !important; }
    </style>
    </head>
<body class="bg-gray-100 text-gray-900">
    <div class="min-h-screen flex flex-col">

        <!-- Dashboard header scrolls away with the page -->
        <header class="bg-white flex items-center justify-between px-4 sm:px-6 py-4 shadow-md">
                <div class="flex items-center gap-3 min-w-0">
                    <img src="{{ asset('img/logo.png') }}" alt="Elysium Healthcare Logo"
                         class="h-10 w-auto object-contain">
                    <h1 class="text-lg sm:text-xl font-semibold text-gray-700">Admin Dashboard</h1>
                </div>

                
        </header>

        <!-- Horizontal navigation tabs stay fixed at the top while scrolling -->
        <nav class="sticky top-0 z-50 w-full bg-teal-700 text-white shadow-md">
                <div class="flex items-center gap-1 px-3 sm:px-6 py-2 overflow-x-auto whitespace-nowrap">
                    <a href="{{ route('admin.index') }}"
                       class="shrink-0 px-4 py-2 rounded-lg transition {{ request()->routeIs('admin.index') ? 'bg-white text-teal-700 font-semibold shadow-sm' : 'hover:bg-teal-600' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('admin.services.index') }}"
                       class="shrink-0 px-4 py-2 rounded-lg transition {{ request()->routeIs('admin.services.*') ? 'bg-white text-teal-700 font-semibold shadow-sm' : 'hover:bg-teal-600' }}">
                        Services
                    </a>
                    <a href="{{ route('admin.insurances.index') }}"
                       class="shrink-0 px-4 py-2 rounded-lg transition {{ request()->routeIs('admin.insurances.*') ? 'bg-white text-teal-700 font-semibold shadow-sm' : 'hover:bg-teal-600' }}">
                        Insurance Providers
                    </a>
                    <a href="{{ route('admin.locations.index') }}"
                       class="shrink-0 px-4 py-2 rounded-lg transition {{ request()->routeIs('admin.locations.*') ? 'bg-white text-teal-700 font-semibold shadow-sm' : 'hover:bg-teal-600' }}">
                        Locations
                    </a>
                    <a href="{{ route('admin.patients.index') }}"
                       class="shrink-0 px-4 py-2 rounded-lg transition {{ request()->routeIs('admin.patients.*') ? 'bg-white text-teal-700 font-semibold shadow-sm' : 'hover:bg-teal-600' }}">
                        Patients
                    </a>
                    <a href="{{ route('admin.users.index') }}"
                       class="shrink-0 px-4 py-2 rounded-lg transition {{ request()->routeIs('admin.users.*') ? 'bg-white text-teal-700 font-semibold shadow-sm' : 'hover:bg-teal-600' }}">
                        Users
                    </a>
                    <a href="{{ route('admin.reports.index') }}"
                       class="shrink-0 px-4 py-2 rounded-lg transition {{ request()->routeIs('admin.reports.*') ? 'bg-white text-teal-700 font-semibold shadow-sm' : 'hover:bg-teal-600' }}">
                        Reports
                    </a>
                


                

                </div>
     
        </nav>

        <!-- Main Page Content -->
        <main class="w-full min-w-0 flex-1 p-0 bg-gray-50">
            
            {{ $slot }}
        </main>
    </div>
</body>
</html>
