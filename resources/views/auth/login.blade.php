<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@lang('translation.signin') - {{ config('app.name', 'Markaz LMS') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'media',
            theme: {
                extend: {
                    colors: {
                        dark: {
                            'bg-primary': '#0f172a',
                            'bg-secondary': '#1e293b'
                        },
                        light: {
                            'bg-primary': '#f8fafc',
                            'bg-secondary': '#ffffff'
                        }
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @livewireStyles
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .animate-gradient {
            background-size: 400%;
            -webkit-animation: gradient 15s ease infinite;
            animation: gradient 15s ease infinite;
        }
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-light-bg-primary to-light-bg-secondary dark:from-slate-900 dark:to-gray-900 animate-gradient transition-colors duration-200">
    <div class="min-h-screen flex flex-col items-center justify-center px-4 sm:px-6 lg:px-8 -mt-20">
        <div class="w-full max-w-md">
            <!-- Logo Container with fixed height -->
            <div class="mb-8 h-[100px]">
                <livewire:settings.login-logo-component lazy />
            </div>

            <!-- Login Form Container -->

            <!-- Login Form Container -->
            <div class="bg-white/80 dark:bg-white/10 backdrop-blur-xl rounded-2xl p-8 shadow-2xl space-y-8">
                <div class="text-center">
                    <h2 class="text-xl font-semibold tracking-tight text-gray-900 dark:text-white mb-2">
                        {{ $nameSetting->value ?? 'Markaz LMS' }}
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Assalomu alaykum!
                    </p>
                </div>

                <form class="space-y-6" action="{{ route('login') }}" method="POST">
                    @csrf

                    <!-- Username/Email Field -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                            @lang('translation.username')
                        </label>
                        <div class="mt-1">
                            <input id="email"
                                   name="email"
                                   type="text"
                                   value="{{ old('email') }}"
                                   required
                                   class="block w-full px-4 py-3 rounded-xl border-0 bg-gray-100 dark:bg-white/5 text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-white/10 focus:ring-2 focus:ring-inset focus:ring-blue-500 sm:text-sm transition duration-200"
                                   placeholder="@lang('translation.Enter your username')">
                            @error('email')
                                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                            @lang('translation.password')
                        </label>
                        <div class="mt-1 relative">
                            <input id="password"
                                   name="password"
                                   type="password"
                                   required
                                   class="block w-full px-4 py-3 rounded-xl border-0 bg-gray-100 dark:bg-white/5 text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-white/10 focus:ring-2 focus:ring-inset focus:ring-blue-500 sm:text-sm transition duration-200"
                                   placeholder="@lang('translation.Enter your password')">
                            <button type="button"
                                    onclick="togglePassword()"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-white transition-colors">
                                <svg id="eyeIcon" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                            @error('password')
                                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button type="submit"
                                class="w-full flex justify-center py-3 px-4 rounded-xl bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-semibold shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                            @lang('translation.signin')
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="fixed bottom-0 w-full py-4 text-center text-sm text-gray-600 dark:text-gray-400 bg-white/50 dark:bg-gray-900/50 backdrop-blur-sm">
        <div class="container mx-auto px-4">
            <p class="mb-1 font-semibold">{{ config('app.name', 'Markaz LMS') }}</p>
            <p>&copy; {{ date('Y') }} Markaz LMS. @lang('translation.all_rights_reserved')</p>
        </div>
    </footer>
    @livewireScripts
    <script>
    // Password toggle functionality
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.innerHTML = `
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
            `;
        } else {
            passwordInput.type = 'password';
            eyeIcon.innerHTML = `
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            `;
        }
    }
 </script>
</body>
</html>
