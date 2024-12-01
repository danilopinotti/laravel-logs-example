<?php

namespace App\Providers;

use App\Support\ExecutionId;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class LogServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ExecutionId::class, static function () {
            return new ExecutionId();
        });

        Log::shareContext([
            'traceId' => app(ExecutionId::class)->get(),
        ]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
