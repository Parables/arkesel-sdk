<?php

/*
 * @author Parables Boltnoel <parables95@gmail.com>
 * @package arkesel-sdk
 *  @version 1.0.0
 */

namespace Parables\ArkeselSdk\Exceptions;

class ArkeselSmsException extends \Exception
{
    public static function messageIsRequired()
    {
        return new static('Sms message is required and cannot be empty');
    }

    public static function senderIsRequired()
    {
        return new static('Sender is required to identify the sms');
    }

    public static function senderLengthExceeded()
    {
        return new static('Sender should not exceed 11 characters');
    }

    public static function recipientsAreRequired()
    {
        return new static('No recipients were specified for this sms');
    }

    public static function apiKeyIsRequired()
    {
        return new static('Sms API key is required to send SMS through Arkesel');
    }

    public static function invalidSmsApiVersion()
    {
        return new static('The SMS API version given is invalid');
    }
}
