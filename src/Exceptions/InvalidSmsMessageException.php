<?php

/*
* @author Parables Boltnoel <parables95@gmail.com>
 * @package arkesel-sdk
 *  @version 1.0.0
 */

namespace Parables\ArkeselSdk\Exceptions;

class InvalidSmsMessageException extends \Exception
{
    public function __construct($message)
    {
        return new static($message);
    }
}
