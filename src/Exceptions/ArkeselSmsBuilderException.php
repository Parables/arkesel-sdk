<?php

/*
 * @author Parables Boltnoel <parables95@gmail.com>
 * @package arkesel-sdk
 *  @version 1.0.0
 */

namespace Parables\ArkeselSdk\Exceptions;

class ArkeselSmsBuilderException extends \Exception
{
    public static function messageIsRequired()
    {
        return new static('Sms message is required and cannot be empty', 400);
    }

    public static function senderIsRequired()
    {
        return new static('Sender is required to identify the sms', 400);
    }

    public static function senderLengthExceeded()
    {
        return new static('Sender should not exceed 11 characters', 400);
    }

    public static function recipientsAreRequired()
    {
        return new static('No recipients were specified for this sms', 400);
    }

    public static function apiKeyIsRequired()
    {
        return new static('Sms API key is required to send SMS through Arkesel', 400);
    }
}
