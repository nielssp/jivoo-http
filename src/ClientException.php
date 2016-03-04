<?php
// Jivoo HTTP
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http;

/**
 * Thrown to indicate a client error.
 */
class ClientException
{
    
    /**
     * @var int|null Optional HTTP status code, see {@see Message\Status}.
     */
    public $statusCode = null;
    
    /**
     * @var string Optional reason phrase.
     */
    public $reasonPhrase = '';
}
