<?php

/*
 * @author Parables Boltnoel <parables95@gmail.com>
 * @package arkesel-sdk
 *  @version 1.0.0
 */

namespace Parables\ArkeselSdk\BulkSms;

use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Parables\ArkeselSdk\Exceptions\ArkeselMessageBuilderException;

class ArkeselMessageBuilder
{
    /**
     * the sms message.
     *
     * @var string
     *
     * A one-page sms = 160 character, so you send a sms with 200 characters, that will be 2 pages.
     */
    protected string $message = '';

    /**
     * the name or number that identifies the sender of the SMS.
     *
     * @var null|string
     */
    protected ?string $sender = null;

    /**
     * phone numbers to which to send sms to.
     *
     * @var array
     */
    protected array $recipients = [];

    /**
     * schedule when the sms should be sent.
     *
     * @var null|Carbon
     *
     * @see https://developers.arkesel.com/#operation/send_schedule_sms_v1
     * @see https://developers.arkesel.com/#operation/send_sms
     */
    protected ?Carbon $schedule = null;

    /**
     * A URL that will be called to notify you about the status of the sms to a particular number.
     *
     * @var null|string
     *
     * @see developershttps://developers.arkesel.com/#operation/send_sms
     */
    protected ?string $callbackUrl = null;

    /**
     * if true, sms messages are not forwarded to the mobile network providers for delivery,
     *  hence you are not billed for the operation. Use this to test your application.
     *
     * @var null|bool
     */
    protected ?bool $sandbox = null;

    /**
     * Arkesel SMS API Key.
     *
     * @var null|string
     */
    protected ?string $apiKey = null;

    /**
     * set the sms message.
     *
     * @param  string  $message
     * @return $this
     */
    public function message(string $message): self
    {
        $message = trim($message);

        throw_if(empty($message), ArkeselMessageBuilderException::messageIsEmpty());

        $this->message = $message;

        return $this;
    }

    /**
     * Get the sms message.
     *
     * @return  string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * set the name or number that identifies the sender of an sms.
     *
     * @param  string  $sender
     * @return $this
     */
    public function sender(string $sender): self
    {
        $sender = trim($sender);

        throw_if(empty($sender), ArkeselMessageBuilderException::senderIsRequired());

        $this->sender = $sender;

        return $this;
    }

    /**
     * Get the name or number that identifies the sender of the SMS.
     *
     * @return  null|string
     */
    public function getSender(): null|string
    {
        return $this->sender;
    }

    /**
     * set the phone numbers to receive the sms.
     *
     * SMS API V1: "233544919953,233544919953,233544919953"
     *
     * SMS API V2: ["233544919953", "233544919953", "233544919953"]
     *
     * @param  string|array  $recipients
     * @return $this
     */
    public function recipients(string|array $recipients): self
    {
        $recipients = [
            ...array_unique(
                array_filter(
                    is_string($recipients)
                        ? explode(',', $recipients)
                        : $recipients,
                    fn ($recipient) => is_string($recipient) && !empty(trim($recipient))
                )
            )
        ];

        throw_if(empty($recipients), ArkeselMessageBuilderException::noRecipients());

        $this->recipients = $recipients;

        Log::info('recipients', ['recipients' => $recipients]);

        return $this;
    }

    /**
     * Get phone numbers to which to send sms to.
     *
     * @return  string|array
     */
    public function getRecipients(string $apiVersion = 'v2'): string|array
    {
        return $apiVersion === 'v1'
            ? implode(',', $this->recipients)
            : $this->recipients;
    }

    /**
     *  set/schedule when the sms should be sent.
     *
     *
     * @see https://developers.arkesel.com/#operation/send_schedule_sms_v1
     * @see https://developers.arkesel.com/#operation/send_sms
     *
     * @param  string|Carbon  $schedule
     * @throws InvalidFormatException
     * @return $this
     */
    public function schedule(string|Carbon $schedule): self
    {
        $schedule = is_string($schedule) ? Carbon::parse($schedule) : $schedule;

        $this->schedule = $schedule;

        return $this;
    }

    /**
     * Get schedule when the sms should be sent.
     * @param string $apiVersion
     * @return  null|string
     */
    public function getSchedule(string $apiVersion = 'v2'): null|string
    {
        if (!empty($this->schedule)) {
            return $this->schedule->format(
                $apiVersion === 'v1'
                    ? 'd-m-Y h:i A'  //E.g: "13-01-2021 05:30 PM"
                    : 'Y-m-d h:i A'  //E.g: "2021-03-17 07:00 AM"
            );
        }
        return null;
    }

    /**
     * set a URL that will be called to notify you about the status of the sms to a particular number.
     *
     * @param  string  $callbackUrl
     *
     * @see developershttps://developers.arkesel.com/#operation/send_sms
     *
     * @return $this
     */
    public function callbackUrl(string $callbackUrl = null): self
    {
        $this->callbackUrl = $callbackUrl;

        return $this;
    }

    /**
     * Get a URL that will be called to notify you about the status of the sms to a particular number.
     *
     * @return  null|string
     */
    public function getCallbackUrl(): null|string
    {
        return $this->callbackUrl;
    }

    /**
     * set the environment mode. In sandbox mode, sms are not delivered to the recipients
     *
     * @param bool sandbox
     * @return $this
     */
    public function sandbox(bool $sandbox = true): self
    {
        $this->sandbox = $sandbox;

        return $this;
    }

    /**
     * Get the SMS environment mode
     *
     * @return  null|bool
     */
    public function getSandbox(): null|bool
    {
        return $this->sandbox;
    }

    /**
     * sets the API key to used to authenticate the request
     * Overrides the API key set in the `.env` file.
     *
     * @param  string  $apiKey
     * @return $this
     */
    public function apiKey(string $apiKey): self
    {
        $apiKey = trim($apiKey);

        throw_if(empty($apiKey), ArkeselMessageBuilderException::apiKeyIsRequired());

        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * Get arkesel SMS API Key to use for this request.
     *
     * @return  null|string
     */
    public function getApiKey(): null|string
    {
        return $this->apiKey;
    }
}
