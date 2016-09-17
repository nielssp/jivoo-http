<?php
// Jivoo HTTP
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http;

/**
 * Thrown to indicate a client error.
 */
class ClientException extends \RuntimeException implements \Jivoo\Exception
{
    
    /**
     * @var int|null Optional HTTP status code, see {@see Message\Status}.
     */
    public $statusCode = null;
    
    /**
     * @var string Optional reason phrase.
     */
    public $reasonPhrase = '';
    
    public function __construct($message = "", $statusCode = null, $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->statusCode = $statusCode;
        if (isset($this->statusCode)) {
            $this->reasonPhrase = Message\Status::phrase($this->statusCode);
        }
    }
}
