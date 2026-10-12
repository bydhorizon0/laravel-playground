<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CommentPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Comment $comment): Response
    {
        return $user->id === $comment->user_id
            ? Response::allow()
            : Response::deny('댓글 수정 권한이 없습니다. 본인 댓글만 수정할 수 있습니다.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Comment $comment): Response
    {
        if ($user->id === $comment->user_id) {
            return Response::allow();
        }

        $comment->loadMissing('post');

        if ($comment->post() !== null && $user->id === $comment->post->user_id) {
            return Response::allow();
        }

        return Response::deny('댓글을 삭제할 권한이 없습니다.');
    }
}
