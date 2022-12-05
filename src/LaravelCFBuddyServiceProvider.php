<?php
/**
* LaravelCFBuddyServiceProvider
*
* @author: tuanha
* @last-mod: 13-Dec-2020
*/
namespace Bkstar123\CFBuddy;

use Bkstar123\CFBuddy\Services\CFIP;
use Illuminate\Support\ServiceProvider;
use Bkstar123\CFBuddy\Services\CFZoneFW;
use Bkstar123\CFBuddy\Services\ZoneMgmt;
use Bkstar123\CFBuddy\Services\CustomSSL;

class LaravelCFBuddyServiceProvider extends ServiceProvider
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
        $this->app->singleton('zoneMgmt', function ($app) {
            return new ZoneMgmt;
        });
        $this->app->singleton('customSSL', function ($app) {
            return new CustomSSL;
        });
        $this->app->singleton('cfZoneFW', function ($app) {
            return new CFZoneFW;
        });
        $this->app->singleton('cfip', function ($app) {
            return new CFIP;
        });
        $this->mergeConfigFrom(__DIR__.'/Config/bkstar123_laravel_cfbuddy.php', 'bkstar123_laravel_cfbuddy');
    }
}
