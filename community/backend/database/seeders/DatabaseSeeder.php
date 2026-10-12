<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. 관리자(Admin) 계정 생성 (password는 User 모델의 'hashed' 캐스트가 자동 해시)
        $admin = User::query()->firstWhere('email', 'admin@example.com')
            ?? User::factory()->admin()->create([
                'name' => '최고관리자',
                'email' => 'admin@example.com',
                'password' => 'password123',
            ]);

        // 2. 테스트용 일반 사용자 + 추가 사용자 8명 생성
        $testUser = User::query()->firstWhere('email', 'user@example.com')
            ?? User::factory()->create([
                'name' => '홍길동',
                'email' => 'user@example.com',
                'password' => 'password123',
            ]);

        $users = User::factory(8)->create();
        $allUsers = collect([$admin, $testUser])->concat($users);

        // 3. 고정 카테고리 생성 (slug는 README/5장 예시 응답과 동일하게 맞춥니다)
        $categoriesData = [
            ['name' => '자유게시판', 'slug' => 'free-board', 'description' => '자유롭게 일상을 나누는 공간입니다.'],
            ['name' => '기술 Q&A', 'slug' => 'tech-qa', 'description' => '라라벨 및 웹 개발 기술 관련 질문과 답변입니다.'],
            ['name' => '공지사항', 'slug' => 'notice', 'description' => '서비스 공식 공지사항입니다.'],
            ['name' => '개발 꿀팁', 'slug' => 'tips', 'description' => '유용한 개발 팁과 노하우를 공유합니다.'],
        ];

        $categories = collect();
        foreach ($categoriesData as $cat) {
            $categories->push(Category::firstOrCreate(['slug' => $cat['slug']], $cat));
        }

        // 4. 각 사용자별로 게시글 3~5개씩 생성
        foreach ($allUsers as $user) {
            $posts = Post::factory(rand(3, 5))->create([
                'user_id' => $user->id,
                'category_id' => $categories->random()->id,
            ]);

            // 각 게시글마다 1차 댓글 및 대댓글 생성
            foreach ($posts as $post) {
                // 1차 댓글 2~4개 생성
                $rootComments = Comment::factory(rand(2, 4))->create([
                    'post_id' => $post->id,
                    'user_id' => $allUsers->random()->id,
                    'parent_id' => null,
                ]);

                // 각 1차 댓글마다 대댓글 1~2개 생성
                foreach ($rootComments as $parentComment) {
                    Comment::factory(rand(1, 2))->create([
                        'post_id' => $post->id,
                        'user_id' => $allUsers->random()->id,
                        'parent_id' => $parentComment->id,
                    ]);
                }

                // 무작위 좋아요 시딩
                $likers = $allUsers->random(rand(1, min(5, $allUsers->count())));
                foreach ($likers as $liker) {
                    $post->likes()->create(['user_id' => $liker->id]);
                }
            }
        }

    }
}
