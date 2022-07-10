<?php

use Parables\ArkeselSdk\BulkSms\ArkeselMessage;

test('receives config array', function () {
    $message = new ArkeselMessage(message: 'Hello World', recipients: ["233242158675"]);
    arkeselSendSms(message: $message);
});
