<?php
// Jivoo HTTP 
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http\Message;

/**
 * A stream wrapper for strings.
 */
class StringStream implements \Psr\Http\Message\StreamInterface
{
    
    /**
     * @var string
     */
    private $string;
    
    /**
     * @var int
     */
    private $length;
    
    /**
     * @var bool
     */
    private $mutable;
    
    /**
     * @var int
     */
    private $offset = 0;
    
    /**
     * Construct string stream.
     * @param string $string The string.
     * @param bool $mutable Whether the stream should be writable.
     */
    public function __construct($string, $mutable = true)
    {
        $this->string = $string;
        $this->length = strlen($string);
        $this->mutable = $mutable;
    }
    
    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $this->offset = $this->length;
        return $this->string;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        $this->detach();
    }

    /**
     * {@inheritdoc}
     */
    public function detach()
    {
        unset($this->string);
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function eof()
    {
        return $this->offset >= $this->length;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents()
    {
        return $this->read($this->length - $this->offset);
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($key = null)
    {
        if (isset($key)) {
            return null;
        }
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        return $this->length;
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable()
    {
        return isset($this->string);
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable()
    {
        return isset($this->string);
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable()
    {
        return isset($this->string) and $this->mutable;
    }

    /**
     * {@inheritdoc}
     */
    public function read($length)
    {
        $data = substr($this->string, $this->offset, $length);
        $this->offset += strlen($data);
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->offset = 0;
    }

    /**
     * {@inheritdoc}
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        switch ($whence) {
            case SEEK_SET:
                $this->offset = $offset;
                break;
            case SEEK_CUR:
                $this->offset += $offset;
                break;
            case SEEK_END:
                $this->offset = $this->length + $offset;
                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function tell()
    {
        return $this->offset;
    }

    /**
     * {@inheritdoc}
     */
    public function write($string)
    {
        $length = strlen($string);
        $this->string = substr_replace($this->string, $string, $this->offset, $length);
        $this->offset += $length;
        $this->length = strlen($this->string);
        return $length;
    }
}
