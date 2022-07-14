<?php

/*
 * @author Parables Boltnoel <parables95@gmail.com>
 * @package arkesel-sdk
 *  @version 1.0.0
 */


use Parables\ArkeselSdk\BulkSms\ArkeselSmsBuilder;
use Parables\ArkeselSdk\Test\TestCase;
use Parables\ArkeselSdk\Utils\ArkeselEndpoints;

uses(TestCase::class, ArkeselEndpoints::class, ArkeselSmsBuilder::class);

$recipients = ['233203708218', '233203708218'];

//TODO: mock all these test to prevent making real calls to Arkesel servers

// exceptions
test('arkeselSmsResponseOrException: Wrong action', function ($smsApiVersion) use ($recipients) {
    expect(
        fn () =>
        arkeselSms()
            ->message('Hello World')
            ->recipients($recipients)
            ->smsApiVersion($smsApiVersion)
            ->setInvalidAction('this should throw')
            ->send()
    )->toThrow(Parables\ArkeselSdk\Exceptions\ArkeselSmsResponseOrException::class);
})
    ->with(function () {
        yield 'v1';
    });

test('arkeselSmsResponseOrException: Authentication Failed', function ($smsApiVersion) use ($recipients) {
    expect(
        fn () =>
        arkeselSms()
            ->message('Hello World')
            ->recipients($recipients)
            ->smsApiVersion($smsApiVersion)
            ->smsApiKey(uniqid())
            ->send()
    )->toThrow(Parables\ArkeselSdk\Exceptions\ArkeselSmsResponseOrException::class);
})
    ->with(function () {
        yield 'v1';
        yield 'v2';
    });

test('arkeselSmsResponseOrException: Invalid phone number', function ($smsApiVersion) {
    expect(
        fn () =>
        arkeselSms()
            ->message('Hello World')
            ->recipients('1234567890')
            ->smsApiVersion($smsApiVersion)
            ->send()
    )->toThrow(Parables\ArkeselSdk\Exceptions\ArkeselSmsResponseOrException::class);
})
    ->with(function () {
        yield 'v1';
        yield 'v2';
    });

test('arkeselSmsResponseOrException: Phone coverage not active', function ($smsApiVersion) {
    expect(
        fn () =>
        arkeselSms()
            ->message('Hello World')
            ->recipients('12992945528')
            ->smsApiVersion($smsApiVersion)
            ->send()
    )->toThrow(Parables\ArkeselSdk\Exceptions\ArkeselSmsResponseOrException::class);
})
    ->with(function () {
        yield 'v1';
        yield 'v2';
    });


test('arkeselSmsResponseOrException: Insufficient balance', function ($smsApiVersion) use ($recipients) {
    expect(
        fn () =>
        arkeselSms()
            ->message('Hello World')
            ->recipients($recipients)
            ->smsApiVersion($smsApiVersion)
            ->send()
    )->toThrow(Parables\ArkeselSdk\Exceptions\ArkeselSmsResponseOrException::class);
})
    ->with(function () {
        yield 'v1';
    });

test('arkeselSmsResponseOrException: Invalid Sender ID', function ($smsApiVersion) use ($recipients) {
    expect(
        fn () =>
        arkeselSms()
            ->message('Hello World')
            ->recipients($recipients)
            ->sender('Arkesel')
            ->smsApiVersion($smsApiVersion)
            ->send()
    )->toThrow(Parables\ArkeselSdk\Exceptions\ArkeselSmsResponseOrException::class);
})
    ->with(function () {
        yield 'v1';
        yield 'v2';
    });

test('arkeselSmsResponseOrException: Invalid Schedule Time', function () use ($recipients) {
    expect(
        fn () =>
        arkeselSms()
            ->message('Hello World')
            ->recipients($recipients)
            ->schedule('2022/40/15')
            ->send()
    )->toThrow(Carbon\Exceptions\InvalidFormatException::class);
});
