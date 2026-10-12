<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use Gate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;

use function intval;
use function max;
use function min;

#[Middleware('auth:sanctum', only: ['store', 'update', 'destroy'])]
class CommentController extends Controller
{
    public function index(Request $request, Post $post): JsonResponse
    {
        Gate::forUser($request->user('sanctum'))->authorize('view', $post);

        $perPage = $request->query('per_page', 20)
                |> intval(...)
                |> (fn (int $val) => max($val, 1))
                |> (fn (int $val) => min($val, 50));

        // 최상위 댓글(parent_id !== null)만 페이징하고, 대댓글(replies)과 작성자는 Eager Loading 한다.
        $comments = $post->commentsWithTrashed()
            ->whereNull('parent_id')
            ->where(function (Builder $query) {
                $query->whereNull('deleted_at')->orHas('replies');
            })
            ->with([
                'author',
                'replies' => fn (HasMany $query): HasMany => $query->withTrashed()->with('author'),
            ])
            ->oldest()
            ->paginate($perPage);

        return $this->paginatedResponse(
            $comments,
            CommentResource::collection($comments),
            '댓글 목록을 성공적으로 조회했습니다.'
        );
    }

    public function store(StoreCommentRequest $request, Post $post): JsonResponse
    {
        Gate::authorize('create', Comment::class);
        // 다른 사람의 임시저장 글에는 댓글을 달 수 없다.
        Gate::authorize('view', $post);

        $validated = $request->validated();
        $user = $request->user();

        $comment = $post->comments()->create([
            'user_id' => $user->id,
            'parent_id' => $validated['parent_id'] ?? null,
            'content' => $validated['content'],
        ]);

        $comment->load(['author', 'replies']);

        return $this->successResponse(
            new CommentResource($comment),
            '댓글이 성공적으로 등록되었습니다.'
        );
    }

    public function update(UpdateCommentRequest $request, Comment $comment): JsonResponse
    {
        Gate::authorize('update', $comment);

        $comment->update($request->validated());
        $comment->load(['author', 'replies']);

        return $this->successResponse(
            new CommentResource($comment),
            '댓글이 성공적으로 수정되었습니다.'
        );
    }

    public function destroy(Comment $comment): JsonResponse
    {
        Gate::authorize('delete', $comment);

        $comment->delete();

        return $this->successResponse(null, '댓글이 성공적으로 삭제되었습니다.');
    }
}
