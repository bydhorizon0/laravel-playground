@extends('layouts.app')

@section('title', $post->title)

@section('content')
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
                    <span>조회수 {{ $post->view_count }}</span>
                    <span>좋아요 {{ $post->like_count }}</span>
                    <span>댓글 {{ $post->comment_count }}</span>
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
        <h2 class="text-lg font-bold text-gray-900">댓글 ({{ $post->comment_count }})</h2>

        <!-- 댓글 작성 폼 -->
        <form action="{{ route('comments.store', $post) }}" method="POST" class="space-y-3">
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
        </form>

        <!-- 댓글 목록 -->
        <div class="divide-y divide-gray-100">
            @forelse($post->comments as $comment)
                <div class="py-5">
                    <!-- 댓글 헤더 -->
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-center gap-2">
                            <!-- 프로필 -->
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-sm font-bold text-blue-600">
                                {{ mb_substr($comment->user->name, 0, 1) }}
                            </div>

                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold text-gray-800">
                                        {{ $comment->user->name }}
                                    </span>

                                    <span class="text-xs text-gray-400">
                                        {{ $comment->created_at->diffForHumans() }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- 댓글 액션 -->
                        <div class="flex items-center gap-2 text-xs">
                            <button
                                    type="button"
                                    onclick="document.getElementById('reply-{{ $comment->id }}').classList.toggle('hidden')"
                                    class="rounded px-2 py-1 text-gray-500 transition hover:bg-blue-50 hover:text-blue-600"
                            >
                                답글
                            </button>

                            @can('update', $comment)
                                <button
                                        type="button"
                                        onclick="document.getElementById('edit-comment-{{ $comment->id }}').classList.toggle('hidden')"
                                        class="rounded px-2 py-1 text-gray-500 transition hover:bg-gray-100 hover:text-gray-800"
                                >
                                    수정
                                </button>
                            @endcan

                            @can('delete', $comment)
                                <form
                                        action="{{ route('comments.destroy', [$post, $comment]) }}"
                                        method="POST"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                            type="submit"
                                            class="rounded px-2 py-1 text-gray-500 transition hover:bg-red-50 hover:text-red-600"
                                    >
                                        삭제
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </div>

                    <!-- 댓글 내용 -->
                    <div class="mt-3 pl-10">
                        @if($comment->trashed())
                            <p class="text-sm leading-6 text-gray-700">
                                삭제된 댓글입니다.
                            </p>
                        @else
                            <p class="text-sm leading-6 text-gray-700">
                                {{ $comment->content }}
                            </p>
                        @endif

                        <!-- 댓글 수정 폼 -->
                        @can('update', $comment)
                            <form
                                    id="edit-comment-{{ $comment->id }}"
                                    action="{{ route('comments.update', [$post, $comment]) }}"
                                    method="POST"
                                    class="mt-4 hidden rounded-lg bg-gray-50 p-4"
                            >
                                @csrf
                                @method('PUT')

                                <textarea
                                        name="content"
                                        rows="3"
                                        required
                                        class="w-full resize-none rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-800 outline-none transition placeholder:text-gray-400 focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                                >{{ old('content', $comment->content) }}</textarea>

                                <div class="mt-2 flex justify-end gap-2">
                                    <button
                                            type="button"
                                            onclick="document.getElementById('edit-comment-{{ $comment->id }}').classList.add('hidden')"
                                            class="rounded-lg px-3 py-2 text-sm text-gray-500 transition hover:bg-gray-200"
                                    >
                                        취소
                                    </button>

                                    <button
                                            type="submit"
                                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700"
                                    >
                                        수정
                                    </button>
                                </div>
                            </form>
                        @endcan

                        <!-- 답글 작성 폼 -->
                        <form
                                id="reply-{{ $comment->id }}"
                                action="{{ route('comments.store', $post) }}"
                                method="POST"
                                class="mt-4 hidden rounded-lg bg-gray-50 p-4"
                        >
                            @csrf

                            <input
                                    type="hidden"
                                    name="parent_id"
                                    value="{{ $comment->id }}"
                            >

                            <textarea
                                    name="content"
                                    rows="3"
                                    placeholder="답글을 입력하세요..."
                                    class="w-full resize-none rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-800 outline-none transition placeholder:text-gray-400 focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                            ></textarea>

                            <div class="mt-2 flex justify-end gap-2">
                                <button
                                        type="button"
                                        onclick="document.getElementById('reply-{{ $comment->id }}').classList.add('hidden')"
                                        class="rounded-lg px-3 py-2 text-sm text-gray-500 transition hover:bg-gray-200"
                                >
                                    취소
                                </button>

                                <button
                                        type="submit"
                                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700"
                                >
                                    답글 작성
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- 대댓글 -->
                    @if($comment->replies->isNotEmpty())
                        <div class="mt-4 ml-10 space-y-3 border-l-2 border-gray-100 pl-4">
                            @foreach($comment->replies as $reply)
                                <div class="rounded-lg bg-gray-50 px-4 py-3">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex items-center gap-2">
                                            <div class="flex h-7 w-7 items-center justify-center rounded-full bg-gray-200 text-xs font-bold text-gray-600">
                                                {{ mb_substr($reply->user->name, 0, 1) }}
                                            </div>

                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-sm font-semibold text-gray-700">
                                                        {{ $reply->user->name }}
                                                    </span>

                                                    <span class="text-xs text-gray-400">
                                                        {{ $reply->created_at->diffForHumans() }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        @can('delete', $reply)
                                            <form
                                                    action="{{ route('comments.destroy', [$post, $reply]) }}"
                                                    method="POST"
                                            >
                                                @csrf
                                                @method('DELETE')

                                                <button
                                                        type="submit"
                                                        class="text-xs text-gray-400 transition hover:text-red-500"
                                                >
                                                    삭제
                                                </button>
                                            </form>
                                        @endcan
                                    </div>

                                    <p class="mt-2 text-sm leading-6 text-gray-700">
                                        {{ $reply->content }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <div class="py-10 text-center">
                    <p class="text-sm text-gray-400">
                        아직 등록된 댓글이 없습니다.
                    </p>

                    <p class="mt-1 text-xs text-gray-300">
                        첫 번째 댓글을 남겨보세요.
                    </p>
                </div>
            @endforelse
        </div>
    </section>
</div>
@endsection