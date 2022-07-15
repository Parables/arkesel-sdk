<?php

/*
 * @author Parables Boltnoel <parables95@gmail.com>
 * @package arkesel-sdk
 *  @version 1.0.0
 */

use Parables\ArkeselSdk\BulkSms\ArkeselMessageBuilder;
use Parables\ArkeselSdk\BulkSms\ArkeselSmsBuilder;
use Parables\ArkeselSdk\Exceptions\ArkeselSmsBuilderException;
use Parables\ArkeselSdk\Test\TestCase;
use Parables\ArkeselSdk\Utils\ArkeselEndpoints;

uses(TestCase::class, ArkeselEndpoints::class, ArkeselSmsBuilder::class);

test('smsBuilderException:throws on empty message', function () {
    $builder = new ArkeselMessageBuilder();

    expect(fn () => $builder->message(''))->toThrow(ArkeselSmsBuilderException::class);

    expect(fn () => $builder->message(' '))->toThrow(ArkeselSmsBuilderException::class);
});

test('smsBuilderException:throws api is required', function () {
    $builder = new ArkeselMessageBuilder();

    expect(fn () => $builder->smsApiKey(''))->toThrow(ArkeselSmsBuilderException::class);

    expect(fn () => $builder->smsApiKey(' '))->toThrow(ArkeselSmsBuilderException::class);
});

test('smsBuilderException:throws no recipients for sms', function () {
    $builder = new ArkeselMessageBuilder();

    expect(fn () => $builder->recipients(''))->toThrow(ArkeselSmsBuilderException::class);

    expect(fn () => $builder->recipients([]))->toThrow(ArkeselSmsBuilderException::class);

    expect(fn () => $builder->recipients(['', ' ']))->toThrow(ArkeselSmsBuilderException::class);
});

test('smsBuilderException:throws no sender should not exceed 11 characters', function () {
    $builder = new ArkeselMessageBuilder();

    expect(fn () => $builder->sender('My App Name is too long to be used as a sender id'))->toThrow(ArkeselSmsBuilderException::class);

    expect(fn () => $builder->sender('MyApp'))->not()->toThrow(ArkeselSmsBuilderException::class);
});
