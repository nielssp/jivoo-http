<?php
// Jivoo HTTP 
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http\Message;

/**
 * Implements {@see \Psr\Http\Message\RequestInterface}.
 */
trait RequestTrait
{
    protected $method = 'GET';
    
    protected $requestTarget = '';
    
    protected $uri;
    
    protected abstract function hasHeader($name);
    
    protected abstract function setHeader($name, $value);
    
    public function getMethod()
    {
        return $this->method;
    }

    public function getRequestTarget()
    {
        if ($this->requestTarget != '') {
            return $this->requestTarget;
        }
        $target = $this->uri->getPath();
        if ($target == '') {
            $target = '/';
        }
        $query = $this->uri->getQuery();
        if ($query != '') {
            $target .= '?' . $query;
        }
        return $target;
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
        if (! $preserveHost or ! $this->hasHeader('Host')) {
            $host = $uri->getHost();
            if ($host != '') {
                $request->setHeader('Host', $host);
            }
        }
        return $request;
    }
}
