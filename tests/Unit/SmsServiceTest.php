<?php

/*
 * @author Parables Boltnoel <parables95@gmail.com>
 * @package arkesel-sdk
 *  @version 1.0.0
 */

use Parables\ArkeselSdk\BulkSms\MessageBuilder;
use Parables\ArkeselSdk\Exceptions\MessageBuilderException;
use Parables\ArkeselSdk\Test\TestCase;

uses(TestCase::class);


test('receives config array', function () {
    $builder = new MessageBuilder();
    $builder->message('Hello World')->recipients(['233203708218']);
    $response = arkeselSms()
        ->message($builder->getMessage())
        ->to($builder->getRecipients())
        ->sandbox(true)
        ->send();

    dump($response->json());
});

test('message builder:set message', function () {
    $message = 'Hello World';

    $builder = new MessageBuilder();
    $builder->message($message);

    expect($builder->getMessage())->toEqual($message);
});

test('message builder:throws on empty message', function () {
    $builder = new MessageBuilder();
    expect(fn () => $builder->message(''))->toThrow(MessageBuilderException::class);
    expect(fn () => $builder->message(' '))->toThrow(MessageBuilderException::class);
});
