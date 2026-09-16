<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@hasSection('title')@yield('title') - @elseif(isset($title)){{ $title }} - @endif{{ config('app.name', 'Laravel') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen flex flex-col font-sans antialiased">
    <!-- Navigation Bar -->
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Brand & Main Nav -->
                <div class="flex items-center gap-8">
                    <a href="{{ route('main') }}" class="flex items-center gap-2 text-xl font-bold text-blue-600 hover:text-blue-700">
                        <span>💬</span>
                        <span>{{ config('app.name', 'Laravel') }}</span>
                    </a>

                    <div class="hidden sm:flex sm:items-center sm:gap-4">
                        <a href="{{ route('posts.index') }}"
                           class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('posts.index') ? 'text-blue-600 bg-blue-50' : 'text-gray-700 hover:text-blue-600 hover:bg-gray-50' }}">
                            게시판
                        </a>
                    </div>
                </div>

                <!-- Right Side Actions -->
                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ route('posts.create') }}"
                           class="hidden sm:inline-flex items-center gap-1.5 bg-blue-600 text-white px-3.5 py-1.5 rounded-md text-sm font-medium hover:bg-blue-700 transition">
                            <span>✏️</span>
                            <span>새 글 작성</span>
                        </a>

                        <div class="flex items-center gap-2 pl-2 border-l border-gray-200 text-sm">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700">
                                {{ mb_substr(auth()->user()->name, 0, 1) }}
                            </span>
                            <span class="font-medium text-gray-700 hidden md:inline">{{ auth()->user()->name }}</span>
                        </div>

                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-sm text-gray-500 hover:text-red-600 px-2 py-1 rounded hover:bg-gray-100 transition">
                                로그아웃
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-medium text-gray-700 hover:text-blue-600 px-3 py-1.5 rounded-md hover:bg-gray-50 transition">
                            로그인
                        </a>
                        <a href="{{ route('register') }}" class="text-sm font-medium bg-blue-600 text-white px-3.5 py-1.5 rounded-md hover:bg-blue-700 transition">
                            회원가입
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 w-full mt-4">
        @if (session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-r-md text-sm mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r-md text-sm mb-4">
                {{ session('error') }}
            </div>
        @endif

        @if (session('status'))
            <div class="bg-blue-50 border-l-4 border-blue-500 text-blue-700 p-4 rounded-r-md text-sm mb-4">
                {{ session('status') }}
            </div>
        @endif
    </div>

    <!-- Main Content -->
    <main class="flex-1 py-6 px-4 sm:px-6 lg:px-8">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 py-6 text-center text-sm text-gray-500 mt-auto">
        <div class="max-w-5xl mx-auto px-4">
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. All rights reserved.</p>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
