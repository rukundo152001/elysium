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
    <div x-data="{ open: true }" class="flex h-screen overflow-hidden">

        <!-- Sidebar -->
<aside :class="{ 'w-64': open, 'w-0': !open }" 
 class="w-64 min-h-screen bg-teal-700 text-white flex flex-col shadow-lg transition-all duration-700 ease-in-ou">

    <!-- Logo Section -->
    <div class="p-6 border-b border-teal-600 flex items-center justify-center">
    <img src="{{ asset('img/logo.png') }}" 
         alt="Elysium Healthcare Logo" 
         class="w-full h-auto object-contain">
</div>

    <!-- Sidebar Navigation -->
    <nav class="flex-1 overflow-y-auto mt-4 space-y-1 px-3">

        <!-- Dashboard -->
        <a href="{{ route('admin.index') }}"
           class="flex items-center py-2.5 px-4 rounded-lg transition
                  {{ request()->routeIs('admin.index') ? 'bg-white text-teal-700 font-semibold shadow-sm' : 'hover:bg-teal-600' }}">
            <span class="ml-1">Dashboard</span>
        </a>

        <!-- Services -->
        <a href="{{ route('admin.services.index') }}"
           class="flex items-center py-2.5 px-4 rounded-lg transition
                  {{ request()->routeIs('admin.services.*') ? 'bg-white text-teal-700 font-semibold shadow-sm' : 'hover:bg-teal-600' }}">
            <span class="ml-1">Services</span>
        </a>

        <!-- Insurance Providers -->
        <a href="{{ route('admin.insurances.index') }}"
           class="flex items-center py-2.5 px-4 rounded-lg transition
                  {{ request()->routeIs('admin.insurances.*') ? 'bg-white text-teal-700 font-semibold shadow-sm' : 'hover:bg-teal-600' }}">
            <span class="ml-1">Insurance Providers</span>
        </a>

        <!-- Locations -->
        <a href="{{ route('admin.locations.index') }}"
           class="flex items-center py-2.5 px-4 rounded-lg transition
                  {{ request()->routeIs('admin.locations.*') ? 'bg-white text-teal-700 font-semibold shadow-sm' : 'hover:bg-teal-600' }}">
            <span class="ml-1">Locations</span>
        </a>

        <!-- Patients -->
        <a href="{{ route('admin.patients.index') }}"
           class="flex items-center py-2.5 px-4 rounded-lg transition
                  {{ request()->routeIs('admin.patients.*') ? 'bg-white text-teal-700 font-semibold shadow-sm' : 'hover:bg-teal-600' }}">
            <span class="ml-1">Patients</span>
        </a>

        <!-- Users -->
        <a href="{{ route('admin.users.index') }}"
           class="flex items-center py-2.5 px-4 rounded-lg transition
                  {{ request()->routeIs('admin.users.*') ? 'bg-white text-teal-700 font-semibold shadow-sm' : 'hover:bg-teal-600' }}">
            <span class="ml-1">Users</span>
        </a>

        <!-- Reports -->
        <a href="{{ route('admin.reports.index') }}"
           target="_blank"
           class="flex items-center py-2.5 px-4 rounded-lg transition
                  {{ request()->routeIs('admin.reports.*') ? 'bg-white text-teal-700 font-semibold shadow-sm' : 'hover:bg-teal-600' }}">
            <span class="ml-1">Reports</span>
        </a>

        

    </nav>

    <!-- Sidebar Footer -->
    <div class="p-4 border-t border-teal-600">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="w-full text-center py-2 px-4 rounded-lg bg-red-600 hover:bg-red-700 transition text-white">
                Logout
            </button>
        </form>
    </div>

</aside>


        <!-- Main Content -->
        <div class="flex-1 flex flex-col">

            <!-- Navbar -->
            <header class="bg-white shadow-md flex items-center justify-between px-6 py-4">
                <div class="flex items-center space-x-4">
                    
                <!-- Toggle Sidebar -->
                <button @click="open = !open" class="focus:outline-none text-gray-600 hover:text-white transition duration-300 ease-in-out">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                         stroke-width="2" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                    <h1 class="text-xl font-semibold text-gray-700">Admin Dashboard</h1>
                </div>

                <!-- Dropdown -->
                <div class="relative" x-data="{ dropdown: false }">
                    <button @click="dropdown = !dropdown" class="flex items-center space-x-2 focus:outline-none">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name ?? 'User') }}&background=0D8ABC&color=fff"
                             class="w-8 h-8 rounded-full border border-gray-300" alt="User Avatar">
                        <span class="text-gray-700 font-medium hidden md:inline">{{ Auth::user()->name ?? 'Admin Panel' }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                             viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                             class="w-5 h-5 text-gray-600">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="dropdown" @click.away="dropdown = false"
                         class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-2 z-20">
                        <a href="{{ route('profile.edit') }}" 
                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-300">Profile</a>
                        <a href="{{-- route('settings') --}}" 
                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-300">Settings</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-red-600 hover:text-white transition">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Main Page Content -->
            <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
                {{ $slot }}
            </main>
        </div>
    </div>

</body>
</html>
