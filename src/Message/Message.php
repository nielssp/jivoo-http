<?php
// Jivoo HTTP 
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http\Message;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

/**
 * An HTTP message.
 */
class Message implements MessageInterface
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
    protected $protocolVersion = '1.1';
    
    /**
     * @var StreamInterface
     */
    private $body;
    
    /**
     * Construct message.
     *
     * @param StreamInterface $body Message body.
     */
    public function __construct(StreamInterface $body)
    {
        $this->body = $body;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getBody()
    {
        return $this->body;
    }
    
    /**
     * Set the value of a header.
     *
     * @param string $name Header name.
     * @param string|string[] $value Header value.
     */
    protected function setHeader($name, $value)
    {
        if (! is_array($value)) {
            $value = [$value];
        }
        $this->headers[$name] = $value;
        $this->headerKeys[strtolower($name)] = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeader($name)
    {
        $name = strtolower($name);
        if (! isset($this->headerKeys[$name])) {
            return [];
        }
        return $this->headers[$this->headerKeys[$name]];
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaderLine($name)
    {
        return implode(', ', $this->getHeader($name));
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * {@inheritdoc}
     */
    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    /**
     * {@inheritdoc}
     */
    public function hasHeader($name)
    {
        return isset($this->headerKeys[strtolower($name)]);
    }

    /**
     * {@inheritdoc}
     */
    public function withAddedHeader($name, $value)
    {
        $message = clone $this;
        $key = strtolower($name);
        if (! isset($message->headerKeys[$key])) {
            $message->headers[$name] = [];
            $message->headerKeys[$key] = $name;
        }
        $message->headers[$message->headerKeys[$key]][] = $value;
        return $message;
    }

    /**
     * {@inheritdoc}
     */
    public function withBody(StreamInterface $body)
    {
        $message = clone $this;
        $message->body = $body;
        return $message;
    }

    /**
     * {@inheritdoc}
     */
    public function withHeader($name, $value)
    {
        $message = clone $this;
        $message->setHeader($name, $value);
        return $message;
    }

    /**
     * {@inheritdoc}
     */
    public function withProtocolVersion($version)
    {
        $message = clone $this;
        $message->protocolVersion = $version;
        return $message;
    }

    /**
     * {@inheritdoc}
     */
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
    
    /**
     * Format a date for use in HTTP headers.
     * @param int $timestamp UNIX timestamp.
     * @return string Formatted date.
     */
    public static function date($timestamp)
    {
        return gmdate('D, d M Y H:i:s', $timestamp) . ' GMT';
    }
}
