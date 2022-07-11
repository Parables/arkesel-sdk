<?php

/*
 * @author Parables Boltnoel <parables95@gmail.com>
 * @package arkesel-sdk
 *  @version 1.0.0
 */

use Parables\ArkeselSdk\BulkSms\ArkeselMessageBuilder;
use Parables\ArkeselSdk\Exceptions\ArkeselMessageBuilderException;
use Parables\ArkeselSdk\Test\TestCase;

uses(TestCase::class);

// TEST: replace these with real values
$recipients = ['233234567890', '233234567890'];
$apiKey = '62cc5136389d1';

test('receives config array', function () use ($apiKey) {
    expect(arkeselSms()->getConfig())->toMatchArray([
        'api_key' => $apiKey,
        'api_version' => 'v2',
        'sms_url' => 'https://sms.arkesel.com/api/v2/sms/send',
        'sms_sender' => 'Test App',
        // 'sms_callback_url' => '',
        'sms_sandbox' => true,
    ]);
});

test('message builder:all setters and getters works', function () {
    $message = 'Hello World';

    $builder = new ArkeselMessageBuilder();
    $builder->message($message);

    expect($builder->getMessage())->toEqual($message);
});

test('message builder:throws on empty message', function () {
    $builder = new ArkeselMessageBuilder();
    expect(fn () => $builder->message(''))->toThrow(ArkeselMessageBuilderException::class);
    expect(fn () => $builder->message(' '))->toThrow(ArkeselMessageBuilderException::class);
});

test('message builder:throws api is required', function () {
    $builder = new ArkeselMessageBuilder();
    expect(fn () => $builder->apiKey(''))->toThrow(ArkeselMessageBuilderException::class);
    expect(fn () => $builder->apiKey(' '))->toThrow(ArkeselMessageBuilderException::class);
});

test('message builder:throws no recipients for sms', function () {
    $builder = new ArkeselMessageBuilder();
    expect(fn () => $builder->recipients(''))->toThrow(ArkeselMessageBuilderException::class);
    expect(fn () => $builder->recipients([]))->toThrow(ArkeselMessageBuilderException::class);
    expect(fn () => $builder->recipients(['', ' ']))->toThrow(ArkeselMessageBuilderException::class);
});

test('message builder:return unique recipients', function () {
    $builder = new ArkeselMessageBuilder();
    expect($builder->recipients(',2,2,3,,4')->getRecipients())->toEqual(['2', '3', '4']);
    expect($builder->recipients(['', '2', ' ', '2', '3', '4'])->getRecipients())->toEqual(['2', '3', '4']);
});

test('sendSms: in sandbox mode', function () use ($recipients) {
    $response = arkeselSms()
        ->message('Hello World')
        ->to($recipients)
        ->sandbox()
        ->send();

    expect($response->json('status'))->toEqual('success');
});

test('sendSms: sendSms methods are proxies for ArkeselMessageBuilder methods', function () use ($recipients) {
    $response = arkeselSms()
        ->message('Hello World')
        ->to($recipients)
        ->send();

    expect($response->json('status'))->toEqual('success');

    // expect that duplicates were filtered out
    expect(count($response->json('data')))->toEqual(1);

    // expect only one unique recipient
    expect($response->json('data')[0]['recipient'])->toEqual($recipients[0]);
});

test('sendSms: using a message builder', function () use ($recipients) {
    $builder = new ArkeselMessageBuilder();

    $builder
        // ->sandbox(false);
        ->message('Hello World')
        ->recipients($recipients);

    $response = arkeselSms(builder: $builder)->send();

    expect($response->json('status'))->toEqual('success');
});

// More Tests
// message builder: schedule sms using string or Carbon datetime
// message builder: config defaults are used
// message builder: config defaults are overridden by ArkeselMessageBuilder setters
// notification: with string message that creates new MessageBuilderInstances
// notification: using ArkeselMessageBuilder
