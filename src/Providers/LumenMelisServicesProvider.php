<?php
namespace MelisPlatformFrameworkLumen\Providers;

use Illuminate\Support\ServiceProvider;
use MelisPlatformFrameworkLumen\MelisServices;

class LumenMelisServicesProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
//        $this->app->singleton(MelisServices::class, function ($app) {
//            return new MelisServices();
//        });
    }
}