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
            'Sms' => 'Parables\ArkeselSdk\Facades\ArkeselSms',
            'ArkeselSms' => 'Parables\ArkeselSdk\Facades\ArkeselSms',
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
            'api_key' => '62cc5136389d1', // TEST: Replace with a valid SMS API key
            'api_version' => 'v2',
            'sms_url' => 'https://sms.arkesel.com/api/v2/sms/send',
            'sms_sender' => 'Test App',
            // 'sms_callback_url' => '',
            'sms_sandbox' => true,
        ]);
    }
}
