<?php

/*
 * @author Parables Boltnoel <parables95@gmail.com>
 * @package arkesel-sdk
 *  @version 1.0.0
 */

namespace Parables\ArkeselSdk\BulkSms;

use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Notification;
use Parables\ArkeselSdk\Exceptions\ArkeselNotificationChannelException;

class ArkeselChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     *
     * @throws \Parables\ArkeselSdk\NotificationChannel\Exceptions\CouldNotSendNotification
     */
    public function send($notifiable, Notification $notification)
    {
        throw_if(
            !method_exists($notification, 'toArkesel'),
            ArkeselNotificationChannelException::methodDoesNotExist(notification: $notification),
        );

        // get the message from the Notification class
        $toArkesel = $notification->toArkesel($notifiable);

        $messageBuilder = new ArkeselMessageBuilder();

        if (is_string($toArkesel)) {
            $messageBuilder->message(message: $toArkesel);
        } elseif ($toArkesel instanceof ArkeselMessageBuilder) {
            $messageBuilder = $toArkesel;
        } else {
            throw ArkeselNotificationChannelException::invalidReturnType($notification);
        }

        // if no recipients,
        // fallback to the `routeNotificationForArkesel()` method or the `phone_number` field on the model
        if (empty($messageBuilder->getRecipients())) {
            $messageBuilder->recipients(
                $notifiable instanceof AnonymousNotifiable
                    ? $notifiable->routeNotificationFor('arkesel')
                    : $notifiable->phone_number ?? []
            );
        }

        arkeselSms(builder: $messageBuilder)->send();
    }
}
