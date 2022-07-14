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
    protected ArkeselSms $arkeselSms;

    public function __construct(ArkeselSms $arkeselSms)
    {
        $this->arkeselSms = $arkeselSms;
    }

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return array
     *
     * @throws \Parables\ArkeselSdk\Exceptions\ArkeselSmsBuilderException
     * @throws \Parables\ArkeselSdk\Exceptions\ArkeselSmsResponseOrException
     * @throws \Parables\ArkeselSdk\Exceptions\ArkeselNotificationChannelException
     */
    public function send($notifiable, Notification $notification): array
    {
        throw_if(
            ! method_exists($notification, 'toArkesel'),
            ArkeselNotificationChannelException::methodDoesNotExist(notification: $notification),
        );

        // get the message from the Notification class
        $builder = $notification->toArkesel($notifiable);

        // compose the sms using the fluent setter methods
        if (is_string($builder)) {
            // set the message only. the other fields will fallback to the defaults
            $this->arkeselSms->message(message: $builder);
        } elseif ($builder instanceof ArkeselMessageBuilder) {
            // create a new instance with the builder
            $this->arkeselSms = new ArkeselSms(builder: $builder);
        } else {
            throw ArkeselNotificationChannelException::invalidReturnType($notification);
        }

        // if no recipients, get the recipients using a chain of fallbacks
        if (empty($this->arkeselSms->getRecipients())) {
            $this->arkeselSms->recipients(
                $this->getRecipients(notifiable: $notifiable, notification: $notification)
            );
        }

        // send the sms notification
        return $this->arkeselSms->send();
    }

    /**
     * Attempts to get the recipients to be used for sending the notification using a chain of fallbacks.
     *
     * @param  mixed  $notifiable
     * @param  Notification  $notification
     * @return string|array
     */
    public function getRecipients($notifiable, Notification $notification): string|array
    {
        // handles on demand notifications first
        return $notifiable instanceof AnonymousNotifiable
            ? $notifiable->routeNotificationFor('arkesel') ?? [] // [] is a fallback that will throw an exception
            : $this->getRecipientsFromNotifiable($notifiable, $notification);
    }

    /**
     * Get the recipients from methods and properties defined on the `$notifiable` class.
     *
     * @param  mixed  $notifiable
     * @param  Notification  $notification
     * @return string|array
     */
    private function getRecipientsFromNotifiable($notifiable, Notification $notification): string|array
    {
        return $this->getValueFromMethodOrProperty('routeNotificationForArkesel', $notifiable, $notification)
            ?? $this->getValueFromMethodOrProperty('recipients', $notifiable, $notification)
            ?? $this->getValueFromMethodOrProperty('recipient', $notifiable, $notification)
            ?? $this->getValueFromMethodOrProperty('phoneNumbers', $notifiable, $notification)
            ?? $this->getValueFromMethodOrProperty('phone_numbers', $notifiable, $notification)
            ?? $this->getValueFromMethodOrProperty('phoneNumber', $notifiable, $notification)
            ?? $this->getValueFromMethodOrProperty('phone_number', $notifiable, $notification)
            ?? []; // [] is a fallback that will throw an exception
    }

    /**
     * returns the value from a method or a property on the `notifiable` if it exists
     * using the plural name for the method first, then the property and repeating it
     * for the singular name for the method, then the property.
     *
     * @param  string  $name
     * @param  mixed  $notifiable
     * @param  Notification  $notification
     * @return string|string[]|null
     */
    private function getValueFromMethodOrProperty(string $name, mixed $notifiable, Notification $notification): string|array|null
    {
        if (method_exists($notifiable, $name)) {
            return $notifiable->$name($notification);
        } elseif (property_exists($notifiable, $name)) {
            return $notifiable->$name;
        }

        return null;
    }
}
