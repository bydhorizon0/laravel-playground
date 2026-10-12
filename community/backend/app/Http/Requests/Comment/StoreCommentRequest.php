<?php

namespace App\Http\Requests\Comment;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'min:2', 'max:1000'],
            'parent_id' => ['nullable', 'integer', Rule::exists('comments', 'id')->whereNull('deleted_at')],
        ];
    }

    /**
     * 라우트 파라미터와 부모 댓글의 관계 유효성 검증
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            $parentId = $this->input('parent_id');
            if (! $parentId) {
                return;
            }

            // 라우트 모델 바인딩 여부에 따른 안정한 post_id 추출
            $routePost = $this->route('post');
            $postId = $routePost instanceof Post ? $routePost->id : (int) $routePost;

            $parentComment = Comment::query()->find($parentId);

            if (! $parentComment) {
                $v->errors()->add('parent_id', '부모 댓글을 찾을 수 없습니다.');

                return;
            }

            if ($parentComment->post_id !== $postId) {
                $v->errors()->add('parent_id', '부모 댓글이 현재 게시글에 속해 있지 않습니다.');

                return;
            }

            if ($parentComment->parent_id !== null) {
                $v->errors()->add('parent_id', '답글에는 추가 답글을 작성할 수 없습니다.');
            }
        });
    }
}
