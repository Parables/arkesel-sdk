<?php

/*
 * @author Parables Boltnoel <parables95@gmail.com>
 * @package arkesel-sdk
 *  @version 1.0.0
 */

namespace Parables\ArkeselSdk\BulkSms;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Parables\ArkeselSdk\Exceptions\ArkeselSmsResponseException;

class ArkeselSms
{
    protected string $smsUrl;
    protected string $apiVersion;
    protected ?string $apiKey;
    protected ?string $smsSender;
    protected ?string $smsCallbackUrl;
    protected bool $smsSandbox;
    protected ArkeselMessageBuilder $builder;

    public function __construct(?ArkeselMessageBuilder $builder)
    {
        $this->smsUrl = config('arkesel.sms_url', 'https://sms.arkesel.com/api/v2/sms/send');
        $this->apiVersion = config('arkesel.api_version', 'v2');
        $this->apiKey = config('arkesel.api_key');
        $this->smsSender = config('arkesel.sms_sender');
        $this->smsCallbackUrl = config('arkesel.sms_callback_url');
        $this->smsSandbox = config('arkesel.sms_sandbox', false);
        $this->builder = $builder ?? new ArkeselMessageBuilder();
    }

    /**
     * proxy to the __constructor to be used as a Facade.
     *
     * @return array
     */
    public function make(?ArkeselMessageBuilder $builder): self
    {
        $this->__construct($builder);

        return $this;
    }

    /**
     * return an array of the values received from the `config/arkesel.php` file.
     *
     * @return array
     */
    public function getConfig(): array
    {
        return config('arkesel');
    }

    /**
     * set the content to be sent.
     *
     * @param  string  $content
     * @return $this
     */
    public function message(string $message): self
    {
        $this->builder->message($message);

        return $this;
    }

    /**
     * set the name or number that identifies the sender of an sms.
     *
     * @param  string  $sender
     * @return $this
     */
    public function from(string $sender): self
    {
        $this->builder->sender($sender);

        return $this;
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
    public function to(string|array $recipients): self
    {
        $this->builder->recipients($recipients);

        return $this;
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
    public function schedule(string|Carbon $schedule): self
    {
        $this->builder->schedule($schedule);

        return $this;
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
    public function callbackUrl(string $callbackUrl): self
    {
        $this->builder->callbackUrl($callbackUrl);

        return $this;
    }

    /**
     * if true, sms messages are not forwarded to the mobile network providers for delivery,
     *  hence you are not billed for the operation. Use this to test your application.
     *
     * @param bool sandbox
     * @return $this
     */
    public function sandbox(bool $sandbox = true): self
    {
        $this->builder->sandbox($sandbox);

        return $this;
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
        $this->builder->apiKey($apiKey);

        return $this;
    }

    /**
     * sends the sms to the recipients.
     *
     * @return Response
     */
    public function send(): Response
    {
        $this->setMessageBuilderDefaults();

        $payload = $this->apiVersion === 'v1'
            ? array_filter([
                'action' => 'send-sms',
                'api_key' => $this->builder->getApiKey(),
                'to' => $this->builder->getRecipients(apiVersion: $this->apiVersion),
                'from' => $this->builder->getSender(),
                'sms' => $this->builder->getMessage(),
                'schedule' => $this->builder->getSchedule(),
            ])
            : array_filter([
                'sender' => $this->builder->getSender(),
                'recipients' => $this->builder->getRecipients(),
                'message' => $this->builder->getMessage(),
                'callback_url' => $this->builder->getCallbackUrl(),
                'scheduled_date' => $this->builder->getSchedule(),
                'sandbox' => $this->builder->getSandbox(),
            ]);

        Log::info('payload', $payload);

        $response = $this->apiVersion === 'v1'
            ? Http::get($this->smsUrl, $payload)
            : Http::withHeaders([
                'api-key' => $this->builder->getApiKey(),
            ])->post($this->smsUrl, $payload);

        Log::info('SMS Client: ', ['response' => $response->json()]);

        throw_if($response->failed(), ArkeselSmsResponseException::handleResponse(response: $response));

        return $response;
    }

    /**
     * set the optional properties of the ArkeselMessageBuilder
     * to use the default values specified in the `arkesel` config file.
     *
     * @return void
     */
    protected function setMessageBuilderDefaults()
    {
        $this->builder->apiKey($this->builder->getApiKey() ?? $this->apiKey ?? '');
        $this->builder->sender($this->builder->getSender() ?? $this->smsSender ?? '');
        $this->builder->callbackUrl($this->builder->getCallbackUrl() ?? $this->smsCallbackUrl);
        $this->builder->sandbox($this->builder->getSandbox() ?? $this->smsSandbox);
    }
}
