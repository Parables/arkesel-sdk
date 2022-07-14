<?php

/*
 * @author Parables Boltnoel <parables95@gmail.com>
 * @package arkesel-sdk
 *  @version 1.0.0
 */

use Parables\ArkeselSdk\BulkSms\ArkeselMessageBuilder;
use Parables\ArkeselSdk\BulkSms\ArkeselSmsBuilder;
use Parables\ArkeselSdk\Test\TestCase;

uses(TestCase::class, ArkeselSmsBuilder::class);


$recipients = ['233203708218', '233203708218', ' 233242158675 ', '233242158675'];


test('smsBuilder: message setter trims value', function () {

    $builder = new ArkeselMessageBuilder();

    $message = ' This is a message ';

    expect($builder->message($message)->getMessage())->toEqual(trim($message));

    $message = 'Hello World';

    expect($builder->message($message)->getMessage())->toEqual($message);
});


test('smsBuilder: recipient setter removes empty values returns unique values', function () {

    $builder = new ArkeselMessageBuilder();

    expect($builder->recipients(',2,2,3,,4')->getRecipients())->toEqual(['2', '3', '4']);

    expect($builder->recipients(['', '2', ' ', '2', '3', '4'])->getRecipients())->toEqual(['2', '3', '4']);
});

test('smsBuilder: recipient getter returns unique comma-separated string for smsApiVersion v1 and unique array for smsApiVersion v2', function () use ($recipients) {

    $builder = new ArkeselMessageBuilder();

    expect($builder->recipients($recipients)->smsApiVersion('v1')->getRecipients())
        ->toEqual(trim($recipients[0]) . ',' . trim($recipients[3]));

    expect($builder->getRecipients())->toHaveLength(25);

    expect($builder->recipients($recipients)->getRecipients('v2'))
        ->toEqual([trim($recipients[0]), trim($recipients[3])]);

    expect($builder->getRecipients('v2'))->toHaveCount(2);
});


test('smsBuilder: sender is set and retrieved', function () {

    $builder = new ArkeselMessageBuilder();

    expect($builder->sender('from test')->getSender())->toEqual('from test');
});


test('smsBuilder: schedule getter return the correct for for smsApiVersion v1', function () {
    $builder = new ArkeselMessageBuilder();

    $now = now();

    expect($builder->schedule($now->addMinute())->getSchedule('v1'))->toEqual($now->format('d-m-Y h:i A'));

    // alternative
    expect($builder->schedule($now->addMinute())->smsApiVersion('v1')->getSchedule())->toEqual($now->format('d-m-Y h:i A'));
});

test('smsBuilder: schedule getter return the correct for for smsApiVersion v2', function () {
    $builder = new ArkeselMessageBuilder();

    $now = now();

    expect($builder->schedule($now->addMinute())->getSchedule('v2'))->toEqual($now->format('Y-m-d h:i A'));

    // alternative
    expect($builder->schedule($now->addMinute())->smsApiVersion('v2')->getSchedule())->toEqual($now->format('Y-m-d h:i A'));
});

test('smsBuilder: getSchedule `smsApiVersion`  param vs `smsApiVersion()` setter order of precedence ', function () {
    $builder = new ArkeselMessageBuilder();

    $now = now();

    // v1 format takes precedence
    expect($builder->schedule($now->addMinute())->smsApiVersion('v1')->getSchedule())->toEqual($now->format('d-m-Y h:i A'));

    // v2 format takes precedence
    expect($builder->schedule($now->addMinute())->smsApiVersion('v2')->getSchedule())->toEqual($now->format('Y-m-d h:i A'));

    // v1 format takes precedence
    expect($builder->schedule($now->addMinute())->smsApiVersion('v2')->getSchedule('v1'))->toEqual($now->format('d-m-Y h:i A'));

    // v2 format takes precedence
    expect($builder->schedule($now->addMinute())->smsApiVersion('v1')->getSchedule('v2'))->toEqual($now->format('Y-m-d h:i A'));
});


test('smsBuilder: callbackUrl setter trim the value', function () {

    $builder = new ArkeselMessageBuilder();

    $callbackUrl = ' http://localhost:3000 ';

    expect($builder->callbackUrl($callbackUrl)->getCallbackUrl())->toEqual(trim($callbackUrl));
});

test('smsBuilder: callbackUrl setters sets value to null if value is empty', function () {

    expect(arkeselSms()->callbackUrl('')->getCallbackUrl())->toBeNull();
});


test('smsBuilder: sandbox setter by default sets to true', function () {

    $builder = new ArkeselMessageBuilder();

    expect($builder->sandbox()->getSandbox())->toBeTrue();
});

test('smsBuilder: sandbox setter sets bool value', function () {

    $builder = new ArkeselMessageBuilder();

    expect($builder->sandbox(false)->getSandbox())->toBeFalse();

    expect($builder->sandbox(true)->getSandbox())->toBeTrue();

    expect($builder->sandbox(0)->getSandbox())->toBeFalse();

    expect($builder->sandbox(1)->getSandbox())->toBeTrue();
});
