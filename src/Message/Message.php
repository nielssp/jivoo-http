<?php
// Jivoo HTTP 
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http\Message;

/**
 * Description of Message
 */
class Message implements \Psr\Http\Message\MessageInterface
{
    
    /**
     * @var string[][]
     */
    private $headers = [];
    
    /**
     * @var string[]
     */
    private $headerKeys = [];
    
    /**
     * @var type
     */
    private $protocolVersion = '1.1';
    
    /**
     * @var \Psr\Http\Message\StreamInterface
     */
    private $body;
    
    public function __construct(\Psr\Http\Message\StreamInterface $body)
    {
        $this->body = $body;
    }
    
    public function getBody()
    {
        return $this->body;
    }
    
    protected function setHeader($name, $value)
    {
        if (! is_array($value)) {
            $value = [$value];
        }
        $message->headers[$name] = $value;
        $message->headerKeys[strtolower($name)] = $name;
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
        $message = clone $this;
        $message->setHeader($name, $value);
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
