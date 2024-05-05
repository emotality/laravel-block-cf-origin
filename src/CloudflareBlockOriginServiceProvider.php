<?php

namespace Emotality\Cloudflare;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class CloudflareBlockOriginServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([CloudflareNetmasks::class]);
            $this->publishes([
                __DIR__.'/../config/cloudflare-block.php' => App::configPath('cloudflare-block.php'),
            ], 'config');
        }
    }
}
