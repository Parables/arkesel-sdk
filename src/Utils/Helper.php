<?php

/*
 * @author Parables Boltnoel <parables95@gmail.com>
 * @package arkesel-sdk
 *  @version 1.0.0
 */

use Parables\ArkeselSdk\BulkSms\ArkeselMessage;
use Parables\ArkeselSdk\BulkSms\SmsClient;

if (! function_exists('arkeselSendSms')) {
    /**
     * Access the SmsClient class through helper.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    function arkeselSendSms(ArkeselMessage $message)
    {
        return app(SmsClient::class, [$message]);
    }
}
