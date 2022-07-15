<?php

/*
 * @author Parables Boltnoel <parables95@gmail.com>
 * @package arkesel-sdk
 *  @version 1.0.0
 */

namespace Parables\ArkeselSdk\Utils;

use Exception;
use Illuminate\Support\Arr;

trait ArkeselEndpoints
{
    private array $endpoints = [
        'send_sms' => [
            'v1' => '/sms/api?action=send-sms',
            'v2' => '/api/v2/sms/send',
        ],
        'sms_balance' => [
            'v1' => '/sms/api?action=check-balance',
            'v2' => '/api/v2/clients/balance-details',
        ],
        'save_contact' => [
            'v1' => '/contacts/api',
            'v2' => '/api/v2/contacts',
        ],
        'sms_details' => '/api/v2/sms',
        'create_contact_group' => '/api/v2/contacts/groups',
        'sms_to_group' => '/api/v2/sms/send/contact-group',
        'voice_sms' => '/api/v2/sms/voice/send',
        'generate_otp' => '/api/otp/generate',
        'verify_otp' => '/api/otp/verify',
        'ussd' => '/ussd-endpoint-url',
        'initiate_charge' => '/api/v1/payment/charge/initiate',
        'verify_charge' => '/api/v1/verify/transaction',
    ];

    /**
     * constructs the REST API endpoint URL to access the feature.
     *
     * @param  string  $resource
     * @param  string|null  $apiVersion
     * @return string
     */
    public function getEndpoint(string $baseServer, string $resource, ?string $apiVersion)
    {
        /** @var null|string|array */
        $resourceEndpoint = Arr::get($this->endpoints, $resource);

        throw_if(empty($resourceEndpoint), new Exception('No endpoints specified for the resource: '.$resource));

        $path = is_array($resourceEndpoint) && ! empty($resourceEndpoint)
            ? Arr::get($resourceEndpoint, $apiVersion) ?? Arr::last($resourceEndpoint)
            : $resourceEndpoint;

        throw_if(empty($path), new Exception('No endpoint specified for API version: '.$apiVersion.' for this resource: '.$resource));

        return $baseServer.$path;
    }
}
