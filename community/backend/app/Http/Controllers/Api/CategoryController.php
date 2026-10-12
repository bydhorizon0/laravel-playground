<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::query()
            ->withCount(['posts' => fn (Builder $query): Builder => $query->published()])
            ->oldest()
            ->get();

        return $this->successResponse(
            CategoryResource::collection($categories),
            '카테고리 목록을 성공적으로 조회했습니다.'
        );
    }
}
