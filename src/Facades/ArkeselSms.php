<?php

/*
 * @author Parables Boltnoel <parables95@gmail.com>
 * @package arkesel-sdk
 *  @version 1.0.0
 */

namespace Parables\ArkeselSdk\Facades;

use Illuminate\Support\Facades\Facade;
use Parables\ArkeselSdk\BulkSms\ArkeselSms as Sms;

/**
 * ArkeselSms Facade to send messages.
 * @method static self make(?ArkeselMessageBuilder $builder)
 * @method static self getConfig(): array
 * @method static self message(string $message)
 * @method static self from(string $sender)
 * @method static self to(string|array $recipients)
 * @method static self schedule(string|Carbon $schedule)
 * @method static self callbackUrl(string $callbackUrl)
 * @method static self sandbox(bool $sandbox = true)
 * @method static self apiKey(string $apiKey)
 * @method static Illuminate\Http\Client\Response send()
 */
class ArkeselSms extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Sms::class;
    }
}
