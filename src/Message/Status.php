<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http\Message;

/**
 * HTTP status codes
 */
class Status
{

    const OK = 200;
    const CREATED = 201;
    const ACCEPTED = 202;
    const NO_CONTENT = 204;
    const MULTIPLE_CHOICES = 300;
    const MOVED_PERMANENTLY = 301;
    const FOUND = 302;
    const SEE_OTHER = 303;
    const NOT_MODIFIED = 304;
    const USE_PROXY = 305;
    const SWITCH_PROXY = 306;
    const TEMPORARY_REDIRECT = 307;
    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const PAYMENT_REQUIRED = 402;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const METHOD_NOT_ALLOWED = 405;
    const NOT_ACCEPTABLE = 406;
    const INTERNAL_SERVER_ERROR = 500;
    const NOT_IMPLEMENTED = 501;
    const SERVICE_UNAVAILABLE = 503;

    private function __construct()
    {
    }
  
  
    /**
     * Returns the phrase for an HTTP status code.
     *
     * @param int $status HTTP status code.
     * @return string Phrase.
     */
    public static function phrase($status)
    {
        switch ($status) {
            case 200:
                return 'OK';
            case 201:
                return 'Created';
            case 202:
                return 'Accepted';
            case 204:
                return 'No Content';
            case 300:
                return 'Multiple Choices';
            case 301:
                return 'Moved Permanently';
            case 302:
                return 'Found';
            case 303:
                return 'See Other';
            case 304:
                return 'Not Modified';
            case 305:
                return 'Use Proxy';
            case 306:
                return 'Switch Proxy';
            case 307:
                return 'Temporary Redirect';
            case 400:
                return 'Bad Request';
            case 401:
                return 'Unauthorized';
            case 402:
                return 'Payment Required';
            case 403:
                return 'Forbidden';
            case 404:
                return 'Not Found';
            case 405:
                return 'Method Not Allowed';
            case 406:
                return 'Not Acceptable';
            case 500:
                return 'Internal Server Error';
            case 501:
                return 'Not Implemented';
            case 503:
                return 'Service Unavailable';
        }
        return false;
    }
}
