<?php

namespace Uuu9\PhpApiLog\Provider;

use Illuminate\Support\ServiceProvider;
use Uuu9\PhpApiLog\Constants;
use Uuu9\PhpApiLog\Middleware\ApiLog;

class ApiLogServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->singleton(ApiLog::class, function ($app) {
            return new ApiLog($app);
        });
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', Constants::CONFIG_PREFIX);

        //lumen
        $this->app->middleware([
            ApiLog::class
        ]);
    }
}
