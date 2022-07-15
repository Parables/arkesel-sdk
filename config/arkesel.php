<?php

/*
 * @author Parables Boltnoel <parables95@gmail.com>
 * @package arkesel-sdk
 *  @version 1.0.0
 */

return [

    /*
    |--------------------------------------------------------------------------
    | SMS API Key
    |--------------------------------------------------------------------------
    |
    | Arkesel SMS API key. Get an API Key from Arkesel dashboard at:
    | https://sms.arkesel.com/user/sms-api/info
    |
    */

    'sms_api_key' => env('ARKESEL_SMS_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Payment API Key
    |--------------------------------------------------------------------------
    |
    | Arkesel Payment API key. Get an API Key from Arkesel dashboard at:
    | https://sms.arkesel.com/user/sms-api/info
    |
    */

    'payment_api_key' => env('ARKESEL_PAYMENT_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | SMS API Version
    |--------------------------------------------------------------------------
    |
    | Specify which API version you want.
    | Available options are: 'v1', 'v2'
    |
    */

    'sms_api_version' => env('ARKESEL_SMS_API_VERSION', 'v2'),

    /*
    |--------------------------------------------------------------------------
    | Sender
    |--------------------------------------------------------------------------
    |
    | This is the name or number that identifies the sender of an SMS message.
    | Note that this field should be 11 characters max including space.
    | Anything more than that will result in your messages failing.
    |
    */

    'sms_sender' => env('ARKESEL_SMS_SENDER', config('app.name')),

    /*
    |--------------------------------------------------------------------------
    | Callback URL
    |--------------------------------------------------------------------------
    |
    | A URL that will be called to notify you about the status of the message
    | in the SMS V2 API. It must be a valid URL. This callback will receive
    | 2 query parameters: a unique `sms_id`(UUID) and the message `status`.
    |
    */

    'sms_callback_url' => env('ARKESEL_SMS_CALLBACK_URL'),

    /*
    |--------------------------------------------------------------------------
    | Sandbox
    |--------------------------------------------------------------------------
    |
    | A URL that will be called to notify you about the status of the message to
    | a particular number. It must be a valid URL. This callback will receive
    | 2 query parameters: a unique `sms_id`(UUID) and the message `status`.
    |
    */

    'sms_sandbox' => env('ARKESEL_SMS_SANDBOX', true),
];
