<?php

/*
 * @author Parables Boltnoel <parables95@gmail.com>
 * @package arkesel-sdk
 *  @version 1.0.0
 */

namespace Parables\ArkeselSdk\Exceptions;

use Illuminate\Http\Client\Response;

class ArkeselSmsResponseException extends \Exception
{
    /**
     * handle Sms response errors from Arkesel
     *
     * @param \Illuminate\Http\Client\Response $response
     */
    public static function handleResponse(Response $response)
    {
        if ($response->status() === 100) {
            $errorMsg = 'Bad gateway request';
        } elseif ($response->status() === 101) {
            $errorMsg = 'Wrong action';
        } elseif ($response->status() === 102) {
            $errorMsg = 'Authentication failed';
        } elseif ($response->status() === 103) {
            $errorMsg = 'Invalid phone number';
        } elseif ($response->status() === 104) {
            $errorMsg = 'Phone coverage not active';
        } elseif ($response->status() === 105) {
            $errorMsg = 'Insufficient balance';
        } elseif ($response->status() === 106) {
            $errorMsg = 'Invalid Sender ID';
        } elseif ($response->status() === 109) {
            $errorMsg = 'Invalid Schedule Time';
        } elseif ($response->status() === 111) {
            $errorMsg = 'SMS contains spam word. Wait for approval';
        } elseif ($response->status() === 401) {
            $errorMsg = 'Authentication failed';
        } elseif ($response->status() === 402) {
            $errorMsg = 'Insufficient balance';
        } elseif ($response->status() === 403) {
            $errorMsg = 'Inactive Gateway';
        } elseif ($response->status() === 422) {
            $errorMsg = 'Validation Errors';
        } elseif ($response->status() === 500) {
            $errorMsg = 'Internal error';
        } else {
            $errorMsg = 'Unknown Error';
        }

        return new static($errorMsg . ": " . $response->json('message'));
    }
}
