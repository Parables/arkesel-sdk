<?php

/*
 * @author Parables Boltnoel <parables95@gmail.com>
 * @package arkesel-sdk
 *  @version 1.0.0
 */

use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Mockery\MockInterface;
use Parables\ArkeselSdk\BulkSms\ArkeselChannel;
use Parables\ArkeselSdk\BulkSms\ArkeselMessageBuilder;
use Parables\ArkeselSdk\BulkSms\ArkeselSms;
use Parables\ArkeselSdk\Test\TestCase;

uses(TestCase::class);

beforeEach(function () {
    // TODO: create a reusable mock object
});

afterEach(fn () =>  Mockery::close());

test('notification: sent on demand', function () {
    //
})->skip();

test('notification: throws method does not exist', function () {
    //
})->skip();

test('notification: sent on using a string message', function () {
    $notification = new TestNotificationUsingStringMessage(message: 'Hello World');
    $notifiable = new TestNotifiable();

    /** @var \Mockery\MockInterface|\Mockery\LegacyMockInterface $arkeselSmsMock */
    // $arkeselSmsMock = Mockery::mock(ArkeselSms::class);
    $arkeselSmsMock = $this->mock(ArkeselSms::class, function (MockInterface $mock) {
        $mock->shouldReceive('message')
            ->once()
            ->with('Hello World');

        $mock->shouldReceive('getRecipients')
            ->once()
            ->andReturn([]);

        $mock->shouldReceive('recipients')
            ->once()
            ->with('233242158675');

        $mock->shouldReceive('send')
            ->once()
            ->andReturn(
                [
                    'status' => 'success',
                    'data' => [
                        [
                            'recipient' => '233242158675',
                            'id' => '9b752841-7ee7-4d40-b4fe-768bfb1da4f0',
                        ],
                    ],
                ]
            );
    });

    // binding mock to Laravel's service container
    // @see: https://laravel.com/docs/9.x/mocking#mocking-objects
    // $this->instance(ArkeselSms::class, $arkeselSmsMock);

    expect($arkeselSmsMock)->toBeInstanceOf(ArkeselSms::class);

    /** @var ArkeselSms $arkeselSmsMock */
    $channel = new ArkeselChannel(arkeselSms: $arkeselSmsMock);

    // dispatch the notification to the Arkesel channel
    $channel->send($notifiable, $notification);
});

test('notification: sent using an ArkeselMessageBuilder', function () {
    $notification = new TestNotificationUsingArkeselMessageBuilderWithMessage();
    $notifiable = new TestNotifiable();

    /** @var \Mockery\MockInterface|\Mockery\LegacyMockInterface $arkeselSmsMock */
    $arkeselSmsMock = $this->mock(ArkeselSms::class, function (MockInterface $mock) {

        // $mock->shouldReceive('message')
        //     ->once()
        //     ->with('Hello World')
        //     ->andReturnSelf();

        // $mock->shouldReceive('sender')
        //     ->once()
        //     ->with('SDK Tests')
        //     ->andReturnSelf();

        // $mock->shouldReceive('recipients')
        //     ->once()
        //     ->with(['233242158675', '233242158675'])
        //     ->andReturnSelf();

        // $mock->shouldReceive('schedule')
        //     ->once()
        //     ->with('first day of May 2022')
        //     ->andReturnSelf();

        // $mock->shouldReceive('callbackUrl')
        //     ->once()
        //     ->with('')
        //     ->andReturnSelf();

        // $mock->shouldReceive('send')
        //     ->once()
        //     ->andReturn(response()->json([
        //         "status" => "success",
        //         "data" => [
        //             [
        //                 "recipient" => "233242158675",
        //                 "id" => "9b752841-7ee7-4d40-b4fe-768bfb1da4f0"
        //             ],
        //         ]
        //     ]));
    });

    expect($arkeselSmsMock)->toBeInstanceOf(ArkeselSms::class);

    /** @var ArkeselSms $arkeselSmsMock */
    $channel = new ArkeselChannel(arkeselSms: $arkeselSmsMock);

    // dispatch the notification to the Arkesel channel
    $channel->send($notifiable, $notification);
});

