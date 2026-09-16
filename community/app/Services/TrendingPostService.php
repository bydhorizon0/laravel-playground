<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Support\Facades\Cache;

use function now;

/**
 * 단순 조회수만 보면 어뷰징(새로고침)에 취약하므로, 2026_09_13_130644_create_posts_table.php 테이블에 있는 컬럼들을 조합해 가중치를 주는 것이 일반적이다:
 * • 주간 베스트: 최근 7일간 등록된 글 중 (좋아요 * 3) + (댓글수 * 2) + 조회수 상위 10건
 * • 실시간 인기글: 최근 24시간 내 등록된 글 중 (좋아요 * 5) + (댓글수 * 3) + 조회수 상위 10건
 *
 * 컨트롤러에 집계 쿼리를 넣기보다 재사용하기 좋게 전용 Service 클래스를 만드는 것이 좋다.
 * (앞서 확인한 Laravel 13의 객체 역직렬화 보안 문제를 피하고 Redis 메모리를 절약하기 위해, 필요한 컬럼만 select하여 배열 형태로 캐싱하는 것을 권장한다.)
 */
class TrendingPostService
{
    /**
     * 주간 베스트 TOP 10 (30분 캐시)
     */
    public function getWeeklyBest(): array
    {
        return Cache::remember('posts:trending:weekly', now()->addMinutes(30), function (): array {
            return Post::query()
                ->select(['id', 'title', 'view_count', 'like_count', 'comment_count'])
                ->where('is_hidden', false)
                ->where('created_at', '>=', now()->subDays(7))
                ->orderByRaw('(like_count * 3 + comment_count * 2 + view_count) DESC')
                ->limit(10)
                ->get()
                ->toArray(); // 모델 객체 대신 순수 배열로 캐싱 (안전 & 가벼움)
        });
    }

    /**
     * 실시간 인기글 TOP 10 (5분 캐시)
     */
    public function getRealtimePopular(): array
    {
        return Cache::remember('posts:trending:realtime', now()->addMinutes(5), function (): array {
            return Post::query()
                ->select(['id', 'title', 'view_count', 'like_count', 'comment_count'])
                ->where('is_hidden', false)
                ->where('created_at', '>=', now()->subDay())
                ->orderByRaw('(like_count * 5 + comment_count * 3 + view_count) DESC')
                ->limit(10)
                ->get()
                ->toArray();
        });
    }
}
