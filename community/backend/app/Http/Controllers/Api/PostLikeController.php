<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostLike;
use Gate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

use function now;

#[Middleware('auth:sanctum', only: ['toggle'])]
class PostLikeController extends Controller
{
    public function toggle(Request $request, Post $post): JsonResponse
    {
        Gate::authorize('view', $post);

        $user = $request->user();

        $result = DB::transaction(function () use ($user, $post): array {
            // 먼저 삭제를 시도. 삭제된 행이 있으면 "좋아요 취소"
            $deleted = PostLike::query()
                ->where('post_id', $post->id)
                ->where('user_id', $user->id)
                ->delete();

            $isLiked = $deleted === 0;

            // 삭제된 행이 없으면 "좋아요 등록"
            // 동시에 같은 요청이 와도 유니크 제약 + INSERT IGNORE 덕분에 중복 행이 생기지 않는다.
            if ($isLiked) {
                PostLike::query()
                    ->insertOrIgnore([
                        'post_id' => $post->id,
                        'user_id' => $user->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
            }

            return [
                'is_liked' => $isLiked,
                'likes_count' => PostLike::query()
                    ->where('post_id', $post->id)
                    ->count(),
            ];
        });

        $message = $result['is_liked']
            ? '게시글에 좋아요를 눌렀습니다.'
            : '게시글 좋아요를 취소했습니다.';

        return $this->successResponse($result, $message);
    }
}