test('notification: order of preference to get recipients', function () {
    $notifiables = [
        'routeNotificationForArkesel()' => TestNotifiablePrefersRouteNotificationForArkeselMethod::class,
        'recipients()' => TestNotifiablePrefersRecipientsMethod::class,
        'recipients' => TestNotifiablePrefersRecipientsProperty::class,
        'recipient()' => TestNotifiablePrefersRecipientMethod::class,
        'recipient' => TestNotifiablePrefersRecipientProperty::class,
        'phoneNumbers()' => TestNotifiablePrefersPhoneNumbersMethod::class,
        'phoneNumbers' => TestNotifiablePrefersPhoneNumbersProperty::class,
        'phone_numbers' => TestNotifiablePrefersSnakeCasedPhoneNumbersProperty::class,
        'phoneNumber()' => TestNotifiablePrefersPhoneNumberMethod::class,
        'phoneNumber' => TestNotifiablePrefersPhoneNumberProperty::class,
        'phone_number' => TestNotifiablePrefersSnakeCasedPhoneNumberProperty::class,
    ];

    $notification = new TestNotificationUsingStringMessage(message: 'Hello World');

    $channel = new ArkeselChannel(arkeselSms: new ArkeselSms());

    foreach ($notifiables as $arg => $notifiable) {
        expect($channel->getRecipients(notifiable: (new $notifiable), notification: $notification))
            ->toEqual($arg);
    }
});

class TestNotificationUsingStringMessage extends Notification
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

class TestNotificationUsingArkeselMessageBuilderWithMessage extends Notification
{
    public function via($notifiable)
    {
        return ['arkesel'];
    }

    public function toArkesel($notifiable)
    {
        return (new ArkeselMessageBuilder)
            ->message('Hello World')
            ->sender('SDK Tests')
            ->recipients(['233242158675', '233242158675'])
            ->schedule('first day of May 2022')
            ->callbackUrl('');
    }
}

class TestNotificationWithoutToArkeselMethod extends Notification
{
    public function via($notifiable)
    {
        return ['arkesel'];
    }
}

class TestNotifiable
{
    use Notifiable;

    public function routeNotificationForArkesel($notification)
    {
        return '233242158675';
    }
}

//region order of preference to get recipients
class TestNotifiablePrefersRouteNotificationForArkeselMethod
{
    use Notifiable;

    /*1st*/
    public function routeNotificationForArkesel($notification)
    {
        return 'routeNotificationForArkesel()';
    }

    /*2nd*/
    public function recipients($notification)
    {
        return 'recipients()';
    }

    /*3rd*/
    public string $recipients = 'recipients';

    /*4th*/
    public function recipient($notification)
    {
        return 'recipient';
    }

    /*5th*/
    public string $recipient = 'recipient';

    /*6th*/
    public function phoneNumbers($notification)
    {
        return 'phoneNumbers()';
    }

    /*7th*/
    public string $phoneNumbers = 'phoneNumbers';

    /*8th*/
    public string $phone_numbers = 'phone_numbers';

    /*9th*/
    public function phoneNumber($notification)
    {
        return 'phoneNumber()';
    }

    /*10th*/
    public string $phoneNumber = 'phoneNumber';

    /*11th*/
    public string $phone_number = 'phone_number';
}

class TestNotifiablePrefersRecipientsMethod
{
    use Notifiable;

    /*2nd*/
    public function recipients($notification)
    {
        return 'recipients()';
    }

    /*3rd*/
    public string $recipients = 'recipients';

    /*4th*/
    public function recipient($notification)
    {
        return 'recipient';
    }

    /*5th*/
    public string $recipient = 'recipient';

    /*6th*/
    public function phoneNumbers($notification)
    {
        return 'phoneNumbers()';
    }

    /*7th*/
    public string $phoneNumbers = 'phoneNumbers';

    /*8th*/
    public string $phone_numbers = 'phone_numbers';

    /*9th*/
    public function phoneNumber($notification)
    {
        return 'phoneNumber()';
    }

    /*10th*/
    public string $phoneNumber = 'phoneNumber';

    /*11th*/
    public string $phone_number = 'phone_number';
}

class TestNotifiablePrefersRecipientsProperty
{
    use Notifiable;

    /*3rd*/
    public string $recipients = 'recipients';

