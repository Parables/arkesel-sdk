<?php

namespace Parables\ArkeselSdk\BulkSms;

use Exception;
use Illuminate\Support\Facades\Log;
use Parables\ArkeselSdk\Exceptions\ArkeselSmsBuilderException;

trait ArkeselSmsBuilder
{
    /**
     * the sms message.
     *
     * @var null|string
     *
     * A one-page sms = 160 character, so you send a sms with 200 characters, that will be 2 pages.
     */
    protected ?string $message = null;

    /**
     * the name or number that identifies the sender of the SMS.
     *
     * @var null|string
     */
    protected ?string $sender = null;

    /**
     * phone numbers to which to send sms to.
     *
     * @var null|array
     */
    protected null|array $recipients = null;

    /**
     * schedule when the sms should be sent.
     *
     * @var null|\Illuminate\Support\Carbon
     *
     * @see https://developers.arkesel.com/#operation/send_schedule_sms_v1
     * @see https://developers.arkesel.com/#operation/send_sms
     */
    protected ?\Illuminate\Support\Carbon $schedule = null;

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
    protected ?string $smsApiKey = null;

    /**
     * Arkesel SMS API version.
     *
     * @var null|string
     */
    protected ?string $smsApiVersion = null;

    /**
     * set the sms message.
     *
     * @param  string  $message
     * @return $this
     * @throws \Parables\ArkeselSdk\Exceptions\ArkeselSmsBuilderException
     */
    public function message(string $message): self
    {
        $message = trim($message);

        throw_if(empty($message), ArkeselSmsBuilderException::messageIsRequired());

        $this->message = $message;

        return $this;
    }

    /**
     * Get the sms message.
     *
     * @return null|string
     */
    public function getMessage(): null|string
    {
        return $this->message;
    }

    /**
     * set the name or number that identifies the sender of an sms.
     *
     * @param  string  $sender
     * @return $this
     * @throws \Parables\ArkeselSdk\Exceptions\ArkeselSmsBuilderException
     */
    public function sender(string $sender): self
    {
        $sender = trim($sender);

        throw_if(empty($sender), ArkeselSmsBuilderException::senderIsRequired());

        throw_if(strlen($sender) > 11, ArkeselSmsBuilderException::senderLengthExceeded());

        $this->sender = $sender;

        return $this;
    }

    /**
     * Get the name or number that identifies the sender of the SMS.
     *
     * @return null|string
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
     * @throws \Parables\ArkeselSdk\Exceptions\ArkeselSmsBuilderException
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

        throw_if(empty($recipients), ArkeselSmsBuilderException::recipientsAreRequired());

        $this->recipients = $recipients;

        Log::info('recipients', ['recipients' => $recipients]);

        return $this;
    }

    /**
     * Get phone numbers to which to send sms to.
     * @param null|string $smsApiVersion
     * @return null|string|array
     */
    public function getRecipients(string $smsApiVersion = null): null|string|array
    {
        if (empty($this->recipients)) {
            return null;
        }
        $smsApiVersion = $smsApiVersion ?? $this->getSmsApiVersion();

        return $smsApiVersion  === 'v1'
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
     * @param  string|\Illuminate\Support\Carbon  $schedule
     * @return $this
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
     *
     * @param  string  $apiVersion
     * @return null|string
     */
    public function getSchedule(string $smsApiVersion = null): null|string
    {
        $smsApiVersion = $smsApiVersion ?? $this->getSmsApiVersion();

        if (!empty($this->schedule)) {
            return $this->schedule->format(
                $smsApiVersion  === 'v1'
                    ? 'd-m-Y h:i A'  //E.g: "13-01-2021 05:30 PM"
                    : 'Y-m-d h:i A'  //E.g: "2021-03-17 07:00 AM"
            );
        }

        return null;
    }

    /**
     * set a URL that will be called to notify you about the status of the sms to a particular number.
     *
     * In cases where a default callbackUrl is specified in the config
     * but you prefer to send sms without the default callbackUrl, passing an empty
     * string to the callbackUrl will set a null value while a passing a null
     * value will fallback to the default callbackUrl is specified in the config
     * @param  string  $callbackUrl
     *
     * @see developershttps://developers.arkesel.com/#operation/send_sms
     *
     * @return $this
     */
    public function callbackUrl(string $callbackUrl = null): self
    {
        if ($callbackUrl === '') {
            $this->callbackUrl = null;
        } elseif (empty($callbackUrl = trim($callbackUrl))) {
            config('arkesel.sms_callback_url');
        } else {
            $this->callbackUrl = $callbackUrl;
        }
        return $this;
    }

    /**
     * Get a URL that will be called to notify you about the status of the sms to a particular number.
     *
     * @return null|string
     */
    public function getCallbackUrl(): null|string
    {
        return $this->callbackUrl;
    }

    /**
     * set the environment mode. In sandbox mode, sms are not delivered to the recipients.
     *
     * @param bool sandbox
     * @return $this
     */
    public function sandbox(bool $sandbox = true): self
    {
        $this->sandbox = boolval($sandbox);

        return $this;
    }

    /**
     * Get the SMS environment mode.
     *
     * @return null|bool
     */
    public function getSandbox(): null|bool
    {
        return $this->sandbox;
    }

    /**
     * sets the SMS API Key to used to authenticate the request
     *
     * Overrides the `ARKESEL_SMS_API_KEY` set in the `.env` file.
     *
     * @param  string  $smsApiKey
     * @return $this
     * @throws \Parables\ArkeselSdk\Exceptions\ArkeselSmsBuilderException
     */
    public function smsApiKey(string $smsApiKey): self
    {
        $smsApiKey = trim($smsApiKey);

        throw_if(empty($smsApiKey), ArkeselSmsBuilderException::apiKeyIsRequired());

        $this->smsApiKey = $smsApiKey;

        return $this;
    }

    /**
     * Get arkesel SMS API Key to use for this request.
     *
     * @return null|string
     */
    public function getSmsApiKey(): null|string
    {
        return $this->smsApiKey;
    }

    /**
     * sets the SMS API Version to use for this request
     *
     * Overrides the `ARKESEL_SMS_API_VERSION` set in the `.env` file.
     *
     * @param  string  $smsApiVersion
     * @return $this
     */
    public function smsApiVersion(string $smsApiVersion = 'v2'): self
    {
        $validVersions = ['v1', 'v2'];

        throw_if(!in_array($smsApiVersion, $validVersions), new Exception('SMS API version is not valid'));

        $this->smsApiVersion = $smsApiVersion;

        return $this;
    }

    /**
     * Get arkesel SMS API version to use for this request.
     *
     * @return null|string
     */
    public function getSmsApiVersion(): null|string
    {
        return $this->smsApiVersion;
    }
}
