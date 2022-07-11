<?php

/*
 * @author Parables Boltnoel <parables95@gmail.com>
 * @package arkesel-sdk
 *  @version 1.0.0
 */

namespace Parables\ArkeselSdk\BulkSms;

use Exception;
use Illuminate\Support\Facades\Log;
use Parables\ArkeselSdk\Exceptions\MessageBuilderException;

class MessageBuilder
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
     * @var null|string
     *
     * @see https://developers.arkesel.com/#operation/send_schedule_sms_v1
     * @see https://developers.arkesel.com/#operation/send_sms
     */
    protected ?string $schedule = null;

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
     * @var bool
     */
    protected bool $sandbox = false;

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

        throw_if(empty($message), MessageBuilderException::messageIsEmpty());

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

        throw_if(empty($sender), MessageBuilderException::senderIsRequired());

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
        $recipients = array_unique(array_filter(is_string($recipients) ? explode(',', $recipients) : $recipients));

        throw_if(empty($recipients), MessageBuilderException::noRecipients());

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
     * @var string
     *
     * @see https://developers.arkesel.com/#operation/send_schedule_sms_v1
     * @see https://developers.arkesel.com/#operation/send_sms
     *
     * @param  string  $schedule
     * @return $this
     */
    public function schedule(string $schedule): self // TODO: change from string to carbon date and parse it into the correct format
    {
        $this->schedule = $schedule;

        return $this;
    }

    /**
     * Get schedule when the sms should be sent.
     *
     * @return  null|string
     */
    public function getSchedule(): null|string
    {
        return $this->schedule;
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
     * @return  bool
     */
    public function getSandbox(): bool
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

        throw_if(empty($apiKey), MessageBuilderException::apiKeyIsRequired());

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
