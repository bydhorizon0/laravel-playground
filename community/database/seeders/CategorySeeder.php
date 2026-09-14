<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::create([
            'name' => '자유게시판',
            'slug' => 'free',
            'sort_order' => 1,
        ]);

        Category::create([
            'name' => '질문',
            'slug' => 'question',
            'sort_order' => 2,
        ]);

        Category::create([
            'name' => '정보',
            'slug' => 'info',
            'sort_order' => 3,
        ]);

        Category::create([
            'name' => '공지',
            'slug' => 'notice',
            'sort_order' => 3,
        ]);
    }
}
