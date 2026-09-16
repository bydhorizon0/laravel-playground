<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

use function collect;
use function now;

#[Fillable(['name', 'slug'])]
class Tag extends Model
{
    public const CACHE_KEY = 'tags:all';

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class);
    }

    /**
     * @return Collection<int, array{id: int, name: string, slug: string}>
     */
    public static function getCachedAll(): Collection
    {
        $data = Cache::remember(self::CACHE_KEY, now()->addDay(), function (): array {
            return static::query()
                ->select(['id', 'name', 'slug'])
                ->orderBy('name')
                ->get()
                ->toArray();
        });

        return collect($data);
    }
}
