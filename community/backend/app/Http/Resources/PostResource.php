<?php

namespace App\Http\Resources;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Post */
class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $currentUser = $request->user('sanctum');

        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'status' => $this->status,
            'view_count' => $this->view_count,

            // withCount()로 집계된 경우에만 포함 (미집계 시 키 자체를 생략)
            'likes_count' => $this->likes_count,
            'comments_count' => $this->comments_count,

            // 로그인 여부에 따른 좋아요 (비로그인: false, 로그인 시 boolean)
            'is_liked' => $this->isLikedBy($currentUser),

            // N+1 예뱡을 위해 whenLoaded 사용
            'author' => new AuthorResource($this->whenLoaded('author')),
            'category' => new CategoryResource($this->whenLoaded('category')),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'published_at' => $this->published_at,
        ];
    }
}
