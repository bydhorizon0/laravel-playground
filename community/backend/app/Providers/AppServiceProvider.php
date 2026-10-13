<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
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
        // 로컬/테스트 환경에서 N+1 Lazy Loading, 할당 불가 속성 무시, 없는 속성 접근을 엄격히 차단
        $isProduction = $this->app->isProduction();

        Model::preventLazyLoading(! $isProduction);
        Model::preventSilentlyDiscardingAttributes(! $isProduction);
        Model::preventAccessingMissingAttributes(! $isProduction);
        // 위 세 줄은 Model::shouldBeStrict(! $isProduction) 한 줄로도 설정할 수 있다.

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
