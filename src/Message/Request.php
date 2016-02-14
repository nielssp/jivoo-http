<?php
// Jivoo HTTP 
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http\Message;

/**
 * Description of NewRequest
 */
class Request extends Message implements \Psr\Http\Message\RequestInterface
{
    private $method = 'GET';
    
    private $requestTarget = '/';
    
    private $uri = null;
    
    public function getMethod()
    {
        return $this->method;
    }

    public function getRequestTarget()
    {
        return $this->requestTarget;
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function withMethod($method)
    {
        $request = clone $this;
        $request->method = $method;
        return $request;
    }

    public function withRequestTarget($requestTarget)
    {
        $request = clone $this;
        $request->requestTarget = $requestTarget;
        return $request;
    }

    public function withUri(\Psr\Http\Message\UriInterface $uri, $preserveHost = false)
    {
        $request = clone $this;
        $request->uri = $uri;
        return $request;
    }
}
