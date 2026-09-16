@extends('layouts.app')

@section('title', '게시글 목록')

@section('content')
<div class="max-w-4xl mx-auto space-y-4">
    <!-- 실시간 인기글 & 주간 베스트 섹션 -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <!-- 실시간 인기글 TOP 10 -->
        <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200">
            <h3 class="text-base font-bold text-gray-900 mb-3 flex items-center gap-1.5">
                <span>🔥 실시간 인기글</span>
            </h3>
            <ul class="divide-y divide-gray-100 text-sm">
                @forelse($realtimePopularPosts as $index => $popularPost)
                    <li class="py-2 flex items-center justify-between">
                        <div class="flex items-center gap-2.5 truncate mr-2">
                            <span class="font-bold text-xs {{ $index < 3 ? 'text-red-500' : 'text-gray-400' }} w-4">
                                {{ $index + 1 }}
                            </span>
                            <a href="{{ route('posts.show', $popularPost['id']) }}" class="text-gray-800 hover:text-blue-600 truncate">
                                {{ $popularPost['title'] }}
                            </a>
                        </div>
                        <div class="text-xs text-gray-400 flex items-center gap-2 shrink-0">
                            <span>❤️ {{ $popularPost['like_count'] }}</span>
                            <span>💬 {{ $popularPost['comment_count'] }}</span>
                        </div>
                    </li>
                @empty
                    <li class="py-2 text-xs text-gray-400">인기글이 없습니다.</li>
                @endforelse
            </ul>
        </div>

        <!-- 주간 베스트 TOP 10 -->
        <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200">
            <h3 class="text-base font-bold text-gray-900 mb-3 flex items-center gap-1.5">
                <span>👑 주간 베스트</span>
            </h3>
            <ul class="divide-y divide-gray-100 text-sm">
                @forelse($weeklyBestPosts as $index => $weeklyPost)
                    <li class="py-2 flex items-center justify-between">
                        <div class="flex items-center gap-2.5 truncate mr-2">
                            <span class="font-bold text-xs {{ $index < 3 ? 'text-yellow-500' : 'text-gray-400' }} w-4">
                                {{ $index + 1 }}
                            </span>
                            <a href="{{ route('posts.show', $weeklyPost['id']) }}" class="text-gray-800 hover:text-blue-600 truncate">
                                {{ $weeklyPost['title'] }}
                            </a>
                        </div>
                        <div class="text-xs text-gray-400 flex items-center gap-2 shrink-0">
                            <span>❤️ {{ $weeklyPost['like_count'] }}</span>
                            <span>💬 {{ $weeklyPost['comment_count'] }}</span>
                        </div>
                    </li>
                @empty
                    <li class="py-2 text-xs text-gray-400">베스트 글이 없습니다.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <!-- 검색 및 글쓰기 버튼 -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 mb-6">
        <form action="{{ route('posts.index') }}" method="GET" class="flex gap-2 flex-1">
            <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="제목 또는 내용 검색"
                    class="border border-gray-300 rounded px-4 py-2 w-full max-w-xs focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
            >
            <button
                    type="submit"
                    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 font-medium"
            >
                검색
            </button>

            @if(request('search'))
                <a
                        href="{{ route('posts.index') }}"
                        class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300 flex items-center font-medium"
                >
                    초기화
                </a>
            @endif
        </form>

        @auth
            <a href="{{ route('posts.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 font-medium flex items-center justify-center gap-1.5 shrink-0">
                <span>✏️</span>
                <span>글쓰기</span>
            </a>
        @endauth
    </div>

    <!-- 게시글 목록 -->
    <div class="space-y-4">
        @forelse($posts as $post)
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h2 class="text-xl font-bold text-gray-800 mb-2">
                    <a href="{{ route('posts.show', $post) }}" class="hover:text-blue-600">
                        {{ $post->title }}
                    </a>
                </h2>
                <div class="text-sm text-gray-600 space-y-1">
                    <p>작성자: {{ $post->user->name }}</p>
                    <p>카테고리: {{ $post->category->name }}</p>
                    <p>조회수: {{ $post->view_count }}</p>
                    <p>댓글 수: {{ $post->comment_count }}</p>
                    <p>좋아요 수: {{ $post->like_count }}</p>
                    @auth
                        <form action="{{ route('posts.like', $post) }}" method="POST" class="pt-1">
                            @csrf
                            @if($post->is_liked)
                                <button
                                        type="submit"
                                        class="px-3 py-1 bg-red-500 text-white rounded text-sm hover:bg-red-600"
                                >
                                    ❤️ 좋아요
                                </button>
                            @else
                                <button
                                        type="submit"
                                        class="px-3 py-1 bg-gray-200 text-gray-700 rounded text-sm hover:bg-gray-300"
                                >
                                    🤍 좋아요
                                </button>
                            @endif
                        </form>
                    @endauth
                </div>
                @if($post->tags->isNotEmpty())
                    <div class="mt-3 flex items-center gap-1">
                        <span class="text-xs font-semibold text-gray-500">태그:</span>
                        @foreach($post->tags as $tag)
                            <span class="bg-blue-100 text-blue-800 text-xs px-2 py-0.5 rounded">{{ $tag->name }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-white p-12 rounded-lg shadow-sm border border-gray-200 text-center text-gray-400">
                등록된 게시글이 없습니다.
            </div>
        @endforelse
    </div>

    <!-- 페이지네이션 네비게이션 버튼 출력 -->
    <div class="mt-6">
        {{ $posts->links() }}
    </div>
</div>
@endsection
