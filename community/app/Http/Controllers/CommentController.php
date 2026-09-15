<?php

namespace App\Http\Controllers;

use App\Http\Requests\Comment\StoreRequest;
use App\Http\Requests\Comment\UpdateRequest;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;

use function auth;
use function back;

class CommentController extends Controller
{
    public function store(StoreRequest $request, Post $post): RedirectResponse
    {
        $this->authorize('create', Comment::class);

        $validated = $request->validated();

        $post->comments()->create([
            'user_id' => auth()->id(),
            'parent_id' => $validated['parent_id'] ?? null,
            'content' => $validated['content'],
        ]);
        $post->increment('comment_count');

        return back();
    }

    public function update(UpdateRequest $request, Post $post, Comment $comment): RedirectResponse
    {
        $this->authorize('update', $comment);

        $validated = $request->validated();
        $comment->update($validated);

        return back();
    }

    public function destroy(Post $post, Comment $comment): RedirectResponse
    {
        $this->authorize('delete', $comment);

        $comment->delete();
        $post->decrement('comment_count');

        return back();
    }
}
