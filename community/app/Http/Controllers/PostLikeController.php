<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\RedirectResponse;

use function auth;
use function back;

class PostLikeController extends Controller
{
    public function store(Post $post): RedirectResponse
    {
        $like = $post->likes()
            ->where('user_id', auth()->id())
            ->first();

        if ($like) {
            $like->delete();
            $post->decrement('like_count');
        } else {
            $post->likes()->create([
                'user_id' => auth()->id(),
            ]);
            $post->increment('like_count');
        }

        return back();
    }
}
