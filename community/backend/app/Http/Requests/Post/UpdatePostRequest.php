<?php

namespace App\Http\Requests\Post;

use App\Enums\Enums\PostStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePostRequest extends FormRequest
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
            'category_id' => ['sometimes', 'integer', Rule::exists('categories', 'id')],
            'title' => ['sometimes', 'string', 'min:2', 'max:200'],
            'content' => ['sometimes', 'string', 'min:5'],
            'status' => ['sometimes', Rule::enum(PostStatus::class)],
        ];
    }
}
