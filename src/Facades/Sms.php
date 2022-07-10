<?php

/*
 * @author Parables Boltnoel <parables95@gmail.com>
 * @package arkesel-sdk
 *  @version 1.0.0
 */

namespace Parables\ArkeselSdk\Facades;

use Illuminate\Support\Facades\Facade;
use Parables\ArkeselSdk\BulkSms\SmsClient;

/**
 * Sms Facade to send messages.
 *
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
        return SmsClient::class;
    }
}
