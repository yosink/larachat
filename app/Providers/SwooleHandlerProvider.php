<?php

namespace App\Providers;

use App\Handlers\SwooleHandler;
use App\Services\RedisService;
use Illuminate\Support\ServiceProvider;

class SwooleHandlerProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('swoolehandler', function($app) {
           return new SwooleHandler(new RedisService);
        });
    }
}
