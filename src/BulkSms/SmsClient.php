<?php

/*
 * @author Parables Boltnoel <parables95@gmail.com>
 * @package arkesel-sdk
 *  @version 1.0.0
 */

namespace Parables\ArkeselSdk\BulkSms;

use GuzzleHttp\Client;
use Parables\ArkeselSdk\Exceptions\HandleSmsException;
use Parables\ArkeselSdk\Exceptions\InvalidSmsMessageException;

class SmsClient
{
    protected Client $client;
    protected string $apiKey;
    protected string $apiVersion;
    protected string $smsUrl;
    protected string $smsSender;
    protected ?string $smsCallbackUrl;
    protected bool $smsSandbox;

    public function __construct()
    {
        $this->client = new Client(['base_uri' => 'https://sms.arkesel.com']);
        $this->apiKey = config('arkesel.api_key');
        $this->apiVersion = config('arkesel.api_version', 'v2');
        $this->smsUrl = config('arkesel.sms_url', 'https://sms.arkesel.com/api/v2/sms/send');
        $this->smsSender = config('arkesel.sms_sender');
        $this->smsCallbackUrl = config('arkesel.sms_callback_url');
        $this->smsSandbox = config('arkesel.sms_sandbox', true);
    }

    public function send(ArkeselMessage $message)
    {
        if (empty($message->recipients)) {
            throw new InvalidSmsMessageException(message: 'No recipients were specified for this notification');
        }

        $payload = $this->apiVersion === 'v1'
            ? array_filter([
                'action' => 'send-sms',
                'api_key' => $message->apiKey ?? $this->apiKey,
                'to' => implode(',', $message->recipients),
                'from' => $message->sender ?? $this->smsSender,
                'sms' => $message->message,
                'schedule' => $message->schedule ?? null,  // dd-mm-yyyy hh:mm AM/PM
            ])
            : array_filter([
                'sender' => $message->sender ?? $this->smsSender,
                'recipients' => $message->recipients,
                'message' => $message->message,
                'callback_url' => $message->callbackUrl ?? $this->smsCallbackUrl,
                'scheduled_date' => $message->schedule ?? null,  // 'Y-m-d H:i A' //E.g: "2021-03-17 07:00 AM"
                'sandbox' => $message->sandbox ?? $this->smsSandbox,
            ]);

        $response = null;
        try {
            $response = $this->client->request(
                method: $this->apiVersion === 'v1' ? 'GET' : 'POST',
                uri: $this->smsUrl,
                options: array_filter([
                    'headers' => array_filter([
                        'api-key' => $this->apiVersion === 'v2' ? $this->apiKey : null,
                    ]),
                    'query' => $this->apiVersion === 'v1' ? $payload : null,
                    'json' => $payload,
                ]),
            );
        } catch (\Throwable $th) {
            throw new HandleSmsException(response: $response);
        }
    }
}
