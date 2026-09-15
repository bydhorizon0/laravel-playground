<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <title>Document</title>
</head>
<body class="bg-gray-50 p-8">

<div class="max-w-4xl mx-auto space-y-4">
    <form action="{{ route('posts.index') }}" method="GET" class="flex gap-2 mb-6">
        <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="제목 또는 내용 검색"
                class="border border-gray-300 rounded px-4 py-2 w-full max-w-xs focus:outline-none focus:ring-2 focus:ring-blue-500"
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
    <!-- 게시글 목록 -->
    @foreach($posts as $post)
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <h2 class="text-xl font-bold text-gray-800 mb-2">
                <a href="{{ route('posts.show', $post) }}">
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
                    <form action="{{ route('posts.like', $post) }}" method="POST">
                        @csrf
                        @if($post->is_liked)
                            <button
                                    type="submit"
                                    class="px-3 py-1 bg-red-500 text-white rounded"
                            >
                                ❤️ 좋아요
                            </button>
                        @else
                            <button
                                    type="submit"
                                    class="px-3 py-1 bg-gray-200 text-gray-700 rounded"
                            >
                                🤍 좋아요
                            </button>
                        @endif
                    </form>
                @endauth
            </div>
            <div class="mt-3 flex items-center gap-1">
                <span class="text-xs font-semibold text-gray-500">태그:</span>
                @foreach($post->tags as $tag)
                    <span class="bg-blue-100 text-blue-800 text-xs px-2 py-0.5 rounded">{{ $tag->name }}</span>
                @endforeach
            </div>
        </div>
    @endforeach

    <!-- 페이지네이션 네비게이션 버튼 출력 -->
    <div class="mt-6">
        {{ $posts->links() }}
    </div>

</div>

</body>
</html>
