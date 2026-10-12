<?php

namespace App\Models;

use App\Enums\Enums\PostStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

use function array_key_exists;

#[Fillable(['category_id', 'title', 'content', 'status', 'published_at'])]
class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $attributes = [
        'status' => 'published',
        'view_count' => 0,
    ];

    protected function casts(): array
    {
        return [
            'view_count' => 'integer',
            'status' => PostStatus::class,
            'published_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasMany<Comment, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'post_id');
    }

    /**
     * @return HasMany<Comment, $this>
     */
    public function commentsWithTrashed(): HasMany
    {
        return $this->hasMany(Comment::class, 'post_id')->withTrashed();
    }

    /**
     * @return HasMany<Comment, $this>
     */
    public function rootComments(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    /**
     * @return HasMany<PostLike, $this>
     */
    public function likes(): HasMany
    {
        return $this->hasMany(PostLike::class, 'post_id');
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function likedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'post_likes')->withTimestamps();
    }

    /**
     * 특정 사용자가 이 글에 좋아요를 눌렀는지 여부 확인
     *
     * 판별 우선순위: (1) withLikedBy() 스코프가 미리 계산해 둔 `liked_by_me` 값
     * → (2) 이미 로드된 `likes` 컬렉션 → (3) exists() 쿼리
     * 목록 API에서는 (1)을 사용해야 게시글 수만큼 쿼리가 늘어나는 N+1을 피할 수 있습니다.
     */
    public function isLikedBy(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        if (array_key_exists('liked_by_me', $this->attributes)) {
            return (bool) $this->attributes['liked_by_me'];
        }

        if ($this->relationLoaded('likes')) {
            return $this->likes->contains(fn (PostLike $like): bool => $like->user_id === $user->id);
        }

        return $this->likes()->where('user_id', $user->id)->exists();
    }

    /**
     * 공개(Published)된 글만 필터링하는 로컬 스코프
     *
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', PostStatus::Published);
    }

    /**
     * 목록 조회 시 "내가 좋아요 했는지"를 서브쿼리로 한 번에 계산하는 로컬 스코프
     *
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopeWithLikeBy(Builder $query, ?User $user): Builder
    {
        if ($user === null) {
            return $query;
        }

        return $query->withExists([
            'likes as liked_by_me' => fn (Builder $q): Builder => $q->where('user_id', $user->id),
        ]);
    }

    /**
     * 카테고리/작성자/검색어 필터링 스코프
     *
     * @param  Builder<Post>  $query
     * @param  array{category_id?: int|string|null, user_id?: int|string|null, search?: string|null}  $filters
     * @return Builder<Post>
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        // 1. 카테고리 ID / 작성자 ID 필터링
        $query->when($filters['category_id'] ?? null, function (Builder $q, int|string $categoryId): void {
            $q->where('category_id', $categoryId);
        });

        $query->when($filters['user_id'] ?? null, function (Builder $q, int|string $userId): void {
            $q->where('user_id', $userId);
        });

        // 2. 검색어 (제목 또는 본문 검색). LIKE 와일드카드(%, _)는 이스케이프하여 문자 그대로 검색합니다.
        $query->when($filters['search'] ?? null, function (Builder $q, string $search): void {
            $keyword = '%'.addcslashes($search, '%_\\').'%';

            $q->where(function (Builder $sub) use ($keyword): void {
                $sub->where('title', 'like', $keyword)
                    ->orWhere('content', 'like', $keyword);
            });
        });

        return $query;
    }
}
