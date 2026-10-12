<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

use function strtolower;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Scramble::afterOpenApiGenerated(function (OpenApi $openApi) {
            $openApi->secure(SecurityScheme::http('bearer'));
        });

        // 로그인/회원가입: IP + 이메일 조합당 분당 10회
        RateLimiter::for('auth', function (Request $request): Limit {
            return Limit::perMinute(10)->by($request->ip().'|'.strtolower((string) $request->input('email')));
        });

        // 일반 API: 로그인 사용자는 사용자 ID, 비로그인은 IP 기준 분당 60회
        RateLimiter::for('api', function (Request $request): Limit {
            return Limit::perMinute(60)->by($request->user('sanctum')?->id ?? $request->ip());
        });
    }
}
