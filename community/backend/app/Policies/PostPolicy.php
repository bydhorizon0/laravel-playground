<?php

namespace App\Policies;

use App\Enums\Enums\PostStatus;
use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PostPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Post $post): Response
    {
        if ($post->status === PostStatus::Published) {
            return Response::allow();
        }

        return $user?->id === $post->user_id
            ? Response::allow()
            : Response::deny('발행되지 않은 임시 저장 글은 작성자만 조회할 수 있습니다.');
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
    public function update(User $user, Post $post): Response
    {
        return $user->id === $post->user_id
            ? Response::allow()
            : Response::deny('게시글 수정 권한이 없습니다. 본인이 작성한 글만 수정할 수 있습니다.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Post $post): Response
    {
        return $user->id === $post->user_id
            ? Response::allow()
            : Response::deny('게시글 삭제 권한이 없습니다. 본인이 작성한 글만 삭제할 수 있습니다.');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Post $post): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Post $post): bool
    {
        return $user->isAdmin();
    }
}
