@extends('layouts.app')

@section('title', '새 게시글 작성')

@section('content')
<div class="max-w-4xl mx-auto space-y-4">
    <!-- 목록으로 돌아가기 버튼 -->
    <div>
        <a href="{{ route('posts.index') }}" class="text-sm text-gray-600 hover:text-gray-900 flex items-center gap-1">
            &larr; 목록으로 돌아가기
        </a>
    </div>

    <div class="bg-white p-6 sm:p-8 rounded-lg shadow-sm border border-gray-200">
        <h1 class="text-2xl font-bold text-gray-900 mb-6 pb-3 border-b border-gray-100">새 게시글 작성</h1>

        <form action="{{ route('posts.store') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">
                    카테고리 <span class="text-red-500">*</span>
                </label>
                <select
                        name="category_id"
                        id="category_id"
                        required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                >
                    <option value="">카테고리를 선택하세요</option>
                    @foreach($categories as $category)
                        <option value="{{ data_get($category, 'id') }}" {{ old('category_id') == data_get($category, 'id') ? 'selected' : '' }}>
                            {{ data_get($category, 'name') }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                    제목 <span class="text-red-500">*</span>
                </label>
                <input
                        type="text"
                        name="title"
                        id="title"
                        value="{{ old('title') }}"
                        required
                        placeholder="게시글 제목을 입력하세요"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                @error('title')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="content" class="block text-sm font-medium text-gray-700 mb-1">
                    내용 <span class="text-red-500">*</span>
                </label>
                <textarea
                        name="content"
                        id="content"
                        rows="12"
                        required
                        placeholder="게시글 내용을 작성하세요..."
                        class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                >{{ old('content') }}</textarea>
                @error('content')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('posts.index') }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition">
                    취소
                </a>
                <button
                        type="submit"
                        class="bg-blue-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition"
                >
                    작성 완료
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
