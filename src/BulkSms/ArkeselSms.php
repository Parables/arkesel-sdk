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

    use ArkeselSmsBuilder;

    private const BASE_SERVER = 'https://sms.arkesel.com';

    private ?string $invalidAction = null;

    public function __construct(?ArkeselMessageBuilder $builder = null)
    {
        $this->sender(config('arkesel.sms_sender'));
        $this->callbackUrl(config('arkesel.sms_callback_url'));
        $this->sandbox(config('arkesel.sms_sandbox', false));
        $this->smsApiKey(config('arkesel.sms_api_key'));
        $this->smsApiVersion(config('arkesel.sms_api_version', 'v2'));

        if ($builder !== null) { // extract the data

            $this->sender($builder->getSender() ?? config('arkesel.sms_sender'))
                ->callbackUrl($builder->getCallbackUrl() ?? config('arkesel.sms_callback_url'))
                ->sandbox($builder->getSandbox() ?? config('arkesel.sms_sandbox', false))
                ->smsApiKey($builder->getSmsApiKey() ?? config('arkesel.sms_api_key'))
                ->smsApiVersion($builder->getSmsApiVersion() ?? config('arkesel.sms_api_version', 'v2'));

            // set if not empty
            if (! empty(trim($builder->getMessage()))) {
                $this->message($builder->getMessage());
            }

            // set if not empty
            if (! empty($builder->getRecipients())) {
                $this->recipients($builder->getRecipients());
            }
            // set if not empty
            if (! empty($builder->getSchedule())) {
                $this->schedule($builder->getSchedule());
            }
        }

        return $this;
    }

    /**
     * proxy to the __constructor to be used as a Facade.
     *
     * @return $this
     */
    public static function make(?ArkeselMessageBuilder $builder = null): self
    {
        return new ArkeselSms(builder: $builder);
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
     * Set the value of invalidAction.
     *
     * @return self
     */
    public function setInvalidAction(string $invalidAction = null)
    {
        $this->invalidAction = $invalidAction;

        return $this;
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
        // validate the data to be sent
        $this->ensureApiKeyIsSet()
            ->ensureMessageIsSet()
            ->ensureSenderIsSet()
            ->ensureRecipientsAreSet();

        $smsEndpoint = $this->getEndpoint(
            baseServer: self::BASE_SERVER,
            resource: 'send_sms',
            apiVersion: $this->getSmsApiVersion()
        );

        $response = $this->getSmsApiVersion() === 'v1'
            ? Http::get(
                $smsEndpoint,
                array_filter([
                    'action' => $this->invalidAction ?? 'send-sms',
                    'api_key' => $this->getSmsApiKey(),
                    'to' => $this->getRecipients(smsApiVersion: $this->smsApiVersion),
                    'from' => $this->getSender(),
                    'sms' => $this->getMessage(),
                    'schedule' => $this->getSchedule(),
                ])
            )
            : Http::withHeaders(['api-key' => $this->getSmsApiKey()])
            ->post(
                $smsEndpoint,
                array_filter([
                    'sender' => $this->getSender(),
                    'recipients' => $this->getRecipients(),
                    'message' => $this->getMessage(),
                    'callback_url' => $this->getCallbackUrl(),
                    'scheduled_date' => $this->getSchedule(),
                    'sandbox' => $this->getSandbox(),
                ])
            );

        return ArkeselSmsResponseOrException::handleResponse(response: $response);
    }

    /**
     * get the sms balance.
     *
     * @return array
     *
     * @throws Exception
     */
    public function getSmsBalance(): array
    {
        // validate the data to be sent
        $this->ensureApiKeyIsSet();

        $smsBalanceEndpoint = $this->getEndpoint(
            baseServer: self::BASE_SERVER,
            resource: 'sms_balance',
            apiVersion: $this->getSmsApiVersion()
        );

        $response = $this->getSmsApiVersion() === 'v1'
            ? Http::get(
                $smsBalanceEndpoint,
                [
                    'action' => $this->invalidAction ?? 'check-balance',
                    'api_key' => $this->getSmsApiKey(),
                    'response' => 'json',
                ]
            )
            : Http::withHeaders(
                [
                    'api-key' => $this->getSmsApiKey(),
                ]
            )->get($smsBalanceEndpoint);

        return ArkeselSmsResponseOrException::handleResponse(response: $response);
    }

    /**
     * Ensures that the SMS API key is not null.
     *
     * @return self
     *
     * @throws \Parables\ArkeselSdk\Exceptions\ArkeselSmsBuilderException
     */
    public function ensureApiKeyIsSet(): self
    {
        // fallback to the default
        if (empty($this->getSmsApiKey())) {
            $this->smsApiKey(config('arkesel.sms_api_key'));
        }

        // else
        throw_if(empty($this->getSmsApiKey()), ArkeselSmsBuilderException::apiKeyIsRequired());

        return $this;
    }

    /**
     * Ensures that the sms message is not null.
     *
     * @return self
     *
     * @throws \Parables\ArkeselSdk\Exceptions\ArkeselSmsBuilderException
     */
    public function ensureMessageIsSet(): self
    {
        throw_if(empty($this->getMessage()), ArkeselSmsBuilderException::messageIsRequired());

        return $this;
    }

    /**
     * Ensures that the sms has at least one recipient.
     *
     * @return self
     *
     * @throws \Parables\ArkeselSdk\Exceptions\ArkeselSmsBuilderException
     */
    public function ensureRecipientsAreSet(): self
    {
        throw_if(empty($this->getRecipients()), ArkeselSmsBuilderException::recipientsAreRequired());

        return $this;
    }

    /**
     * Ensures that the sms has a sender identifier that is less than 11 characters.
     *
     * @return self
     *
     * @throws \Parables\ArkeselSdk\Exceptions\ArkeselSmsBuilderException
     */
    public function ensureSenderIsSet(): self
    {
        // fallback to the default
        if (empty($this->getSender())) {
            $this->sender(config('arkesel.sms_sender'));
        }

        // else
        throw_if(empty($this->getSender()), ArkeselSmsBuilderException::senderIsRequired());

        throw_if(strlen($this->getSender()) > 11, ArkeselSmsBuilderException::senderLengthExceeded());

        return $this;
    }
}
