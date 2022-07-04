<?php

namespace NotificationChannels\Arkesel;

use NotificationChannels\Arkesel\Exceptions\CouldNotSendNotification;
use Illuminate\Notifications\Notification;

class ArkeselChannel
{
    public function __construct($client)
    {
        // Initialisation code here
    }

    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     *
     * @throws \NotificationChannels\Arkesel\Exceptions\CouldNotSendNotification
     */
    public function send($notifiable, Notification $notification)
    {
        //$response = [a call to the api of your notification send]

        //        if ($response->error) { // replace this by the code need to check for errors
        //            throw CouldNotSendNotification::serviceRespondedWithAnError($response);
        //        }




        try {
            $response =  Http::withHeaders([
                "api-key" =>  config('services.arkesel.key'),
                "Content-Type" => "application/json",
            ])->post(
                url: url(config('services.arkesel.sms_url')),
                data: array_filter([
                    'message' => $message,
                    'recipients' => $recipients,
                    'sender' => $sender,
                    'sandbox' => config('services.arkesel.sms_sandbox'),
                    'scheduled_date' => $scheduled_date,
                ])
            );

            if ($response->ok()) {
                // event(); sms sent
                return response('Successful, SMS delivered', 200);
            }
        } catch (\Throwable $th) {
            Log::error("Something went wrong", ['error' => $th->getMessage()]);
            return response(
                [
                    'message' => 'Failed to send SMS',
                    'error' => $th->getMessage(),
                    // 'content' => $response->body(),
                    // 'status_code' => $response->status(),
                ],
                400,
            );
        }
    }
}
