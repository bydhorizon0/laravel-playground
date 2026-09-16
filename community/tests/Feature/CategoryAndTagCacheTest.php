<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CategoryAndTagCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_cached_active_returns_only_active_categories_in_sort_order_as_collection(): void
    {
        Category::create([
            'name' => 'Second',
            'slug' => 'second',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        Category::create([
            'name' => 'First',
            'slug' => 'first',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Category::create([
            'name' => 'Inactive',
            'slug' => 'inactive',
            'sort_order' => 0,
            'is_active' => false,
        ]);

        $cached = Category::getCachedActive();

        $this->assertInstanceOf(Collection::class, $cached);
        $this->assertCount(2, $cached);
        $this->assertSame('First', $cached->first()['name']);
        $this->assertSame('Second', $cached->last()['name']);

        // 캐시 저장소에는 안전하고 가벼운 순수 배열로 저장되어 있는지 검증
        $this->assertTrue(Cache::has(Category::CACHE_KEY));
        $this->assertIsArray(Cache::get(Category::CACHE_KEY));
    }

    public function test_category_cache_is_invalidated_on_save_and_delete(): void
    {
        $category = Category::create([
            'name' => 'News',
            'slug' => 'news',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Category::getCachedActive();
        $this->assertTrue(Cache::has(Category::CACHE_KEY));

        $category->update(['name' => 'Updated News']);
        $this->assertFalse(Cache::has(Category::CACHE_KEY));

        Category::getCachedActive();
        $this->assertTrue(Cache::has(Category::CACHE_KEY));

        $category->delete();
        $this->assertFalse(Cache::has(Category::CACHE_KEY));
    }

    public function test_tag_cached_all_returns_tags_ordered_by_name_as_collection(): void
    {
        Tag::create(['name' => 'Zebra', 'slug' => 'zebra']);
        Tag::create(['name' => 'Apple', 'slug' => 'apple']);

        $cached = Tag::getCachedAll();

        $this->assertInstanceOf(Collection::class, $cached);
        $this->assertCount(2, $cached);
        $this->assertSame('Apple', $cached->first()['name']);
        $this->assertSame('Zebra', $cached->last()['name']);

        // 캐시 저장소에는 순수 배열로 저장되어 있는지 검증
        $this->assertTrue(Cache::has(Tag::CACHE_KEY));
        $this->assertIsArray(Cache::get(Tag::CACHE_KEY));
    }

    public function test_tag_cache_is_invalidated_on_save_and_delete(): void
    {
        $tag = Tag::create(['name' => 'PHP', 'slug' => 'php']);

        Tag::getCachedAll();
        $this->assertTrue(Cache::has(Tag::CACHE_KEY));

        $tag->update(['name' => 'Laravel']);
        $this->assertFalse(Cache::has(Tag::CACHE_KEY));

        Tag::getCachedAll();
        $this->assertTrue(Cache::has(Tag::CACHE_KEY));

        $tag->delete();
        $this->assertFalse(Cache::has(Tag::CACHE_KEY));
    }
}
