<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ open: false }" x-cloak>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Elysium Staff Portal - Login</title>
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 to-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md" x-data="{ 
        showLogin: {{ $errors->any() ? 'true' : 'false' }}, 
        loginType: '{{ old('user_type', 'staff') }}',

        isSubmitting: false
    }">
        {{-- Success Message Toast --}}
        @if(session('success'))
        <div 
            x-data="{ show: true }" 
            x-show="show" 
            x-init="setTimeout(() => show = false, 5000)"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="mb-4 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg shadow-lg"
            role="alert">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4a1 1 0 001.414-1.414L11.414 10l4.293 4.293a1 1 0 00-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 00-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
                <p class="text-green-800 font-medium">{{ session('success') }}</p>
                <button @click="show = false" class="ml-auto text-green-500 hover:text-green-700">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 011.414 1.414l2 2a1 1 0 001.414 0l4-4a1 1 0 001.414-1.414L11.414 10l4.293 4.293a1 1 0 00-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 00-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
        </div>
        @endif

        {{-- Main Welcome Card --}}
        <div class="bg-white rounded-2xl shadow-2xl p-8 text-center transition-all duration-300" x-show="!showLogin">
            {{-- Added "Staff Portal" badge and internal access notice --}}
            <div class="mb-4">
                <span class="inline-block bg-slate-800 text-white text-xs font-semibold px-3 py-1 rounded-full uppercase tracking-wide">
                    Internal Staff Portal
                </span>
            </div>
            
            <h1 class="text-3xl font-bold text-slate-900 mb-2">ELYSIUM BILLING SYSTEM</h1>
            
            {{-- Word-based Logo --}}
            <div class="mb-6">
                <div class="inline-block bg-gradient-to-r from-slate-700 to-slate-900 text-white px-8 py-4 rounded-lg text-2xl font-bold tracking-wider shadow-lg">
                    <img src="{{ asset('img/logo.png') }}" 
         alt="Elysium Healthcare Logo" 
         class="w-full h-auto object-contain">
                </div>
            </div>
            
            <p class="text-xl text-slate-700 mb-2">Welcome back</p>
            <p class="text-sm text-slate-500 mb-8">Select your role to continue</p>
            
            {{-- Login Buttons --}}
            <div class="space-y-3">
                {{-- Updated button labels to clarify roles --}}
                <button 
                    @click="showLogin = true; loginType = 'staff'"
                    class="w-full bg-slate-700 hover:bg-slate-800 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 shadow-md hover:shadow-lg transform hover:scale-105">
                    <span class="flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Staff (Receptionist)
                    </span>
                </button>
                
                <button 
                    @click="showLogin = true; loginType = 'admin'"
                    class="w-full bg-slate-800 hover:bg-slate-900 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 shadow-md hover:shadow-lg transform hover:scale-105">
                    <span class="flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        Administrator
                    </span>
                </button>

                
            </div>

            {{-- Added patient notice --}}
            <div class="mt-6 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                <p class="text-xs text-amber-800 flex items-center justify-center">
                    <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-medium">For staff use only. Patients should not access this portal.</span>
                </p>
            </div>
        </div>
        
        {{-- Login Form --}}
        <div class="bg-white rounded-2xl shadow-2xl p-8 transition-all duration-300" x-show="showLogin" x-cloak>
            <div class="flex items-center justify-between mb-6">
                {{-- Updated form titles to match new role labels --}}
                <h2 class="text-2xl font-bold text-slate-900">
                    <span x-show="loginType === 'staff' || loginType === 'doctor' || loginType === 'cashier'">Staff Login</span>
                    <span x-show="loginType === 'admin'">Administrator Login</span>
                </h2>
                <button @click="showLogin = false" class="text-slate-500 hover:text-slate-700 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            {{-- Error Messages --}}
            @if($errors->any())
            <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg" role="alert">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-red-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <div class="flex-1">
                        <p class="text-red-800 font-semibold mb-1">Please correct the following errors:</p>
                        <ul class="list-disc list-inside text-red-700 text-sm space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            @endif
            
            <form action="{{ route('login') }}" method="POST" class="space-y-4" @submit="isSubmitting = true">
                @csrf
                <input type="hidden" name="user_type" :value="loginType">

                
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-2">
                        Email Address <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="{{ old('email') }}"
                        required
                        class="w-full px-4 py-2 border @error('email') border-red-500 @else border-slate-300 @enderror rounded-lg focus:ring-2 focus:ring-slate-500 focus:border-transparent transition-colors"
                        placeholder="your.email@elysium.com">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-2">
                        Password <span class="text-red-500">*</span>
                    </label>
                    <div class="relative" x-data="{ showPassword: false }">
                        <input 
                            :type="showPassword ? 'text' : 'password'" 
                            id="password" 
                            name="password" 
                            required
                            class="w-full px-4 py-2 border @error('password') border-red-500 @else border-slate-300 @enderror rounded-lg focus:ring-2 focus:ring-slate-500 focus:border-transparent transition-colors pr-10"
                            placeholder="Enter your password">
                        <button 
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-slate-500 hover:text-slate-700">
                            <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input 
                            type="checkbox" 
                            id="remember" 
                            name="remember"
                            {{ old('remember') ? 'checked' : '' }}
                            class="w-4 h-4 text-slate-600 border-slate-300 rounded focus:ring-slate-500">
                        <label for="remember" class="ml-2 text-sm text-slate-700">Remember me</label>
                    </div>
                    <a href="{{ route('password.request') }}" class="text-sm text-slate-600 hover:text-slate-800 transition-colors">
                        Forgot password?
                    </a>
                </div>
                
                <button 
                    type="submit"
                    :disabled="isSubmitting"
                    class="w-full bg-slate-800 hover:bg-slate-900 disabled:bg-slate-400 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 shadow-md hover:shadow-lg transform hover:scale-105 disabled:transform-none disabled:cursor-not-allowed flex items-center justify-center">
                    <span x-show="!isSubmitting">Sign In</span>
                    <span x-show="isSubmitting" class="flex items-center" x-cloak>
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Signing in...
                    </span>
                </button>
            </form>

            
        </div>
        

        
        {{-- Footer --}}
        <footer class="text-center mt-8 text-slate-600 text-sm">
            <p>&copy; {{ date('Y') }} Elysium Healthcare. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>
