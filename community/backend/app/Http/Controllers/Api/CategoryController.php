<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

use function now;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        // Eloquent 모델 컬렉션 대신 "응답용 배열"을 캐싱한다. (직렬화 크기가 작고 모델 변경에 영향받지 않음)
        $categories = Cache::remember('categories:list', now()->addMinutes(10), function (): array {
            $models = Category::query()
                ->withCount(['posts' => fn (Builder $query): Builder => $query->published()])
                ->oldest()
                ->get();

            return CategoryResource::collection($models)->resolve();
        });

        return $this->successResponse(
            $categories,
            '카테고리 목록을 성공적으로 조회했습니다.'
        );
    }
}
