<?php

namespace App\Http\Controllers;

use App\Http\Requests\Comment\StoreRequest;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

use function auth;
use function back;

class CommentController extends Controller
{
    public function store(StoreRequest $request, Post $post): RedirectResponse
    {
        $validated = $request->validated();

        $post->comments()->create([
            'user_id' => auth()->id(),
            'parent_id' => $validated['parent_id'] ?? null,
            'content' => $validated['content'],
        ]);

        return back();
    }

    public function update(Request $request, Comment $comment)
    {
        //
    }

    public function destroy(Post $post, Comment $comment): RedirectResponse
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return back();
    }
}
