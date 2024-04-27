# Arkesel SDK

[![Latest Version on Packagist](https://img.shields.io/packagist/v/parables/arkesel-sdk.svg?style=flat-square)](https://packagist.org/packages/parables/arkesel-sdk)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/parables/arkesel-sdk/master.svg?style=flat-square)](https://travis-ci.org/parables/arkesel-sdk)
[![StyleCI](https://styleci.io/repos/7548986/shield)](https://styleci.io/repos/510513476)
[![SymfonyInsight](https://insight.symfony.com/projects/507c7189-7732-48c3-b55c-295198d9c193/mini.svg)](https://insight.symfony.com/projects/507c7189-7732-48c3-b55c-295198d9c193)
[![Quality Score](https://img.shields.io/scrutinizer/g/parables/arkesel-sdk.svg?style=flat-square)](https://scrutinizer-ci.com/g/parables/arkesel-sdk)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/parables/arkesel-sdk/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/parables/arkesel-sdk/?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/parables/arkesel-sdk.svg?style=flat-square)](https://packagist.org/packages/parables/arkesel-sdk)

## Contents

-   [Arkesel SDK](#arkesel-sdk)
    -   [Contents](#contents)
    -   [About](#about)
    -   [Features](#features)
    -   [Installation](#installation)
        -   [Setting up the Arkesel service](#setting-up-the-arkesel-service)
    -   [Usage](#usage)
        -   [Bulk SMS](#bulk-sms)
            -   [Notifications to the `arkesel` channel](#notifications-to-the-arkesel-channel)
            -   [SMS Recipients](#sms-recipients)
        -   [Composing SMS](#composing-sms)
        -   [Available methods](#available-methods)
            -   [ArkeselMessageBuilder)](#arkeselmessagebuilder)
            -   [ArkeselSms](#arkeselsms)
    -   [FAQ](#faq)
        -   [ArkeselChannel](#arkeselchannel)
    -   [Changelog](#changelog)
    -   [Testing](#testing)
    -   [Security](#security)
    -   [Contributing](#contributing)
    -   [Credits](#credits)
    -   [License](#license)

## About

This is an unofficial SDK for [Arkesel](https://arkesel.com/) which is a wrapper around [Arkesel API] for PHP and Laravel applications.

## Features

-   [x] Bulk SMS
-   [ ] Payment
-   [ ] Voice
-   [ ] Email
-   [ ] USSD

This SDK includes a Laravel Notification channel that makes it possible to send out Laravel notifications as a SMS using [Arkesel API](https://arkesel.com/)

## Installation

You can install this package via composer:

```bash
composer require parables/arkesel-sdk
```

The service provider gets loaded automatically.

Then publish the config file

```bash
php artisan vendor:publish --provider="Parables\ArkeselSdk\ArkeselServiceProvider" --tag="config"
```

### Setting up the Arkesel service

First, create [Sign up](https://account.arkesel.com/signup) for an account. You will be taken to your [SMS Dashboard](https://sms.arkesel.com/user/sms-api/info) where you can find the SMS API keys.

Then add your API key to the `.env` file

```env
ARKESEL_SMS_API_KEY="your Arkesel API key"
```

The following env variables can be used to customize the package.
Refer to the [Arkesel Docs](https://developers.arkesel.com/) for more info

```env
ARKESEL_API_VERSION="v2" # or "v1"
ARKESEL_SMS_URL= # for SMS API v1, use 'https://sms.arkesel.com/sms/api`
ARKESEL_SMS_SENDER= # defaults to your `APP_NAME` env variable
ARKESEL_SMS_CALLBACK_URL= # for API SMS v2
ARKESEL_SMS_SANDBOX= # for API SMS v2
```

## Usage

### Bulk SMS

```php
$builder = (new ArkeselMessageBuilder)
    ->message('Hello World')
    ->recipients(["233234567890", "233234567890"])
    ->recipients("233234567890,233234567890") // alternative
    ->sandbox(false);

// helper function
$response = arkeselSms(builder: $builder)->send();

// facade
$response = ArkeselSms::make(builder: $builder)->send();

// instance
$response = new ArkeselSms(builder: $builder)->send();
```

#### Notifications to the `arkesel` channel

Create a notification class. Refer to Laravel's documentation on [Notifications](https://laravel.com/docs/9.x/notifications).

1. Add the Notifiable trait to your model

    ```php
    <?php

    namespace App\Models;

    use Illuminate\Foundation\Auth\User as Authenticatable;
    use Illuminate\Notifications\Notifiable;

    class User extends Authenticatable
    {
        use Notifiable;
    }
    ```

2. Create a Notification

    ```bash
    php artisan make:notification WelcomeMessage
    ```

    Then specify the channel with the `via()` method and the message to be sent using the `toArkesel($notifiable)` method

    ```php
    <?php

    namespace App\Notifications;

    use Parables\ArkeselSdk\BulkSms\ArkeselMessageBuilder;
    use Illuminate\Notifications\Notification;
    use Illuminate\Notifications\Messages\MailMessage;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Bus\Queueable;

    class WelcomeMessage extends Notification implements ShouldQueue
    {
        use Queueable;

        protected string $message;

        /**
         * Create a new notification instance.
         *
         * @return void
         */
        public function __construct(string $message)
        {
            $this->message = $message;
        }

        /**
         * Get the notification's delivery channels.
         *
         * @param  mixed  $notifiable
         * @return array
         */
        public function via($notifiable)
        {
            return ['arkesel'];
        }

        /**
         * the content of the notification to be sent.
         *
         * @param  mixed  $notifiable
         * @return string|ArkeselMessageBuilder
         */
        public function toArkesel($notifiable)
        {
            return $this->message;
        }
    }
    ```

3. Send the notification

-   Option 1: using the `notify()` method that is provided by the `Notifiable` trait

    ```php
    use App\Notifications\WelcomeMessage;

    $user->notify(new WelcomeMessage($message));
    ```

-   Option 2: using the Notification Facade

    ```php
    use Illuminate\Support\Facades\Notification;

    Notification::send($users, new WelcomeMessage($message));
    ```

-   Option 3: On demand notification using the Notification's facade `route` method

    ```php
    Notification::route('arkesel', '233123456789')->notify(new WelcomeMessage($message));
    ```

#### SMS Recipients

For on-demand notifications, `recipients` are directly passed to the `Notification::route` method.

```php
Notification::route('arkesel', ['233123456789', '233123456789'])->notify(new WelcomeMessage($message));

// alternative
Notification::route('arkesel', '233123456789,233123456789')->notify(new WelcomeMessage($message));
```

For all other cases, it is **highly recommend** to be explicit about specifying the recipients of your notification using the `recipients()` method on the `ArkeselMessageBuilder` instance or `ArkeselSms` instance/facade/helper function

```php
public function toArkesel($notifiable)
{
    return (new ArkeselMessageBuilder())
        ->message('Hello World')
        ->recipients(["233123456789", "233123456789"]);
        ->recipients("233123456789,233123456789") // alternative
}
```

However, you may want to return a string as the sms message for the `toArkesel($notifiable)` method.

```php
public function toArkesel($notifiable)
{
    return 'Hello World';
}
```

> In such cases, please make sure to define a `routeNotificationForArkesel($notification)` method which will receive the Notification instance `$notification` being sent on your notifiable class and return a comma-separated string or an array of string of phone numbers.
>
> This ensures that the package doesn't attempt to figure out the recipients.

```php
public function routeNotificationForArkesel($notification)
{
    return ['233123456789','233123456789'];
    // or
    return '233123456789,233123456789';
}
```

If a `routeNotificationForArkesel($notification)` method is not defined in your notifiable class, this package will attempt to get the recipients using any of these methods or properties defined on the notifiable class in the following order of preference.

> TL'DR: it prefers:
>
> 1. a plural method over a singular method
> 2. a plural property over a singular property
> 3. a camelCased property over a snake_case property
>
> with these specific names: `routeNotificationForArkesel`, `recipients`, `recipient`, `phoneNumbers`, `phone_numbers`, `phoneNumber`, `phone_number`

Under the hood, it uses the code snippets below to decide how to get the recipients:

```php
    /**
     * Get the recipients from methods and properties defined on the `$notifiable` class
     *
     * @param mixed $notifiable
     * @param Notification $notification
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
```

This is the order of preference to get the recipients:

```php
<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;


class GetRecipientsOrderOfPreference
{
    use Notifiable;

    /*1st*/
    public function routeNotificationForArkesel($notification)
    {
        return ['233123456789','233123456789']
    }

    /*2nd*/
    public function recipients($notification)
    {
        return ['233123456789','233123456789']
    }

    /*3rd*/
    public string $recipients = ['233123456789','233123456789'];

    /*4th*/
    public function recipient($notification)
    {
        return ['233123456789','233123456789']
    }

    /*5th*/
    public string $recipient = ['233123456789','233123456789'];

    /*6th*/
    public function phoneNumbers($notification)
    {
        return ['233123456789','233123456789']
    }

    /*7th*/
    public string $phoneNumbers = ['233123456789','233123456789'];

    /*8th*/
    public string $phone_numbers = ['233123456789','233123456789'];

    /*9th*/
    public function phoneNumber($notification)
    {
        return ['233123456789','233123456789']
    }

    /*10th*/
    public string $phoneNumber = ['233123456789','233123456789'];

    /*11th*/
    public string $phone_number = ['233123456789','233123456789'];
}
```

> Notice that all the methods above receives the Notification instance `$notification` being sent.
>
> If there is none of these methods or properties is defined, it will throw an exception: `ArkeselSmsBuilderException: 'No recipients were specified for this sms'`

### Composing SMS

You can fluently compose the SMS by chaining the setter methods exposed by the `ArkeselSmsBuilder` trait used by both `ArkeselMessageBuilder` and `ArkeselSms` classes;

```php
public function toArkesel($notifiable)
{
    return (new ArkeselMessageBuilder())
        ->message("Your message")
        ->recipients(["233123456789", "233123456789"])
        ->recipients("233123456789,233123456789") // alternative
        ->apiKey("your API key") # this overrides the `.env` variable
        ->schedule(now()->addMinutes(5))
        ->sender("Company") // less than 11 characters
        ->callbackUrl("https://my-sms-callback-url")
        ->sandbox(false)
}
```

### Available methods

#### ArkeselMessageBuilder

-   `message(string $message): self`

    set the message to be sent.

-   `getMessage(): string`

    get the sms message to be sent

-   `sender(string $sender): self`

    set the name or number that identifies the sender of an SMS message.

-   `getSender(): null|string`

    get the name or number that identifies the sender of the SMS message.

-   `recipients(string|array $recipients):self`

    set the phone numbers to receive the sms.

    This method will trim empty strings and filter out unique recipients

-   `getRecipients(string $apiVersion = 'v2'): string|array`

    get the phone numbers to receive the sms.

    returns a comma-separated string for SMS API version `v1` and array of strings for `v2`

-   `schedule(string|Carbon $schedule): self`

    set/schedule when the message should be sent.

    refer <https://carbon.nesbot.com/docs/> for more information

    ```php
    $builder = new ArkeselMessageBuilder();

    $builder->schedule($now->addMinutes(5));

    $builder->schedule('first day of May 2022');
    ```

-   `getSchedule(string $apiVersion = 'v2'): null|string`

    get when the message should be sent for the SMS API version specified.

-   `callbackUrl(string $callbackUrl): self`

    set a URL that will be called to notify you about the status of the message to a particular number.

-   `getCallbackUrl(): null|string`

    set a URL that will be called to notify you about the status of the message to a particular number.

-   `sandbox(bool $sandbox = true): self`

    set the environment for sending sms.
    if true, sms messages are not forwarded to the mobile network providers for delivery hence you are not billed for the operation. Use this to test your application.

-   `getSandbox()`

    Get the SMS environment mode

-   `smsApiKey(string $apiKey): self`

    sets the API key to used to authenticate the request.

    Overrides the API key set in the `.env` file.

-   `getSmsApiKey(): null|string`

    get arkesel SMS API Key to use for this request.

-   `smsApiVersion(string $smsApiVersion = 'v2'): self`

    sets the SMS API Version to use for this request

    Overrides the `ARKESEL_SMS_API_VERSION` set in the `.env` file.

-   `getSmsApiVersion(): null|string`

    Get arkesel SMS API version to use for this request.

#### ArkeselSms

The `ArkeselSms` class it used to send the SMS and get the SMS balance.

```php
// Create an instance
(new ArkeselSms(builder: $builder))->send();
// OR: Use the Facade
ArkeselSms::make(builder: $builder)->send();
// OR: Use the helper function
arkeselSms(builder: $builder)->send();
```

-   `make(ArkeselMessageBuilder $builder): self`

    Must be called first when using the ArkeselSms as a facade

-   `send(): array`

    sends the sms to the recipients

-   `static getSmsBalance(string $smsApiVersion = null, string $smsApiKey = null): array`

    get the sms balance

```php
Arkesel::getSmsBalance();
```

## FAQ

-   Send SMS in sandbox mode

    By default, the SDK sends SMS with the sandbox mode is set to `false`. You have to be explicit if you prefer to send sms in sandbox mode by calling the `sandbox()` or `sandbox(true)` setter method

#### ArkeselChannel

The `ArkeselChannel` class exposes the send methods to send notifications.

-   `send($notifiable, Notification $notification): array`

    Sends the given notification through ArkeselSms.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

```bash
composer test

composer test:coverage
```

## Security

If you discover any security related issues, please email parables95@gmail.com instead of using the issue tracker.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

-   [Parables Boltnoel](https://github.com/parables)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
