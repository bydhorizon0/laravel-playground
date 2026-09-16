<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

use function collect;
use function now;

#[Fillable(['name', 'slug', 'sort_order', 'is_active'])]
class Category extends Model
{
    public const CACHE_KEY = 'categories:all';

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * @return Collection<int, array{id: int, name: string, slug: string, sort_order: int}>
     */
    public static function getCachedActive(): Collection
    {
        $data = Cache::remember(self::CACHE_KEY, now()->addDay(), function (): array {
            return static::query()
                ->select(['id', 'name', 'slug', 'sort_order'])
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->toArray();
        });

        return collect($data);
    }

    /**
     * @return Collection<int, array{id: int, name: string, slug: string, sort_order: int}>
     */
    public static function getCachedAll(): Collection
    {
        return static::getCachedActive();
    }
}
