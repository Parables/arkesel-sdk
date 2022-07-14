<?php

/*
 * @author Parables Boltnoel <parables95@gmail.com>
 * @package arkesel-sdk
 *  @version 1.0.0
 */

namespace Parables\ArkeselSdk\Exceptions;

use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;

class ArkeselSmsResponseOrException extends \Exception
{
    /**
     * handle Sms response errors from Arkesel.
     * Takes in a Illuminate\Http\Client\Response
     * and returns array
     * or throws an Exception.
     *
     * @param  \Illuminate\Http\Client\Response  $response
     * @return array
     *
     * @throws Exception
     */
    public static function handleResponse(Response $response): array
    {
        return self::hasSuccessfulResponse(response: $response);
    }

    /**
     * determines if the response was successful and returns a JsonResponse.
     * Otherwise throws an Exception.
     *
     * @param  Response  $response
     * @return array
     *
     * @throws Exception
     */
    protected static function hasSuccessfulResponse(Response $response): array
    {
        if ( // the response has any of these
            $response->json('status') === 'success' ||
            $response->json('code') === 'OK' ||
            ! is_null($response->json('balance')) ||
            ! is_null($response->json('user')) ||
            ! is_null($response->json('country')) ||
            ! is_null($response->json('sms_balance')) ||
            ! is_null($response->json('main_balance'))
        ) {
            return Arr::wrap($response->json());
        }

        // else ...
        throw self::hasExceptionResponse(response: $response);
    }

    /**
     * parses the response and returns the exception to be thrown with the appropriate message.
     *
     * @param  Response  $response
     * @return Exception
     */
    protected static function hasExceptionResponse(Response $response): Exception
    {
        switch ($response->json('code') ?? $response->json('status')) {
            case '100':
            case '101':
            case '102':
            case '103':
            case '104':
            case '105':
            case '106':
            case '109':
            case '111':
            case '401':
            case '402':
            case '403':
            case '422':
            case '500':
            case 'error':
                return new static($response->json('message'));
                break;

            default:
                dump($response->body());
                break;
        }
    }
}
