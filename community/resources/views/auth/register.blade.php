@extends('layouts.app')

@section('title', '회원가입')

@section('content')
<div class="max-w-md mx-auto my-8">
    <div class="bg-white p-8 rounded-lg shadow-sm border border-gray-200">
        <h1 class="text-2xl font-bold text-gray-900 mb-6 text-center">회원가입</h1>

        <form action="{{ route('register.submit') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">이메일</label>
                <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="xxx@example.com"
                        required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                @error('email')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">이름</label>
                <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        placeholder="이름"
                        required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                @error('name')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">비밀번호</label>
                <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="비밀번호"
                        required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                @error('password')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">비밀번호 확인</label>
                <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        placeholder="비밀번호 재입력"
                        required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                @error('password_confirmation')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <button
                    type="submit"
                    class="w-full bg-blue-600 text-white py-2.5 px-4 rounded-lg font-medium hover:bg-blue-700 transition cursor-pointer"
            >
                가입하기
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-500">
            이미 계정이 있으신가요?
            <a href="{{ route('login') }}" class="text-blue-600 hover:underline font-medium">로그인</a>
        </p>
    </div>
</div>
@endsection
