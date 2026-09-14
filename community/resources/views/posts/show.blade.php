<!doctype html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <title>{{ $post->title }}</title>
</head>
<body class="bg-gray-50 p-8">

<div class="max-w-4xl mx-auto space-y-6">
    <!-- 목록으로 돌아가기 버튼 -->
    <div>
        <a href="{{ route('posts.index') }}" class="text-sm text-gray-600 hover:text-gray-900 flex items-center gap-1">
            &larr; 목록으로 돌아가기
        </a>
    </div>

    <!-- 게시글 본문 영역 -->
    <article class="bg-white p-8 rounded-lg shadow-sm border border-gray-200 space-y-6">
        <!-- 헤더: 카테고리, 제목, 작성자 정보 -->
        <header class="border-b border-gray-100 pb-4">
            <div class="flex items-center gap-2 mb-2">
                <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                    {{ $post->category->name }}
                </span>
                @if($post->is_pinned)
                    <span class="bg-red-100 text-red-800 text-xs font-semibold px-2.5 py-0.5 rounded">고정글</span>
                @endif
            </div>

            <h1 class="text-2xl font-bold text-gray-900 mb-3">{{ $post->title }}</h1>

            <div class="flex items-center justify-between text-sm text-gray-500">
                <div class="flex items-center gap-4">
                    <span>작성자: <strong class="text-gray-700">{{ $post->user->name }}</strong></span>
                    <span>작성일: {{ $post->created_at->format('Y-m-d H:i') }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <span>좋아요 {{ $post->likes_count }}</span>
                    <span>댓글 {{ $post->comments_count }}</span>
                </div>
            </div>
        </header>

        <!-- 본문 내용 -->
        <div class="text-gray-800 leading-relaxed whitespace-pre-line min-h-[150px]">
            {{ $post->content }}
        </div>

        <!-- 태그 목록 -->
        @if($post->tags->isNotEmpty())
            <div class="flex items-center gap-2 pt-4 border-t border-gray-100">
                <span class="text-xs font-semibold text-gray-500">태그:</span>
                @foreach($post->tags as $tag)
                    <span class="bg-gray-100 text-gray-700 text-xs px-2.5 py-1 rounded-full">
                        #{{ $tag->name }}
                    </span>
                @endforeach
            </div>
        @endif

        <!-- 첨부파일 목록 -->
        @if($post->attachments->isNotEmpty())
            <div class="pt-4 border-t border-gray-100">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">첨부파일</h3>
                <ul class="divide-y divide-gray-100 border border-gray-200 rounded-md">
                    @foreach($post->attachments as $attachment)
                        <li class="p-3 flex items-center justify-between text-sm">
                            <span class="text-gray-600">{{ $attachment->original_name }}</span>
                            <a href="{{ Storage::url($attachment->path) }}" download
                               class="text-blue-600 hover:underline">
                                다운로드
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- 관리 버튼 영역 (수정 / 삭제) -->
        <div class="flex justify-end gap-2 pt-4 border-t border-gray-100">
            @can('update', $post)
                <a href="{{ route('posts.edit', $post) }}"
                   class="bg-gray-100 text-gray-700 px-4 py-2 rounded text-sm hover:bg-gray-200">
                    수정
                </a>
            @endcan
            @can('delete', $post)
                <form action="{{ route('posts.destroy', $post) }}" method="POST"
                      onsubmit="return confirm('정말 삭제하시겠습니까?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded text-sm hover:bg-red-600">
                        삭제
                    </button>
                </form>
            @endcan
        </div>
    </article>

    <!-- 댓글 영역 -->
    <section class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 space-y-6">
        <h2 class="text-lg font-bold text-gray-900">댓글 ({{ $post->comments_count }})</h2>

        {{--<!-- 댓글 작성 폼 -->
        <form action="{{ route('posts.comments.store', $post) }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <textarea
                        name="content"
                        rows="3"
                        placeholder="댓글을 작성하세요..."
                        class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                ></textarea>
            </div>
            <div class="flex justify-end">
                <button type="submit"
                        class="bg-blue-500 text-white px-4 py-2 rounded text-sm font-medium hover:bg-blue-600">
                    댓글 등록
                </button>
            </div>
        </form>--}}

        <!-- 댓글 목록 -->
        <div class="divide-y divide-gray-100">
            @forelse($post->comments as $comment)
                <div class="py-4 space-y-1">
                    <div class="flex items-center justify-between text-xs text-gray-500">
                        <span class="font-semibold text-gray-700">{{ $comment->user->name }}</span>
                        <span>{{ $comment->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-sm text-gray-800 leading-normal">{{ $comment->content }}</p>
                </div>
            @empty
                <p class="text-center text-sm text-gray-500 py-4">등록된 댓글이 없습니다.</p>
            @endforelse
        </div>
    </section>
</div>

</body>
</html>