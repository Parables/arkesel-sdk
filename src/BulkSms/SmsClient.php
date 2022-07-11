<?php

/*
 * @author Parables Boltnoel <parables95@gmail.com>
 * @package arkesel-sdk
 *  @version 1.0.0
 */

namespace Parables\ArkeselSdk\BulkSms;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Parables\ArkeselSdk\Exceptions\HandleSmsException;

class SmsClient
{
    protected string $apiKey;
    protected string $apiVersion;
    protected string $smsUrl;
    protected string $smsSender;
    protected ?string $smsCallbackUrl;
    protected bool $smsSandbox;

    public function __construct()
    {
        $this->apiKey = config('arkesel.api_key');
        $this->apiVersion = config('arkesel.api_version', 'v2');
        $this->smsUrl = config('arkesel.sms_url', 'https://sms.arkesel.com/api/v2/sms/send');
        $this->smsSender = config('arkesel.sms_sender');
        $this->smsCallbackUrl = config('arkesel.sms_callback_url');
        $this->smsSandbox = config('arkesel.sms_sandbox', true);
    }

    public function send(ArkeselMessage $message)
    {
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

        Log::info('payload', $payload);

        // try {
        $response = $this->apiVersion === 'v1'
            ? Http::get($this->smsUrl, $payload)
            : Http::withHeaders([
                'api-key' => $this->apiKey,
            ])->post($this->smsUrl, $payload);

        Log::info('SMS Client: ', ['response' => $response->json()]);

        return $response;
        // } catch (\Throwable $th) {
        //     throw new HandleSmsException(response: $response);
        // }
    }
}
