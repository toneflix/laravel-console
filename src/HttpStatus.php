<?php

namespace ToneflixCode\LaravelConsole;

/**
 * HTTP Status codes
 */
class HttpStatus
{
    const OK = '200';                   // OK

    const CREATED = '201';              // Created

    const ACCEPTED = '202';             // Created

    const NO_CONTENT = '204';           // No Content

    const BAD_REQUEST = '400';          // Bad Request

    const UNAUTHORIZED = '401';         // Unauthenticated

    const NOT_FOUND = '404';            // Not Found

    const FORBIDDEN = '403';            // Access Denied

    const METHOD_NOT_ALLOWED = '405';   // Method Not Allowed

    const UNPROCESSABLE_ENTITY = '422'; // Unprocessable Entity

    const TOO_MANY_REQUESTS = '429';    // Too Many Requests

    const SERVER_ERROR = '500';         // Internal Server Error

    public static function message(string $code)
    {
        return (new self)->getMessage($code);
    }

    public function getMessage($code = self::OK)
    {
        switch ($code) {
            case self::OK:
                return 'OK';
                break;

            case self::CREATED:
                return 'Created';
                break;

            case self::ACCEPTED:
                return 'Accepted';
                break;

            case self::NO_CONTENT:
                return 'No Content.';
                break;

            case self::BAD_REQUEST:
                return 'Bad Request.';
                break;

            case self::UNAUTHORIZED:
                return 'Unauthenticated: Please login to continue.';
                break;

            case self::FORBIDDEN:
                return 'Access Denied';
                break;

            case self::METHOD_NOT_ALLOWED:
                return 'Method Not Allowed.';
                break;

            case self::UNPROCESSABLE_ENTITY:
                return 'Unprocessable Entity.';
                break;

            case self::TOO_MANY_REQUESTS:
                return 'Too Many Requests.';
                break;

            case self::SERVER_ERROR:
                return 'Internal Server Error.';
                break;

            case self::NOT_FOUND:
                return 'Not Found.';
                break;

            default:
                return 'Not found.';
                break;
        }
    }
}