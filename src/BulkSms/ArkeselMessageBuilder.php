<?php

/*
 * @author Parables Boltnoel <parables95@gmail.com>
 * @package arkesel-sdk
 *  @version 1.0.0
 */

namespace Parables\ArkeselSdk\BulkSms;

use Parables\ArkeselSdk\Exceptions\ArkeselSmsException;

class ArkeselMessageBuilder
{
    /**
     * the sms message.
     *
     * A one-page sms = 160 character, so you send a sms with 200 characters, that will be 2 pages.
     */
    protected string $message;

    /**
     * the name or number that identifies the sender of the SMS.
     */
    protected string $sender;

    /**
     * phone numbers to which to send sms to.
     *
     */
    protected array $recipients;

    /**
     * schedule when the sms should be sent.
     *
     *
     * @see https://developers.arkesel.com/#operation/send_schedule_sms_v1
     * @see https://developers.arkesel.com/#operation/send_sms
     */
    protected ?\Illuminate\Support\Carbon $schedule;

    /**
     * A URL that will be called to notify you about the status of the sms to a particular number.
     *
     *
     * @see developershttps://developers.arkesel.com/#operation/send_sms
     */
    protected ?string $callbackUrl;

    /**
     * if true, sms messages are not forwarded to the mobile network providers for delivery,
     *  hence you are not billed for the operation. Use this to test your application.
     *
     */
    protected bool $sandbox;

    /**
     * Arkesel SMS API Key.
     *
     */
    protected string $smsApiKey;

    /**
     * Arkesel SMS API version.
     *
     */
    protected string $smsApiVersion;

    /**
     * Set the sms message.
     *
     * @throws \Parables\ArkeselSdk\Exceptions\ArkeselSmsException
     */
    public function message(string $message): self
    {
        $message = trim($message);

        throw_if(
            empty($message),
            ArkeselSmsException::messageIsRequired()
        );

        $this->message = $message;

        return $this;
    }

    /**
     * Get the sms message.
     */
    public function getMessage(): string
    {
        $message = $this->message;

        throw_if(
            empty($message),
            ArkeselSmsException::messageIsRequired()
        );

        return $message;
    }

    /**
     * Set the name or number that identifies the sender of an sms.
     *
     * @throws \Parables\ArkeselSdk\Exceptions\ArkeselSmsException
     */
    public function sender(string $sender): self
    {
        $sender = trim($sender);

        throw_if(
            empty($sender),
            ArkeselSmsException::senderIsRequired()
        );

        throw_if(
            strlen($sender) > 11,
            ArkeselSmsException::senderLengthExceeded()
        );

        $this->sender = $sender;

        return $this;
    }

    /**
     * Get the name or number that identifies the sender of the SMS.
     *
     */
    public function getSender(): string
    {
        $sender = $this->sender ?? config('arkesel.sms_sender', env('APP_NAME'));

        throw_if(
            empty($sender),
            ArkeselSmsException::senderIsRequired()
        );

        throw_if(
            strlen($sender) > 11,
            ArkeselSmsException::senderLengthExceeded()
        );

        return $sender;
    }

    /**
     * set the phone numbers to receive the sms.
     *
     * SMS API V1: "233544919953,233544919953,233544919953"
     *
     * SMS API V2: []
     *
     * @throws \Parables\ArkeselSdk\Exceptions\ArkeselSmsException
     */
    public function recipients(string|array $recipients): self
    {
        $recipients = [
            ...array_unique(
                array_filter(array_map(
                    function ($recipient) {
                        if (is_string($recipient) && !empty($recipient = trim($recipient))) {
                            return $recipient;
                        }
                    },
                    is_string($recipients)
                        ? explode(',', $recipients)
                        : $recipients,
                ))
            ),
        ];

        throw_if(
            empty($recipients),
            ArkeselSmsException::recipientsAreRequired()
        );

        $this->recipients = $recipients;

        return $this;
    }

