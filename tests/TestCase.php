<?php

namespace Parables\ArkeselSdk\Test;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected $loadEnvironmentVariables = true;

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
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
     *
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'Sms' => 'Parables\ArkeselSdk\Facades\Sms',
        ];
    }
}
