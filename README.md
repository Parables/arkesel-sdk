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

- [Arkesel SDK](#arkesel-sdk)
  - [Contents](#contents)
  - [About](#about)
  - [Features](#features)
  - [Installation](#installation)
    - [Setting up the Arkesel service](#setting-up-the-arkesel-service)
  - [Usage](#usage)
    - [Bulk SMS](#bulk-sms)
      - [Notifications to the `arkesel` channel](#notifications-to-the-arkesel-channel)
      - [SMS Recipients](#sms-recipients)
    - [Available Message methods](#available-message-methods)
  - [Changelog](#changelog)
  - [Testing](#testing)
  - [Security](#security)
  - [Contributing](#contributing)
  - [Credits](#credits)
  - [License](#license)

## About

This is an unofficial SDK for [Arkesel](https://arkesel.com/) which is a wrapper around [Arkesel API] for PHP and Laravel applications.

## Features

- [x] Bulk SMS
- [ ] Payment
- [ ] Voice
- [ ] Email
- [ ] USSD

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
ARKESEL_SMS_URL= # for SMS API v1, use 'https://sms.arkesel.com/sms/api?action=send-sms`
ARKESEL_SMS_SENDER= # defaults to your `APP_NAME` env variable
ARKESEL_SMS_CALLBACK_URL= # for API SMS v2 only
ARKESEL_SMS_SANDBOX= # for API SMS v2 only
```

## Usage

### Bulk SMS

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
    php artisan make:notification OtpCodeRequested
    ```

    Then specify the channel with the `via()` method and the message to be sent using the `toArkesel($notifiable)` method

    ```php
    <?php

    namespace App\Notifications;

    use Parables\ArkeselSdk\NotificationChannel\ArkeselMessage;
    use Illuminate\Notifications\Notification;
    use Illuminate\Notifications\Messages\MailMessage;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Bus\Queueable;

    class OtpCodeRequested extends Notification implements ShouldQueue
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

        public function toArkesel($notifiable)
        {
            return (new ArkeselMessage())
                ->message($this->message);
        }
    }
    ```

3. Send the notification

- Option 1: using the `notify()` method that is provided by the `Notifiable` trait

    ```php
    use App\Notifications\OtpCodeRequested;

    $user->notify(new OtpCodeRequested($message));
    ```

- Option 2: using the Notification Facade

    ```php
    use Illuminate\Support\Facades\Notification;

    Notification::send($users, new OtpCodeRequested($message));
    ```

- Option 3: On demand notification using the Notification's facade `route` method

    ```php
    Notification::route('arkesel', '233123456789')->notify(new OtpCodeRequested($message));
    ```

#### SMS Recipients

You can chain the recipients to the `ArkeselMessage` builder

```php
  public function toArkesel($notifiable)
    {
        return (new ArkeselMessage())
            ->message($this->message)
            ->recipients(["233123456789", "233123456789"]); //or "233123456789,233123456789"
    }
```

If no recipients are specified on the `ArkeselMessage` builder, this package will fallback to the `routeNotificationForArkesel` method defined in your notifiable model.

```php
<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * Route notifications for the Arkesel channel.
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return string
     */
    public function routeNotificationForArkesel($notification)
    {
        return $this->phone_number;
    }
}
```

If that method is also not defined, this package assumes that your notifiable Model has a `phone_number` field which will be used as the recipient of the notification.

If there is no `phone_number` field on your notifiable model, this will throw an exception: `'No recipients were specified for this notification'`

### Available Message methods

The following methods can be used on the `ArkeselMessage` builder to construct the message to be sent.

- `constructor`

    ```php
    ArkeselMessage(
        string $message = '',
        string|array $recipients = null,
        string $apiKey = null,
        string $schedule = null,
        string $sender = null,
        string $callbackUrl = null,
        bool $sandbox = false,
    )
    ```

- `message(string $message)`  

    set the message to be sent.

- `apiKey(string $apiKey)`  

    sets the API key to used to authenticate the request.

    Overrides the API key set in the `.env` file.

- `schedule(string $schedule)`  
    set/schedule when the message should be sent.

- `sender(string $sender)`  
    set the name or number that identifies the sender of an SMS message.

- `recipients(string|array $recipients)`  
    set the phone numbers to receive the sms.

- `callbackUrl(string $callbackUrl)`  
    set a URL that will be called to notify you about the status of the message to a particular number.

- `sandbox(bool $sandbox)`  
    set the environment for sending sms.
    if true, sms messages are not forwarded to the mobile network providers for delivery hence you are not billed for the operation. Use this to test your application.

```php
public function toArkesel($notifiable)
{
    return (new ArkeselMessage())
        ->message("Your message")
        ->recipients(["233123456789", "233123456789"]) //or "233123456789,233123456789"
        ->apiKey("your API key") # this overrides the `.env` variable
        ->schedule(now()->addMinutes(5))
        ->sender("Company") // less than 11 characters
        ->callbackUrl("https://my-sms-callback-url")
        ->sandbox(false)
}
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
composer test
```

## Security

If you discover any security related issues, please email parables95@gmail.com instead of using the issue tracker.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Parables Boltnoel](https://github.com/parables)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
