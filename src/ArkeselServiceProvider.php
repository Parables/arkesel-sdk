<?php

/*
 * @author Parables Boltnoel <parables95@gmail.com>
 * @package arkesel-sdk
 *  @version 1.0.0
 */

namespace Parables\ArkeselSdk;

use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;
use Parables\ArkeselSdk\BulkSms\ArkeselChannel;

class ArkeselServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/arkesel.php', 'arkesel');

        // // Bind the main class to use with the facade
        /*       $this->app->singleton(ArkeselSms::class, function () {
            return new ArkeselSms();
        }); */

        Notification::resolved(function (ChannelManager $service) {
            $service->extend('arkesel', function () {
                return new ArkeselChannel();
            });
        });
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/arkesel.php' => $this->app->configPath('arkesel.php'),
            ], 'arkesel');
        }
    }
}
