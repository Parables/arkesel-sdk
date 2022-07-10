<?php

/*
 * @author Parables Boltnoel <parables95@gmail.com>
 * @package arkesel-sdk
 *  @version 1.0.0
 */

use Parables\ArkeselSdk\BulkSms\ArkeselMessage;

if (! function_exists('arkeselSms')) {
    /**
     * Access the SmsClient class through helper.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    function arkeselSms(ArkeselMessage $message)
    {
        return app('sms', [$message]);
    }
}
