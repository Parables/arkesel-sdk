<?php

/*
 * @author Parables Boltnoel <parables95@gmail.com>
 * @package arkesel-sdk
 *  @version 1.0.0
 */

namespace Parables\ArkeselSdk\Exceptions;

use Illuminate\Notifications\Notification;

class ArkeselNotificationChannelException extends \Exception
{
    public static function methodDoesNotExist(Notification $notification)
    {
        return new static(
            get_class().'"toArkesel($notifiable)" method is not defined in '.get_class($notification),
            400
        );
    }

    public static function invalidReturnType(Notification $notification)
    {
        return new static(
            get_class()
                .'"toArkesel($notifiable)" method in '.get_class($notification)
                .' must return either a string or an instance of ArkeselMessage',
            400
        );
    }
}
