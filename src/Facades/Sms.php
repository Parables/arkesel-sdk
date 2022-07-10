<?php

namespace Parables\ArkeselSdk\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Sms Facade to send messages
 * @method static \Psr\Http\Message\ResponseInterface send()
 */
class Sms extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'sms';
    }
}
