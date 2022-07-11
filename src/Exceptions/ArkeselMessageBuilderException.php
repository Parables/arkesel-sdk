<?php

/*
 * @author Parables Boltnoel <parables95@gmail.com>
 * @package arkesel-sdk
 *  @version 1.0.0
 */

namespace Parables\ArkeselSdk\Exceptions;

class ArkeselMessageBuilderException extends \Exception
{
    public static function messageIsEmpty()
    {
        return new static("Message Builder Exception: Sms message cannot be empty", 400);
    }

    public static function senderIsRequired()
    {
        return new static("Message Builder Exception: Sender is required to identify the sms", 400);
    }

    public static function noRecipients()
    {
        return new static("Message Builder Exception: No recipients were specified for this sms", 400);
    }

    public static function apiKeyIsRequired()
    {
        return new static("Message Builder Exception: API key is required to send SMS through Arkesel", 400);
    }
}
