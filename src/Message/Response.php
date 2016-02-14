<?php
// Jivoo HTTP 
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http\Message;

/**
 * Description of NewRequest
 */
class Response extends Message implements \Psr\Http\Message\ResponseInterface
{
    private $status = 200;
    
    private $reason = 'OK';
    
    public function __construct($status, \Psr\Http\Message\StreamInterface $body)
    {
        parent::__construct($body);
        $this->status = $status;
        $this->reason = Status::phrase($status);
    }
    
    public function getReasonPhrase()
    {
        return $this->reason;
    }

    public function getStatusCode()
    {
        return $this->status;
    }

    public function withStatus($code, $reasonPhrase = '')
    {
        $response = clone $this;
        $response->status = $code;
        if ($reasonPhrase == '') {
            $reasonPhrase = Status::phrase($code);
        }
        $response->reason = $reasonPhrase;
        return $response;
    }

}
