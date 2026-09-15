<?php

namespace App\Http\Controllers;

use App\Http\Requests\Post\CreateRequest;
use App\Http\Requests\Post\UpdateRequest;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

use function auth;
use function compact;
use function redirect;
use function view;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $posts = Post::query()
            // 1. N+1 문제 예방: 단일/다대다 관계 Eager Loading
            ->with(['user', 'category', 'tags'])
            ->withExists([
                'likes as is_liked' => function ($query) {
                    $query->where('user_id', auth()->id());
                },
            ])
            // 검색어가 입력되었을 때만 쿼리 조건 추가
            ->when($request->filled('search'), function (Builder $query) use ($request) {
                $search = $request->input('search');
                // OR 조건 적용 시 다른 쿼리 조건에 영향을 주지 않도록 파라미터 그룹화
                $query->where(function (Builder $query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                });
            })
            // 최신순 정렬
            ->latest('created_at')
            ->latest('id')
            // 페이지네이션
            ->paginate(20)
            // 검색/필터링 Query String
            ->withQueryString();

        return view('posts.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $categories = Category::all();
        $tags = Tag::all();

        return view('posts.create', compact('categories', 'tags'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $post = Post::create([
            'user_id' => $request->user()->id,
            'category_id' => $validated['category_id'],
            'title' => $validated['title'],
            'content' => $validated['content'],
        ]);

        // 태그 다대다 관계 첨부 예시 (Request에 tags 배열이 포함된 경우)
        /*if (!empty($validated['tags'])) {
            $post->tags()->attach($validated['tags']);
        }*/

        return redirect()->route('posts.show', compact('post'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post): View
    {
        $post->load([
            'user',
            'category',
            'tags',
            'attachments',
            'reports',
            'comments' => function ($query) {
                // 댓글을 최신순으로 정렬하고, 댓글 작성자 정보를 함께 로드
                $query->whereNull('parent_id')
                    ->latest()
                    ->with(['user', 'replies.user']);
            },
        ]);

        $post->increment('view_count');

        return view('posts.show', compact('post'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post): View
    {
        // 백엔드 권한 검증 (권한 없을 시 403 Forbidden)
        $this->authorize('update', $post);

        $post->load([
            'tags',
            'attachments',
        ]);

        // 수정 폼 내 선택 변경을 위한 전체 카테고리, 태그 목록 로드
        $categories = Category::all();
        $tags = Tag::all();

        return view('posts.edit', compact('post', 'categories', 'tags'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, Post $post): RedirectResponse
    {
        $this->authorize('update', $post);

        $validated = $request->validated();

        $post->update($validated);

        // 태그 동기화 예시
        if (array_key_exists('tags', $validated)) {
            $post->tags()->sync($validated['tags']);
        }

        return redirect()->route('posts.show', compact('post'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post): RedirectResponse
    {
        $this->authorize('delete', $post);

        $post->delete();

        return redirect()->route('posts.index');
    }
}
