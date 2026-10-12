<?php

namespace App\Http\Controllers\Api;

use App\Enums\Enums\PostStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Cache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

use function intval;
use function max;
use function now;

#[Middleware('auth:sanctum', only: ['store', 'update', 'destroy'])]
class PostController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 15)
                |> intval(...)
                |> (fn (int $val) => max($val, 1))
                |> (fn (int $val) => min($val, 50));

        $query = Post::query()
            ->published()
            ->with(['author', 'category'])
            ->withCount(['likes', 'comments'])
            ->withLikeBy($request->user('sanctum'))
            ->filter([
                'category_id' => $request->query('category_id'),
                'user_id' => $request->query('user_id'),
                'search' => $request->query('search'),
            ]);

        // 정렬은 허용된 값만 받는 화이트리스트 방식으로 처리한다.
        match ($request->query('sort', 'latest')) {
            'oldest' => $query->oldest('id'),
            'popular' => $query->orderByDesc('view_count')->orderByDesc('id'),
            default => $query->latest('id'),
        };

        $posts = $query->paginate($perPage);

        return $this->paginatedResponse(
            $posts,
            PostResource::collection($posts),
            '게시글 목록을 성공적으로 조회했습니다.'
        );
    }

    public function store(StorePostRequest $request): JsonResponse
    {
        Gate::authorize('create', Post::class);

        $validated = $request->validated();

        $user = $request->user();

        $status = isset($validated['status'])
            ? PostStatus::from($validated['status'])
            : PostStatus::Published;

        $post = $user->posts()->create([
            'category_id' => $validated['category_id'],
            'title' => $validated['title'],
            'content' => $validated['content'],
            'status' => $status,
            'published_at' => $status === PostStatus::Published ? now() : null,
        ]);

        $post->load(['author', 'category'])
            ->loadCount(['likes', 'comments']);

        return $this->successResponse(
            new PostResource($post),
            '게시글이 성공적으로 등록되었습니다.'
        );
    }

    public function show(Request $request, Post $post): JsonResponse
    {
        $viewer = $request->user('sanctum');

        Gate::forUser($viewer)->authorize('view', $post);

        $viewerKey = $viewer?->getAuthIdentifier() ?? $request->ip();
        if (Cache::add("posts:{$post->id}:viewed:{$viewerKey}", true, now()->addMinutes(30))) {
            $post->increment('view_count');
        }

        $post->load(['author', 'category'])
            ->loadCount(['likes', 'comments']);

        return $this->successResponse(
            new PostResource($post),
            '게시글 상세 정보를 조회했습니다.'
        );
    }

    public function update(UpdatePostRequest $request, Post $post): JsonResponse
    {
        Gate::authorize('update', $post);

        $validated = $request->validated();

        if (($validated['status'] ?? null) === PostStatus::Published->value && $post->published_at === null) {
            $validated['published_at'] = now();
        }

        $post->update($validated);
        $post->load(['author', 'category'])
            ->loadCount(['likes', 'comments']);

        return $this->successResponse(
            new PostResource($post),
            '게시글이 성공적으로 수정되었습니다.'
        );
    }

    public function destroy(Post $post): JsonResponse
    {
        Gate::authorize('delete', $post);

        $post->delete();

        return $this->successResponse(null, '게시글이 성공적으로 삭제되었습니다.');
    }
}
