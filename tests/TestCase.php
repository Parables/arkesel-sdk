<?php

/*
 * @author Parables Boltnoel <parables95@gmail.com>
 * @package arkesel-sdk
 *  @version 1.0.0
 */

namespace Parables\ArkeselSdk\Test;

use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected $loadEnvironmentVariables = true;

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            'Parables\ArkeselSdk\ArkeselServiceProvider',
        ];
    }

    /**
     * Override application aliases.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'Sms' => 'Parables\ArkeselSdk\Facades\Sms',
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function defineEnvironment($app)
    {
        $app['config']->set('arkesel',  [
            'api_key' => 'T2RkS3NkUlJ1QlliQkRXWmh3a3k',
            'api_version' => 'v2',
            'sms_url' => 'https://sms.arkesel.com/api/v2/sms/send',
            'sms_sender' => 'ASDK',
            // 'sms_callback_url' => '',
            // 'sms_sandbox' => false,
        ]);
    }
}
