<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Arr;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use function fake;
use function min;
use function now;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::disableQueryLog();

        $userIds = User::pluck('id')->toArray();
        $categoryIds = Category::pluck('id')->toArray();

        if (empty($userIds) || empty($categoryIds)) {
            $this->command->error('User 또는 Category 데이터가 먼저 존재해야 합니다.');

            return;
        }

        $totalCount = 200_000; // 총 생성 수량
        $chunkSize = 3_000; // 1회 Bulk Insert당 레코드 수
        $now = now()->toDateTimeString();

        $progressBar = $this->command->getOutput()->createProgressBar($totalCount);
        $progressBar->start();

        for ($i = 0; $i < $totalCount; $i += $chunkSize) {
            $currentChunkSize = min($chunkSize, $totalCount - $i);
            $posts = [];

            for ($j = 0; $j < $currentChunkSize; $j++) {
                $posts[] = [
                    'user_id' => Arr::random($userIds),
                    'category_id' => Arr::random($categoryIds),
                    'title' => fake()->sentence(),
                    'content' => fake()->paragraph(3, true),
                    'is_pinned' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            Post::insert($posts);

            $progressBar->advance($currentChunkSize);
            unset($posts);
        }

        $progressBar->finish();
        $this->command->info('\n20만 건의 Post 데이터 생성이 완료되었습니다.');
    }
}
