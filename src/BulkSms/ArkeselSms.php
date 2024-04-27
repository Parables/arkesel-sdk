<?php

/*
 * @author Parables Boltnoel <parables95@gmail.com>
 * @package arkesel-sdk
 *  @version 1.0.0
 */

namespace Parables\ArkeselSdk\BulkSms;

use Illuminate\Support\Facades\Http;
use Parables\ArkeselSdk\Exceptions\ArkeselSmsBuilderException;
use Parables\ArkeselSdk\Exceptions\ArkeselSmsResponseOrException;
use Parables\ArkeselSdk\Utils\ArkeselEndpoints;

class ArkeselSms
{
    use ArkeselEndpoints;

    private const BASE_SERVER = 'https://sms.arkesel.com';

    public function __construct(
        private ArkeselMessageBuilder $builder
    ) {
    }

    /**
     * proxy to the __constructor to be used as a Facade.
     *
     * @return $this
     */
    public static function make(ArkeselMessageBuilder $builder): self
    {
        return new ArkeselSms(builder: $builder);
    }

    /**
     * sends the sms to the recipients.
     *
     * @return array
     *
     * @throws Exception
     */
    public function send(): array
    {

        $smsEndpoint = $this->getEndpoint(
            baseServer: self::BASE_SERVER,
            resource: 'send_sms',
            apiVersion: $this->builder->getSmsApiVersion()
        );

        $response = $this->builder->getSmsApiVersion() === 'v1'
            ? Http::get(
                $smsEndpoint,
                array_filter([
                    'action' => 'send-sms',
                    'api_key' => $this->builder->getSmsApiKey(),
                    'to' => $this->builder->getRecipients(),
                    'from' => $this->builder->getSender(),
                    'sms' => $this->builder->getMessage(),
                    'schedule' => $this->builder->getSchedule(),
                ])
            )
            : Http::withHeaders(['api-key' => $this->builder->getSmsApiKey()])
            ->post(
                $smsEndpoint,
                array_filter([
                    'sender' => $this->builder->getSender(),
                    'recipients' => $this->builder->getRecipients(),
                    'message' => $this->builder->getMessage(),
                    'callback_url' => $this->builder->getCallbackUrl(),
                    'scheduled_date' => $this->builder->getSchedule(),
                    'sandbox' => $this->builder->getSandbox(),
                ])
            );

        return ArkeselSmsResponseOrException::handleResponse(response: $response);
    }

    /**
     * get the sms balance.
     *
     * @return array
     *
     * @throws \Exception
     */
    public static function getSmsBalance(string $smsApiVersion = null, string $smsApiKey = null): array
    {
        $builder = new ArkeselMessageBuilder();
        $smsApiVersion ??= $builder->getSmsApiVersion();
        $smsApiKey ??= $builder->getSmsApiKey();

        $smsBalanceEndpoint = self::getEndpoint(
            baseServer: self::BASE_SERVER,
            resource: 'sms_balance',
            apiVersion: $smsApiVersion,
        );

        $response = $smsApiVersion === 'v1'
            ? Http::get($smsBalanceEndpoint, [
                'action' => 'check-balance',
                'api_key' => $smsApiKey,
                'response' => 'json',
            ])
            : Http::withHeaders([
                'api-key' => $smsApiKey,
            ])->get($smsBalanceEndpoint);

        return ArkeselSmsResponseOrException::handleResponse(response: $response);
    }
}
