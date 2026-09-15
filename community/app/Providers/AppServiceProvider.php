<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

use function app;
use function logger;
use function sprintf;
use function str_contains;

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
        if (app()->environment('local')) {
            DB::listen(function ($query) {
                if (str_contains($query->sql, 'cache') || str_contains($query->sql, 'jobs')) {
                    return;
                }

                logger()->info(sprintf(
                    "\n⏱️ [%s ms] \n%s;\n",
                    $query->time,
                    $query->toRawSql()
                ));
            });
        }
    }
}
