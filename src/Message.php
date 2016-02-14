<?php
// Jivoo_HTTP 
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http;

/**
 * Description of Message
 */
abstract class Message implements \Psr\Http\Message\MessageInterface
{
    private $headers = [];
    
    private $headerKeys = [];
    
    private $protocolVersion = '1.1';
    
    private $body = null;
    
    public function getBody()
    {
        if (! isset($this->body)) {
            // TODO
        }
        return $this->body;
    }

    public function getHeader($name)
    {
        $name = strtolower($name);
        if (! isset($this->headerKeys[$name])) {
            return [];
        }
        return $this->headers[$this->headerKeys[$name]];
    }

    public function getHeaderLine($name)
    {
        return implode(',', $this->getHeader($name));
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    public function hasHeader($name)
    {
        return isset($this->headersKeys[strtolower($name)]);
    }

    public function withAddedHeader($name, $value)
    {
        $message = clone $this;
        if (!isset($message->headers[$name])) {
            $message->headers[$name] = [];
            $message->headerKeys[strtolower($name)] = $name;
        }
        $message->headers[$name][] = $value;
        return $message;
    }

    public function withBody(\Psr\Http\Message\StreamInterface $body)
    {
        $message = clone $this;
        $message->body = $body;
        return $message;
    }

    public function withHeader($name, $value)
    {
        if (! is_array($value)) {
            $value = [$value];
        }
        $message = clone $this;
        $message->headers[$name] = $value;
        $message->headerKeys[strtolower($name)] = $name;
        return $message;
    }

    public function withProtocolVersion($version)
    {
        $message = clone $this;
        $message->protocolVersion = $version;
        return $message;
    }

    public function withoutHeader($name)
    {
        $name = strtolower($name);
        if (! isset($this->headerKeys[$name])) {
            return $this;
        }
        $message = clone $this;
        unset($message->headers[$message->headerKeys[$name]]);
        unset($message->headerKeys[$name]);
        return $message;
    }

}
