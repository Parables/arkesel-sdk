<?php

/*
 * @author Parables Boltnoel <parables95@gmail.com>
 * @package arkesel-sdk
 *  @version 1.0.0
 */

use Parables\ArkeselSdk\Test\TestCase;
use Parables\ArkeselSdk\Utils\ArkeselEndpoints;

uses(TestCase::class, ArkeselEndpoints::class);

// Tip: dataset values are spread to the closure in order
test('arkesel endpoints using combined datasets: given a feature, it returns the endpoint URL', function ($baseServer, $apiVersion, $resource, $resourceEndpoint) {
    expect($this->getEndpoint($baseServer, $resource, $apiVersion))->toEqual($baseServer.$resourceEndpoint);
})->with([
    ['https://sms.arkesel.com', 'v1', 'send_sms', '/sms/api?action=send-sms'],
    ['https://sms.arkesel.com', 'v2', 'send_sms', '/api/v2/sms/send'],
    ['https://sms.arkesel.com', 'v1', 'sms_balance', '/sms/api?action=check-balance'],
    ['https://sms.arkesel.com', 'v2', 'sms_balance', '/api/v2/clients/balance-details'],
    ['https://sms.arkesel.com', 'v1', 'save_contact', '/contacts/api'],
    ['https://sms.arkesel.com', 'v2', 'save_contact', '/api/v2/contacts'],
    // should work even if apiVersion is invalid
    ['https://sms.arkesel.com', null, 'sms_details', '/api/v2/sms'],
    ['https://sms.arkesel.com', 5, 'create_contact_group', '/api/v2/contacts/groups'],
    ['https://sms.arkesel.com', 'v5', 'sms_to_group', '/api/v2/sms/send/contact-group'],
    ['https://sms.arkesel.com', 'v5', 'voice_sms', '/api/v2/sms/voice/send'],
    ['https://sms.arkesel.com', 'v5', 'generate_otp', '/api/otp/generate'],
    ['https://sms.arkesel.com', 'v5', 'verify_otp', '/api/otp/verify'],
    ['https://sms.arkesel.com', 'v5', 'ussd', '/ussd-endpoint-url'],
    ['https://payment.arkesel.com', 'v5', 'initiate_charge', '/api/v1/payment/charge/initiate'],
    ['https://payment.arkesel.com', 'v5', 'verify_charge', '/api/v1/verify/transaction'],
]);
