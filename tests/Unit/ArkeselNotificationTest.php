<?php

/*
 * @author Parables Boltnoel <parables95@gmail.com>
 * @package arkesel-sdk
 *  @version 1.0.0
 */

use Illuminate\Support\Facades\Notification;
use Parables\ArkeselSdk\Test\TestCase;

uses(TestCase::class);

// TEST: replace these with real values
$recipients = ['233234567890', '233234567890'];
$apiKey = '62cc5136389d1';

test('notification: sent on demand', function () {
    //
})->skip();

test('notification: sent on using a string message', function () {
    //
})->skip();

test('notification: sent using an ArkeselMessageBuilder', function () {
    //
})->skip();

// More Tests
// notification: with string message that creates new MessageBuilderInstances
// notification: using ArkeselMessageBuilder

class TestNotification extends Notification
{
    protected string $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function via($notifiable)
    {
        return ['arkesel'];
    }

    public function toArkesel($notifiable)
    {
        return $this->message;
    }
}