    /*4th*/
    public function recipient($notification)
    {
        return 'recipient';
    }

    /*5th*/
    public string $recipient = 'recipient';

    /*6th*/
    public function phoneNumbers($notification)
    {
        return 'phoneNumbers()';
    }

    /*7th*/
    public string $phoneNumbers = 'phoneNumbers';

    /*8th*/
    public string $phone_numbers = 'phone_numbers';

    /*9th*/
    public function phoneNumber($notification)
    {
        return 'phoneNumber()';
    }

    /*10th*/
    public string $phoneNumber = 'phoneNumber';

    /*11th*/
    public string $phone_number = 'phone_number';
}

class TestNotifiablePrefersRecipientMethod
{
    use Notifiable;

    /*4th*/
    public function recipient($notification)
    {
        return 'recipient()';
    }

    /*5th*/
    public string $recipient = 'recipient';

    /*6th*/
    public function phoneNumbers($notification)
    {
        return 'phoneNumbers()';
    }

    /*7th*/
    public string $phoneNumbers = 'phoneNumbers';

    /*8th*/
    public string $phone_numbers = 'phone_numbers';

    /*9th*/
    public function phoneNumber($notification)
    {
        return 'phoneNumber()';
    }

    /*10th*/
    public string $phoneNumber = 'phoneNumber';

    /*11th*/
    public string $phone_number = 'phone_number';
}

class TestNotifiablePrefersRecipientProperty
{
    use Notifiable;

    /*5th*/
    public string $recipient = 'recipient';

    /*6th*/
    public function phoneNumbers($notification)
    {
        return 'phoneNumbers()';
    }

    /*7th*/
    public string $phoneNumbers = 'phoneNumbers';

    /*8th*/
    public string $phone_numbers = 'phone_numbers';

    /*9th*/
    public function phoneNumber($notification)
    {
        return 'phoneNumber()';
    }

    /*10th*/
    public string $phoneNumber = 'phoneNumber';

    /*11th*/
    public string $phone_number = 'phone_number';
}

class TestNotifiablePrefersPhoneNumbersMethod
{
    use Notifiable;

    /*6th*/
    public function phoneNumbers($notification)
    {
        return 'phoneNumbers()';
    }

    /*7th*/
    public string $phoneNumbers = 'phoneNumbers';

    /*8th*/
    public string $phone_numbers = 'phone_numbers';

    /*9th*/
    public function phoneNumber($notification)
    {
        return 'phoneNumber()';
    }

    /*10th*/
    public string $phoneNumber = 'phoneNumber';

    /*11th*/
    public string $phone_number = 'phone_number';
}

class TestNotifiablePrefersPhoneNumbersProperty
{
    use Notifiable;

    /*7th*/
    public string $phoneNumbers = 'phoneNumbers';

    /*8th*/
    public string $phone_numbers = 'phone_numbers';

    /*9th*/
    public function phoneNumber($notification)
    {
        return 'phoneNumber()';
    }

    /*10th*/
    public string $phoneNumber = 'phoneNumber';

    /*11th*/
    public string $phone_number = 'phone_number';
}

class TestNotifiablePrefersSnakeCasedPhoneNumbersProperty
{
    use Notifiable;

    /*8th*/
    public string $phone_numbers = 'phone_numbers';

    /*9th*/
    public function phoneNumber($notification)
    {
        return 'phone_numbers';
    }

    /*10th*/
    public string $phoneNumber = 'phoneNumber';

    /*11th*/
    public string $phone_number = 'phone_number';
}

class TestNotifiablePrefersPhoneNumberMethod
{
    use Notifiable;

    /*9th*/
    public function phoneNumber($notification)
    {
        return 'phoneNumber()';
    }

    /*10th*/
    public string $phoneNumber = 'phoneNumber';

    /*11th*/
    public string $phone_number = 'phone_number';
}

class TestNotifiablePrefersPhoneNumberProperty
{
    use Notifiable;

    /*10th*/
    public string $phoneNumber = 'phoneNumber';

    /*11th*/
    public string $phone_number = 'phone_number';
}

class TestNotifiablePrefersSnakeCasedPhoneNumberProperty
{
    use Notifiable;

    /*11th*/
    public string $phone_number = 'phone_number';
}
//endregion
