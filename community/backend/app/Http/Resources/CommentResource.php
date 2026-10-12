<?php

namespace App\Http\Resources;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Comment */
class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'post_id' => $this->post_id,
            'parent_id' => $this->parent_id,
            'content' => $this->trashed() ? '삭제된 댓글입니다.' : $this->content,
            'is_deleted' => $this->trashed(),

            // 삭제된 댓글의 작성자 정보는 노출하지 않는다.
            'author' => $this->trashed() ? null : new AuthorResource($this->whenLoaded('author')),

            // 재귀적 자식 댓글
            'replies' => CommentResource::collection($this->whenLoaded('replies')),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
