<?php

/*
 * @author Parables Boltnoel <parables95@gmail.com>
 * @package arkesel-sdk
 *  @version 1.0.0
 */

use Parables\ArkeselSdk\BulkSms\Sms;

if (!function_exists('arkeselSms')) {
    /**
     * Access the Sms class through helper.
     *
     * @return Sms
     */
    function arkeselSms()
    {
        return app(Sms::class);
    }
}