    /**
     * Get phone numbers to which to send sms to.
     */
    public function getRecipients(
        string $smsApiVersion = null,
        bool $shouldThrow = true,
    ): null|string|array {
        if (empty($this->recipients) and $shouldThrow) {
            throw ArkeselSmsException::recipientsAreRequired();
        }

        $smsApiVersion = $smsApiVersion ?? $this->getSmsApiVersion();

        return $smsApiVersion === 'v1'
            ? implode(',', $this->recipients ?? [])
            : $this->recipients ?? [];
    }

    /**
     *  Set/schedule when the sms should be sent.
     *
     *
     * @see https://developers.arkesel.com/#operation/send_schedule_sms_v1
     * @see https://developers.arkesel.com/#operation/send_sms
     *
     * @throws Carbon\Exceptions\InvalidFormatException
     */
    public function schedule(string|\Illuminate\Support\Carbon $schedule): self
    {
        $schedule = $schedule instanceof \Illuminate\Support\Carbon ? $schedule : \Illuminate\Support\Carbon::parse($schedule);

        $this->schedule = $schedule;

        return $this;
    }

    /**
     * Get schedule when the sms should be sent.
     */
    public function getSchedule(string $smsApiVersion = null): null|string
    {
        $smsApiVersion = $smsApiVersion ?? $this->getSmsApiVersion();

        if (!empty($this->schedule)) {
            return $this->schedule->format(
                $smsApiVersion === 'v1'
                    ? 'd-m-Y h:i A'  //E.g: "13-01-2021 05:30 PM"
                    : 'Y-m-d h:i A'  //E.g: "2021-03-17 07:00 AM"
            );
        }

        return null;
    }

    /**
     * Set a URL that will be called to notify you about the status of the sms to a particular number.
     *
     * @see developershttps://developers.arkesel.com/#operation/send_sms
     *
     */
    public function callbackUrl(string $callbackUrl = null): self
    {
        $this->callbackUrl = $callbackUrl;

        return $this;
    }

    /**
     * Get a URL that will be called to notify you about the status of the sms to a particular number.
     */
    public function getCallbackUrl(): null|string
    {
        return $this->callbackUrl ?? config('arkesel.sms_callback_url');
    }

    /**
     * Set the environment mode. In sandbox mode, sms are not delivered to the recipients.
     */
    public function sandbox(bool $sandbox = true): self
    {
        $this->sandbox = boolval($sandbox);

        return $this;
    }

    /**
     * Get the SMS environment mode.
     */
    public function getSandbox(): bool
    {
        return (bool) ($this->sandbox ?? config('arkesel.sms_sandbox', false));
    }

    /**
     * Sets the SMS API Key to used to authenticate the request.
     *
     * Overrides the `ARKESEL_SMS_API_KEY` set in the `.env` file.
     *
     * @throws \Parables\ArkeselSdk\Exceptions\ArkeselSmsException
     */
    public function smsApiKey(string $smsApiKey): self
    {
        $smsApiKey = trim($smsApiKey);

        throw_if(
            empty($smsApiKey),
            ArkeselSmsException::apiKeyIsRequired()
        );

        $this->smsApiKey = $smsApiKey;

        return $this;
    }

    /**
     * Get arkesel SMS API Key to use for this request.
     */
    public function getSmsApiKey(): string
    {
        $smsApiKey = $this->smsApiKey ?? config('arkesel.sms_api_key');

        throw_if(
            empty($smsApiKey),
            ArkeselSmsException::apiKeyIsRequired()
        );

        return $smsApiKey;
    }

    /**
     * Sets the SMS API Version to use for this request.
     *
     * Overrides the `ARKESEL_SMS_API_VERSION` set in the `.env` file.
     */
    public function smsApiVersion(string $smsApiVersion = 'v2'): self
    {
        $validVersions = ['v1', 'v2'];

        throw_if(
            !in_array($smsApiVersion, $validVersions),
            ArkeselSmsException::invalidSmsApiVersion()
        );

        $this->smsApiVersion = $smsApiVersion;

        return $this;
    }

    /**
     * Get arkesel SMS API version to use for this request.
     */
    public function getSmsApiVersion(): null|string
    {
        return $this->smsApiVersion ?? config('arkesel.sms_api_version', 'v2');
    }
}
