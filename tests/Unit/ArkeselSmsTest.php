<?php

/*
 * @author Parables Boltnoel <parables95@gmail.com>
 * @package arkesel-sdk
 *  @version 1.0.0
 */

use Illuminate\Support\Arr;
use Parables\ArkeselSdk\BulkSms\ArkeselSms;
use Parables\ArkeselSdk\BulkSms\ArkeselSmsBuilder;
use Parables\ArkeselSdk\Facades\ArkeselSms as ArkeselSmsFacade;
use Parables\ArkeselSdk\Test\TestCase;
use Parables\ArkeselSdk\Utils\ArkeselEndpoints;

uses(TestCase::class, ArkeselEndpoints::class, ArkeselSmsBuilder::class);

$recipients = ['233203708218', '233203708218'];

//TODO: mock all these test to prevent making real calls to Arkesel servers

test('receives config array', function () {
    expect(arkeselSms()->getConfig())->toMatchArray([
        'base_server' => 'https://sms.arkesel.com',
        'sms_api_key' => 'VHdVd0NPZnJCVkhqSk9ud3VkbGc',
        'sms_api_version' => 'v2',
        'sms_sender' => 'Test App',
        // 'sms_callback_url' => '',
        'sms_sandbox' => true,
    ]);
});

test('arkeselSmsFacade: extends Facade', function () {
    expect((new ArkeselSmsFacade)->getFacadeRoot())->toBeInstanceOf(\Parables\ArkeselSdk\BulkSms\ArkeselSms::class);

    expect(method_exists((new ArkeselSmsFacade), 'getFacadeAccessor'))->toBeTrue();

    expect(ArkeselSms::make())->toBeInstanceOf(ArkeselSms::class);
});

test('sendSms: v2 with instance', function () use ($recipients) {
    $response = (new ArkeselSms())
        ->message('Hello World')
        ->recipients($recipients)
        ->send();

    expect(Arr::get($response, 'status'))->toEqual('success');
});

test('sendSms: v2 with facade', function () use ($recipients) {
    $response = ArkeselSms::make()
        ->message('Hello World')
        ->recipients($recipients)
        ->send();

    expect(Arr::get($response, 'status'))->toEqual('success');
});

test('sendSms: sends to only unique recipients', function () use ($recipients) {
    $response = arkeselSms()
        ->message('Hello World')
        ->recipients($recipients)
        ->send();

    expect(Arr::get($response, 'status'))->toEqual('success');

    // expect that duplicates were filtered out
    expect(Arr::get($response, 'data'))->toHaveCount(1);

    // expect only one unique recipient
    expect(Arr::get($response, 'data')[0]['recipient'])->toEqual($recipients[0]);
});

test('getSmsBalance: using the sms server', function ($apiVersion, $baseServer) {
    config()->set('arkesel.base_server', $baseServer);

    $response = arkeselSms()
        ->smsApiVersion($apiVersion)
        ->getSmsBalance();

    if ($apiVersion === 'v1') {
        expect(Arr::get($response, 'balance'))->toEqual(0);
        expect(Arr::get($response, 'user'))->toEqual('Parables Boltnoel');
        expect(Arr::get($response, 'country'))->toEqual('Ghana');
    } elseif ($apiVersion === 'v2') {
        expect(Arr::get($response, 'status'))->toEqual('success');
        expect(Arr::get($response, 'data.sms_balance'))->toBeString();
        expect(Arr::get($response, 'data.main_balance'))->toBeString();
    }
})->with(function () {
    yield 'v1';
    yield 'v2';
})->with(function () {
    yield 'https://sms.arkesel.com';
});
