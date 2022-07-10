<?php

/*
 * @author Parables Boltnoel <parables95@gmail.com>
 * @package arkesel-sdk
 *  @version 1.0.0
 */

namespace Parables\ArkeselSdk\NotificationChannel;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Arr;
use Parables\ArkeselSdk\BulkSms\ArkeselMessage;
use Parables\ArkeselSdk\BulkSms\SmsClient;
use Parables\ArkeselSdk\Exceptions\InvalidSmsMessageException;

class ArkeselChannel
{
    protected SmsClient $smsClient;

    public function __construct(SmsClient $smsClient)
    {
        $this->smsClient = $smsClient;
    }

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
        if (! method_exists($notification, 'toArkesel')) {
            throw new  InvalidSmsMessageException(message: '"toArkesel($notifiable)" method does not exist');
        }

        // get the message from the Notification class
        $message = $notification->toArkesel($notifiable);

        if (is_string($message)) {
            $message = new ArkeselMessage(message: $message);
        } elseif (! $message instanceof ArkeselMessage) {
            throw new  InvalidSmsMessageException(
                message: '"toArkesel($notifiable)" must return either a string or an instance of ArkeselMessage'
            );
        }

        // if no recipients,
        // fallback to the `routeNotificationForArkesel()` method or the `phone_number` field on the model
        if (empty($message->recipients)) {
            $message->recipients(
                recipients: Arr::wrap($notifiable->routeNotificationFor('arkesel', $notification)
                    ?? $notifiable->phone_number)
            );
        }

        $this->smsClient->send(message: $message);
    }
}
