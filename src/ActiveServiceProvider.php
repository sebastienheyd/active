<?php

namespace SebastienHeyd\Active;

use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\ServiceProvider;

class ActiveServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    public function boot()
    {
        app('router')->matched(
            function (RouteMatched $event) {
                app('active')->updateInstances($event->route, $event->request);
            }
        );

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/config' => config_path(),
            ], ['active', 'active-config']);
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(
            'active',
            function ($app) {
                return new Active($app['router']->getCurrentRequest());
            }
        );

        $this->mergeConfigFrom(__DIR__."/config/active.php", "active");
    }
}
