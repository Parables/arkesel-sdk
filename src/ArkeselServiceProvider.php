<?php

namespace NotificationChannels\Arkesel;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Notification;
use Illuminate\Notifications\ChannelManager;
use GuzzleHttp\Client;

class ArkeselServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/arkesel.php', 'arkesel');

        Notification::resolved(function (ChannelManger $service) {
            $service->extend('arkesel', function ($app) {
                return new ArkeselChannel(
                    $app->make(Client::class),
                    $app['config']['arkesel']
                );
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
                __DIR__ . '/../config/arkesel.php' => $this->app->configPath('arkesel.php'),
            ], 'arkesel');
        }
    }
}