<?php

/*
 * @author Parables Boltnoel <parables95@gmail.com>
 * @package arkesel-sdk
 *  @version 1.0.0
 */

use Parables\ArkeselSdk\BulkSms\ArkeselMessageBuilder;
use Parables\ArkeselSdk\BulkSms\ArkeselSms;

if (! function_exists('arkeselSms')) {
    /**
     * Access the ArkeselSms class through helper.
     *
     * @return ArkeselSms
     */
    function arkeselSms(ArkeselMessageBuilder $builder = null)
    {
        return app(ArkeselSms::class, ['builder' => $builder]);
    }
}
