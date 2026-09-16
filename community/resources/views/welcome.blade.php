@extends('layouts.app')

@section('title', '홈')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <!-- Hero Section -->
    <div class="bg-white p-8 md:p-12 rounded-xl shadow-sm border border-gray-200 text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-50 text-blue-600 rounded-full mb-4 text-3xl">
            💬
        </div>
        <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 tracking-tight mb-4">
            {{ config('app.name', 'Laravel') }} 커뮤니티에 오신 것을 환영합니다!
        </h1>
        <p class="text-base md:text-lg text-gray-600 max-w-2xl mx-auto mb-8">
            다양한 관심사와 지식을 함께 나누는 공간입니다. 최신 소식을 확인하고 커뮤니티 멤버들과 함께 대화해 보세요.
        </p>
        <div class="flex flex-wrap justify-center gap-4">
            <a href="{{ route('posts.index') }}" class="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition shadow-sm flex items-center gap-2">
                <span>📋</span>
                <span>게시글 둘러보기</span>
            </a>
            @auth
                <a href="{{ route('posts.create') }}" class="bg-gray-100 text-gray-800 px-6 py-3 rounded-lg font-semibold hover:bg-gray-200 transition flex items-center gap-2">
                    <span>✏️</span>
                    <span>새 글 작성하기</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="bg-gray-100 text-gray-800 px-6 py-3 rounded-lg font-semibold hover:bg-gray-200 transition flex items-center gap-2">
                    <span>🔑</span>
                    <span>로그인</span>
                </a>
                <a href="{{ route('register') }}" class="border border-gray-300 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-50 transition flex items-center gap-2">
                    <span>✨</span>
                    <span>회원가입</span>
                </a>
            @endauth
        </div>
    </div>

    <!-- Quick Cards Section -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="text-2xl mb-2">🔥</div>
            <h2 class="text-lg font-bold text-gray-900 mb-1">인기 & 베스트</h2>
            <p class="text-sm text-gray-600 mb-4">실시간 인기글과 주간 베스트 게시글을 놓치지 말고 확인해보세요.</p>
            <a href="{{ route('posts.index') }}" class="text-sm text-blue-600 hover:underline font-medium">인기글 보러가기 &rarr;</a>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="text-2xl mb-2">💬</div>
            <h2 class="text-lg font-bold text-gray-900 mb-1">댓글 & 대댓글</h2>
            <p class="text-sm text-gray-600 mb-4">게시글에 자유롭게 의견을 남기고 대댓글로 소통을 이어가세요.</p>
            <a href="{{ route('posts.index') }}" class="text-sm text-blue-600 hover:underline font-medium">참여하기 &rarr;</a>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="text-2xl mb-2">🏷️</div>
            <h2 class="text-lg font-bold text-gray-900 mb-1">카테고리 & 태그</h2>
            <p class="text-sm text-gray-600 mb-4">체계적인 카테고리와 해시태그를 통해 원하는 주제의 글을 빠르게 찾으세요.</p>
            <a href="{{ route('posts.index') }}" class="text-sm text-blue-600 hover:underline font-medium">검색해보기 &rarr;</a>
        </div>
    </div>
</div>
@endsection
